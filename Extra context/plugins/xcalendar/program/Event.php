<?php
namespace XCalendar;

/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

// need to use RCUBE_INSTALL_PATH because caldav subdomain server causes problems with require paths otherwise
require_once RCUBE_INSTALL_PATH . "plugins/xcalendar/program/Entity.php";
require_once __DIR__ . "/../../xframework/common/Format.php";
require_once __DIR__ . "/Timezone.php";

use XFramework\Utils;
use Sabre\VObject\Reader;

DEFINE("ALARM_BEFORE_START", "0");
DEFINE("ALARM_AFTER_START", "1");
DEFINE("ALARM_BEFORE_END", "2");
DEFINE("ALARM_AFTER_END", "3");
DEFINE("ALARM_ABSOLUTE_TIME", "4");
DEFINE("ALARM_TYPE_POPUP", 0);
DEFINE("ALARM_TYPE_EMAIL", 1);

class Event extends Entity
{
    private array $attendeeBackgroundColors = ["1" => "#F4511E", "3" => "#FF8A65"]; // yes / maybe
    private string $attendeeTextColor = "#fff";
    private string $timezoneString;
    private \DateTimeZone $timezone;

    public function __construct()
    {
        parent::__construct();
        $this->timezoneString = $this->rcmail->config->get("timezone", "UTC");
        try {
            $this->timezone = new \DateTimeZone($this->timezoneString);
        } catch (\Exception) {
            $this->timezone = new \DateTimeZone("UTC");
        }
    }

    public function getRemoteEventList(string $rangeStart, string $rangeEnd): array
    {
        $events = [];

        if (!($calendars = $this->getCalendarList([Calendar::CALDAV, Calendar::HOLIDAY, Calendar::GOOGLE], true, true))) {
            return [];
        }

        foreach ($calendars as $calendar) {
            switch ($calendar['type']) {
                case Calendar::CALDAV:
                    try {
                        $events = array_merge($events, ClientCaldav::getEvents($calendar['id'], $rangeStart, $rangeEnd));
                    } catch (\Exception) {}
                    break;
                case Calendar::HOLIDAY:
                case Calendar::GOOGLE:
                    try {
                        $events = array_merge($events, ClientGoogle::getEvents($calendar['id'], $rangeStart, $rangeEnd));
                    } catch (\Exception) {}
                    break;
            }
        }

        return $events;
    }

    public static function getEventByUid(string $uid): bool|array
    {
        if (empty($uid)) {
            return false;
        }

        return xdb()->row('xcalendar_events', ['user_id' => xrc()->get_user_id(), 'vevent_uid' => $uid]);
    }

    /**
     * Returns the list of events gathered from all the local calendars.
     *
     * @param string $rangeStart
     * @param string $rangeEnd
     * @param bool $includeVEvent
     * @return array
     */
    public function getLocalEventList(string $rangeStart, string $rangeEnd, bool $includeVEvent = false): array
    {
        $list = [];
        $calendarColors = [];
        $sharedCalendarIds = [];
        $calendarIds = [];

        if (!($calendars = $this->getCalendarList(Calendar::LOCAL, true, true))) {
            $calendars = [];
        }

        // create arrays of calendar ids and colors so we can use it later
        foreach ($calendars as $calendar) {
            $calendarIds[] = $calendar['id'];
            $calendarColors[$calendar['id']] = ["bg_color" => $calendar['bg_color'], "tx_color" => $calendar['tx_color']];
            if ($calendar['user_id'] != $this->userId) {
                $sharedCalendarIds[] = $calendar['id'];
            }
        }

        $endCol = $this->db->col("end");

        // get the array of events (look separately for start and end within the specified range to also find
        // events that start in one month and end in another.)
        $array = [];
        if (!empty($calendarIds) &&
            !empty($rangeStart) &&
            !empty($rangeEnd) &&
            ($rangeStartInt = strtotime($rangeStart)) !== false &&
            ($rangeEndInt = strtotime($rangeEnd)) !== false
        ) {
            $rangeStart = date("Y-m-d H:i:s", $rangeStartInt);
            $rangeEnd = date("Y-m-d H:i:s", $rangeEndInt);

            $array = $this->db->all(
                "SELECT *, DATE(start) AS day FROM {xcalendar_events}
                    WHERE calendar_id IN (" . implode(",", $calendarIds) . ") AND
                    ((start >= ? AND start <= ?) OR ($endCol >= ? AND $endCol <= ?) OR (start <= ? AND $endCol >= ?)) 
                    AND removed_at IS NULL",
                [$rangeStart, $rangeEnd, $rangeStart, $rangeEnd, $rangeStart, $rangeEnd]
            );
        }

        is_array($array) || $array = [];

        // create the events array with event id as key
        $events = [];
        foreach ($array as $event) {
            // if shared calendar, check if event should be shown or change its title if need be
            if (!$this->sharedEventSecurityCheck($event, $sharedCalendarIds, $calendars)) {
                continue;
            }

            $events[$event['id']] = $event;
        }

        // if there are any shared calendars, get the event customized data and overwrite the event properties
        $values = [$this->userId];
        $markers = [];
        foreach ($events as $key => $event) {
            if (in_array($event['calendar_id'], $sharedCalendarIds)) {
                $values[] = $key;
                $markers[] = "?";
            }
        }

        if (!empty($markers)) {
            $custom = $this->db->all(
                "SELECT * FROM {xcalendar_events_custom} WHERE user_id = ? AND "
                    . "event_id IN (" . implode(",", $markers) . ") AND use_calendar_colors != 1",
                $values
            );

            if (is_array($custom)) {
                foreach ($custom as $event) {
                    $events[$event['event_id']]['use_calendar_colors'] = false;
                    $events[$event['event_id']]['bg_color'] = $event['bg_color'];
                    $events[$event['event_id']]['tx_color'] = $event['tx_color'];
                }
            }
        }

        // add repeating events to the array
        $repeat = false;

        if (!empty($calendarIds)) {
            $repeat = $this->db->all(
                "SELECT *, DATE(start) AS day FROM {xcalendar_events} " .
                "WHERE calendar_id IN (" . implode(",", $calendarIds) . ") AND " .
                "removed_at IS NULL AND " .
                "repeat_rule != '' AND repeat_end >= ?",
                [$rangeStart]
            );
        }

        if ($repeat) {
            foreach ($repeat as $event) {
                // if shared calendar, check if event should be shown or change its title if need be
                if ($this->sharedEventSecurityCheck($event, $sharedCalendarIds, $calendars)) {
                    $events = array_merge($events, $this->getRepeatedEvents($event, $rangeStart, $rangeEnd));
                }
            }
        }

        $eventIds = [];
        foreach ($events as $event) {
            $eventIds[] = $event['id'];
        }

        // get the list of removed repeated events
        $removedEvents = [];

        if (!empty($eventIds)) {
            $removedArray = $this->db->all(
                "SELECT DATE(day) AS day, event_id FROM {xcalendar_events_removed} ".
                "WHERE day >= ? AND day <= ? AND event_id IN (" . implode(",", array_unique($eventIds)) . ")",
                [$rangeStart, $rangeEnd]
            );

            if (is_array($removedArray)) {
                foreach ($removedArray as $item) {
                    $removedEvents[$item['event_id']][] = $item['day'];
                }
            }
        }

        $categories = $this->getCategories(true);
        $useBorders = $this->rcmail->config->get("xcalendar_event_border", $this->getDefaultSettings()['event_border']);

        // convert the event array to the fullcalendar object format and remove deleted repeated events
        if ($events) {
            foreach ($events as $event) {

                if (!empty($removedEvents[$event['id']]) && in_array($event['day'], $removedEvents[$event['id']])) {
                    continue;
                }

                if ($event['all_day']) {
                    // in the ical standard all day events start on one day and end on the following day, but in our db
                    // we store the end day on the actual day when it's supposed to end, so the editor can show the date
                    // properly to the user. Since fullcalendar displays events according to the ical standard, we need
                    // to add 1 day to the end day for the all day events
                    $event['start'] = date("Y-m-d 00:00:00", strtotime($event['start']));
                    $event['end'] = date("Y-m-d 00:00:00", strtotime($event['end'] . " +1 day"));
                } else {
                    try {
                        $event['start'] = Timezone::convertToTimezone(
                            $event['start'],
                            $event['timezone_start'],
                            $this->timezoneString
                        );
                        $event['end'] = Timezone::convertToTimezone(
                            $event['end'],
                            $event['timezone_end'],
                            $this->timezoneString
                        );
                    } catch (\Exception) {
                        continue;
                    }
                }

                // if event doesn't belong to the user's calendar, but the user is only an attendee of an event from
                // a different calendar belonging to a different user
                if (!empty($event['attendee_status'])) {
                    $backgroundColor = $this->attendeeBackgroundColors[$event['attendee_status']];
                    $textColor = $this->attendeeTextColor;
                    $borderColor = "transparent";
                } else {
                    // the event that belongs to the current user
                    if ($event['use_calendar_colors']) {
                        $backgroundColor = $calendarColors[$event['calendar_id']]['bg_color'];
                        $textColor = $calendarColors[$event['calendar_id']]['tx_color'];
                    } else {
                        $backgroundColor = $event['bg_color'];
                        $textColor = $event['tx_color'];
                    }

                    if ($useBorders && array_key_exists($event['category'], $categories)) {
                        $borderColor = $categories[$event['category']];
                    } else {
                        $borderColor = "transparent";
                    }
                }

                $record = [
                    "id" => $event['id'],
                    "calendar_id" => $event['calendar_id'],
                    "calendar" => strip_tags($calendars[$event['calendar_id']]['name']),
                    "type" => Calendar::LOCAL,
                    "title" => $event['title'],
                    "start" => $event['start'],
                    "end" => $event['end'],
                    "timezoneStart" => $event['timezone_start'],
                    "timezoneEnd" => $event['timezone_end'],
                    "allDay" => (bool)(int)$event['all_day'],
                    "backgroundColor" => $backgroundColor,
                    "textColor" => $textColor,
                    "borderColor" => $borderColor,
                    "editable" => $calendars[$event['calendar_id']]['permissions']->edit_events,
                ];

                if ($includeVEvent) {
                    $record['uid'] = $event['uid'];
                    $record['vevent'] = $event['vevent'];
                }

                $list[] = $record;
            }
        }

        // sort the events by date/time
        usort($list, function(array $a, array $b) {
            return $a['start'] <=> $b['start'];
        });

        return $list;
    }

