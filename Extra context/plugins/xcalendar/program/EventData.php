<?php
namespace XCalendar;

use XFramework\Utils;
use XFramework\Format;
use XFramework\DatabaseGeneric;

/**
 * Class for manipulating a single calendar event data.
 */
class EventData
{
    private array $data;
    private \rcube $rcmail;
    private DatabaseGeneric $db;
    private Format $format;
    private ?int $userId;
    private ?string $userEmail;
    private string $timezoneString;
    private Permission $permission;

    public function __construct()
    {
        $this->db = xdb();
        $this->rcmail = xrc();
        $this->format = xformat();
        $this->permission = new Permission();
        $this->userId = $this->rcmail->get_user_id();
        $this->userEmail = $this->rcmail->get_user_email();
        $this->timezoneString = (string)$this->rcmail->config->get("timezone", "UTC");
        $this->data = $this->getEmptyData();
    }

    /**
     * Loads the data into the object from the database. $param can be the id of the object or an array of query
     * parameters that retrieve the event from the xcalendar_events table.
     *
     * @param $param
     * @param bool $allowRemoved
     * @return bool
     */
    public function loadFromDb($param, bool $allowRemoved = false): bool
    {
        if (!is_array($param)) {
            if (is_numeric($param)) {
                $param = ["id" => $param];
            } else {
                return false;
            }
        }

        if (!$allowRemoved) {
            $param['removed_at'] = NULL;
        }

        // load event data
        if (!($data = $this->db->row("xcalendar_events", $param))) {
            return false;
        }

        // load the calendar this event belongs to
        if (empty($calendarData = CalendarData::load($data['calendar_id']))) {
            return false;
        }

        // decode attachments
        $data['attachments'] = $data['attachments'] ? json_decode($data['attachments'], true) : [];

        // decode repeat rule
        if (!is_array($data['repeat_rule'])) {
            $data['repeat_rule'] = $this->decodeRRule($data['repeat_rule'], $data['start']);
        }

        // load alarms
        $data['alarms'] = [];

        if ($records = $this->db->all("SELECT * FROM {xcalendar_alarms} WHERE event_id = ?", [$data['id']])) {
            // postgres returns numerical values as int while mysql returns strings; if we send them to the frontend as
            // int, they won't get selected in the <select> element -- need to convert them to strings
            foreach ($records as $record) {
                $data['alarms'][] = [
                    "alarm_number" => $record['alarm_number'],
                    "alarm_units" => $record['alarm_units'],
                    "alarm_position" => (string)$record['alarm_position'],
                    "absolute_datetime" => $record['absolute_datetime'],
                    "alarm_type" => (string)$record['alarm_type'],
                ];
            }
        }

        // load attendees
        $data['attendees'] = [];

        if ($records = $this->db->all("SELECT * FROM {xcalendar_attendees} WHERE event_id = ?", [$data['id']])) {
            foreach ($records as $record) {
                if (!array_key_exists($record['email'], $data['attendees'])) {
                    $data['attendees'][] = [
                        "user_id" => $record['user_id'],
                        "email" => $record['email'],
                        "name" => $record['name'],
                        "organizer" => (int)$record['organizer'],
                        "role" => (string)$record['role'],
                        "notify" => (bool)$record['notify'],
                        "status" => (string)$record['status'],
                    ];
                }
            }
        }

        // load excluded events (events removed from the recurring sequence)
        $data['excluded'] = [];

        if ($records = $this->db->all("SELECT * FROM {xcalendar_events_removed} WHERE event_id = ?", [$data['id']])) {
            foreach ($records as $record) {
                $day = substr($record['day'], 0, 10);

                if (!in_array($day, $data['excluded'])) {
                    $data['excluded'][] = $day;
                }
            }
        }

        // load custom colors
        if ($calendarData->getUserId() != $this->userId) {
            if ($record = $this->db->row("xcalendar_events_custom", ['user_id' => $this->userId, 'event_id' => $data['id']])) {
                $data['custom'] = $record;
            }
        }

        $this->data = $data;

        // if timezones are empty (the event was created by an older version of xcalendar that didn't support timezones)
        // load the timezones from vevent (ics string)
        if (empty($this->data['timezone_start']) || empty($data['timezone_end'])) {
            Timezone::extractZonesFromVEvent(
                $data['vevent'],
                $this->timezoneString,
                $this->data['timezone_start'],
                $this->data['timezone_end']
            );

            try {
                $this->saveToDb();
            } catch (\Exception) {
                return false;
            }
        }

        return true;
    }

    public function loadFromVCalendar(string $vcalendar, $calendarId = false, string $href = "",
                                      string $etag = ""): bool
    {
        if (empty($vcalendar)) {
            return false;
        }

        $array = (new Event())->vEventToDataArray($vcalendar);
        if (empty($array[0])) {
            return false;
        }

        $this->importData($array[0]);

        // if calendar id specified, make sure the user can write to it
        if ($calendarId) {
            $calendarData = CalendarData::load($calendarId);
            if ($calendarData->isWritable()) {
                $this->setValue("calendar_id", $calendarId);
            }
        }
        $this->setValue("href", $href);
        $this->setValue("etag", $etag);

        return true;
    }

    /**
     * @throws \Exception
     */
    public function save(?CalendarData $calendarData = null): void
    {
        if (empty($calendarData) && empty($calendarData = CalendarData::load($this->data['calendar_id']))) {
            throw new \Exception("Cannot load calendar (173382)");
        }

        if ($calendarData->getType() == Calendar::CALDAV) {
            ClientCaldav::saveEvent($calendarData->getId(), $this->data);
        } else {
            $this->saveToDb($calendarData);
        }
    }

    /**
     * @throws \Exception
     */
    public function saveToDb(?CalendarData $calendarData = null): void
    {
        if (empty($calendarData) && empty($calendarData = CalendarData::load($this->data['calendar_id']))) {
            throw new \Exception("Cannot save event: unable to load calendar (381992)");
        }

        // make a copy of the data to work on
        $data = $this->data;
        $this->db->beginTransaction();

        try {
            // fill the possible missing values
            empty($data['calendar_id']) && ($data['calendar_id'] = $calendarData->getId());
            empty($data['user_id']) && ($data['user_id'] = $this->userId);

            // encode the attachments data
            $data['attachments'] = $data['attachments'] ? json_encode($data['attachments']) : "";

            // encode repeat rule
            if (is_array($data['repeat_rule'])) {
                $data['repeat_end'] = $this->createRepeatEnd($data['repeat_rule'], $data['start']);

                // if the original rrule has been preserved (for example, as coming in from caldav) save the original
                // rule
                if (empty($data['repeat_rule_orig'])) {
                    $data['repeat_rule'] = $this->encodeRRule($data['repeat_rule'], true);
                } else {
                    $data['repeat_rule'] = $data['repeat_rule_orig'];
                    unset($data['repeat_rule_orig']);
                }
            }

            // remove the created at if it doesn't exist (it'll be auto-created, but we can't pass null, or we'll get
            // errors)
            if (empty($data['created_at'])) {
                unset($data['created_at']);
            }

            // if new event, save it to get the event id
            if (empty($data['id'])) {
                if (!$this->db->insert("xcalendar_events", $this->getSavableEventData($data)) ||
                    !($data['id'] = $this->db->lastInsertId("xcalendar_events"))
                ) {
                    throw new \Exception();
                }
            }

            // save the alarms
            $this->saveAlarms($data);

            // save attendees
            $this->saveAttendees($data);

            // save excluded
            $this->saveExcluded($data);

            // save custom colors (on duplicate key update is not supported on sqlite, deleting and re-creating)
            if (!empty($data['custom'])) {
                $this->db->remove(
                    "xcalendar_events_custom",
                    ["user_id" => $this->userId, "event_id" => $data['id']]
                );
                $this->db->insert(
                    "xcalendar_events_custom",
                    [
                        "user_id" => $this->userId,
                        "event_id" => $data['id'],
                        "use_calendar_colors" => $data['use_calendar_colors'],
                        "bg_color" => $data['bg_color'],
                        "tx_color" => $data['tx_color'],
                    ]
                );
            }

            // generate uid if it doesn't exist
            if (empty($data['uid'])) {
                $data['uid'] = $data['vevent_uid'] = Utils::uuid();
            }

            if (empty($data['vevent_uid'])) {
                $data['vevent_uid'] = $data['uid'];
            }

            // encode the event data to vevent text
            $data['vevent'] = $this->createVEvent($data);

            // save event
            if (!$this->db->update("xcalendar_events", $this->getSavableEventData($data), ["id" => $data['id']])) {
                throw new \Exception();
            }

            $this->db->commit();

            // update the properties that could have changed during the save process in the object's data
            $this->data['id'] = $data['id'];
            $this->data['uid'] = $data['uid'];
            $this->data['vevent_uid'] = $data['vevent_uid'];
            $this->data['has_attendees'] = $data['has_attendees'];
            $this->data['repeat_end'] = $data['repeat_end'];
            $this->data['vevent'] = $data['vevent'];
            $this->data['excluded'] = $data['excluded'];
        } catch (\Exception) {
            $this->db->rollBack();
            throw new \Exception("Cannot save event (193449)");
        }
    }

