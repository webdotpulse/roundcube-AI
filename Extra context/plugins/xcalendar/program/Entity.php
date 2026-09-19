<?php
namespace XCalendar;

use XFramework\DatabaseGeneric;
use XFramework\Format;

/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

// This is a common ancestor for Event and Calendar

abstract class Entity
{
    protected \rcube $rcmail;
    protected DatabaseGeneric $db;
    protected Format $format;
    protected int $userId;
    protected ?string $userEmail;

    public function __construct()
    {
        $this->rcmail = xrc();
        $this->db = xdb();
        $this->format = xformat();
        $this->setUserId($this->rcmail->get_user_id());
        $this->setUserEmail($this->rcmail->get_user_email());
    }

    public function setUserId($userId): void
    {
        $this->userId = (int)$userId;
    }

    public function setUserEmail($userEmail): void
    {
        $this->userEmail = $userEmail;
    }

    /**
     * Get data for an editing form from the database.
     *
     * @param string $table
     * @param int $id
     * @return array|boolean
     */
    public function getFormData(string $table, int $id): bool|array
    {
        if (!$id) {
            return false;
        }

        return $this->db->row($table, ['id' => $id, 'removed_at' => NULL]);
    }

    /**
     * Save editing form in the database.
     *
     * @param string $table
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function saveFormData(string $table, $id, array $data): mixed
    {
        // make sure data doesn't contain any columns that don't exist in the table
        $columns = $this->db->getColumns($table);

        foreach ($data as $key => $val) {
            if (!in_array($key, $columns)) {
                unset($data[$key]);
            }
        }

        if ($id) {
            $data['modified_at'] = date('Y-m-d H:i:s');
            if ($this->db->update($table, $data, ['id' => $id])) {
                return $id;
            }
        } else {
            $data['user_id'] = $this->userId;
            $data['created_at'] = date('Y-m-d H:i:s');
            if ($this->db->insert($table, $data)) {
                return $this->db->lastInsertId($table);
            }
        }

        return false;
    }

    /**
     * Get the array of the calendars from the db. Create a default calendar if the list is empty.
     *
     * @param array|int $type
     * @param bool $includeShared
     * @param bool $enabledOnly
     * @param bool $writableOnly
     * @param bool $editEventsOnly
     * @return array
     */
    public function getCalendarList(array|int $type = [], bool $includeShared = true, bool $enabledOnly = false,
                                    bool $writableOnly = false, bool $editEventsOnly = false): array
    {
        if (!is_array($type)) {
            $type = [$type];
        }

        $caldavClientEnabled = ClientCaldav::enabled();
        $birthdayCalendarEnabled = ClientBirthday::enabled();

        // remove these calendars from $type if they're not enabled
        $caldavClientEnabled || ($type = array_diff($type, [Calendar::CALDAV]));
        $birthdayCalendarEnabled || ($type = array_diff($type, [Calendar::BIRTHDAY]));

        // if $type empty, add
        if (empty($type)) {
            $type = [Calendar::LOCAL, Calendar::HOLIDAY, Calendar::GOOGLE];
            $caldavClientEnabled && ($type[] = Calendar::CALDAV);
            $birthdayCalendarEnabled && ($type[] = Calendar::BIRTHDAY);
        }

        $query = "SELECT id, name, description, enabled, bg_color, tx_color, user_id, url, type, 
                default_event_visibility, properties
            FROM {xcalendar_calendars} 
            WHERE user_id = ? AND removed_at IS NULL" . ($enabledOnly ? " AND enabled = 1" : "") .
            " AND type IN (" . implode(",", array_fill(0, count($type), "?")) . ")";

        $param = array_merge([$this->userId], $type);
        $array = $this->db->all($query, $param);

        // add shared calendars
        if (is_array($array) && $includeShared) {
            $array = array_merge($array, $this->getAddedSharedCalendars($enabledOnly, $editEventsOnly));
        }

        if (empty($array)) {
            return [];
        }

        // add owner permissions to all retrieved calendars that belong to this user
        $ownerPermissions = Permission::getCalendarOwnerPermissions();
        foreach ($array as $key => $val) {
            if ($val['user_id'] == $this->userId) {
                $array[$key]['permissions'] = $ownerPermissions;
            }
        }

        // make sure the int values are actual ints, js needs them to be, and we don't want to use
        // JSON_NUMERIC_CHECK on json_encode for backward compatibility
        foreach ($array as $key => $val) {
            $array[$key]['id'] = (int)$val['id'];
            $array[$key]['user_id'] = (int)$val['user_id'];
            $array[$key]['enabled'] = (int)$val['enabled'];
        }

        $list = [];
        $googleKey = $this->rcmail->config->get('xcalendar_google_calendar_key');

        foreach ($array as $val) {
            // don't add google and holiday calendars if the api key is not specified
            if (empty($googleKey) && in_array($val['type'], [Calendar::HOLIDAY, Calendar::GOOGLE])) {
                continue;
            }

            // don't add readonly caldav calendars if $writableOnly is true
            if ($writableOnly &&
                $val['type'] == Calendar::CALDAV &&
                !empty($val['properties']) &&
                ($properties = json_decode($val['properties'], true)) &&
                !empty($properties['caldav_readonly'])
            ) {
                continue;
            }

            $val['busy'] = false;
            $list[$val['id']] = $val;
        }

        return $list;
    }

    /**
     * Returns an array of shared calendars that are added to by the current user.
     *
     * @param bool $enabledOnly
     * @param bool $editEventsOnly
     * @return array
     */
    public function getAddedSharedCalendars(bool $enabledOnly = false, bool $editEventsOnly = false): array
    {
        $shared = $this->db->all(
            "SELECT {xcalendar_calendars}.id, {xcalendar_calendars_shared}.name, {xcalendar_calendars_shared}.enabled,
            {xcalendar_calendars_shared}.bg_color, {xcalendar_calendars_shared}.tx_color, permissions, user_id, type,
            default_event_visibility
            FROM {xcalendar_calendars_shared}
            LEFT JOIN {xcalendar_calendars} ON id = calendar_id
            WHERE {xcalendar_calendars}.removed_at IS NULL AND 
              {xcalendar_calendars_shared}.email = ? AND {xcalendar_calendars_shared}.added = 1".
            ($enabledOnly ? ' AND {xcalendar_calendars_shared}.enabled = 1' : ''),
            $this->userEmail
        );

        if (is_array($shared)) {
            foreach ($shared as $key => $val) {
                $permissions = Permission::decodeCalendarPermissions($val['permissions']);
                if ($editEventsOnly && empty($permissions->edit_events)) {
                    unset($shared[$key]);
                } else {
                    $shared[$key]['permissions'] = $permissions;
                }
            }
        }

        return is_array($shared) ? $shared : [];
    }

    public static function getDefaultSettings(): array
    {
        return [
            'view' => 'agendaWeek',
            'first_day' => '1',
            'agenda_week_span' => 1,
            'slot_duration' => '00:30:00',
            'scroll_time' => '06:00:00',
            'calendar' => 0,
            'refresh' => 0,
            'week_numbers' => false,
            'categories' => [
                ['name' => 'Personal', 'color' => '#8B008B'],
                ['name' => 'Work', 'color' => '#483D8B'],
                ['name' => 'Family', 'color' => '#006400'],
            ],
            'event_border' => 1,
            'alarm_sound' => 'maramba',
            'show_sunrise' => false,
            'show_sunset' => false,
            'default_notification_type' => 'none',
            'default_notification_position' => 'before_start',
            'default_notification_number' => 10,
            'default_notification_units' => 'minutes',
        ];
    }
}