    private function sharedEventSecurityCheck(&$event, $sharedCalendarIds, $calendars): bool
    {
        if (!in_array($event['calendar_id'], $sharedCalendarIds)) {
            return true;
        }

        // if shared and private, don't show
        if ($event['visibility'] == "private" ||
            ($event['visibility'] == "default" && $calendars[$event['calendar_id']]['default_event_visibility'] == "private")
        ) {
            return false;
        }

        // if not allowed to see details, show available/busy instead of title
        if (isset($calendars[$event['calendar_id']]['permissions']->see_details) &&
            !$calendars[$event['calendar_id']]['permissions']->see_details
        ) {
            $event['title'] = "[" . $this->rcmail->gettext("xcalendar." . ($event['busy'] ? "busy" : "available")) . "]";
        }

        return true;
    }

    /**
     * Get the array of dates that this event will repeat on, from the start date to the end date specified
     * as parameters.
     *
     * To get the event days, we'll extract slice of time (weeks, months, years), called units and iterate days
     * in each unit to find events, then we'll skip to the next unit based on repeat_interval.
     *
     * FYI: Sabre RRuleIterator will not work properly with the rules like this: FREQ=YEARLY;BYDAY=MO;BYMONTH=5
     * If the start of the event is in March, it'll create events in March and in May, instead of just in May.
     * This is a Sabre problem, can't do anything about this.
     *
     * @param array $event
     * @param $rangeStart
     * @param $rangeEnd
     * @return array
     */
    public function getRepeatedEvents(array $event, $rangeStart, $rangeEnd): array
    {
        try {
            if (empty($event['repeat_rule'])) {
                throw new \Exception();
            }

            $allDay = $event['all_day'] == "1";

            // if event end is 'until' we re-encode the event with time in the 'until' string indicating the end of the
            // day (23:59), otherwise the event might end one day too early or too late (normally 'until' shouldn't
            // include the time, or the other apps won't display things properly via caldav)
            $rrule = EventData::decodeRRule($event['repeat_rule']);

            if ($rrule['range'] == "until") {
                $event['repeat_rule'] = EventData::encodeRRule($rrule, true);
            }

            $startDate = new \DateTime($allDay ? substr($event['start'], 0, 10) : $event['start'], $this->timezone);
            $endDate = new \DateTime($rangeEnd, $this->timezone);
            $rangeStartDate = new \DateTime($rangeStart, $this->timezone);
            $interval = strtotime($event['end']) - strtotime($event['start']);
            $iterator = new \Sabre\VObject\Recur\RRuleIterator($event['repeat_rule'], $startDate);

            $result = [];

            if ($startDate <= $rangeStartDate) {
                $iterator->fastForward($rangeStartDate);
            } else {
                $iterator->next();
            }

            $current = $iterator->current();

            while ($current && $current <= $endDate) {
                $event['start'] = $current->format("Y-m-d H:i:s");
                $event['day'] = $current->format("Y-m-d");
                $event['end'] = date("Y-m-d H:i:s", strtotime($event['start']) + $interval);
                $result[] = $event;
                $iterator->next();
                $current = $iterator->current();
            }

            return $result;

        } catch (\Exception) {
            return [];
        }
    }

    /**
     * Checks if the specified calendar has any events to export.
     *
     * @param $calendarId
     * @return bool|string
     */
    public function hasEventsToExport($calendarId): bool|string
    {
        return $this->exportEvents($calendarId, $calendarName, true);
    }

    /**
     * Exports all events into a string wrapped in VCALENDAR.
     *
     * @param $calendarId
     * @param ?string $calendarName
     * @param bool $checkOnly
     * @return bool|string
     */
    public function exportEvents($calendarId, ?string &$calendarName = "", bool $checkOnly = false): bool|string
    {
        if (empty($this->userId) ||
            !($calendar = $this->db->row("xcalendar_calendars", ["id" => $calendarId, "removed_at" => NULL]))
        ) {
            return false;
        }

        $calendarName = $calendar['name'];
        $seeDetails = true;
        $isOwner = $calendar['user_id'] == $this->userId;

        // if the user is not the owner of the calendar, check if maybe it's a shared calendar
        if (!$isOwner) {
            $shared = $this->getAddedSharedCalendars();

            if (empty($shared)) {
                return false;
            }

            $found = false;

            foreach ($shared as $val) {
                if ($val['id'] == $calendarId) {
                    $found = true;
                    $seeDetails = $val['permissions']->see_details;
                    $calendarName = $val['name'];
                    break;
                }
            }

            if (!$found) {
                return false;
            }
        }

        // get the list of events
        $events = $this->db->all(
            "SELECT id, busy, vevent, timezone_start, timezone_end, all_day, visibility ".
            "FROM {xcalendar_events} ".
            "WHERE calendar_id = ? AND removed_at IS NULL ORDER BY start",
            [$calendarId]
        );

        // if we're only checking if there are events to export, return
        if ($checkOnly) {
            return !empty($events);
        }

        $result = "";
        $timezones = [];

        // go through the events
        foreach ($events as $event) {
            // only hide private events when exporting a shared calendar;
            // the owner gets a complete export of their own data
            if (!$isOwner) {
                if ($event['visibility'] == "private" ||
                    ($event['visibility'] == "default" && $calendar['default_event_visibility'] == "private")
                ) {
                    continue;
                }
            }

            // if there's no vevent for some reason, create it
            if (empty($event['vevent'])) {
                $eventData = new EventData();
                $eventData->loadFromDb($event['id']);

                if (!($event['vevent'] = $eventData->ensureVEvent())) {
                    continue;
                }
            }

            // if vevent is wrapped in vcalendar, extract it
            if (str_contains($event['vevent'], "BEGIN:VCALENDAR") &&
                ($i = strpos($event['vevent'], "BEGIN:VEVENT")) !== false &&
                ($j = strpos($event['vevent'], "END:VCALENDAR", $i)) !== false
            ) {
                $event['vevent'] = substr($event['vevent'], $i, $j - $i - 1) . "\n";
            }

            // if details shouldn't be visible (shared calendar)
            if (!$seeDetails) {
                $event['vevent'] = preg_replace(
                    "/SUMMARY:.*?\\n/",
                    "SUMMARY:[" . $this->rcmail->gettext("xcalendar." . ($event['busy'] ? "busy" : "available")) . "]\n",
                    $event['vevent']
                );
            }

            $result .= $event['vevent'];

            // get timezones that will be added to the exported file, but only if it's not an all day event, since all
            // day events don't have timezones
            if (!$event['all_day']) {
                $this->ensureTimezones($event, $timezoneStart, $timezoneEnd);
                in_array($timezoneStart, $timezones) || ($timezones[] = $timezoneStart);
                in_array($timezoneEnd, $timezones) || ($timezones[] = $timezoneEnd);
            }
        }

        if (empty($result)) {
            return false;
        }

        // all the timezones gathered from the events to the header of the vcalendar
        $timezoneString = "";
        foreach ($timezones as $timezone) {
            $timezoneString .= Timezone::getVTimezone($timezone);
        }

        // wrap in vcalendar and return
        return self::wrapInVCalendar($result, $timezoneString);
    }