    public function ensureVEvent(): bool|string
    {
        if (empty($this->data['vevent'])) {
            try {
                $this->data['vevent'] = $this->createVEvent($this->getData());
            } catch (\Exception) {
                return false;
            }

            if ($this->data['id'] &&
                !$this->db->update(
                    "xcalendar_events",
                    ['vevent' => $this->data['vevent']],
                    ["id" => $this->data['id']]
                )
            ) {
                return false;
            }
        }

        return $this->data['vevent'];
    }

    public function getValue($field)
    {
        return $this->data[$field] ?? null;
    }

    public function setValue($field, $value)
    {
        return $this->data[$field] = $value;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function importData(array $data): void
    {
        foreach ($data as $key => $val) {
            // prevent overwriting of the core values
            if (!in_array($key, ["id", "user_id", "calendar_id"])) {
                $this->data[$key] = $val;
            }
        }
    }

    public function getDataForEditing(bool $duplicate = false, string &$error = ""): bool|array
    {
        // get the calendar record to which this event belongs
        if (!($calendar = $this->db->fetch(
            "SELECT {users}.user_id, username FROM {xcalendar_calendars} ".
            "LEFT JOIN {users} USING (user_id) WHERE id = ? LIMIT 1",
            $this->data['calendar_id']
        ))) {
            $error = "Invalid calendar (3049920)";
            return false;
        }

        // user must own the event in order to edit it
        if ($this->data['user_id'] != $this->userId) {
            if (!$this->hasPermission($this->data['calendar_id'], "edit_events")) {
                $error = "No permission to edit event (291003)";
                return false;
            }
        }

        // copy the data and make sure the values for the select boxes are in string format (mysql returns them as
        // strings, postgres as int)
        $data = $this->data;
        $data['calendar_id'] = (string)$data['calendar_id'];
        $data['busy'] = (string)$data['busy'];
        $data['priority'] = (string)$data['priority'];

        // add / convert the values
        $start = strtotime($data['start']);
        $end = strtotime($data['end']);
        $dateFormat = $this->format->getDateFormat();
        $data['start_date'] = date($dateFormat, $start);
        $data['end_date'] = date($dateFormat, $end);
        $data['start_time'] = $data['all_day'] ? "12:00:00" : date("H:i:s", $start);
        $data['end_time'] = $data['all_day'] ? "13:00:00" : date("H:i:s", $end);
        $data['owner'] = $calendar['user_id'] == $this->userId;
        $data['ownerEmail'] = $calendar['username'];

        // decode the repeat rule and add intervals
        $data['repeat'] = $data['repeat_rule'];

        // if category empty, set it to 'No category' - when saving this will be changed back to empty if 'No category'
        // is still selected
        if (empty($data['category']) || !$this->isCategoryNameValid($data['category'])) {
            $data['category'] = $this->rcmail->gettext("xcalendar.no_category");
        }

        $this->db->fix($data, BOOL, ["all_day", "use_calendar_colors"]);

        if (!$data['owner']) {
            // if not owner, don't send these values (the controls are hidden, but we don't want to expose the values)
            $data['busy'] = false;
            $data['visibility'] = "default";

            // get the customized properties
            if ($data['id'] &&
                ($custom = $this->db->row("xcalendar_events_custom", ["user_id" => $this->userId, "event_id" => $data['id']]))
            ) {
                $data['use_calendar_colors'] = $custom['use_calendar_colors'];
                $data['bg_color'] = $custom['bg_color'];
                $data['tx_color'] = $custom['tx_color'];
            }
        }

        empty($data['attachments']) && ($data['attachments'] = []);
        empty($data['timezone_start']) && ($data['timezone_start'] = $this->timezoneString);
        empty($data['timezone_end']) && ($data['timezone_end'] = $this->timezoneString);

        if ($duplicate) {
            $data['id'] = false;
            $data['uid'] = Utils::uuid();
            $data['href'] = '';
            $data['vevent'] = '';
            $data['vevent_uid'] = '';
            $suffix = $this->rcmail->gettext('xcalendar.duplicate_suffix');
            if (!str_ends_with($data['title'], $suffix)) {
                $data['title'] .= ' ' . $suffix;
            }
        }

        return $data;
    }

    /**
     * @throws \Exception
     */
    public function getDataForPreview(): array
    {
        // get the calendar this event belongs to
        if (!($calendar = $this->db->row("xcalendar_calendars", ["id" => $this->data['calendar_id']]))) {
            throw new \Exception("Invalid calendar (482983)");
        }

        // check if the owner or shared calendar
        $owner = $this->data['user_id'] == $this->userId;

        if ($owner) {
            $canView = true;
            $canEdit = true;
            $calendarName = $calendar['name'];
        } else {
            if (!($sharedCalendar = $this->getSharedCalendarData($this->data['calendar_id']))) {
                throw new \Exception("Invalid shared calendar (498924)");
            }

            $canView = !empty($sharedCalendar['permissions']['see_details']);
            $canEdit = !empty($sharedCalendar['permissions']['edit_events']);
            $calendarName = $sharedCalendar['name'];
        }

        $format = $this->format->getDateFormat() . ($this->data['all_day'] ? "" : " " . $this->format->getTimeFormat());

        $data = [
            "id" => $this->data['id'],
            "title" => $canView ? $this->data['title'] :
                "[" . $this->rcmail->gettext("xcalendar." . ($this->data['busy'] ? "busy" : "available")) . "]",
            "location" => $canView ? trim($this->data['location']) : "",
            "description" => $canView ? self::getDescriptionForPreview($this->data['description']) : "",
            "url" => $canView ? trim($this->data['url']) : "",
            "calendar_id" => $this->data['calendar_id'],
            "calendar_type" => Calendar::LOCAL,
            "calendar_name" => $calendarName,
            "all_day" => (int)$this->data['all_day'],
            "start" => date($format, strtotime($this->data['start'])),
            "end" => date($format, strtotime($this->data['end'])),
            "has_attendees" => false,
            "attendance" => [0, 0, 0, 0, 0],
            "attendance_status" => 0,
            "show_attendance_response" => false,
            "can_edit" => $canEdit,
            "repeat" => $this->data['repeat_rule'],
            "error" => false,
        ];

        // add timezone info if different from the currently set timezone
        if (!$data['all_day']) {
            if (!empty($this->data["timezone_start"]) && $this->data["timezone_start"] != $this->timezoneString) {
                $data['start'] .= " (" . Timezone::getTimezoneLabel($this->data["timezone_start"]) . ")";
            }

            if ($this->data["timezone_end"] && $this->data["timezone_end"] != $this->timezoneString) {
                $data['end'] .= " (" . Timezone::getTimezoneLabel($this->data["timezone_end"]) . ")";
            }
        }

        // get attendee data
        if ($canView && !empty($this->data['attendees']) && is_array($this->data['attendees'])) {
            $userEmails = Utils::getUserEmails();

            foreach ($this->data['attendees'] as $attendee) {
                $data['has_attendees'] = true;
                $data['attendance'][$attendee['status'] ?? 0]++;

                if (in_array($attendee['email'], $userEmails) && !$data['show_attendance_response']) {
                    $data['show_attendance_response'] = true;
                    $data['attendance_status'] = (int)$attendee['status'];
                }
            }
        }

        return $data;
    }

    /**
     * @throws \Exception
     */
    public function saveDropData(array $data): void
    {
        if (empty($data['id']) || empty($data['start']) || empty($data['end'])) {
            throw new \Exception();
        }

        if ($data['type'] == Calendar::LOCAL) {
            if (!$this->loadFromDb($data['id'])) {
                throw new \Exception();
            }
        } else if ($data['type'] == Calendar::CALDAV) {
            if (empty($data['vcalendar']) ||
                empty($data['calendar_id']) ||
                !$this->loadFromVCalendar($data['vcalendar'], $data['calendar_id'], $data['href'], $data['etag'])
            ) {
                throw new \Exception();
            }
        } else {
            throw new \Exception();
        }

        if (!$this->permission->hasCalendarPermission(
            $this->getValue("calendar_id"),
            "edit_events",
            $this->userId,
            $this->userEmail
        )) {
            throw new \Exception("Cannot modify event: permission denied.");
        }

        $vevent = $this->createVEvent($this->data);
        $allDay = $data['all_day'] ?? 1;
        $this->setValue("all_day", $allDay);

        // all day events don't use timezone information, so we just assign the new start and end
        if ($allDay) {
            $this->setValue("start", $data['start']);
            $this->setValue("end", $data['end']);
        } else {
            // When we send events to the frontend we recalculate their start and end times from the event timezone to
            // the user timezone (as specified in the settings.) We do this, so we can show the events in the calendar
            // grid in the user's timezone. This means that when the event is dropped, the start/end we get in the $data
            // will be in the user timezone and not the original event timezone. We need to re-calculate it here back to
            // the event timezone before setting and saving it, otherwise every time you drop the event, its time will
            // change. (convertToTimezone does nothing if the timezones are the same.)
            $this->setValue(
                "start",
                Timezone::convertToTimezone($data['start'], $this->timezoneString, $this->getValue("timezone_start"))
            );
            $this->setValue(
                "end",
                Timezone::convertToTimezone($data['end'], $this->timezoneString, $this->getValue("timezone_end"))
            );
        }

        $this->save();

        // notify attendees if needed
        if ($this->getValue("has_attendees")) {
            Event::sendAttendeeEmailNotifications($vevent, $this->getValue("vevent"));
        }
    }

    /**
     * @throws \Exception
     */
    public function savePostData(array $data): void
    {
        if (empty($data['calendar_id'])) {
            throw new \Exception("Invalid calendar (4837728)");
        }

        if (empty($calendarData = CalendarData::load($data['calendar_id']))) {
            throw new \Exception("Cannot load calendar (3829912)");
        }

        // fill potentially missing values
        empty($data['timezone_start']) && ($data['timezone_start'] = $this->timezoneString);
        empty($data['timezone_end']) && ($data['timezone_end'] = $this->timezoneString);

        // check essential values
        if (!strtotime($data['start']) ||
            !strtotime($data['end']) ||
            !Timezone::validTimezoneString($data['timezone_start']) ||
            !Timezone::validTimezoneString($data['timezone_end'])
        ) {
            throw new \Exception("Invalid start/end/timezone (199403)");
        }

        // if not the owner, check if it's a shared calendar and if the user has the edit permission
        $owner = $calendarData->getUserId() == $this->userId;

        if (!$owner) {
            if (!($shared = $this->db->row(
                    "xcalendar_calendars_shared",
                    ["email" => $this->userEmail, "calendar_id" => $data['calendar_id'], "added" => "1"]
                )) ||
                !($permissions = json_decode($shared['permissions'], true)) ||
                !$permissions['edit_events']
            ) {
                throw new \Exception("No permission to save event (829912)");
            }
        }

        // If editing an existing event, authorize against the event's CURRENT calendar, not just
        // the calendar_id supplied in the request. Without this, a user who can edit calendar X
        // could overwrite ANY user's event by passing that event's id with calendar_id = X
        // (the save below is an UPDATE keyed only by id, with no ownership scoping).
        if (!empty($data['id'])) {
            if (!($existing = $this->db->row("xcalendar_events", ["id" => (int)$data['id'], "removed_at" => NULL]))) {
                throw new \Exception("Event not found (4837730)");
            }

            if (!Permission::hasCalendarPermission(
                $existing['calendar_id'],
                "edit_events",
                $this->userId,
                $this->userEmail
            )) {
                throw new \Exception("No permission to edit this event (4837731)");
            }

            // for non-owners, don't allow moving someone else's event into a different calendar
            if ($existing['user_id'] != $this->userId && (int)$data['calendar_id'] != (int)$existing['calendar_id']) {
                throw new \Exception("Cannot move this event to another calendar (4837732)");
            }
        }

        // if start/end doesn't have time, it's an all day event
        if (!isset($data['all_day'])) {
            $data['all_day'] = strlen($data['start']) <= 10 && strlen($data['end']) <= 10 ? 1 : 0;
        }

        // if start later than end, set end to start + 1 hour
        if (!$data['all_day'] && ($tm = strtotime($data['start'])) > strtotime($data['end'])) {
            $data['end'] = date("Y-m-d H:i:s", $tm + 3600);
        }

        // reset time for clarity's sake and timezone so the user gets a clean slate when changing the event to not-all
        // day in the future
        if ($data['all_day']) {
            $data['start'] =  date("Y-m-d 00:00:00", strtotime($data['start']));
            $data['end'] =  date("Y-m-d 00:00:00", strtotime($data['end']));
            $data['timezone_start'] = $this->timezoneString;
            $data['timezone_end'] = $this->timezoneString;
        }

        // reset category to empty if a category with the text 'No category' is selected
        if ($data['category'] == $this->rcmail->gettext("xcalendar.no_category")) {
            $data['category'] = "";
        }

        // set the data
        $this->data['id'] = $data['id'] ? (int)$data['id'] : false;
        $this->data['uid'] = empty($data['uid']) ? "" : $data['uid'];
        $this->data['href'] = empty($data['href']) ? "" : $data['href'];
        $this->data['etag'] = empty($data['etag']) ? "" : $data['etag'];
        $this->data['user_id'] = $calendarData->getUserId(); // events user_id should always be the user_id of the owner of the calendar
        $this->data['calendar_id'] = (int)$data['calendar_id'];
        $this->data['title'] = empty($data['title']) ? $this->rcmail->gettext("xcalendar.new_event") : (string)$data['title'];
        $this->data['location'] = empty($data['location']) ? "" : (string)$data['location'];
        $this->data['description'] = empty($data['description']) ? "" : (string)$data['description'];
        $this->data['url'] = empty($data['url']) ? "" : (string)$data['url'];
        $this->data['all_day'] = (int)$data['all_day'];
        $this->data['start'] = (string)$data['start'];
        $this->data['end'] = (string)$data['end'];
        $this->data['timezone_start'] = empty($data['timezone_start']) ? "" : (string)$data['timezone_start'];
        $this->data['timezone_end'] = empty($data['timezone_end']) ? "" : (string)$data['timezone_end'];
        $this->data['priority'] = empty($data['priority']) || $data['priority'] < 0 || $data['priority'] > 9 ? 0 : (int)$data['priority'];
        $this->data['category'] = empty($data['category']) ? "" : (string)$data['category'];
        $this->data['repeat_rule'] = !empty($data['repeat']) && is_array($data['repeat']) ? $data['repeat'] : [];
        $this->data['repeat_end'] = $this->createRepeatEnd($this->data['repeat_rule'], $data['start']);
        $this->data['attendees'] = !empty($data['attendees']) && is_array($data['attendees']) ? $data['attendees'] : [];
        $this->data['has_attendees'] = !empty($this->data['attendees']);
        $this->data['alarms'] = !empty($data['alarms']) && is_array($data['alarms']) ? $data['alarms'] : [];
        $this->data['excluded'] = !empty($data['excluded']) && is_array($data['excluded']) ? $data['excluded'] : [];
        $this->data['attachments'] = !empty($data['attachments']) && is_array($data['attachments']) ? $data['attachments'] : [];

        if ($owner) {
            $this->data['use_calendar_colors'] = (int)$data['use_calendar_colors'];
            $this->data['bg_color'] = (string)($data['bg_color'] ?: '');
            $this->data['tx_color'] = (string)($data['tx_color'] ?: '');
            $this->data['busy'] = (int)$data['busy'];
            $this->data['visibility'] = in_array($data['visibility'], ["default", "public", "private"]) ? $data['visibility'] : "default";
        } else {
            $this->data['custom'] = [
                'use_calendar_colors' => (int)$data['use_calendar_colors'],
                'bg_color' => (string)$data['bg_color'],
                'tx_color' => (string)$data['tx_color'],
            ];
        }

        // save event data
        $this->save($calendarData);

        // remove attachment references from xcalendar_attachments_temp (using $data because $this->data['attachments']
        // is encoded by now)
        if (!empty($data['attachments']) && is_array($data['attachments'])) {
            $paths = [];
            $markers = [];
            foreach ($data['attachments'] as $attachment) {
                if (!empty($attachment['path']) && ($path = Event::attachmentUrlToPath($attachment['path']))) {
                    $paths[] = $path;
                    $markers[] = "?";
                }
            }

            if (!empty($markers)) {
                $this->db->query(
                    "DELETE FROM {xcalendar_attachments_temp} WHERE filename IN (" . implode(",", $markers) . ")",
                    $paths
                );
            }
        }

        // perform attachment cleanup: remove attachments that don't belong to any event
        if (Event::areAttachmentsEnabled()) {
            $this->removeUnusedAttachments();
        }

        // send notification emails to attendees but only if the previous version of the event is empty (meaning it's a
        // new event) or the current user is the organizer -- we don't send anything if editing an event that's been
        // organized by someone else and this is just a copy of the original event (added by itip)
        // we're checking both previous and current version of vevent, because we want to react to both adding attendees
        // and removing them (sending cancellations)
        if (empty($data['vevent']) ||
            Event::currentUserIsOrganizer($data['vevent']) ||
            Event::currentUserIsOrganizer($this->data['vevent'])
        ) {
            Event::sendAttendeeEmailNotifications($data['vevent'] ?: null, $this->data['vevent']);
        }
    }

    /**
     * Updates the attendance status of the user by their email.
     *
     * @param $email
     * @param $status - string or integer representation of status, for example, ACCEPTED or 1
     * @return bool - True if status has been modified, false if it was the same and didn't need modification.
     */
    public function setAttendanceByEmail($email, $status): bool
    {
        $email = str_replace("mailto:", "", $email);
        $status = (string)(is_numeric($status) ? (int)$status : EventData::vStatusToStatus($status));
        $result = false;

        foreach ($this->data['attendees'] as $key => $val) {
            if ($val['email'] == $email && $val['status'] != $status) {
                $this->data['attendees'][$key]['status'] = $status;
                $result = true;
            }
        }

        return $result;
    }

    public function getEmptyData(): array
    {
        // load default calendar and store its reusable settings in xdata
        if (!xdata()->has("default_calendar_data")) {
            $calendarData = CalendarData::loadDefault();
            xdata()->set(
                "default_calendar_data",
                [
                    "id" => $calendarData->getId(),
                    "bg_color" => $calendarData->get("bg_color"),
                    "tx_color" => $calendarData->get("tx_color"),
                ]
            );
        }

        $defaultCalendar = xdata()->get("default_calendar_data");
        $alarms = [];
        $type = $this->rcmail->config->get('xcalendar_default_notification_type');

        if (in_array($type, ['popup', 'email'])) {
            $position = match ($this->rcmail->config->get('xcalendar_default_notification_position', 'before_start')) {
                'after_start' => ALARM_AFTER_START,
                'before_end' => ALARM_BEFORE_END,
                'after_end' => ALARM_AFTER_END,
                default => ALARM_BEFORE_START,
            };
            $units = $this->rcmail->config->get('xcalendar_default_notification_units', 'minutes');
            $number = $this->rcmail->config->get('xcalendar_default_notification_number', 10);

            $alarms[] = [
                "alarm_type" => (string)($type == "popup" ? ALARM_TYPE_POPUP : ALARM_TYPE_EMAIL),
                "alarm_position" => $position,
                "alarm_units" => in_array($units, ['minutes', 'hours', 'days', 'weeks']) ? $units : "minutes",
                "alarm_number" => $number >= 0 && $number < 60 ? $number : 10,
            ];
        }

        return [
            'id' => false,
            'user_id' => $this->userId,
            'calendar_id' => (string)$defaultCalendar['id'],
            'uid' => Utils::uuid(),
            'title' => $this->rcmail->gettext("xcalendar.new_event"),
            'location' => "",
            'description' => "",
            'url' => "",
            'start' => date("Y-m-d 00:00:00"),
            'timezone_start' => $this->timezoneString,
            'timezone_end' => $this->timezoneString,
            'end' => date("Y-m-d 00:00:00"),
            'all_day' => "0",
            'repeat_rule' => self::decodeRRule(""),
            'repeat_end' => NULL,
            'use_calendar_colors' => "1",
            'bg_color' => $defaultCalendar['bg_color'],
            'tx_color' => $defaultCalendar['tx_color'],
            'busy' => "1",
            'visibility' => "default",
            'priority' => "0",
            'category' => "",
            'attachments' => [],
            'has_attendees' => "0",
            'vevent' => "",
            'created_at' => NULL,
            'modified_at' => NULL,
            'removed_at' => NULL,
            'attendees' => [],
            'alarms' => $alarms,
            'excluded' => [],
            'custom' => [],
        ];
    }

    /**
     * Calculate the real date when this event will end.
     *
     * @param $rule
     * @param $start
     * @return false|mixed|string|null
     */
    protected function createRepeatEnd($rule, $start): mixed
    {
        // the event doesn't repeat, set the end to 0
        if (!is_array($rule) || empty($rule['freq']) || $rule['freq'] == "never") {
            return null;
        }

        // the event ends on a date, return the date (but the last minute of it, so we include that day as well)
        // careful: use format_>strToTimeWithFormat() instead of strtotime() to properly format the d/m/y dates.
        if ($rule['range'] == "until") {
            return date("Y-m-d 23:59:59", $this->format->stringToTimeWithFormat($rule['until']));
        }

        // the event repeats x times, calculate the final date
        if ($rule['range'] == "count") {
            try {
                $iterator = new \Sabre\VObject\Recur\RRuleIterator($this->encodeRRule($rule), new \DateTime($start));
                $end = new \DateTime($start);
                $end->add(new \DateInterval("P20Y"));
                $result = $start;

                do {
                    $iterator->next();
                    if ($current = $iterator->current()) {
                        $result = $current->format("Y-m-d H:i:s");
                    }
                } while ($current && $current <= $end);

                return $result;
            } catch (\Exception) {
            }
        }

        // the event repeats forever
        // mysql timestamp supports dates till 2038-01-19 03:14:07
        return date("Y-m-d 00:00:00", strtotime("2038-01-01"));
    }

    /**
     * Encodes the array of properties used to display the recurrence in the UI into the RRULE string.
     *
     * http://www.kanzaki.com/docs/ical/
     * http://sabre.io/vobject/recurrence/
     *
     * @param array $data
     * @param bool $includeTimeInUntil
     * @return string
     */
    public static function encodeRRule(array $data = [], bool $includeTimeInUntil = false): string
    {
        // if non-repeating: return empty string
        if (empty($data['freq']) || !in_array($data['freq'], ["DAILY", "WEEKLY", "MONTHLY", "YEARLY"])) {
            return "";
        }

        // set RRULE values
        $result = [
            "FREQ" => $data['freq'],
            "BYDAY" => [],
            "BYMONTHDAY" => [],
            "BYMONTH" => [],
        ];

        // only add interval if it's > 1; 1 is default and doesn't need to be in the RRULE string
        if (!empty($data['interval']) && self::validInterval($data['interval']) && $data['interval'] > 1) {
            $result['INTERVAL'] = (int)$data['interval'];
        }

        if ($data['freq'] == "WEEKLY" && !empty($data['weekly_byday']) && is_array($data['weekly_byday'])) {
            foreach ($data['weekly_byday'] as $key => $val) {
                if ($val && self::validDayOfWeek($key)) {
                    $result['BYDAY'][] = $key;
                }
            }
        }

        if (($data['freq'] == "MONTHLY" || $data['freq'] == "YEARLY") && !empty($data['bytype'])) {
            if ($data['bytype'] == "byday" && !empty($data['byday_prefix']) && !empty($data['byday_value'])) {
                if ($data['byday_value'] == "WEEKDAY") {
                    // using BYSETPOS to find the X day of the week
                    $result['BYDAY'] = ["MO", "TU", "WE", "TH", "FR"];
                    $result['BYSETPOS'] = $data['byday_prefix'];
                } else if ($data['byday_value'] == "WEEKEND_DAY") {
                    $result['BYDAY'] = ["SA", "SU"];
                    $result['BYSETPOS'] = $data['byday_prefix'];
                } else if ($data['byday_value'] == "DAY") {
                    $result['BYDAY'] = self::daysOfWeek();
                    $result['BYSETPOS'] = $data['byday_prefix'];
                } else {
                    // standard prefix + day of the week
                    $result['BYDAY'][] = $data['byday_prefix'] . $data['byday_value'];
                }
            } else if ($data['bytype'] == "bymonthday" && !empty($data['bymonthday']) && is_array($data['bymonthday'])) {
                foreach ($data['bymonthday'] as $key => $val) {
                    if ($val && self::validByMonthDayValue($key)) {
                        $result['BYMONTHDAY'][] = (int)$key;
                    }
                }
            }
        }

        if ($data['freq'] == "YEARLY" && !empty($data['bymonth']) && is_array($data['bymonth'])) {
            foreach ($data['bymonth'] as $key => $val) {
                if ($val && self::validByMonthValue($key)) {
                    $result['BYMONTH'][] = (int)$key;
                }
            }
        }

        // add the range values
        if (!empty($data['range'])) {
            if ($data['range'] == "count" &&
                !empty($data['count']) &&
                (int)$data['count'] >= 1 &&
                (int)$data['count'] <= 999
            ) {
                $result['COUNT'] =  (int)$data['count'];
            } else if ($data['range'] == "until" &&
                !empty($data['until']) &&
                ($dt = \DateTime::createFromFormat(xformat()->getDateFormat(), trim($data['until'])))
            ) {
                $result['UNTIL'] = $dt->format($includeTimeInUntil ? 'Ymd\\T235959' : 'Ymd');
            }
        }

        // create the RRULE string
        $result['BYDAY'] = implode(",", $result['BYDAY']);
        $result['BYMONTHDAY'] = implode(",", $result['BYMONTHDAY']);
        $result['BYMONTH'] = implode(",", $result['BYMONTH']);

        $array = [];
        foreach ($result as $key => $val) {
            if (!empty($val)) {
                $array[] = "$key=$val";
            }
        }

        return implode(";", $array);
    }

    /**
     * Decodes the RRULE string and populates the array used to display the recurrence in the UI. This function doesn't
     * support all the possible RRULE combinations, because the UI would get too confusing for the user. Instead, we
     * support the most common settings, like all the other calendar programs out there (in fact, we support more
     * options than most.) The unsupported parts of the original RRULE string simply get lost when saved.
     *
     * https://www.rfc-editor.org/rfc/rfc5545
     * https://icalendar.org/rrule-tool.html
     *
     * @param $rule
     * @param $eventStart
     * @return array
     */
    public static function decodeRRule($rule, $eventStart = ""): array
    {
        if (is_array($rule)) {
            return $rule;
        }

        $rcmail = xrc();
        $format = xformat();
        $data = [];

        // explode the RRULE string into key/value pairs and fix values
        foreach (explode(';', $rule) as $item) {
            $parts = explode("=", $item, 2);
            if (count($parts) === 2) {
                $data[strtoupper($parts[0])] = $parts[1];
            }
        }

        if (empty($data['FREQ']) || !in_array($data['FREQ'], ["DAILY", "WEEKLY", "MONTHLY", "YEARLY"])) {
            $data['FREQ'] = "";
        }

        if (empty($data['INTERVAL']) || !self::validInterval($data['INTERVAL'])) {
            $data['INTERVAL'] = 1;
        }

        // Thunderbird allows daily + "on weekdays" and saves it as FREQ=DAILY;BYDAY=MO,TU,WE,TH,FR which technically isn't correct.
        // Outlook also allows daily + "on weekdays", but saves it as FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR--we're converting it to that
        // format, so we can display it properly in the UI.
        if ($data['FREQ'] == "DAILY" && !empty($data['BYDAY'])) {
            $data['FREQ'] = "WEEKLY";
        }

        // create the result array
        $result = [
            "freq" => $data['FREQ'],
            "interval" => (int)$data['INTERVAL'],
            "range" => "forever", // forever, count, until
            "until" => date($format->getDateFormat(), strtotime("+1 year")),
            "count" => 1,
            "weekly_byday" => array_fill_keys(self::daysOfWeek(), false), // ["MO" => false, "TU" => false, ...]
            "bytype" => empty($data['BYMONTHDAY']) ? "byday" : "bymonthday",
            "byday_prefix" => "",
            "byday_value" => "MO",
            "bymonthday" => array_fill_keys(self::byMonthDayValues(), false), // [-1=> false, 1 => false, 2 => false, ...]
            "bymonth" => array_fill_keys(self::byMonthValues(), false), // [1 => false, 2 => false, ...]
        ];

        if ($result['freq'] == "WEEKLY" && !empty($data['BYDAY'])) {
            foreach (explode(",", $data['BYDAY']) as $val) {
                if (self::validDayOfWeek($val)) {
                    $result['weekly_byday'][$val] = true;
                }
            }
        }

        if (!empty($data['BYMONTHDAY'])) {
            foreach (explode(",", $data['BYMONTHDAY']) as $val) {
                if (self::validByMonthDayValue($val)) {
                    $result['bymonthday'][$val] = true;
                }
            }
        }

        if (!empty($data['BYMONTH'])) {
            foreach (explode(",", $data['BYMONTH']) as $val) {
                if (self::validByMonthValue($val)) {
                    $result['bymonth'][$val] = true;
                }
            }
        }

        if (isset($data['BYSETPOS']) && self::validByDayPrefix($data['BYSETPOS'])) {
            $result['byday_prefix'] = $data['BYSETPOS'];
        }

        if (!empty($data['BYDAY'])) {
            if (self::areByDayStringsEqual($data['BYDAY'], "MO,TU,WE,TH,FR")) {
                $result['byday_value'] = "WEEKDAY";
            } else if (self::areByDayStringsEqual($data['BYDAY'], "SA,SU")) {
                $result['byday_value'] = "WEEKEND_DAY";
            } else if (self::areByDayStringsEqual($data['BYDAY'], "MO,TU,WE,TH,FR,SA,SU")) {
                $result['byday_value'] = "DAY";
            } else {
                // byday can have multiple values, but the UI of all the mainstream programs allows for only one value,
                // and so do we
                $day = explode(",", $data['BYDAY'])[0];

                // if the byday prefix exists, set it, overwriting the BYSETPOS prefix, if previously set
                if (self::validByDayPrefix($prefix = substr($day, 0, -2))) {
                    $result['byday_prefix'] = $prefix;
                }
                if (self::validDayOfWeek($value = substr($day, -2))) {
                    $result['byday_value'] = $value;
                }
            }
        }

        if (isset($data['COUNT'])) {
            $result['range'] = "count";
            $result['count'] =  (int)$data['COUNT'] >= 1 && (int)$data['COUNT'] <= 999 ? (int)$data['COUNT'] : 1;
        } else if (isset($data['UNTIL'])) {
            $result['range'] = "until";
            $result['until'] = date($format->getDateFormat(), strtotime($data['UNTIL']));
        }

        // if the event start date is specified and valid, use it to set BYDAY, BYMONTH, and BYMONTHDAY values if they
        // haven't been set
        try {
            if ($eventStart) {
                $startDate = new \DateTime($eventStart, new \DateTimeZone($rcmail->config->get("timezone")));

                if (!in_array(true, $result['weekly_byday'])) {
                    $result['weekly_byday'][strtoupper(substr($startDate->format("D"), 0, 2))] = true;
                }

                if (!in_array(true, $result['bymonthday'])) {
                    $result['bymonthday'][$startDate->format("j")] = true;
                }

                if (!in_array(true, $result['bymonth'])) {
                    $result['bymonth'][$startDate->format("n")] = true;
                }
            }
        } catch (\Exception) {
        }

        return $result;
    }

    private function getSavableEventData(array $data): array
    {
        $fields = [
            "id", "user_id", "calendar_id", "uid", "title", "location", "description", "url", "start", "timezone_start",
            "timezone_end", "end", "all_day", "repeat_rule", "repeat_end", "use_calendar_colors", "bg_color",
            "tx_color", "busy", "visibility", "priority", "category", "attachments", "has_attendees", "vevent",
            "created_at", "modified_at", "removed_at", "vevent_uid",
        ];

        foreach ($data as $key => $val) {
            if (!in_array($key, $fields)) {
                unset($data[$key]);
            }
        }

        // if id is there, but it's false, remove it, so it gets auto-created in the db
        // with the id set to false it'll work on mysql, but won't work on postgres and sqlite
        if (empty($data["id"])) {
            unset($data["id"]);
        }

        return $data;
    }

    /**
     * Converts the row of event data to vevent.
     * Reference: https://www.ietf.org/rfc/rfc2445.txt
     *
     * @param array $data
     * @return string|boolean
     * @throws \DateMalformedStringException
     */
    public static function createVEvent(array $data): bool|string
    {
        $rcmail = xrc();
        $userEmail = $rcmail->get_user_email();

        if (empty($data['calendar_id']) || empty($calendarData = CalendarData::load($data['calendar_id']))) {
            return false;
        }

        $vcalendar = new \Sabre\VObject\Component\VCalendar();
        $vcalendar->prodid = Event::getICalProdId();
        $timezoneString = $rcmail->config->get("timezone");
        try {
            $timezone = new \DateTimeZone($timezoneString);
        } catch (\Exception) {
            $timezone = new \DateTimeZone("UTC");
        }

        $vevent = $vcalendar->add(
            "VEVENT",
            [
                "UID" => empty($data['vevent_uid']) ? $data['uid'] : $data['vevent_uid'],
                "SUMMARY" => $data['title'],
                "TRANSP" => (int)$data['busy'] ? "OPAQUE" : "TRANSPARENT",
            ]
        );

        try {
            $timezoneStart = empty($data['timezone_start']) ? $timezone : new \DateTimeZone($data['timezone_start']);
            $timezoneEnd = empty($data['timezone_end']) ? $timezone : new \DateTimeZone($data['timezone_end']);
        } catch (\Exception) {
            $timezoneStart = $timezone;
            $timezoneEnd = $timezone;
        }

        try {
            $dtStartDate = new \DateTime($data['start'], $timezoneStart);
            $dtEndDate = new \DateTime($data['end'], $timezoneEnd);
        } catch (\Exception) {
            return false;
        }

        if ($data['all_day']) {
            $dtStart = $vevent->add("DTSTART", $dtStartDate->format("Ymd"));
            $dtEnd = $vevent->add("DTEND", $dtEndDate->modify("+1 day")->format("Ymd"));
            $dtStart['VALUE'] = 'DATE';
            $dtEnd['VALUE'] = 'DATE';
        } else {
            $vevent->add("DTSTART", $dtStartDate);
            $vevent->add("DTEND", $dtEndDate);
        }

        $visibility = $data['visibility'] == "default" ? $calendarData->get("default_event_visibility") : $data['visibility'];
        $visibility != "public" && $vevent->add("CLASS", strtoupper($visibility));
        $data['description'] && $vevent->add("DESCRIPTION", $data['description']);
        $data['url'] && $vevent->add("URL", $data['url']);
        $data['location'] && $vevent->add("LOCATION", $data['location']);
        $data['priority'] && $vevent->add("PRIORITY", $data['priority']);
        $data['modified_at'] != NULL && $vevent->add("LAST-MODIFIED", self::UtcTime($data['modified_at']));
        $data['category'] && $vevent->add("CATEGORIES", $data['category']);
        !empty($data['repeat_rule']) && $vevent->add("RRULE", $data['repeat_rule']);
        $vevent->add("CREATED", self::UtcTime($data['created_at'] ?? date("Y-m-d H:i:s")));

        if (!empty($data['attachments'])) {
            $attachments = is_array($data['attachments']) ? $data['attachments'] : json_decode($data['attachments'], true);

            foreach ($attachments as $attachment) {
                $name = trim($attachment['name'] ?? "");
                $path = trim($attachment['path'] ?? "");
                $size = trim($attachment['size'] ?? "");

                if (!empty($name) && !empty($path)) {
                    $properties = [
                        "FILENAME" => $name,
                        "X-EVOLUTION-CALDAV-ATTACHMENT-NAME" => $name,
                    ];

                    // size is not a standard property, adding our own custom one to store the size
                    if (!empty($size)) {
                        $properties["X-ROUNDCUBEPLUS-ATTACHMENT-SIZE"] = $attachment['size'];
                    }

                    $vevent->add("ATTACH", $path, $properties);
                }
            }
        }

        // add excluded (deleted) events from a repeated event, the deleted events don't have a time attached to them
        // just the date (the time is 00:00:00) so for non-all-day events we take the date and the start time because
        // the time of the excluded event needs to be the same as the start of the event, otherwise it won't be excluded
        if (!empty($data['excluded'])) {
            foreach ($data['excluded'] as $excluded) {
                try {
                    if ($data['all_day']) {
                        $vevent->add(
                            "EXDATE",
                            str_replace("-", "", substr($excluded, 0, 10)),
                            ["VALUE" => "DATE"]
                        );
                    } else {
                        $vevent->add(
                            "EXDATE",
                            new \DateTime(substr($excluded, 0, 10) . " " . $dtStartDate->format("H:i:s"), $timezone)
                        );
                    }
                } catch (\Exception) {
                }
            }
        }

        // add attendees
        if (!empty($data['attendees'])) {
            foreach ($data['attendees'] as $attendee) {
                isset($attendee['name']) || ($attendee['name'] = "");
                isset($attendee['guests']) || ($attendee['guests'] = 0);
                isset($attendee['comment']) || ($attendee['comment'] = 0);
                isset($attendee['status']) || ($attendee['status'] = 0);
                isset($attendee['organizer']) || ($attendee['organizer'] = 0);
                isset($attendee['role']) || ($attendee['role'] = 0);

                $param = [
                    "CUTYPE" => "INDIVIDUAL",
                    "ROLE" => self::roleToVRole($attendee['role']),
                    "PARTSTAT" => self::statusToVStatus($attendee['status']),
                    "X-NUM-GUESTS" => (int)$attendee['guests'],
                    "X-NOTIFY" => $attendee['notify'] ? "TRUE" : "FALSE",
                ];

                if ($name = trim(strtolower($attendee['name'])) == trim(strtolower($attendee['email'])) ? false : $attendee['name']) {
                    $param['CN'] = $name;
                }

                if ($comment = trim($attendee['comment'])) {
                    $param['X-RESPONSE-COMMENT'] = htmlspecialchars($comment, ENT_QUOTES, "UTF-8", false);
                }

                // add organizer / attendee
                if ((int)$attendee['organizer']) {
                    // when saving the event in Roundcube, try getting the organizer's name from the db so the
                    // invitations get sent from Name <email> instead of just email
                    if (empty($param['CN']) &&
                        !empty($attendee['user_id']) &&
                        $attendee['user_id'] == $rcmail->get_user_id() &&
                        ($identity = $rcmail->user->get_identity()) && !empty($identity['name'])
                    ) {
                        $param['CN'] = $identity['name'];
                    }
                    $vevent->add("ORGANIZER", "mailto:{$attendee['email']}", $param);
                } else {
                    $vevent->add("ATTENDEE", "mailto:{$attendee['email']}", $param);
                }
            }
        }

        // add alarms
        if (!empty($data['alarms'])) {
            foreach ($data['alarms'] as $alarm) {
                $valarm = $vcalendar->createComponent("VALARM");

                try {
                    if ($alarm['alarm_position'] == ALARM_ABSOLUTE_TIME) {
                        $dt = TimeZone::convertToTimezone($alarm['absolute_datetime'], $timezoneString, "UTC", true);
                        $triggerKey = "TRIGGER;VALUE=DATE-TIME";
                        $triggerVal = $dt->format("Ymd") . "T" . $dt->format("His") . "Z";
                    } else {
                        $triggerKey = "TRIGGER;RELATED=" .
                            (in_array($alarm['alarm_position'], [ALARM_BEFORE_START, ALARM_AFTER_START]) ? "START" : "END");
                        $triggerVal = (in_array($alarm['alarm_position'], [ALARM_BEFORE_START, ALARM_BEFORE_END]) ? "-" : "");
                        $triggerVal .= match ($alarm['alarm_units']) {
                            "hours" => "PT{$alarm['alarm_number']}H",
                            "days" => "P{$alarm['alarm_number']}D",
                            "weeks" => "P{$alarm['alarm_number']}W",
                            default => "PT{$alarm['alarm_number']}M",
                        };
                    }
                } catch (\Exception) {
                    continue;
                }

                $valarm->add($triggerKey, $triggerVal);
                $valarm->DESCRIPTION = $data['title'];

                if ($alarm['alarm_type'] == "1") {
                    $valarm->ACTION = "EMAIL";
                    $valarm->SUMMARY = $data['title'];
                    $valarm->ATTENDEE = $userEmail;
                } else {
                    $valarm->ACTION = "DISPLAY";
                }

                $vevent->add($valarm);
            }
        }

        // re-insert properties that should be preserved from the original ics if the event is created via caldav -- for more information
        // see vEventToDataArray()
        if (!empty($data['preserve_ics_properties']) && is_array($data['preserve_ics_properties'])) {
            foreach ($data['preserve_ics_properties'] as $key => $val) {
                $vevent->add($key, $val);
            }
        }

        // use this to return the event wrapped in VCALENDAR
        //return $vcalendar->serialize();

        return $vevent->serialize();
    }

    public function isCategoryNameValid($category): bool
    {
        $categories = $this->rcmail->config->get("xcalendar_categories");
        is_array($categories) || ($categories = []);

        foreach ($categories as $val) {
            if ($category == $val['name']) {
                return true;
            }
        }

        return false;
    }

    public function getSharedCalendarData($calendarId): bool|array
    {
        if ($data = $this->db->fetch(
            "SELECT permissions, name, description, bg_color, tx_color FROM {xcalendar_calendars_shared}
             WHERE email = ? AND calendar_id = ? AND added = 1",
            [$this->rcmail->get_user_email(), $calendarId]
        )) {
            if (!($data['permissions'] = json_decode($data['permissions'], true))) {
                $data['permissions'] = [
                    "publish_calendar" => false,
                    "share_calendar" => false,
                    "edit_events" => false,
                    "see_details" => false,
                ];
            }

            return $data;
        }

        return false;
    }

    public function hasPermission($calendarId, $permission): bool
    {
        if ($data = $this->getSharedCalendarData($calendarId)) {
            return !empty($data['permissions'][$permission]);
        }

        return false;
    }

    public function findUserIdByEmail($email)
    {
        $result = 0;

        // if no user id, try to find user id from email
        if ($row = $this->db->row("users", ["username" => $email])) {
            $result = $row['user_id'];
        }

        // if still not user id, try identities
        if (!$result && ($row = $this->db->row("identities", ["email" => $email]))) {
            $result = $row['user_id'];
        }

        return $result;
    }

    /**
     * Removes the attachments that have been uploaded but are not used by any event. This happens when the user
     * uploads an attachments and then cancels editing the event without saving it.
     */
    private function removeUnusedAttachments(): void
    {
        $date = date("Y-m-d H:i:s", strtotime("-24 hours"));

        if (!($files = $this->db->all(
            "SELECT filename FROM {xcalendar_attachments_temp} WHERE uploaded_at < ?",
            $date)
        )) {
            return;
        }

        $this->db->query("DELETE FROM {xcalendar_attachments_temp} WHERE uploaded_at < ?", $date);

        $dir = Event::getAttachmentDir();
        foreach ($files as $file) {
            @unlink($dir . $file['filename']);
        }
    }

    /**
     * Saves event notifications (alarms.) First remove all the records and then re-insert.
     * @param array $data
     * @throws \Exception
     */
    private function saveAlarms(array &$data): void
    {
        if (!$this->db->query("DELETE FROM {xcalendar_alarms} WHERE event_id = ?", [$data['id']])) {
            throw new \Exception("Cannot save alarms (839275)");
        }

        if (empty($data['alarms'])) {
            return;
        }

        $alarmCrc = [];

        foreach ($data['alarms'] as $key => $record) {
            // validate data
            empty($record['absolute_datetime']) && ($record['absolute_datetime'] = $data['start']);
            ($record['alarm_number'] < 0 || $record['alarm_number'] > 999) && ($record['alarm_number'] = 0);
            !in_array($record['alarm_units'], ["minutes", "hours", "days", "weeks"]) && ($record['alarm_units'] = "minutes");
            !in_array((int)$record['alarm_position'], [0, 1, 2, 3, 4]) && ($record['alarm_position'] = 0);
            !in_array((int)$record['alarm_type'], [0, 1]) && ($record['alarm_type'] = 0);
            $alarmTime = null;

            if (empty($data['repeat_rule'])) {
                try {
                    $timezone = new \DateTimeZone($this->timezoneString);
                } catch (\Exception) {
                    $timezone = new \DateTimeZone("UTC");
                }

                try {
                    switch ($record['alarm_position']) {
                        case ALARM_BEFORE_START:
                            $alarmTime = new \DateTime($data['start'], $timezone);
                            $alarmTime->modify("-{$record['alarm_number']} {$record['alarm_units']}");
                            break;
                        case ALARM_AFTER_START:
                            $alarmTime = new \DateTime($data['start'], $timezone);
                            $alarmTime->modify("+{$record['alarm_number']} {$record['alarm_units']}");
                            break;
                        case ALARM_BEFORE_END:
                            $alarmTime = new \DateTime($data['end'], $timezone);
                            $alarmTime->modify("-{$record['alarm_number']} {$record['alarm_units']}");
                            break;
                        case ALARM_AFTER_END:
                            $alarmTime = new \DateTime($data['end'], $timezone);
                            $alarmTime->modify("+{$record['alarm_number']} {$record['alarm_units']}");
                            break;
                        case ALARM_ABSOLUTE_TIME:
                            $alarmTime = new \DateTime($record['absolute_datetime'], $timezone);
                            break;
                        default:
                            throw new \Exception();
                    }

                    // saving the alarm time in utc because we don't know what the server time is that will be used to
                    // retrieve it in cron
                    $alarmTime->setTimezone(new \DateTimeZone("UTC"));
                    $alarmTime = $alarmTime->format("Y-m-d H:i:00");
                } catch (\Exception) {
                    continue;
                }
            }

            $array = [
                "user_id" => (int)$data['user_id'],
                "event_id" => (int)$data['id'],
                "event_end" => empty($data['repeat_rule']) ? NULL : $data['repeat_end'],
                "alarm_number" => (int)$record['alarm_number'],
                "alarm_units" => (string)$record['alarm_units'],
                "alarm_position" => (int)$record['alarm_position'],
                "alarm_time" => $alarmTime,
                "absolute_datetime" => $record['absolute_datetime'],
                "alarm_type" => (int)$record['alarm_type'],
            ];

            // don't save duplicates
            $crc = crc32(json_encode($array));

            if (in_array($crc, $alarmCrc)) {
                unset($data['alarms'][$key]);
                continue;
            } else {
                $alarmCrc[] = $crc;
            }

            if (!$this->db->insert("xcalendar_alarms", $array)) {
                throw new \Exception("Cannot save alarms (388923)");
            }
        }
    }

    /**
     * Save event attendees. First remove all the records, then re-insert.
     * @param array $data
     * @throws \Exception
     */
    private function saveAttendees(array &$data): void
    {
        $data['has_attendees'] = 0;

        if (!$this->db->query("DELETE FROM {xcalendar_attendees} WHERE event_id = ?", [$data['id']])) {
            throw new \Exception("Cannot save attendees (491004)");
        }

        if (empty($data['attendees'])) {
            return;
        }

        // clear ['attendees'] so we can re-fill it with fixed and valid information, the calling function needs this
        // updated information
        $attendees = $data['attendees'];
        $data['attendees'] = [];
        $emails = [];

        foreach ($attendees as $record) {
            $record['email'] = trim($record['email']);

            // don't save duplicates
            if (in_array($record['email'], $emails)) {
                continue;
            }

            // fill the missing data and/or fix it
            $record['name'] = empty($record['name']) ? "" : trim($record['name']);
            $record['organizer'] = empty($record['organizer']) ? 0 : 1;
            $record['notify'] = empty($record['notify']) ? 0 : 1;
            $record['role'] = empty($record['role']) ? 0 : (int)$record['role'];
            $record['status'] = empty($record['status']) ? 0 : (int)$record['status'];
            empty($record['user_id']) && ($record['user_id'] = $this->findUserIdByEmail($record['email']));
            (empty($record['role']) || !in_array($record['role'], [1, 2, 3])) && ($record['role'] = 0);
            (empty($record['status']) || !in_array($record['status'], [1, 2, 3, 4])) && ($record['status'] = 0);

            if (!$this->db->insert("xcalendar_attendees", [
                "event_id" => (int)$data['id'],
                "calendar_id" => (int)$data['calendar_id'],
                "user_id" => (int)$record['user_id'],
                "email" => $record['email'],
                "name" => $record['name'],
                "organizer" => $record['organizer'],
                "role" => $record['role'],
                "notify" => $record['notify'],
                "status" => $record['status'],
            ])) {
                throw new \Exception("Cannot save attendees (185749)");
            }

            $data['attendees'][] = $record;
            $emails[] = $record['email'];
            $data['has_attendees'] = 1;
        }
    }

    /**
     * Saves the dates on which this event is excluded from the repeating series. First delete all the records and then
     * re-insert.
     *
     * @param array $data
     * @throws \Exception
     */
    private function saveExcluded(array &$data): void
    {
        if (!$this->db->query("DELETE FROM {xcalendar_events_removed} WHERE event_id = ?", [$data['id']])) {
            throw new \Exception("Cannot save removed sequences (672995)");
        }

        $data['excluded'] = array_unique($data['excluded']);

        if (!empty($data['excluded'])) {
            foreach ($data['excluded'] as $day) {
                if (!empty(strtotime($day))) {
                    if (!$this->db->insert("xcalendar_events_removed", ["day" => $day, "event_id" => $data['id']])) {
                        throw new \Exception("Cannot save removed sequences (839004)");
                    }
                }
            }
        }
    }

    /**
     * This function converts the links in the plain description text to html to make sure that invitation links from
     * Microsoft Teams, for example, can be easily clicked. We encode the rest of the text to make it safe to insert as
     * html. The text of the clickable links must immediately precede the link without any spaces (again, Microsoft
     * Teams style); the text will be extracted from the previous new line all the way to the beginning of the link. We
     * also make sure that another link can't be used as the link text.
     *
     * @param string $text
     * @return string
     */
    public static function getDescriptionForPreview(string $text): string
    {
        // remove any long underlines (Microsoft Teams invitations use lines of _ to separate paragraphs)
        $text = preg_replace('/^_{2,}[\s]*$/m', '', $text);

        // encode the entire text to prevent any unwanted html execution
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // convert text to clickable links; add spaces before and after link to make sure it's separated from the reset
        // of the text (for example, if found right after |)
        $text = preg_replace_callback('/([^\n|]+[^\s;])&lt;((https?:\/\/|tel:)(?:(?!&gt;).)+)&gt;/', function($matches) {
            return " <a href=\"$matches[2]\" target=\"_blank\">" . trim($matches[1]) . "</a> ";
        }, $text);


        // remove empty new lines
        $text = preg_replace('/(\r?\n){2,}/', "\n", $text);

        // convert new lines to <br> and trim
        return nl2br(trim($text));
    }

    public static function UtcTime($time): bool|string
    {
        try {
            $dt = new \DateTime($time, new \DateTimeZone(xrc()->config->get("timezone")));
            $dt->setTimezone(new \DateTimeZone("UTC"));
            return $dt->format("Ymd\THis\Z");
        } catch (\Exception) {
            return false;
        }
    }

    public static function statusToVStatus($status, $lowercase = false): string
    {
        $result = match ($status) {
            1 => "ACCEPTED",
            2 => "DECLINED",
            3 => "TENTATIVE",
            4 => "DELEGATED",
            default => "NEEDS-ACTION",
        };

        return $lowercase ? strtolower($result) : $result;
    }

    public static function vStatusToStatus($vStatus): int
    {
        return match (strtoupper($vStatus)) {
            "ACCEPTED" => 1,
            "DECLINED" => 2,
            "TENTATIVE" => 3,
            "DELEGATED" => 4,
            default => 0,
        };
    }

    public static function vRoleToRole($vRole): int
    {
        return match ($vRole) {
            "OPT-PARTICIPANT" => 1,
            "NON-PARTICIPANT" => 2,
            "CHAIR" => 3,
            default => 0,
        };
    }

    public static function vCutypeToCutype($vCutype): int
    {
        return match ($vCutype) {
            "INDIVIDUAL" => 1,
            "GROUP" => 2,
            "RESOURCE" => 3,
            "ROOM" => 4,
            default => 0,
        };
    }

    public static function roleToVRole($role): string
    {
        return match ($role) {
            1 => "OPT-PARTICIPANT",
            2 => "NON-PARTICIPANT",
            3 => "CHAIR",
            default => "REQ-PARTICIPANT",
        };
    }

    /**
     * Checks if the attendance response string is valid and supported.
     *
     * @param $response
     * @return bool
     */
    public static function isAttendanceResponseValid($response): bool
    {
        return in_array($response, ["ACCEPTED", "TENTATIVE", "DECLINED"]);
    }

    /**
     * Checks if the attendance response status is valid and supported.
     *
     * @param $status
     * @return bool
     */
    public static function isAttendanceStatusValid($status): bool
    {
        return in_array((int)$status, [1, 2, 3]);
    }

    /**
     * Returns the valid RRULE BYMONTH values
     *
     * @return array
     */
    public static function byMonthValues(): array
    {
        return range(1, 12);
    }

    /**
     * Checks if the RRULE BYMONTH value is valid.
     *
     * @param $value
     * @return bool
     */
    public static function validByMonthValue($value): bool
    {
        return in_array($value, self::byMonthValues());
    }

    /**
     * Returns the valid RRULE BYMONTHDAY values
     *
     * @return array
     */
    public static function byMonthDayValues(): array
    {
        return array_merge([-1], range(1, 31));
    }

    /**
     * Checks if the RRULE BYMONTHDAY value is valid.
     *
     * @param $value
     * @return bool
     */
    public static function validByMonthDayValue($value): bool
    {
        return in_array($value, self::byMonthDayValues());
    }

    /**
     * Returns the valid RRULE days of the week.
     *
     * @return string[]
     */
    public static function daysOfWeek(): array
    {
        return ["MO", "TU", "WE", "TH", "FR", "SA", "SU"];
    }

    /**
     * Checks if the RRULE day of the week value is valid
     *
     * @param $value
     * @return bool
     */
    public static function validDayOfWeek($value): bool
    {
        return in_array($value, self::daysOfWeek());
    }

    /**
     * Returns the valid RRULE BYDAY prefixes (the -1 part of -1MO = last Monday, or 1 of 1SU = first Sunday)
     *
     * @return string[]
     */
    public static function byDayPrefixes(): array
    {
        return ["", "1", "2", "3", "4", "5", "-1", "-2"];
    }

    /**
     * Checks if the RRULE BYDAY prefix is valid.
     *
     * @param $value
     * @return bool
     */
    public static function validByDayPrefix($value): bool
    {
        return in_array($value, self::byDayPrefixes());
    }

    /**
     * Checks if the RRULE INTERVAL value is valid. (There's no max interval value in the specs, so we allow max 999.
     * Some programs allow 999, some 400, etc. It's up to us, and 999 seems sensible.)
     *
     * @param $value
     * @return bool
     */
    public static function validInterval($value): bool
    {
        return is_numeric($value) && $value > 0 && $value <= 999;
    }

    /**
     * Checks if two RRULE BYDAY strings are the same, even if they're in different order
     *
     * @param $string1
     * @param $string2
     * @return bool
     */
    public static function areByDayStringsEqual($string1, $string2): bool
    {
        $array1 = explode(',', $string1);
        $array2 = explode(',', $string2);
        sort($array1);
        sort($array2);
        return $array1 === $array2;
    }
}