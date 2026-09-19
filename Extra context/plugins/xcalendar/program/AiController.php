<?php
namespace XCalendar;

/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

class AiController
{
    protected \rcube $rcmail;

    public function __construct()
    {
        $this->rcmail = xrc();
    }

    /**
     * @throws \Exception
     */
    public function get_calendars(array $arg, array &$payload): array
    {
        $result = [];
        $calendar = new Calendar();

        foreach ($calendar->getCalendarList() as $data) {
            $result[] = [
                'id' => $data['id'],
                'name' => $data['name'],
                'type' => $calendar->typeToString($data['type']),
                'enabled' => $data['enabled'],
                'read_only' => !$data['permissions']->edit_events,
            ];
        }

        if (empty($result)) {
            throw new \Exception('No calendars found.');
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function get_calendar_events(array $arg, array &$payload): array
    {
        if (empty($arg['start']) ||
            empty($arg['end']) ||
            ($start = strtotime($arg['start'])) === false ||
            ($end = strtotime($arg['end'])) === false
        ) {
            throw new \Exception('Invalid start or end date.');
        }

        if ((new \DateTime($arg['end'])) > (new \DateTime($arg['start']))->add(new \DateInterval('P3M'))) {
            throw new \Exception('Cannot retrieve events spanning more than 3 months. Inform the user.');
        }

        $start = date("Y-m-d H:i:s", $start);
        $end = date("Y-m-d H:i:s", $end);

        if ($end <= $start) {
            throw new \Exception('End date must be after start date.');
        }

        $result = [];

        $ev = new Event();
        $events = array_merge(
            $ev->getLocalEventList($start, $end, true),
            $ev->getRemoteEventList($start, $end)
        );

        foreach ($events as $event) {
            if (!empty($event['vevent'])) { // local calendars
                $result[] = $event['vevent'];
            } else if (!empty($event['vcalendar'])) { // caldav calendars
                $result[] = $event['vcalendar'];
            }
        }

        if (empty($result)) {
            throw new \Exception('No events found in this date range.');
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function enable_calendar(array $arg, array &$payload): string
    {
        $enable = (bool)($arg['enable'] ?? false);
        if (!(new Calendar())->enableCalendar((string)($arg['id'] ?? ''), $enable)) {
            throw new \Exception('Cannot find calendar. Call get_calendars for valid calendar ids.');
        }
        // run function in frontend to make sure everything is properly refreshed
        $payload['js_command'] = 'xcalendar_enable_calendar';
        $payload['js_command_arg'] = ['calendar_id' => $arg['id'], 'enable' => $enable];

        return $enable ?
            'Calendar enabled. Its events are now visible to the user.' :
            'Calendar disabled. Its events are now hidden from the user.';
    }

    /**
     * @throws \Exception
     */
    public function create_calendar_event(array $arg, array &$payload): string
    {
        if (!empty($arg['calendar_id'])) {
            if (empty($calendarData = CalendarData::load($arg['calendar_id']))) {
                throw new \Exception('Cannot find calendar. Call get_calendars for valid calendar ids.');
            }

            if (!$calendarData->isWritable()) {
                throw new \Exception('Calendar is read-only');
            }
        }

        $ed = new EventData();
        $vcalendar = (string)($arg['vcalendar'] ?? '');

        if (!$vcalendar || !$ed->loadFromVCalendar(str_replace('\n', "\n", $vcalendar), $arg['calendar_id'] ?? false)) {
            throw new \Exception('Invalid vcalendar format.');
        }
        $ed->save();

        // refresh calendar in the frontend so the event is displayed
        $payload['js_command'] = 'xcalendar_refresh_calendar';
        $payload['js_command_arg'] = ['calendar_id' => $ed->getValue('calendar_id')];

        return 'Event created.';
    }

    /**
     * @throws \Exception
     */
    public function edit_calendar_event(array $arg, array &$payload): string
    {
        $ed = new EventData();
        $vcalendar = (string)($arg['vcalendar'] ?? '');

        if (!$vcalendar || !$ed->loadFromVCalendar(str_replace('\n', "\n", $vcalendar))) {
            throw new \Exception('Invalid vcalendar format.');
        }

        // get the id of this event so we can overwrite it (verifies if event belongs to the current user)
        if (!($event = Event::getEventByUid($ed->getValue('vevent_uid') ?? ''))) {
            throw new \Exception('Cannot find event with this UID.');
        }

        if (empty($calendarData = CalendarData::load($event['calendar_id']))) {
            throw new \Exception('Cannot find calendar');
        }

        if (!$calendarData->isWritable()) {
            throw new \Exception('Calendar is read-only');
        }

        $ed->setValue('id', $event['id']);
        $ed->save();

        // refresh calendar in the frontend so the event changes are displayed
        $payload['js_command'] = 'xcalendar_refresh_calendar';
        $payload['js_command_arg'] = ['calendar_id' => $ed->getValue('calendar_id')];

        return 'Event updated.';
    }
}