    /**
     * @param $ics
     * @param $calendarId
     * @return array|bool
     */
    public function importEvents($ics, $calendarId): array|bool
    {
        if (!$ics) {
            Utils::logError("Import file empty (44736)");
            return false;
        }

        if (!$calendarId) {
            Utils::logError("Invalid calendar id (44737)");
            return false;
        }

        // check if we have permission
        if (!Permission::hasCalendarPermission($calendarId, "edit_events", $this->userId, $this->userEmail)) {
            Utils::logError("Permission denied (44738)");
            return false;
        }

        if (empty($events = $this->vEventToDataArray($ics))) {
            Utils::logError("Cannot decode vevent (38194)");
            return false;
        }

        $result = ["success" => 0, "error" => 0];

        foreach ($events as $data) {
            $eventData = new EventData();
            $eventData->loadFromDb(["vevent_uid" => $data['uid'], "calendar_id" => $calendarId, "user_id" => $this->userId]);
            $eventData->importData($data);
            $eventData->setValue("calendar_id", $calendarId);
            try {
                $eventData->save();
                $result["success"]++;
            } catch (\Exception) {
                $result["error"]++;
            }
        }

        return $result;
    }

    /**
     * Moves the uploaded attachment file to the attachments directory, performing all sorts of checks on the way.
     *
     * @return array
     * @throws \Exception
     */
    public function uploadAttachment(): array
    {
        if (!$this->rcmail->config->get("xcalendar_enable_event_attachments", false)) {
            throw new \Exception("Event attachment functionality is disabled.");
        }

        $attachmentDir = $this->getAttachmentDir();

        if (!is_writable($attachmentDir)) {
            throw new \Exception("Attachment directory doesn't exist or is not writable.");
        }

        if (empty($_FILES['file'])) {
            throw new \Exception("The uploaded file does not exist.");
        }

        $file = $_FILES['file'];

        if ($file['error']) {
            throw new \Exception("There was an error while uploading the file.");
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \Exception("This file has not been properly uploaded.");
        }

        $maxSize = $this->getMaxAttachmentSize();

        if ($file['size'] > $maxSize) {
            throw new \Exception(
                $this->rcmail->gettext([
                    "name" => "xcalendar.attachment_too_large",
                    "vars" => ["s" => Utils::sizeToString($maxSize)]
                ])
            );
        }

        // make the target directory
        $dir = Utils::structuredDirectory($this->userId) . Utils::encodeId($this->userId) . "/";

        if (!file_exists($attachmentDir . $dir) && !mkdir($attachmentDir . $dir, 0777, true)) {
            throw new \Exception("Cannot create event attachment directory.");
        }

        // get a unique file name
        $fileName = Utils::uniqueFileName($attachmentDir . $dir);

        // move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $attachmentDir . $dir . $fileName)) {
            throw new \Exception("Cannot move the uploaded file.");
        }

        if (!$this->db->insert(
            "xcalendar_attachments_temp",
            ["filename" => $dir . $fileName, "uploaded_at" => date("Y-m-d H:i:s")]
        )) {
            @unlink($attachmentDir . $dir . $fileName);
            throw new \Exception("Cannot save attachment information in the database.");
        }

