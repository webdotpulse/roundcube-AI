<?php
namespace XCalendar;

use XFramework\DatabaseGeneric;

/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

class ItipEventInfo
{
    private \rcube $rcmail;
    private DatabaseGeneric $db;
    private array $data = [
        "event_id" => false,
        "start" => "",
        "end" => "",
        "timezone_start" => "",
        "timezone_end" => "",
        "all_day" => "",
        "title" => "",
        "location" => "",
        "description" => "",
        "url" => "",
        "removed_at" => null,
        "attendees" => [],
    ];

    /**
     * ItipEventInfo constructor.
     *
     * @param string $eventUid - uid of the event to find
     * @param $userId - current user id
     * @param /XFramework/Database $db - database object instance
     */
    public function __construct(string $eventUid, $userId)
    {
        $this->rcmail = xrc();
        $this->db = xdb();
        $this->loadEvent($eventUid, $userId);
    }

    /**
     * Returns the loaded event id or false if the event couldn't be loaded.
     */
    public function getEventId()
    {
        return $this->data['event_id'];
    }

    /**
     * Returns the entire array of the event data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns the attendance status for the specified email address, or 0 (needs reply) if the event couldn't be loaded
     * or the email doesn't exist in the attendee list.
     *
     * @param string $email
     * @return mixed
     */
    public function getStatus(string $email): mixed
    {
        if (array_key_exists($email, $this->data['attendees'])) {
            return $this->data['attendees'][$email];
        }
        
        return 0;
    }

    public function isRemoved(): bool
    {
        return (bool)$this->data['removed_at'];
    }

    /**
     * Compares the basic data of the loaded event with the data of the passed event and returns true if there are
     * differences (for example, in title, start, etc.) or false if they are identical.
     *
     * @param array $event
     * @return bool
     */
    public function isInfoDifferent(array $event): bool
    {
        $fields = ["start", "end", "timezone_start", "timezone_end", "all_day", "title", "location", "description", "url"];
        $events = [$this->data, $event];
        $timezone = $this->rcmail->config->get("timezone");

        foreach ($events as $key => $event) {
            empty($event['timezone_start']) && ($events[$key]['timezone_start'] = $timezone);
            empty($event['timezone_end']) && ($events[$key]['timezone_end'] = $timezone);
            !isset($event['all_day']) && ($events[$key]['all_day'] = 1);
            $event['all_day'] && ($events[$key]['start'] = substr($event['start'], 0, 10));
            $event['all_day'] && ($events[$key]['end'] = substr($event['end'], 0, 10));
        }

        foreach ($fields as $field) {
            foreach ($events as $key => $event) {
                $events[$key][$field] = !isset($event[$field]) ? "" : $event[$field];
            }

            if (trim(strtoupper($events[0][$field])) != trim(strtoupper($events[1][$field]))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Finds the event by its uid (and the current user) and loads its basic info.
     *
     * @param $eventUid
     * @param $userId
     */
    protected function loadEvent($eventUid, $userId): void
    {
        $userId = (int)$userId;

        if (!$userId) {
            return;
        }

        if ($rows = $this->db->all(
            "SELECT {xcalendar_attendees}.email, {xcalendar_attendees}.status, event_id, `start`, `end`, timezone_start,
                timezone_end, all_day, title, location, {xcalendar_events}.description, {xcalendar_events}.url, 
                {xcalendar_events}.removed_at
             FROM {xcalendar_attendees} 
             LEFT JOIN {xcalendar_events} ON event_id = {xcalendar_events}.id  
             LEFT JOIN {xcalendar_calendars} ON {xcalendar_events}.calendar_id = {xcalendar_calendars}.id
             WHERE uid = ? AND {xcalendar_events}.user_id = ? AND {xcalendar_calendars}.type = 1 AND 
                {xcalendar_calendars}.removed_at IS NULL",
            [$eventUid, $userId])
        ) {
            foreach ($rows as $row) {
                if ($this->data['event_id'] === false) {
                    $this->data["event_id"] = (int)$row['event_id'];
                    $this->data["start"] = $row['start'];
                    $this->data["end"] = $row['end'];
                    $this->data["timezone_start"] = $row['timezone_start'];
                    $this->data["timezone_end"] = $row['timezone_end'];
                    $this->data["all_day"] = (int)$row['all_day'];
                    $this->data["title"] = $row['title'];
                    $this->data["location"] = $row['location'];
                    $this->data["description"] = $row['description'];
                    $this->data["url"] = $row['url'];
                    $this->data["removed_at"] = $row['removed_at'];
                }
                
                $this->data['attendees'][$row['email']] = (int)$row['status'];
            }
        }
    }
}