<?php
namespace XCalendar;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Event.php';
require_once __DIR__ . '/EventData.php';

use Sabre\CalDAV;
use Sabre\DAV\Sharing\Plugin as SharingPlugin;
use Sabre\DAV\PropPatch;
use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV\Backend\SyncSupport;
use Sabre\CalDAV\Backend\SchedulingSupport;
use XFramework\DatabaseGeneric;

class CalDavCalendar extends AbstractBackend implements SyncSupport, SchedulingSupport
{
    private $calendarsForUser = null;
    private DatabaseGeneric $db;
    private $sync;
    private $event;
    private $syncData;

    function __construct($sync, $event)
    {
        $this->db = xdb();
        $this->sync = $sync;
        $this->event = $event;
    }

    public function setSyncData($syncData): void
    {
        $this->syncData = $syncData;
    }

    /**
     * Not available via caldav.
     *
     * @param string $principalUri
     * @param string $calendarUri
     * @param array $properties
     * @return void
     */
    public function createCalendar($principalUri, $calendarUri, array $properties): void
    {
        CalDavLog::log();
    }

    /**
     * Not available via caldav.
     *
     * @param mixed $calendarId
     * @param PropPatch $propPatch
     */
    public function updateCalendar($calendarId, PropPatch $propPatch): void
    {
        CalDavLog::log();
    }

    /**
     * Not available via caldav.
     *
     * @param mixed $calendarId
     */
    public function deleteCalendar($calendarId): void
    {
        CalDavLog::log();
    }