        return [
            "path" => Utils::getUrl("?xcalendar=" . Utils::encodeUrlAction(
                "download-attachment",
                ["path" => $dir . $fileName, "name" => $file['name']]
            )),
            "name" => $file['name'],
            "size" => $file['size']
        ];
    }

    /**
     * Dispatches the event attachment as a download to the browser.
     *
     * @param string $name
     * @param string $path
     */
    public function dispatchAttachment(string $name, string $path): void
    {
        try {
            if (!$this->areAttachmentsEnabled()) {
                throw new \Exception();
            }

            if (!$name || !$path) {
                throw new \Exception();
            }

            // resolve and contain the path within the attachment directory to prevent traversal
            $base = realpath($this->getAttachmentDir());
            $path = realpath($this->getAttachmentDir() . $path);

            if (!$base || !$path || !str_starts_with($path, $base . DIRECTORY_SEPARATOR)) {
                throw new \Exception();
            }

            if (!($size = filesize($path))) {
                throw new \Exception();
            }

            // check for ranges
            if(isset($_SERVER['HTTP_RANGE'])) {
                list($sizeUnit, $rangeOrig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                if ($sizeUnit == 'bytes') { // use only first range even if more specified
                    list($range) = explode(',', $rangeOrig, 2);
                } else {
                    $range = "";
                }
            } else {
                $range = "";
            }

            // figure out download piece from range (if set)
            if ($range) {
                list($seekStart, $seekEnd) = explode('-', $range, 2);
            }

            // set start and end based on range (if set), else set defaults, also check for invalid ranges
            $seekEnd = (empty($seekEnd)) ? ($size - 1) : min(abs(intval($seekEnd)), ($size - 1));
            $seekStart = (empty($seekStart) || $seekEnd < abs(intval($seekStart))) ? 0 : max(abs(intval($seekStart)), 0);

            // Close the session before the download begins to prevent problems.
            // Only one session can be open at a time, so when we dispatch a large file, it keeps the session
            // open and any later calls that try to open the session have to wait until the file is completely
            // dispatched and the session is released. This results in the program hanging.
            session_write_close();

            // only send partial content header if downloading a piece of the file (IE workaround)
            if ($seekStart > 0 || $seekEnd < ($size - 1)) {
                header('HTTP/1.1 206 Partial Content');
            }

            header('Accept-Ranges: bytes');
            header('Content-Range: bytes ' . $seekStart . '-' . $seekEnd . '/' . $size);
            header('Content-Length: ' . ($seekEnd - $seekStart + 1));
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"". rawurlencode($name) ."\"");
            header("Content-Transfer-Encoding: binary");

            if (ob_get_contents()) {
                @ob_end_clean();
            }

            $fp = fopen($path, 'rb');

            fseek($fp, $seekStart);

            while(!feof($fp)) {
                set_time_limit(30);
                echo fread($fp, 8192);
                flush();
                @ob_flush();
            }
            fclose($fp);
            exit();

        } catch (\Exception) {
            Utils::exit404();
        }
    }

    public function dispatchEvent($eventId, $name): void
    {
        try {
            $eventData = new EventData();
            if (!$eventData->loadFromDb($eventId)) {
                throw new \Exception();
            }

            // check if the user has permission to download the event
            $perm = Permission::getCalendarPermissions(
                $eventData->getValue("calendar_id"),
                $this->userId,
                $this->userEmail
            );
            if (empty($perm->see_details)) {
                throw new \Exception();
            }

            // wrap vevent in vcalendar and include the necessary timezones in vcalendar
            if ((int)$eventData->getValue("all_day")) {
                $vtimezone = false;
            } else {
                $timezoneStart = $eventData->getValue("timezone_start");
                $timezoneEnd = $eventData->getValue("timezone_end");
                $vtimezone = Timezone::getVTimezone($timezoneStart);

                if ($timezoneStart != $timezoneEnd) {
                    $vtimezone .= Timezone::getVTimezone($timezoneEnd);
                }
            }

            $vevent = self::wrapInVCalendar($eventData->getValue("vevent"), $vtimezone);
            $filename = Utils::ensureFileName($name);

            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'ics') {
                $filename .= '.ics';
            }

            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
            exit($vevent);

        } catch (\Exception) {
            Utils::exit404();
        }
    }

    public function snooze($alarmId, $minutes): bool
    {
        if (!is_numeric($minutes) || $minutes <= 0 || $minutes > 60 * 24 * 14) {
            return false;
        }

        if (!($alarm = $this->db->row("xcalendar_alarms", ["id" => $alarmId, "user_id" => $this->userId]))) {
            return false;
        }

        // if not recurring, and we have alarm_time, simply change alarm_time
        if ($alarm['alarm_time']) {
            try {
                $time = new \DateTime($alarm['alarm_time']);
                $time->modify("+$minutes minutes");

                $this->db->query(
                    "UPDATE {xcalendar_alarms} SET alarm_time = ? WHERE id = ?",
                    [$time->format("Y-m-d H:i:00"), $alarmId]
                );
            } catch (\Exception) {}
        } else {
            // if recurring or old style (before 1.9) increase the snooze value
            $this->db->query("UPDATE {xcalendar_alarms} SET snooze = snooze + ? WHERE id = ?", [$minutes, $alarmId]);
        }

        return true;
    }

    /**
     * Gets the list of events that the user should be notified about right now. Js calls this function at the beginning
     * of every minute, we get the alarms and events, calculate in the notification time and snooze and check if
     * there's an event starting at the resulting minute. Cron also uses this function for email notifications.
     *
     * @param $type
     * @param \DateTime|null $currentTime - for testing purposes only, see xcalendar::runCron()
     * @return array
     */
    public function getAlarms($type, ?\DateTime $currentTime = null): array
    {
        if (!in_array($type, [ALARM_TYPE_POPUP, ALARM_TYPE_EMAIL])) {
            return [];
        }

        if (empty($currentTime)) {
            try {
                $currentTime = new \DateTime("now", new \DateTimeZone("UTC"));
            } catch (\Exception) {
                return [];
            }
        }

        $currentMinute = $currentTime->format("Y-m-d H:i:00");
        $userQuery = $type == ALARM_TYPE_POPUP ? " AND alarms.user_id = ?" : "";
        $userParam = $type == ALARM_TYPE_POPUP ? [$this->userId] : [];
        $result = [];

        $alarms = $this->db->all(
            "SELECT *, alarms.id AS alarm_id FROM {xcalendar_alarms} AS alarms 
             LEFT JOIN {xcalendar_events} AS events ON event_id = events.id
             LEFT JOIN {xcalendar_calendars} as calendars ON events.calendar_id = calendars.id
             WHERE alarms.alarm_time = ? AND alarm_type = ? AND alarms.event_end IS NULL $userQuery 
                 AND events.removed_at IS NULL AND calendars.removed_at IS NULL",
            array_merge([$currentMinute, $type], $userParam)
        );

        // find alarms that belong to recurring events, alarm_time must be null (first time notification) or smaller
        // than the current minute, meaning the event from last week/month/year was processed and alarm_time was set
        // to midnight the next day while processing_started was set to 0 -- this allows for future events to be sent
        $recurring = $this->db->all(
            "SELECT *, alarms.id AS alarm_id FROM {xcalendar_alarms} AS alarms
             LEFT JOIN {xcalendar_events} AS events ON event_id = events.id
             LEFT JOIN {xcalendar_calendars} as calendars ON events.calendar_id = calendars.id
             WHERE (alarm_time IS NULL OR alarm_time < ?) AND alarm_type = ? AND alarms.event_end >= ? $userQuery
                AND events.removed_at IS NULL AND calendars.removed_at IS NULL",
            array_merge([$currentMinute, $type, $currentMinute], $userParam)
        );

        if (!empty($recurring)) {
            foreach ($recurring as $alarm) {
                $projectedEventStart = clone $currentTime;

                try {
                    switch ($alarm['alarm_position']) {
                        case ALARM_BEFORE_START:
                            $projectedEventStart->modify("+{$alarm['alarm_number']} {$alarm['alarm_units']}");
                            break;
                        case ALARM_AFTER_START:
                            $projectedEventStart->modify("-{$alarm['alarm_number']} {$alarm['alarm_units']}");
                            break;
                        case ALARM_BEFORE_END:
                            $projectedEventStart->modify("-" . (strtotime($alarm['end']) - strtotime($alarm['start'])) . " seconds");
                            $projectedEventStart->modify("+{$alarm['alarm_number']} {$alarm['alarm_units']}");
                            break;
                        case ALARM_AFTER_END:
                            $projectedEventStart->modify("-" . (strtotime($alarm['end']) - strtotime($alarm['start'])) . " seconds");
                            $projectedEventStart->modify("-{$alarm['alarm_number']} {$alarm['alarm_units']}");
                            break;
                    }

                    $projectedEventStart->modify("-{$alarm['snooze']} minutes");
                } catch (\Exception) {
                    continue;
                }

                // old non-recurring events that don't have alarm_time, before the 1.9 upgrade
                if (empty($alarm['repeat_rule'])) {
                    if ($projectedEventStart->format("Y-m-d H:i:00") == $alarm['start']) {
                        $alarms[] = $alarm;
                    }
                } else {
                    // for repeated events we take the time at which the event should start, convert it to the event
                    // timezone and run getRepeatedEvents with the same start and end range, if there is an event
                    // that starts during the specified minute, it'll be returned -- only one event will be returned
                    try {
                        $rangeStart = Timezone::convertToTimezone(
                            $projectedEventStart->format("Y-m-d H:i:00"),
                            "UTC",
                            $alarm['timezone_start']
                        );

                        $repeatedEvents = $this->getRepeatedEvents($alarm, $rangeStart, $rangeStart);

                        if (!empty($repeatedEvents)) {
                            $alarms[] = $repeatedEvents[0];
                        }
                    } catch (\Exception) {}
                }
            }
        }

        if ($alarms) {
            foreach ($alarms as $alarm) {
                if ($type == ALARM_TYPE_POPUP) {
                    $result[] = [
                        "id" => $alarm['alarm_id'],
                        "title" => $alarm['title'],
                        "description" => $alarm['description'],
                        "snooze" => [5, 10, 20, 30, 60, 120, 1440, 10080], // minutes
                    ];
                } else {
                    $result[] = $alarm;
                }
            }
        }

        return $result;
    }

    public function canEditEvent($eventId): bool
    {
        if (!$eventId) {
            return true;
        }

        return ($calendarId = $this->db->value("calendar_id", "xcalendar_events", ["id" => $eventId])) &&
            Permission::hasCalendarPermission($calendarId, "edit_events", $this->userId, $this->userEmail);
    }

    /**
     * Update the user attendance status in all the events with this uid - this updates it for all the user that have
     * this event in their calendars. This also updates all the deleted events, in case people change their response
     * this attendee status will be up-to-date.
     *
     * @param $uid
     * @param $email
     * @param $response
     * @param $excludeId - exclude this event id in case we have already set its attendance
     */
    public static function setAttendeeStatus($uid, $email, $response, $excludeId = null): void
    {
        if ($rows = xdb()->all("SELECT id FROM {xcalendar_events} WHERE uid = ?", [$uid])) {
            foreach ($rows as $row) {
                if ($excludeId && $row['id'] == $excludeId) {
                    continue;
                }

                $eventData = new EventData();

                if ($eventData->loadFromDb($row['id']) && $eventData->setAttendanceByEmail($email, $response)) {
                    try {
                        $eventData->save();
                    } catch (\Exception) {}
                }
            }
        }
    }

    /**
     * Sends an invitation email to the event attendees.
     *
     * @param $oldIcs
     * @param $newIcs
     * @return bool
     */
    public static function sendAttendeeEmailNotifications($oldIcs, $newIcs): bool
    {
        $email = false;

        if ($oldIcs) {
            $oldIcs = Event::wrapInVCalendar($oldIcs);
            $email = Event::findCurrentUserEmailInIcs($oldIcs);
        }

        if ($newIcs) {
            $newIcs = Event::wrapInVCalendar($newIcs);

            if (!$email) {
                $email = Event::findCurrentUserEmailInIcs($newIcs);
            }
        }

        // Compares two ics files: original and updated, looking from the perspective of the specified email address. It
        // creates and sends the appropriate email messages depending on what changes it finds.
        try {
            // this compares the new and old ics and creates the appropriate itip message objects based on the changes
            // it detects
            $broker = new \Sabre\VObject\ITip\Broker();
            $messages = $broker->parseEvent($newIcs, $email, $oldIcs);

            // the broker decided there are no (significant) changes and there's no need to send any emails
            if (empty($messages)) {
                return false;
            }

            // pass the itip message objects to the imip plugin to create and send the appropriate emails
            $cdi = new CalDavIMip($email);

            // schedule the emails, if $oldIcs exists, meaning the user is editing the event, check if the recipient
            // existed in the list of attendees before the modification and send true to schedule() which will change
            // the email subject from "Invitation" to "Event modified"
            foreach ($messages as $message) {
                $cdi->schedule($message, $oldIcs && Event::emailExistsInIcsAttendees($message->recipient, $oldIcs));
            }

            return true;

        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Remove event. If deleting a repeated instance of an event, we actually add it to the events_removed table
     * and leave the original event unchanged.
     *
     * @param int $eventId
     * @param string $day
     * @return bool
     */
    public function removeEvent(int $eventId, string $day = ""): bool
    {
        if (!$this->canEditEvent($eventId)) {
            return false;
        }

        $eventData = new EventData();

        if (!$eventData->loadFromDb($eventId)) {
            return false;
        }

        // remove a single event in a series
        if ($day) {
            $originalVevent = $eventData->ensureVEvent();
            $excluded = $eventData->getValue("excluded");
            $excluded[] = $day;
            $eventData->setValue("excluded", $excluded);
        } else {
            $eventData->setValue("removed_at", date("Y-m-d H:i:s"));
        }

        try {
            $eventData->save();
        } catch (\Exception) {
            return false;
        }

        // don't send notifications if running from caldav - they're sent automatically
        if ($eventData->getValue("has_attendees") && !defined("XCALENDAR_CALDAV")) {
            if ($day) {
                $this->sendAttendeeEmailNotifications($originalVevent, $eventData->getValue("vevent"));
            } else {
                $this->sendAttendeeEmailNotifications($eventData->getValue("vevent"), null);
            }
        }

        return true;
    }

    /**
     * Restore removed event. If restoring a repeated instance of an event we remove it from the events_removed table.
     *
     * @param int $eventId
     * @param string $day
     * @return bool
     */
    public function restoreEvent(int $eventId, string $day = ""): bool
    {
        if (!$this->canEditEvent($eventId)) {
            return false;
        }

        $eventData = new EventData();

        if (!$eventData->loadFromDb($eventId, true)) {
            return false;
        }

        // restore a single event in a series
        if ($day) {
            $day = substr($day, 0, 10); // cut out the time
            $originalVevent = $eventData->ensureVEvent();
            $excluded = $eventData->getValue("excluded");

            if (($key = array_search($day, $excluded)) !== false) {
                unset($excluded[$key]);
                $eventData->setValue("excluded", $excluded);
            }
        } else {
            $eventData->setValue("removed_at", NULL);
        }

        try {
            $eventData->save();
        } catch (\Exception) {
            return false;
        }

        if ($eventData->getValue("has_attendees")) {
            if ($day) {
                self::sendAttendeeEmailNotifications($originalVevent, $eventData->getValue("vevent"));
            } else {
                $this->sendAttendeeEmailNotifications(null, $eventData->getValue("vevent"));
            }
        }

        return true;
    }

    /**
     * Makes sure that timezone_start and timezone_end are included in the event. If they're not (the event was
     * created using the older version of xcalendar) it extracts the timezone data from the event's vevent string and
     * updates the event in the database. It returns the timezones in parameters that were passed.
     *
     * @param $event
     * @param $timezoneStart
     * @param $timezoneEnd
     */
    public function ensureTimezones($event, &$timezoneStart, &$timezoneEnd): void
    {
        if (!empty($event['timezone_start']) && !empty($event['timezone_end'])) {
            $timezoneStart = $event['timezone_start'];
            $timezoneEnd = $event['timezone_end'];
            return;
        }

        Timezone::extractZonesFromVEvent($event['vevent'], $this->timezoneString, $timezoneStart, $timezoneEnd);

        $this->db->update(
            "xcalendar_events",
            ["timezone_start" => $timezoneStart, "timezone_end" => $timezoneEnd],
            ["id" => $event['id']]
        );
    }

    public static function wrapInVCalendar($string, $timezone = false, $method = false): string
    {
        // if already includes the VCALENDAR wrapper, return
        if (str_contains($string, "BEGIN:VCALENDAR")) {
            return $string;
        }

        return "BEGIN:VCALENDAR\r\n".
            "VERSION:2.0\r\n".
            "CALSCALE:GREGORIAN\r\n".
            "PRODID:" . self::getICalProdId() . "\r\n".
            ($method ? "METHOD:$method\r\n" : "").
            ($timezone ? trim($timezone) . "\r\n" : "").
            trim($string) . "\r\n".
            "END:VCALENDAR\r\n";
    }

    public static function matchCategory($category, $categories = false)
    {
        $categories || ($categories = self::getCategories());

        // try 1-to-1 match
        foreach ($categories as $item) {
            if ($item['name'] == $category) {
                return $item['name'];
            }
        }

        $category = strtolower($category);
        foreach ($categories as $item) {
            if (strtolower($item['name']) == $category) {
                return $item['name'];
            }
        }

        return false;
    }

    public static function getCategories($nameKeys = false): array
    {
        $array = xrc()->config->get("xcalendar_categories");

        if (!is_array($array) || empty($array)) {
            $array = self::getDefaultSettings()['categories'];
        }

        $categories = [];
        $names = [];
        foreach ($array as $item) {
            if(is_array($item) &&
                !empty($item['name']) &&
                !empty($item['color']) &&
                str_starts_with($item['color'], "#") &&
                !in_array($item['name'], $names) // remove duplicates
            ) {

                $names[] = $item['name'];
                $categories[] = $item;
            }
        }

        // if $nameKeys is specified, restructure the result. so it's in the name => color format
        if ($nameKeys) {
            $result = [];
            foreach ($categories as $category) {
                $result[$category['name']] = $category['color'];
            }
            return $result;
        }

        return $categories;
    }

    /**
     * Removes the attachment specified by path.
     *
     * @param string $path
     */
    public function removeAttachment(string $path): void
    {
        if (!str_starts_with($path, "http") || !($path = $this->attachmentUrlToPath($path))) {
            return;
        }
        $base = realpath($this->getAttachmentDir());
        $full = realpath($this->getAttachmentDir() . $path);
        if (!$base || !$full || !str_starts_with($full, $base . DIRECTORY_SEPARATOR)) {
            return;
        }
        @unlink($full);
        $this->db->remove("xcalendar_attachments_temp", ["filename" => $path]);
    }

    public static function attachmentUrlToPath(string $url)
    {
        if (($i = strpos($url, "?xcalendar=")) &&
            Utils::decodeUrlAction(substr($url, $i + 11), $data) == "download-attachment" &&
            !empty($data['path'])
        ) {
            return $data['path'];
        }

        return false;
    }

    /**
     * Decodes vcalendar into an array.
     * @param string $ics
     * @param bool $adjustForLocalStorage - when we decode vcalendar to be saved as a local event, we make some
     *      adjustments; for example, we change the end date of all day events. See explanations below.
     * @return array
     */
    public function vEventToDataArray(string $ics, bool $adjustForLocalStorage = true): array
    {
        if (empty($ics)) {
            return [];
        }

        // make sure vevent is wrapped in vcalendar, otherwise Reader will throw an error
        if (str_starts_with($ics, 'BEGIN:VEVENT')) {
            $ics = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-" . self::getICalProdId() . "\n" . rtrim($ics, "\r\n") .
                "\nEND:VCALENDAR";
        }

        // correct lines that will cause parsing errors: dtstart and dtend can't be arrays, they need to be strings
        $ics = str_replace("DTSTART;VALUE=DATE;VALUE=DATE:", "DTSTART;VALUE=DATE:", $ics);
        $ics = str_replace("DTEND;VALUE=DATE;VALUE=DATE:", "DTEND;VALUE=DATE:", $ics);

        // parse the calendar, this will throw an exception if the file is invalid
        try {
            $vcalendar = Reader::read($ics, Reader::OPTION_FORGIVING | Reader::OPTION_IGNORE_INVALID_LINES);
        } catch (\Exception) {
            Utils::logError("Cannot parse ics data (57846)");
            return [];
        }

        $categories = self::getCategories();
        $attachmentIndex = 1;
        $result = [];

        foreach ($vcalendar->children() as $item) {
            if (!is_a($item, "Sabre\VObject\Component\VEvent")) {
                continue;
            }

            $data = [
                'uid' => "",
                'title' => "",
                'location' => "",
                'description' => "",
                'url' => "",
                'start' => date("Y-m-d 00:00:00"),
                'end' => date("Y-m-d 00:00:00"),
                'all_day' => '0',
                'repeat_rule_orig' => '',
                'repeat_end' => NULL,
                'recurrence_id' => NULL,
                'busy' => "1",
                'visibility' => "default",
                'priority' => '0',
                'category' => '',
                'attendees' => [],
                'attachments' => [],
                'excluded' => [],
                'alarms' => [],
                'preserve_ics_properties' => [],
                'readonly' => false,
            ];

            $attendees = [];
            $attachments = [];
            $excluded = [];
            $alarms = [];
            set_time_limit(60);

            // When an event is created/edited by a caldav client, there might be some additional properties in the ics
            // text that we won't be saving to the database, but we want to preserve in the ics that will be sent back
            // to the client after the event is saved on the server. Normally we pull out from the ics the properties we
            // want to save to the db, save them, and then re-create the ics text and send it back to the browser. But
            // in this process, the properties we don't save to the db get lost. For now, we're only saving the
            // Thunderbird additional properties.
            // We're mostly interested in X-MOZ-SEND-INVITATIONS. Here's how it works:
            // When 'Prefer client-side scheduling' is set in the calendar properties in Thunderbird and the event is
            // saved in Thunderbird, each attendee will get an additional property: 'SCHEDULE-AGENT: CLIENT' -- this
            // instructs the server not to send the notification to that attendee. In this scenario, when 'Notify
            // attendees' is set in Thunderbird event properties, an extra property will be included in the ics:
            // X-MOZ-SEND-INVITATIONS:TRUE. We need to preserve this property and send it back to Thunderbird after the
            // event is saved on the server to instruct it to send the notifications. Otherwise, the server won't send
            // the notification (because of SCHEDULE-AGENT: CLIENT) and Thunderbird won't send the notification because
            // there's no X-MOZ-SEND-INVITATIONS:TRUE in the ics it gets back.
            // TO DO: SEQUENCE might need to be stored in the db and incremented on change
            $ics_properties_to_preserve = ['SEQUENCE', 'X-MOZ-SEND-INVITATIONS', 'X-MOZ-SEND-INVITATIONS-UNDISCLOSED', 
                'X-MOZ-GENERATION', 'X-MOZ-LASTACK', 'X-MOZ-SNOOZE-TIME'];

            foreach ($item->children() as $property) {

                if (in_array($property->name, $ics_properties_to_preserve)) {
                    $data['preserve_ics_properties'][$property->name] = $property->getValue();
                    continue;
                }

                switch ($property->name) {
                    case "UID":
                        $data["uid"] = $data['vevent_uid'] = $property->getValue();
                        break;
                    case "CREATED":
                        $data["created_at"] = $property->getDateTime()->setTimeZone($this->timezone)->format("Y-m-d H:i:s");
                        break;
                    case "SUMMARY":
                        $data["title"] = $this->fixVEventString($property->getValue());
                        break;
                    case "TRANSP":
                        $data["busy"] = (string)(int)($property->getValue() == "OPAQUE");
                        break;
                    case "CLASS":
                        $data["visibility"] = $property->getValue() == "PRIVATE" ? "private" : "public";
                        break;
                    case "DESCRIPTION":
                        $data["description"] = $this->fixVEventString($property->getValue());
                        break;
                    case "URL":
                        $data["url"] = $this->fixVEventString($property->getValue());
                        break;
                    case "LOCATION":
                        $data["location"] = $this->fixVEventString($property->getValue());
                        break;
                    case "PRIORITY":
                        $data["priority"] = $property->getValue();
                        break;
                    case "LAST-MODIFIED":
                        $data["modified_at"] = $property->getDateTime()->setTimeZone($this->timezone)->format("Y-m-d H:i:s");
                        break;
                    case "DTSTART":
                        if ($property->hasTime()) {
                            // if timezone is included in the date/time, save that timezone
                            if (!$property->isFloating()) {
                                $data['timezone_start'] = $property->getDateTime()->getTimeZone()->getName();
                            }

                            // if timezone is included, $this->timezone won't be used; otherwise the date will be
                            // converted to $this->timezone
                            $data["start"] = $property->getDateTime($this->timezone)->format("Y-m-d H:i:s");
                            $data['all_day'] = 0;
                        } else {
                            // if it's an all day event, add time
                            $data["start"] = $property->getDateTime()->format("Y-m-d 00:00:00");
                            $data['all_day'] = 1;
                        }
                        break;
                    case "DTEND":
                        if ($property->hasTime()) {
                            // NOT an all day event: has time in the string
                            if (!$property->isFloating()) {
                                $data['timezone_end'] = $property->getDateTime()->getTimeZone()->getName();
                            }
                            $data["end"] = $property->getDateTime($this->timezone)->format("Y-m-d H:i:s");
                        } else {
                            // all day event: if we'll be saving the event in the local database, move it one day
                            // back--the end of all day events in ical are marked as 00:00:00 the next day, but in our
                            // db we store all day events as being on the same day because users need to see it that way
                            // in the editor.
                            if ($adjustForLocalStorage) {
                                $data["end"] = $property->getDateTime()->modify("-1 days")->format("Y-m-d 00:00:00");
                            } else {
                                // no modification if using caldav client
                                $data["end"] = $property->getDateTime()->format("Y-m-d 00:00:00");
                            }
                        }
                        break;
                    case "CATEGORIES":
                        // we use only one category, let's match it with an existing category
                        $array = explode(",", $property->getValue());
                        if ($value = $this->matchCategory($array[0], $categories)) {
                            $data["category"] = $value;
                        }
                        break;
                    case "RRULE":
                        if ($value = $property->getValue()) {
                            $data['repeat_rule_orig'] = $value;
                        }
                        break;
                    case "ATTACH":
                        $attachment = $this->decodeImportAttachments($property, $attachmentIndex);
                        if ($attachment) {
                            $attachments[] = $attachment;
                            $attachmentIndex++;
                        }
                        break;
                    case "ATTENDEE":
                    case "ORGANIZER":
                        $email = str_replace("mailto:", "", $property->getValue());

                        // there can only be one organizer, unset all the others if set
                        if ($property->name == "ORGANIZER") {
                            foreach ($attendees as $key => $val) {
                                $attendees[$key]['organizer'] = 0;
                            }
                        }

                        if (!array_key_exists($email, $attendees)) {
                            $attendees[$email] = ["email" => $email, "organizer" => (int)($property->name == "ORGANIZER")];
                        }

                        if (!empty($property->parameters) && is_array($property->parameters)) {
                            foreach ($property->parameters as $key => $param) {
                                switch ($key) {
                                    case "ROLE":
                                        $attendees[$email]['role'] = (string)EventData::vRoleToRole($param->getValue());
                                        break;
                                    case "PARTSTAT":
                                        $attendees[$email]['status'] = (string)EventData::vStatusToStatus($param->getValue());
                                        break;
                                    case "CN":
                                        $attendees[$email]['name'] = $param->getValue();
                                        break;
                                    case "X-NOTIFY":
                                        // X-NOTIFY is xcalendar specific to preserve the notify property
                                        $attendees[$email]['notify'] = (int)(strtolower($param->getValue()) == "true");
                                        break;
                                    case "X-NUM-GUESTS":
                                        $attendees[$email]['guests'] = (int)$param->getValue();
                                        break;
                                    case "X-RESPONSE-COMMENT":
                                        $attendees[$email]['comment'] = $param->getValue();
                                        break;
                                    case "CUTYPE":
                                        // cutype is not used or saved anywhere for now
                                        $attendees[$email]['cutype'] = EventData::vCutypeToCutype($param->getValue());
                                        break;
                                }
                            }
                        }
                        break;
                    case "EXDATE":
                        $excluded[] = $property->getDateTime()->setTimeZone($this->timezone)->format("Y-m-d 00:00:00");
                        break;
                    case "RECURRENCE-ID":
                        $data["recurrence_id"] = $property->getDateTime($this->timezone)->format("Y-m-d 00:00:00");
                        break;
                    case "VALARM":
                        if ($alarm = $this->vAlarmObjectToAlarm($property)) {
                            $alarms[] = $alarm;
                        }
                        break;
                }
            }

            // events created by Evolution have event uid in the file name, let's strip it
            foreach ($attachments as $key => $val) {
                if (str_starts_with($val['name'], $data['uid'])) {
                    $attachments[$key]['name'] = substr($val['name'], strlen($data['uid']) + 1);
                }
            }

            // if there's no repeat rule, create a default array; if there is one, decode it we're keeping the original
            // rrule string (repeat_rule_orig), so we can save it in the db as is (not all the rules can be converted
            // and represented in the UI, so we don't want to lose any data that's not supported by the UI)
            if (empty($data['repeat_rule_orig'])) {
                $data['repeat_rule'] = EventData::decodeRRule("");
            } else {
                $data['repeat_rule'] = EventData::decodeRRule(
                    $data['repeat_rule_orig'],
                    empty($data['start']) ? false : $data['start']
                );
            }

            empty($attendees) || ($data['attendees'] = $attendees);
            empty($attachments) || $data['attachments'] = $attachments;
            empty($excluded) || $data['excluded'] = $excluded;
            empty($alarms) || ($data['alarms'] = $alarms);

            // make sure the attendees notify property is set and defaults to 1
            foreach ($data['attendees'] as $key => $val) {
                if (!isset($val['notify'])) {
                    $data['attendees'][$key]['notify'] = 1;
                }
            }

            $result[] = $data;
        }

        return $result;
    }

    private function vAlarmObjectToAlarm($valarm): bool|array
    {
        $result = [
            "alarm_type" => "0",
            "alarm_number" => "10",
            "alarm_units" => "minutes",
            "alarm_position" => "0",
            "absolute_datetime" => null,
        ];

        try {
            // set the alarm type
            if ($valarm->ACTION->getValue() == "EMAIL") {
                $result['alarm_type'] = "1";
            }

            // get the trigger value
            if (empty($valarm->TRIGGER) || !($triggerValue = $valarm->TRIGGER->getValue())) {
                return false;
            }

            // get the trigger type (this is not always included, but if it's not included its DURATION)
            $triggerType = "DURATION";
            if (isset($valarm->TRIGGER->parameters['VALUE']) &&
                ($v = $valarm->TRIGGER->parameters['VALUE']->getValue())
            ) {
                $triggerType = $v;
            }

            // the alarm has absolute time
            if ($triggerType == "DATE-TIME") {
                $result['alarm_position'] = ALARM_ABSOLUTE_TIME;
                $tzConvertFrom = "UTC";

                // if timezone is specified, convert from that timezone to the current user timezone, if not, we'll be
                // converting from UTC
                if (!str_ends_with($triggerValue, "Z") &&
                    isset($valarm->TRIGGER->parameters['TZID']) &&
                    ($tz = $valarm->TRIGGER->parameters['TZID']->getValue()) &&
                    Timezone::validTimezoneString($tz)
                ) {
                    $tzConvertFrom = $this->timezoneString;
                }

                $dt = Timezone::convertToTimezone($triggerValue, $tzConvertFrom, $this->timezoneString, true);
                $result['absolute_datetime'] = $dt->format("Y-m-d H:i:s");

                return $result;
            }

            if ($triggerType == "DURATION") {
                $startEnd = "START";

                if (isset($valarm->TRIGGER->parameters['RELATED']) &&
                    ($v = $valarm->TRIGGER->parameters['RELATED']->getValue()) &&
                    $v == "END"
                ) {
                    $startEnd = "END";
                }

                if (str_starts_with($triggerValue, "-")) {
                    $result['alarm_position'] = $startEnd == "START" ? ALARM_BEFORE_START : ALARM_BEFORE_END;
                    $triggerValue = substr($triggerValue, 1);
                } else {
                    $result['alarm_position'] = $startEnd == "START" ? ALARM_AFTER_START : ALARM_AFTER_END;
                }

                $interval = \Sabre\VObject\DateTimeParser::parseDuration($triggerValue);
                $result['alarm_number'] = (string)round((new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp() / 60);

                return $result;
            }
        } catch (\Exception) {
        }

        return false;
    }

    public static function getICalProdId(): string
    {
        return "-//RoundcubePlus//XCalendar//Sabre//VObject//" . \Sabre\VObject\Version::VERSION . "//EN";
    }

    /**
     * Convert new line escaped characters to actual new lines and trim. (JEvents ical files include those.)
     *
     * @param string $string
     * @return string
     */
    public static function fixVEventString(string $string): string
    {
        return trim(str_replace("\\n","\n", $string));
    }

    /**
     * Checks if the event attachments are enabled in the config and if the directory specified in the config is
     * writable.
     *
     * @return bool
     */
    public static function areAttachmentsEnabled(): bool
    {
        return xrc()->config->get("xcalendar_enable_event_attachments", false) && is_writable(self::getAttachmentDir());
    }

    public static function getMaxAttachmentSize()
    {
        return xrc()->config->get("xcalendar_max_attachment_size", 1000000);
    }

    /**
     * Returns the attachment directory as specified in the config.
     *
     * @return string
     */
    public static function getAttachmentDir(): string
    {
        return Utils::addSlash(xrc()->config->get("xcalendar_attachment_dir", RCUBE_INSTALL_PATH . "data/xcalendar"));
    }

    /**
     * @param $object
     * @param $index
     * @return array|bool
     */
    public function decodeImportAttachments($object, $index): bool|array
    {
        if (!$this->rcmail->config->get("xcalendar_enable_event_attachments", false)) {
            return false;
        }

        $class = get_class($object);
        $name = false;
        $path = false;
        $size = false;

        // get name (try different options)
        $nameKeys = [
            'FILENAME',
            'X-LABEL',
            'X-ORACLE-FILENAME',
            'X-EVOLUTION-CALDAV-ATTACHMENT-NAME',
        ];

        foreach ($nameKeys as $val) {
            if (isset($object->parameters[$val])) {
                $name = $object->parameters[$val]->getValue();
                break;
            }
        }

        if (empty($name)) {
            $name =  "attachment_" . $index;
        }

        // attachment is a url link to file
        if ($class == "Sabre\VObject\Property\Uri") {
            $path = $object->getValue();

            if (isset($object->parameters["X-ROUNDCUBEPLUS-ATTACHMENT-SIZE"])) {
                $size = $object->parameters["X-ROUNDCUBEPLUS-ATTACHMENT-SIZE"]->getValue();
            }
        }

        // attachment is an encoded binary file, let's save it to the attachment directory
        if ($class == "Sabre\VObject\Property\Binary") {
            // get and check attachment directory
            $attachmentDir = $this->getAttachmentDir();

            if (!is_writable($attachmentDir)) {
                Utils::logError("The attachment directory is not writable (27844)");
                return false;
            }

            // make the target directory
            $dir = Utils::structuredDirectory($this->userId) . Utils::encodeId($this->userId) . "/";

            if (!file_exists($attachmentDir . $dir) && !mkdir($attachmentDir . $dir, 0777, true)) {
                Utils::logError("Cannot create the attachment directory (27845)");
                return false;
            }

            $fileName = Utils::uniqueFileName($attachmentDir . $dir);
            $content = $object->getValue();

            // save file
            if (!file_put_contents($attachmentDir . $dir . $fileName, $content)) {
                Utils::logError("Cannot save attachment (27846)");
                return false;
            }

            $path = $dir . $fileName;
            $size = strlen($content);
        }

        return ["path" => $path, "name" => $name, "size" => $size];
    }

    /**
     * Returns an array of all the email addresses that belong to the current user (login username + all identity
     * emails.)
     *
     * @param bool $prependMailto
     * @return array
     */
    public static function getCurrentUserEmails(bool $prependMailto = true): array
    {
        $rcmail = xrc();
        $emails = [];

        if (isset($rcmail->username) && filter_var($rcmail->username, FILTER_VALIDATE_EMAIL)) {
            $emails[] = ($prependMailto ? "mailto:" : "") . $rcmail->username;
        }

        foreach ($rcmail->user->list_identities() as $identity) {
            $emails[] = ($prependMailto ? "mailto:" : "") . $identity['email'];
        }

        return $emails;
    }

    /**
     * Returns an array of email addresses that belong to the specified user--taken from the username and identities.
     *
     * @param $userId
     * @return array
     */
    public static function getUserEmails($userId): array
    {
        $emails = [];

        if (!($username = xdb()->value("username", "users", ["user_id" => $userId]))) {
            return [];
        }

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $emails[] = $username;
        }

        if ($identities = xdb()->all("SELECT email FROM {identities} WHERE user_id = ?", [$userId])) {
            foreach ($identities as $identity) {
                if (filter_var($identity['email'], FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $identity['email'];
                }
            }
        }

        return $emails;
    }

    /**
     * Checks all the emails of the current user against the attendee list / organizer in the ics file and returns the
     * one that has been found.
     *
     * @param $ics
     * @return bool|mixed
     */
    public static function findCurrentUserEmailInIcs($ics): mixed
    {
        try {
            if (!($doc = Reader::read($ics))) {
                throw new \Exception("Cannot parse ics (44883).");
            }

            if (empty($emails = self::getCurrentUserEmails())) {
                throw new \Exception("Empty user email list (39200).");
            }

            // check the organizer email
            $organizer = empty($doc->VEVENT->ORGANIZER) ? "" : $doc->VEVENT->ORGANIZER->getValue();

            foreach ($emails as $email) {
                if (!empty($organizer) && $email == $organizer) {
                    return $email;
                }
            }

            // check the attendees emails
            if (!empty($doc->VEVENT->ATTENDEE)) {
                foreach ($doc->VEVENT->ATTENDEE as $attendee) {
                    $attendeeEmailValue = $attendee->getValue();

                    foreach ($emails as $email) {
                        if ($attendeeEmailValue == $email) {
                            return $email;
                        }
                    }
                }
            }
        } catch (\Exception) {}

        return false;
    }

    /**
     * Parses the ics file and checks if the email is in the attendee list, returns true if it does, false if it doesn't
     * exist.
     *
     * @param string $email
     * @param string $ics
     * @return bool
     */
    public static function emailExistsInIcsAttendees(string $email, string $ics): bool
    {
        if (!str_starts_with($email, "mailto:")) {
            $email = "mailto:$email";
        }

        try {
            if (($doc = Reader::read($ics)) && !empty($doc->VEVENT->ATTENDEE)) {
                foreach ($doc->VEVENT->ATTENDEE as $attendee) {
                    if ($attendee->getValue() == $email) {
                        return true;
                    }
                }
            }
        } catch (\Exception) {}

        return false;
    }

    public static function currentUserIsOrganizer(string $ics): bool
    {
        try {
            $vcalendar = Reader::read(
                self::wrapInVCalendar($ics),
                Reader::OPTION_FORGIVING | Reader::OPTION_IGNORE_INVALID_LINES
            );

            return !empty($vcalendar->VEVENT->ORGANIZER) && in_array(
                str_replace("mailto:", "", $vcalendar->VEVENT->ORGANIZER->getValue()),
                Event::getCurrentUserEmails(false),
                true
            );
        } catch (\Exception) {
            return false;
        }
    }

    public function searchEvents($text, $selectedCalendarIds, $startDateString, $endDateString, $page): array
    {
        try {
            if (empty($text = trim($text)) || empty($selectedCalendarIds) || !is_array($selectedCalendarIds)) {
                throw new \Exception();
            }

            // get a list of calendar ids to search in from the calendar data passed via ajax
            $calendarIds = [];
            foreach ($this->getCalendarList(Calendar::LOCAL, true, true) as $val) {
                if (in_array($val['id'], $selectedCalendarIds) && !empty($val['permissions']->see_details)) {
                    $calendarIds[] = $val['id'];
                }
            }

            // check start/end
            try {
                $today = new \DateTime();
                $startDate = new \DateTime($startDateString ?: "");
                $endDate = new \DateTime($endDateString ?: "+6 months");

                if ($rangeYearLimit = (int)$this->rcmail->config->get("xcalendar_search_range_year_limit", 1)) {
                    if ($rangeYearLimit < 0) {
                        $rangeYearLimit = 1;
                    }

                    if ($today->diff($startDate)->days > $rangeYearLimit * 365 ||
                        $today->diff($endDate)->days > $rangeYearLimit * 365
                    ) {
                        throw new \Exception();
                    }
                }
            } catch (\Exception) {
                $startDate = new \DateTime();
                $endDate = new \DateTime("+6 months");
            }

            // prepare the query placeholders and parameters
            $textColumns = ["title", "location", "description"];
            $textParameters = array_fill(0, count($textColumns), "%$text%");
            $textPlaceholders = [];

            foreach ($textColumns as $column) {
                $textPlaceholders[] = "e.$column LIKE ?";
            }

            $textPlaceholders = implode(" OR ", $textPlaceholders);
            $calendarPlaceholders = implode(',', array_fill(0, count($calendarIds), '?'));

            $pageSize = 50;
            $page = (int)$page > 0 ? (int)$page : 1;
            $offset = ($page - 1) * $pageSize;

            $parameters = array_merge(
                $calendarIds,
                [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')],
                $textParameters
            );

            $baseQuery = "FROM {xcalendar_events} e
                LEFT JOIN {xcalendar_calendars} c ON e.calendar_id = c.id
                WHERE e.calendar_id IN ($calendarPlaceholders) AND (start >= ? AND `end` <= ?) AND e.removed_at IS NULL 
                AND c.removed_at IS NULL AND ($textPlaceholders)";

            // get the total record count, so we can set up the paging
            $countResult = $this->db->fetch("SELECT COUNT(*) AS cnt $baseQuery", $parameters);

            // get the result for the current page
            $data = $this->db->all(
                "SELECT e.id, start, `end`, all_day, title, calendar_id, c.user_id, c.name AS calendar_name
                $baseQuery ORDER BY start LIMIT $pageSize OFFSET $offset",
                $parameters
            );

            if (empty($data)) {
                throw new \Exception();
            }

            // get the list of shared calendars in the search, so we can use their names instead of the original owner's
            // names
            $sharedNames = [];
            $sharedParameters = [];
            foreach ($data as $val) {
                if ($val['user_id'] != $this->userId && !in_array($val['calendar_id'], $sharedParameters)) {
                    $sharedParameters[] = $val['calendar_id'];
                }
            }

            if (!empty($sharedParameters)) {
                $sharedPlaceholders = implode(',', array_fill(0, count($sharedParameters), '?'));
                $sharedParameters[] = $this->userEmail;

                $shared = $this->db->all(
                    "SELECT calendar_id, name FROM {xcalendar_calendars_shared}
                    WHERE calendar_id IN ($sharedPlaceholders) AND email = ? AND added = 1",
                    $sharedParameters
                );

                foreach ($shared as $val) {
                    $sharedNames[$val['calendar_id']] = $val['name'];
                }
            }

            // final tweaks on the results
            foreach ($data as $key => $val) {
                // make sure we display the shared calendar name (the name the user gave his/her shared calendar)
                // instead of the original calendar name as set by calendar owner
                if (array_key_exists($val['calendar_id'], $sharedNames)) {
                    $data[$key]['calendar_name'] = $sharedNames[$val['calendar_id']];
                }

                // show date for all-day events, and date/time for events with time
                $data[$key]['start'] = $val['all_day'] == "0" ?
                    $this->format->formatDateTime($val['start']) : $this->format->formatDate($val['start']);
                $data[$key]['end'] = $val['all_day'] == "0" ?
                    $this->format->formatDateTime($val['end']) : $this->format->formatDate($val['end']);
            }

            return [
                "totalPages" => ceil($countResult['cnt'] / $pageSize),
                "page" => $page,
                "data" => $data,
                "startDate" => date($this->format->getDateFormat(), $startDate->getTimestamp()),
                "endDate" => date($this->format->getDateFormat(), $endDate->getTimestamp()),
                "title" => $this->rcmail->gettext([
                    "name" => "xcalendar.search_results",
                    "vars" => ["n" => $countResult['cnt']]
                ])
            ];
        } catch (\Exception) {
            return [
                "totalPages" => 0,
                "page" => 0,
                "data" => [],
                "startDate" => "",
                "endDate" => "",
                "title" => ""
            ];
        }
    }
}