    /**
     * Returns a list of calendars for a principal.
     *
     * Every project is an array with the following keys:
     *  * id, a unique id that will be used by other functions to modify the
     *    calendar. This can be the same as the uri or a database key.
     *  * uri. This is just the 'base uri' or 'filename' of the calendar.
     *  * principaluri. The owner of the calendar. Almost always the same as
     *    principalUri passed to this method.
     *
     * Furthermore, it can contain webdav properties in clark notation. A very
     * common one is '{DAV:}displayname'.
     *
     * Many clients also require:
     * {urn:ietf:params:xml:ns:caldav}supported-calendar-component-set
     * For this property, you can just return an instance of
     * Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet.
     *
     * If you return {http://sabredav.org/ns}read-only and set the value to 1,
     * ACL will automatically be put in read-only mode.
     *
     * @param string $principalUri
     *
     * @return array
     */
    function getCalendarsForUser($principalUri): array
    {
        // if already got it during this call, return it
        if (is_array($this->calendarsForUser)) {
            CalDavLog::log("[CACHED] $principalUri: " . json_encode($this->calendarsForUser));
            return $this->calendarsForUser;
        }

        if (!($sync = $this->sync->getByUsername(basename($principalUri)))) {
            CalDavLog::log("ERROR: SYNC DOES NOT EXIST [$principalUri]");
            return [];
        }

        if (!($calendar = $this->db->row("xcalendar_calendars", ['id' => $sync['calendar_id'], "removed_at" => null]))) {
            CalDavLog::log("ERROR: CALENDAR FOR SYNC DOES NOT EXIST [$principalUri]");
            return [];
        }

        $this->calendarsForUser = [[
            'id'           => $sync['calendar_id'],
            'uri'          => $sync['url'],
            'principaluri' => $principalUri,
            'share-access' => $sync['read_only'] ? SharingPlugin::ACCESS_READ : SharingPlugin::ACCESS_READWRITE,
            'share-resource-uri' => '/ns/share/' . $sync['calendar_id'],
            '{DAV:}displayname' => $sync['name'],
            '{urn:ietf:params:xml:ns:caldav}calendar-description' => empty($sync['description']) ? "" : $sync['description'],
            '{http://apple.com/ns/ical/}calendar-order' => "0",
            '{http://apple.com/ns/ical/}calendar-color' => $calendar['bg_color'],
            '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => null,//"BEGIN:VCALENDAR\r\nBEGIN:VTIMEZONE\r\nTZID:Europe/Berlin\r\nEND:VTIMEZONE\r\nEND:VCALENDAR",
            '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT']),
            '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp('opaque'),

            // not using sync tokens for now
            // '{http://calendarserver.org/ns/}getctag' => 'http://sabre.io/ns/sync/3',
            // '{http://sabredav.org/ns}sync-token' => '3',
        ]];

        CalDavLog::log($principalUri . ': ' . json_encode($this->calendarsForUser));

        return $this->calendarsForUser;
    }

    /**
     * Returns all calendar objects within a calendar.
     *
     * Every item contains an array with the following keys:
     *   * calendardata - The iCalendar-compatible calendar data
     *   * uri - a unique key which will be used to construct the uri. This can
     *     be any arbitrary string, but making sure it ends with '.ics' is a
     *     good idea. This is only the basename, or filename, not the full
     *     path.
     *   * lastmodified - a timestamp of the last modification time
     *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
     *   '  "abcdef"')
     *   * size - The size of the calendar objects, in bytes.
     *   * component - optional, a string containing the type of object, such
     *     as 'vevent' or 'vtodo'. If specified, this will be used to populate
     *     the Content-Type header.
     *
     * Note that the etag is optional, but it's highly encouraged to return for
     * speed reasons.
     *
     * The calendardata is also optional. If it's not returned
     * 'getCalendarObject' will be called later, which *is* expected to return
     * calendardata.
     *
     * If neither etag or size are specified, the calendardata will be
     * used/fetched to determine these numbers. If both are specified the
     * amount of times this is needed is reduced by a great degree.
     *
     * @param mixed $calendarId
     *
     * @return array
     */
    public function getCalendarObjects($calendarId): array
    {
        $calendarId = is_array($calendarId) ? $calendarId[0] : $calendarId;

        if (!$this->calendarExists($calendarId)) {
            CalDavLog::log("ERROR: CALENDAR DOES NOT EXIST [$calendarId]");
            return [];
        }

        $rows = $this->db->all(
            "SELECT * FROM {xcalendar_events} WHERE calendar_id = ? AND removed_at IS NULL",
            $calendarId
        );
        $result = [];

        foreach ($rows as $row) {
            if ($object = $this->eventRowToCalendarObject($row, false)) {
                $result[] = $object;
            }
        }

        CalDavLog::log("$calendarId / " . json_encode($result));

        return $result;
    }

    /**
     * Returns information from a single calendar object, based on it's object
     * uri.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * The returned array must have the same keys as getCalendarObjects. The
     * 'calendardata' object is required here though, while it's not required
     * for getCalendarObjects.
     *
     * This method must return null if the object did not exist.
     *
     * @param mixed  $calendarId
     * @param string $objectUri - this is event url
     *
     * @return array|null
     */
    function getCalendarObject($calendarId, $objectUri): ?array
    {
        $calendarId = is_array($calendarId) ? $calendarId[0] : $calendarId;

        if (!$this->calendarExists($calendarId)) {
            CalDavLog::log("ERROR: CALENDAR DOES NOT EXIST [$calendarId]");
            return null;
        }

        if (!($row = $this->db->row("xcalendar_events", ["calendar_id" => $calendarId, "uid" => $objectUri, "removed_at" => null]))) {
            return null;
        }

        CalDavLog::log("$calendarId / $objectUri");

        return $this->eventRowToCalendarObject($row);
    }

    /**
     * Returns a list of calendar objects.
     *
     * This method should work identical to getCalendarObject, but instead
     * return all the calendar objects in the list as an array.
     *
     * If the backend supports this, it may allow for some speed-ups.
     *
     * @param mixed $calendarId
     * @param array $uris
     * @return array
     */
    public function getMultipleCalendarObjects($calendarId, array $uris): array
    {
        $calendarId = is_array($calendarId) ? $calendarId[0] : $calendarId;

        if (!$this->calendarExists($calendarId)) {
            CalDavLog::log("ERROR: CALENDAR DOES NOT EXIST [$calendarId]");
            return [];
        }

        $param = [$calendarId];
        $in = [];
        $result = [];

        foreach ($uris as $url) {
            $param[] = $url;
            $in[] = "?";
        }

        $rows = $this->db->all(
            "SELECT * FROM {xcalendar_events} WHERE calendar_id = ? AND uid IN (" . implode(",", $in) . ") AND removed_at IS NULL",
            $param
        );

        foreach ($rows as $row) {
            if ($object = $this->eventRowToCalendarObject($row)) {
                $result[] = $object;
            }
        }

        return $result;
    }

    /**
     * Creates a new calendar object.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed  $calendarId
     * @param string $objectUri
     * @param string $calendarData
     *
     * @return string|null
     */
    public function createCalendarObject($calendarId, $objectUri, $calendarData): ?string
    {
        CalDavLog::log("$objectUri / $calendarId / $calendarData");

        $this->verifyPermission();

        if (!($calendar = $this->db->row("xcalendar_calendars", ["id" => $calendarId, "removed_at" => null]))) {
            CalDavLog::log("ERROR: CALENDAR DOES NOT EXIST [$calendarId / $objectUri]");
            return null;
        }

        $array = $this->event->vEventToDataArray($calendarData);

        if (is_array($array) && !empty($array[0])) {
            $eventData = new EventData();

            foreach ($array[0] as $key => $val) {
                $eventData->setValue($key, $val);
            }

            $eventData->setValue("calendar_id", $calendarId);
            $eventData->setValue("user_id", $calendar['user_id']);
            $eventData->setValue("uid", $objectUri);
            try {
                $eventData->save();
            } catch (\Exception) {
                CalDavLog::log("ERROR: CANNOT SAVE EVENT [$calendarId / $objectUri]");
            }
        }

        // $objectUri comes in with .ics while UID inside the vevent text ($calendarData) comes in without .ics.
        // We need to store them both as they are: $objectUri in the uid db field (used by the clients to search/
        // update/delete events) and UID in the vevent_uid db field so we can properly create the vevent db
        // field when we save the event (we're not saving $calendarData exactly as it comes in but we're
        // re-writing it when saving the record.) All clients except for iOS work fine if UID inside vevent
        // has .ics, but iOS expects it to be exactly as it sent it inside $calendarData (without .ics)
        // otherwise the uids don't match and iOS creates double events. This is why we store $objectUri and UID
        // in separate db fields. If the event gets edited later in RC or via caldav, the resulting vevent
        // will be written correctly because it uses UID stored in vevent_uid.

        return null;
    }

    /**
     * Updates an existing calendarobject, based on it's uri.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed  $calendarId
     * @param string $objectUri
     * @param string $calendarData
     *
     * @return string|null
     */
    public function updateCalendarObject($calendarId, $objectUri, $calendarData): ?string
    {
        CalDavLog::log("$objectUri / $calendarId / $calendarData");

        $calendarId = is_array($calendarId) ? $calendarId[0] : $calendarId;
        $this->verifyPermission();
        $eventData = new EventData();

        if (!$eventData->loadFromDb(["uid" => $objectUri, "calendar_id" => $calendarId])) {
            CalDavLog::log("ERROR: EVENT DOES NOT EXIST [$calendarId / $objectUri]");
            return null;
        }

        $array = $this->event->vEventToDataArray($calendarData);

        if (is_array($array) && !empty($array[0])) {
            foreach ($array[0] as $key => $val) {
                $eventData->setValue($key, $val);
            }

            $eventData->setValue("uid", $objectUri);

            try {
                $eventData->save();
                $this->addChange($calendarId, $objectUri, 2);
            } catch (\Exception) {
                CalDavLog::log("ERROR: CANNOT SAVE EVENT [$calendarId / $objectUri]");
            }
        }

        return null;
    }

    /**
     * Deletes an existing calendar object.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * @param mixed  $calendarId
     * @param string $objectUri
     */
    public function deleteCalendarObject($calendarId, $objectUri): void
    {
        $calendarId = is_array($calendarId) ? $calendarId[0] : $calendarId;

        $this->verifyPermission();

        if (!($event = $this->db->row("xcalendar_events", ["calendar_id" => $calendarId, "uid" => $objectUri]))) {
            CalDavLog::log("ERROR: EVENT DOES NOT EXIST [$calendarId / $objectUri]");
            return;
        }

        CalDavLog::log("$calendarId / $objectUri");

        if (!$this->event->removeEvent($event['id'])) {
            CalDavLog::log("ERROR: CANNOT DELETE EVENT [$calendarId / $objectUri]");
            return;
        }

        $this->addChange($calendarId, $objectUri, 3);
    }

    /**
     * Performs a calendar-query on the contents of this calendar.
     *
     * The calendar-query is defined in RFC4791 : CalDAV. Using the
     * calendar-query it is possible for a client to request a specific set of
     * object, based on contents of iCalendar properties, date-ranges and
     * iCalendar component types (VTODO, VEVENT).
     *
     * This method should just return a list of (relative) urls that match this
     * query.
     *
     * The list of filters are specified as an array. The exact array is
     * documented by \Sabre\CalDAV\CalendarQueryParser.
     *
     * Note that it is extremely likely that getCalendarObject for every path
     * returned from this method will be called almost immediately after. You
     * may want to anticipate this to speed up these requests.
     *
     * This method provides a default implementation, which parses *all* the
     * iCalendar objects in the specified calendar.
     *
     * This default may well be good enough for personal use, and calendars
     * that aren't very large. But if you anticipate high usage, big calendars
     * or high loads, you are strongly advised to optimize certain paths.
     *
     * The best way to do so is override this method and to optimize
     * specifically for 'common filters'.
     *
     * Requests that are extremely common are:
     *   * requests for just VEVENTS
     *   * requests for just VTODO
     *   * requests with a time-range-filter on a VEVENT.
     *
     * ..and combinations of these requests. It may not be worth it to try to
     * handle every possible situation and just rely on the (relatively
     * easy to use) CalendarQueryValidator to handle the rest.
     *
     * Note that especially time-range-filters may be difficult to parse. A
     * time-range filter specified on a VEVENT must for instance also handle
     * recurrence rules correctly.
     * A good example of how to interpret all these filters can also simply
     * be found in \Sabre\CalDAV\CalendarQueryFilter. This class is as correct
     * as possible, so it gives you a good idea on what type of stuff you need
     * to think of.
     *
     * This specific implementation (for the PDO) backend optimizes filters on
     * specific components, and VEVENT time-ranges.
     *
     * @param mixed $calendarId
     * @param array $filters
     * @return array
     */
    public function calendarQuery($calendarId, array $filters): array
    {
        $calendarId = is_array($calendarId) ? $calendarId[0] : $calendarId;

        if (!$this->calendarExists($calendarId)) {
            CalDavLog::log("ERROR: CALENDAR DOES NOT EXIST [$calendarId]");
            return [];
        }

        CalDavLog::log($calendarId);

        $requirePostFilter = true;
        $timeRange = null;

        // if no filters were specified, we don't need to filter after a query
        if (!$filters['prop-filters'] && !$filters['comp-filters']) {
            $requirePostFilter = false;
        }

        // Figuring out if there's a component filter
        if (count($filters['comp-filters']) > 0 && !$filters['comp-filters'][0]['is-not-defined']) {
            $componentType = $filters['comp-filters'][0]['name'];

            // Checking if we need post-filters
            $has_time_range = array_key_exists('time-range', $filters['comp-filters'][0]) && $filters['comp-filters'][0]['time-range'];
            if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$has_time_range && !$filters['comp-filters'][0]['prop-filters']) {
                $requirePostFilter = false;
            }
            // There was a time-range filter
            if ('VEVENT' == $componentType && $has_time_range) {
                $timeRange = $filters['comp-filters'][0]['time-range'];

                // If start time OR the end time is not specified, we can do a
                // 100% accurate mysql query.
                if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$filters['comp-filters'][0]['prop-filters'] && $timeRange) {
                    if ((array_key_exists('start', $timeRange) && !$timeRange['start']) || (array_key_exists('end', $timeRange) && !$timeRange['end'])) {
                        $requirePostFilter = false;
                    }
                }
            }
        }

        $select = ($requirePostFilter ? "id, uid, vevent" : "uid") . ", timezone_start, timezone_end";
        $where = "calendar_id = ? AND removed_at IS NULL";
        $values = [$calendarId];

        if ($timeRange && $timeRange['start']) {
            $where .= " AND start >= ?";
            $values[] = $timeRange['start']->format("Y-m-d H:i:s");
        }

        if ($timeRange && $timeRange['end']) {
            $where .= " AND " . $this->db->col("end") . " <= ?";
            $values[] = $timeRange['end']->format("Y-m-d H:i:s");
        }

        $rows = $this->db->all("SELECT $select FROM {xcalendar_events} WHERE $where", $values);

        // add repeating events to the array
        if ($timeRange && $timeRange['start']) {
            $repeat = $this->db->all(
                "SELECT $select FROM {xcalendar_events} " .
                "WHERE calendar_id = ? AND removed_at IS NULL AND repeat_rule != '' AND repeat_end >= ?",
                [$calendarId, $timeRange['start']->format("Y-m-d H:i:s")]
            );

            if ($repeat) {
                foreach ($repeat as $event) {
                    $rows[] = $event;
                }
            }
        }

        $result = [];

        if ($rows) {
            foreach ($rows as $row) {
                if ($requirePostFilter) {
                    // if vevent doesn't exist (event created with an ancient version) create and save it in the db
                    if (empty($row['vevent'])) {
                        $eventData = new EventData();
                        $eventData->loadFromDb($row['id']);

                        if (!($row['vevent'] = $eventData->ensureVEvent())) {
                            continue;
                        }
                    }

                    $timezoneStart = "";
                    $timezoneEnd = "";
                    $this->event->ensureTimezones($row, $timezoneStart, $timezoneEnd);
                    $vtimezone = Timezone::getVTimezone($timezoneStart);

                    if ($timezoneStart != $timezoneEnd) {
                        $vtimezone .= Timezone::getVTimezone($timezoneEnd);
                    }

                    if (!$this->validateFilterForObject(["calendardata" => Event::wrapInVCalendar($row['vevent'], $vtimezone)], $filters)) {
                        continue;
                    }
                }
                $result[] = $row['uid'];
            }
        }

        return $result;
    }

    /**
     * Searches through all of a users calendars and calendar objects to find
     * an object with a specific UID.
     *
     * This method should return the path to this object, relative to the
     * calendar home, so this path usually only contains two parts:
     *
     * calendarpath/objectpath.ics
     *
     * If the uid is not found, return null.
     *
     * This method should only consider * objects that the principal owns, so
     * any calendars owned by other principals that also appear in this
     * collection should be ignored.
     *
     * @param string $principalUri
     * @param string $uid
     *
     * @return string|null
     */
    public function getCalendarObjectByUID($principalUri, $uid): ?string
    {
        if (!($sync = $this->db->fetch(
            "SELECT calendar_id, url FROM {xcalendar_synced} WHERE username = ? AND read_only = 0",
            basename($principalUri)
        ))) {
            CalDavLog::log("ERROR: SYNC DOES NOT EXIST [$principalUri]");
            return null;
        }

        if (!($event = $this->db->fetch(
            "SELECT uid FROM {xcalendar_events} WHERE vevent_uid = ? AND calendar_id = ? AND removed_at IS NULL",
            [$uid, $sync['calendar_id']]
        ))) {
            CalDavLog::log("ERROR: EVENT DOES NOT EXIST [$principalUri, $uid]");
            return null;
        }

        CalDavLog::log("$principalUri / $uid / {$sync['url']} / {$event['uid']}");
        return $sync['url'] . "/" . $event['uid'];
    }

    /**
     * The getChanges method returns all the changes that have happened, since
     * the specified syncToken in the specified calendar.
     *
     * This function should return an array, such as the following:
     *
     * [
     *   'syncToken' => 'The current synctoken',
     *   'added'   => [
     *      'new.txt',
     *   ],
     *   'modified'   => [
     *      'modified.txt',
     *   ],
     *   'deleted' => [
     *      'foo.php.bak',
     *      'old.txt'
     *   ]
     * ];
     *
     * The returned syncToken property should reflect the *current* syncToken
     * of the calendar, as reported in the {http://sabredav.org/ns}sync-token
     * property this is needed here too, to ensure the operation is atomic.
     *
     * If the $syncToken argument is specified as null, this is an initial
     * sync, and all members should be reported.
     *
     * The modified property is an array of nodenames that have changed since
     * the last token.
     *
     * The deleted property is an array with nodenames, that have been deleted
     * from collection.
     *
     * The $syncLevel argument is basically the 'depth' of the report. If it's
     * 1, you only have to report changes that happened only directly in
     * immediate descendants. If it's 2, it should also include changes from
     * the nodes below the child collections. (grandchildren)
     *
     * The $limit argument allows a client to specify how many results should
     * be returned at most. If the limit is not specified, it should be treated
     * as infinite.
     *
     * If the limit (infinite or not) is higher than you're willing to return,
     * you should throw a Sabre\DAV\Exception\TooMuchMatches() exception.
     *
     * If the syncToken is expired (due to data cleanup) or unknown, you must
     * return null.
     *
     * The limit is 'suggestive'. You are free to ignore it.
     *
     * @param mixed  $calendarId
     * @param string $syncToken
     * @param int    $syncLevel
     * @param int    $limit
     *
     * @return void
     */
    public function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null)
    {
        // calendar changes are not supported at this time
        /* $calendarId = is_array($calendarId) ? $calendarId[0] : $calendarId;

        $currentToken = $this->db->value("sync_token", "xcalendar_changes", ["id" => $calendarId]);

        if (is_null($currentToken)) {
            CalDavLog::log("ERROR: CURRENT TOKEN DOES NOT EXIST [$calendarId / $syncToken]");
            return null;
        }

        $result = [
            'syncToken' => $currentToken,
            'added' => [],
            'modified' => [],
            'deleted' => [],
        ];

        if ($syncToken) {
            // Fetching all changes
            $statement = $this->db->query(
                "SELECT uri, operation FROM {xcalendar_changes} ".
                "WHERE sync_token >= ? AND sync_token < ? AND calendar_id = ? ".
                "ORDER BY sync_token" . ($limit > 0 ? " LIMIT " . (int)$limit : ""),
                [$syncToken, $currentToken, $calendarId]
            );

            $changes = [];

            // This loop ensures that any duplicates are overwritten, only the last change on a node is relevant.
            while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $changes[$row['uri']] = $row['operation'];
            }

            foreach ($changes as $uri => $operation) {
                switch ($operation) {
                    case 1:
                        $result['added'][] = $uri;
                        break;
                    case 2:
                        $result['modified'][] = $uri;
                        break;
                    case 3:
                        $result['deleted'][] = $uri;
                        break;
                }
            }
        } else {
            // No synctoken supplied, this is the initial sync.
            $st = $this->db->query("SELECT uri FROM {xcalendar_changes} WHERE calendar_id = ?", [$calendarId]);
            $result['added'] = $st->fetchAll(\PDO::FETCH_COLUMN);
        }

        CalDavLog::log("$calendarId / $syncToken");

        return $result; */
    }

    /**
     * Adds a change record to the calendarchanges table.
     *
     * @param mixed  $calendarId
     * @param string $objectUri
     * @param int    $operation  1 = add, 2 = modify, 3 = delete
     */
    protected function addChange($calendarId, $objectUri, $operation)
    {
        // calendar changes are not supported at this time
        /* $stmt = $this->pdo->prepare('INSERT INTO '.$this->calendarChangesTableName.' (uri, synctoken, calendarid, operation) SELECT ?, synctoken, ?, ? FROM '.$this->calendarTableName.' WHERE id = ?');
        $stmt->execute([
            $objectUri,
            $calendarId,
            $operation,
            $calendarId,
        ]);
        $stmt = $this->pdo->prepare('UPDATE '.$this->calendarTableName.' SET synctoken = synctoken + 1 WHERE id = ?');
        $stmt->execute([
            $calendarId,
        ]); */
    }

    /**
     * Returns a single scheduling object for the inbox collection.
     *
     * The returned array should contain the following elements:
     *   * uri - A unique basename for the object. This will be used to
     *           construct a full uri.
     *   * calendardata - The iCalendar object
     *   * lastmodified - The last modification date. Can be an int for a unix
     *                    timestamp, or a PHP DateTime object.
     *   * etag - A unique token that must change if the object changed.
     *   * size - The size of the object, in bytes.
     *
     * @param string $principalUri
     * @param string $objectUri
     *
     * @return array|null
     */
    public function getSchedulingObject($principalUri, $objectUri): ?array
    {
        if (!($row = $this->db->row("xcalendar_scheduling_objects", ["principal_uri" => $principalUri, "uri" => $objectUri]))) {
            CalDavLog::log("ERROR: SCHEDULING OBJECT NOT FOUND [$principalUri / $objectUri]");
            return null;
        }

        CalDavLog::log("$principalUri / $objectUri");

        return [
            'uri' => $row['uri'],
            'calendardata' => $row['calendar_data'],
            'lastmodified' => $row['modified_at'],
            'etag' => '"'.$row['etag'].'"',
            'size' => (int)$row['size'],
        ];
    }

    /**
     * Returns all scheduling objects for the inbox collection.
     *
     * These objects should be returned as an array. Every item in the array
     * should follow the same structure as returned from getSchedulingObject.
     *
     * The main difference is that 'calendardata' is optional.
     *
     * @param string $principalUri
     *
     * @return array
     */
    public function getSchedulingObjects($principalUri): array
    {
        $rows = $this->db->all(
            "SELECT id, calendar_data, uri, modified_at, etag, size FROM {xcalendar_scheduling_objects} ".
            "WHERE principal_uri = ?",
            $principalUri
        );

        if (empty($rows)) {
            CalDavLog::log("NO SCHEDULING OBJECTS [$principalUri]");
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'calendardata' => $row['calendar_data'],
                'uri' => $row['uri'],
                'lastmodified' => $row['modified_at'],
                'etag' => '"'.$row['etag'].'"',
                'size' => (int) $row['size'],
            ];
        }

        CalDavLog::log("$principalUri");

        return $result;
    }

    /**
     * Deletes a scheduling object from the inbox collection.
     *
     * @param string $principalUri
     * @param string $objectUri
     */
    public function deleteSchedulingObject($principalUri, $objectUri): void
    {
        CalDavLog::log("$principalUri / $objectUri");

        $this->db->remove("xcalendar_scheduling_objects", ["principal_uri" => $principalUri, "uri" => $objectUri]);
    }

    /**
     * Creates a new scheduling object. This should land in a users' inbox.
     *
     * @param string          $principalUri
     * @param string          $objectUri
     * @param string|resource $objectData
     */
    public function createSchedulingObject($principalUri, $objectUri, $objectData): void
    {
        if (is_resource($objectData)) {
            $objectData = stream_get_contents($objectData);
        }

        if (!is_string($objectData)) {
            throw new \InvalidArgumentException('Invalid scheduling object data.');
        }

        CalDavLog::log("$principalUri / $objectUri / $objectData");

        $this->db->insert(
            "xcalendar_scheduling_objects",
            [
                "principal_uri" => $principalUri,
                "calendar_data" => $objectData,
                "uri" => $objectUri,
                "modified_at" => time(),
                "etag" => md5($objectData),
                "size" => strlen($objectData),
            ]
        );
    }

    private function calendarExists($calendarId): bool
    {
        return (bool)$this->db->value("id", "xcalendar_calendars", ["id" => $calendarId, "removed_at" => null]);
    }

    private function verifyPermission(): void
    {
        if ($this->syncData['read_only']) {
            CalDavLog::log("Calendar is read-only.");
            header("HTTP/1.0 403 Calendar is read-only");
            exit();
        }
    }

    private function eventRowToCalendarObject(array $row, $includeVEvent = true): ?array
    {
        // if vevent doesn't exist (event created with an ancient version) create and save it in the db
        if (empty($row['vevent'])) {
            $eventData = new EventData();
            $eventData->loadFromDb($row['id']);
            if (!($row['vevent'] = $eventData->ensureVEvent())) {
                return null;
            }
        }

        // get the timezone strings
        if ((int)$row["all_day"]) {
            $vtimezone = false;
        } else {
            $vtimezone = Timezone::getVTimezone($row["timezone_start"]);

            if ($row["timezone_start"] != $row["timezone_end"]) {
                $vtimezone .= Timezone::getVTimezone($row["timezone_end"]);
            }
        }

        // wrap the event string in vcalendar and add timezone strings if needed
        $row['vevent'] = Event::wrapInVCalendar($row['vevent'], $vtimezone);

        $array = [
            'id' => $row['id'],
            'uri' => $row['uid'],
            'lastmodified' => (int)strtotime($row['modified_at'] ?? ""),
            'etag' => '"' . md5($row['vevent']) . '"',
            'size' => strlen($row['vevent']),
            'component' => "vevent",
        ];

        if ($includeVEvent) {
            $array['calendardata'] = $row['vevent'];
        }

        return $array;
    }


}
