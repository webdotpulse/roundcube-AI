<?php
namespace XCalendar;

/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

require_once __DIR__ . '/Entity.php';
require_once __DIR__ . '/CalDavSync.php';

use XFramework\Utils;

class Calendar extends Entity
{
    const LOCAL = 1;
    const HOLIDAY = 2;
    const GOOGLE = 3;
    const CALDAV = 4;
    const BIRTHDAY = 5;
    const PASSWORD_PLACEHOLDER = '[_password_placeholder_]';

    public function typeToString(int $calendarType): string
    {
        return match ($calendarType) {
            self::LOCAL => 'local',
            self::HOLIDAY => 'holiday',
            self::GOOGLE => 'google',
            self::CALDAV => 'caldav',
            self::BIRTHDAY => 'birthday',
            default => 'unknown',
        };
    }

    /**
     * Returns the list of calendars as an array in the format id => name.
     *
     * @param array $type
     * @param bool $includeShared
     * @param bool $enabledOnly
     * @param bool $writableOnly
     * @param bool $editEventsOnly
     * @return array
     */
    public function getCalendarArray(array $type = [], bool $includeShared = true, bool $enabledOnly = false,
                                     bool $writableOnly = false, bool $editEventsOnly = false): array
    {
        $array = [];
        $list = $this->getCalendarList($type, $includeShared, $enabledOnly, $writableOnly, $editEventsOnly);
        foreach ($list as $val) {
            $array[$val['id']] = $val['name'];
        }

        return $array;
    }

    /**
     * Returns the number of non-removed calendars of the specified type that belong to the current user, or all
     * calendars if type is not specified.
     *
     * @param int|null $type
     * @return string|null
     */
    public static function getCalendarCount(?int $type = null): ?string
    {
        $where = ['user_id' => xrc()->get_user_id(), 'removed_at' => NULL];

        if ($type) {
            $where['type'] = $type;
        }

        return xdb()->count('xcalendar_calendars', $where);
    }

    public static function getCalendarPublishData(int $calendarId): array
    {
        $db = xdb();
        $rc = xrc();
        $url = Utils::removeSlash(Utils::getUrl());

        // if use_secure_urls is set, let's remove the token from the url; use_secure_urls can be true or can specify
        // the token length; if true, it defaults to 16
        if ($tokenLength = intval($rc->config->get('use_secure_urls'))) {
            $tokenLength = $tokenLength > 1 ? $tokenLength : 16;

            if (preg_match("/\/[a-zA-Z0-9]{" . $tokenLength . "}$/", $url, $matches)) {
                $url = substr($url, 0, -strlen($matches[0]));
            }
        }

        return [
            'url' => "$url/?xcalendar-publish=",
            'code_busy' => $db->value('code', 'xcalendar_published', ['calendar_id' => $calendarId, 'full' => 0]),
            'code_full' => $db->value('code', 'xcalendar_published', ['calendar_id' => $calendarId, 'full' => 1]),
        ];
    }

    /**
     * @throws \Exception
     */
    public function addCaldavCalendars(array $data): array
    {
        if (empty($data['url']) || empty($data['username']) || empty($data['password'])) {
            throw new \Exception($this->rcmail->gettext('xcalendar.caldav_client_error_server_info'));
        }

        if (empty($data['caldav_calendars']) || !is_array($data['caldav_calendars'])) {
            throw new \Exception('Incorrect data (44882991)');
        }

        $this->db->beginTransaction();

        try {
            $added = [];

            foreach ($data['caldav_calendars'] as $calendar) {
                if (!empty($calendar['url']) && !empty($calendar['name']) && !empty($calendar['checked'])) {
                    Color::getRandomColors($txColor, $bgColor);
                    $calendarName = substr($calendar['name'], 0, 250);
                    $serverUrl = substr($data['url'], 0, 250);
                    $calendarUrl = substr($calendar['url'], 0, 250);

                    $properties = [
                        'caldav_server_url' => $serverUrl,
                        'caldav_calendar_url' => $calendarUrl,
                        'caldav_username' => substr($data['username'], 0, 250),
                        'caldav_password' => $this->rcmail->encrypt(substr($data['password'], 0, 250)),
                        'caldav_readonly' => !empty($calendar['readonly']),
                    ];

                    // we're generating a hash from the calendar url and storing it in the url field; this value will
                    // only be used for checking if the calendar is already installed, so we don't need to store the
                    // entire url (for which we'd need to enlarge the size of the url db field) -- the actual url is
                    // stored in the properties field
                    $uniqueId = md5(ClientCaldav::resolveServerUrl($properties));

                    // check if this calendar is already subscribed
                    if ($this->db->row(
                        'xcalendar_calendars',
                        ['user_id' => $this->userId, 'type' => self::CALDAV, 'url' => $uniqueId]
                    )) {
                        throw new \Exception(
                            $this->rcmail->gettext([
                                'name' => 'xcalendar.caldav_client_error_calendar_added',
                                'vars' => ['n' => $calendarName],
                            ])
                        );
                    }

                    // save the calendar data
                    $id = $this->saveFormData(
                        'xcalendar_calendars',
                        false,
                        [
                            'type' => self::CALDAV,
                            'url' => $uniqueId,
                            'name' => $calendarName,
                            'description' => '',
                            'bg_color' => $bgColor,
                            'tx_color' => $txColor,
                            'enabled' => 1,
                            'properties' => json_encode($properties),
                        ]
                    );

                    if (!$id) {
                        throw new \Exception(
                            $this->rcmail->gettext([
                                'name' => 'xcalendar.caldav_client_error_add_calendar',
                                'vars' => ['n' => $calendarName],
                            ])
                        );
                    }

                    $added[] = $id;
                }
            }

            $this->db->commit();
            return $added;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function savePublishCode(int $calendarId, bool $full, bool $remove, &$code = ''): bool
    {
        try {
            if (!Permission::hasCalendarPermission($calendarId, 'publish_calendar', $this->userId, $this->userEmail)) {
                throw new \Exception();
            }

            if ($remove) {
                $this->db->remove('xcalendar_published', ['calendar_id' => $calendarId, 'full' => (int)$full]);
            } else {
                $code = $this->createPublishCode();

                if ($this->db->row(
                    'xcalendar_published',
                    ['calendar_id' => $calendarId, 'full' => (int)$full]
                )) {
                    if (!$this->db->update(
                        'xcalendar_published',
                        ['code' => $code],
                        ['calendar_id' => $calendarId, 'full' => (int)$full]
                    )) {
                        throw new \Exception();
                    }
                } else {
                    if (!$this->db->insert(
                        'xcalendar_published',
                        [
                            'user_id' => $this->userId,
                            'calendar_id' => $calendarId,
                            'code' => $code,
                            'full' => (int)$full,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]
                    )) {
                        throw new \Exception();
                    }
                }
            }

            return true;

        } catch (\Exception) {
            Utils::logError('Cannot save calendar publish code (93766)');
            return false;
        }
    }

    public function createPublishCode(): string
    {
        do {
            $code = Utils::getToken(48);
        } while ($this->db->row('xcalendar_published', ['code' => $code]));

        return $code;
    }

    /**
     * Returns the list of all calendars shared with the current user.
     *
     * @return array
     */
    public function getSharedCalendarList(): array
    {
        $data = $this->db->all(
            "SELECT calendar_id, {xcalendar_calendars}.name AS calendar_name, username, 
            {xcalendar_calendars_shared}.created_at, added 
            FROM {xcalendar_calendars_shared} 
            LEFT JOIN {xcalendar_calendars} ON calendar_id = id 
            LEFT JOIN {users} ON {xcalendar_calendars}.user_id = {users}.user_id 
            WHERE {xcalendar_calendars}.removed_at IS NULL AND {xcalendar_calendars_shared}.email = ?",
            $this->userEmail
        );

        if (empty($data)) {
            $data = [];
        }

        foreach ($data as $key => $val) {
            $data[$key]['added'] = (int)$val['added'];
        }

        return $data;
    }

    public function getNewSharedCalendarCount(): ?string
    {
        return $this->db->count('xcalendar_calendars_shared', ['email' => $this->userEmail, 'added' => '0']);
    }

    /**
     * Marks the shared calendar as added to the user calendar list.
     *
     * @param $calendarId
     * @return bool
     */
    public function addSharedCalendar($calendarId): bool
    {
        $calendarId = (int)$calendarId;

        if (empty($calendarId)) {
            return false;
        }
        
        // get the calendar name so we can add (shared) to it; at the same time, check if the calendar is still available
        if (!($name = $this->db->value('name', 'xcalendar_calendars', ['id' => $calendarId]))) {
            return false;
        }

        return $this->db->update(
            'xcalendar_calendars_shared',
            [
                'added' => 1,
                'name' => $name . ' ' . $this->rcmail->gettext('xcalendar.shared_marker'),
            ],
            ['email' => $this->userEmail, 'calendar_id' => $calendarId]
        );
    }

    /**
     * @param string $code
     * @return bool
     */
    public function addSharedCalendarByCode(string $code): bool
    {
        if ($calendarId = $this->db->value('calendar_id', 'xcalendar_calendars_shared', ['add_code' => $code])) {
            // get the calendar name so we can add (shared) to it; at the same time, check if the calendar is still available
            if (!($name = $this->db->value('name', 'xcalendar_calendars', ['id' => $calendarId]))) {
                return false;
            }

            return $this->db->update(
                'xcalendar_calendars_shared',
                [
                    'added' => 1,
                    'name' => $name . ' ' . $this->rcmail->gettext('xcalendar.shared_marker'),
                ],
                ['add_code' => $code]
            );
        }

        return false;
    }

    /**
     * Marks the shared calendar as not added to the user calendar list.
     *
     * @param $calendarId
     * @return bool
     */
    public function removeSharedCalendar($calendarId): bool
    {
        $calendarId = (int)$calendarId;

        if (empty($calendarId)) {
            return false;
        }
        
        return $this->db->update(
            'xcalendar_calendars_shared',
            ['added' => 0],
            ['email' => $this->userEmail, 'calendar_id' => $calendarId]
        );
    }

    /**
     * Deletes the shared calendar record from the database by the action of the user who the calendar was shared with.
     *
     * @param $calendarId
     * @return bool
     */
    public function unshareCalendar($calendarId): bool
    {
        $calendarId = (int)$calendarId;

        if (empty($calendarId)) {
            return false;
        }
        
        return $this->db->remove(
            'xcalendar_calendars_shared',
            ['email' => $this->userEmail, 'calendar_id' => $calendarId]
        );
    }

    /**
     * Enable/disable calendar. If owner, the enabled property is set in xcalendar_calendars, for shared calendars
     * it's set in xcalendar_calendars_shared.
     *
     * @param $calendarId
     * @param bool $enabled
     * @return bool
     */
    public function enableCalendar($calendarId, bool $enabled): bool
    {
        $calendarId = (int)$calendarId;

        if (empty($calendarId)) {
            return false;
        }

        if (Permission::isCalendarOwner($calendarId, $this->userId)) {
            return $this->db->update(
                'xcalendar_calendars',
                ['enabled' => (int)$enabled],
                ['id' => $calendarId, 'user_id' => $this->userId]
            );
        }

        if ($this->db->row(
            'xcalendar_calendars_shared',
            ['email' => $this->userEmail, 'calendar_id' => $calendarId])
        ) {
            return $this->db->update(
                'xcalendar_calendars_shared',
                ['enabled' => (int)$enabled],
                ['email' => $this->userEmail, 'calendar_id' => $calendarId]
            );
        }

        return false;
    }

    /**
     * Remove calendar. Owner deletes the calendar from the db, shared calendars get passed on to
     * removeSharedCalendar();
     *
     * @param $calendarId
     * @return bool
     */
    public function removeCalendar($calendarId): bool
    {
        $calendarId = (int)$calendarId;

        if (empty($calendarId)) {
            return false;
        }        
        
        if (!($record = $this->db->row('xcalendar_calendars', ['id' => $calendarId]))) {
            return false;
        }

        if ($record['user_id'] == $this->userId) {
            // if local, don't delete the record, only mark it with removed_at
            if ($record['type'] == self::LOCAL) {
                return $this->db->update(
                    'xcalendar_calendars',
                    ['removed_at' => date('Y-m-d H:i:s')],
                    ['id' => $calendarId, 'user_id' => $this->userId]
                );
            } else {
                // if not local (holiday, google, etc.) delete the record
                return $this->db->remove(
                    'xcalendar_calendars', 
                    ['id' => $calendarId, 'user_id' => $this->userId]
                );
            }
        } else {
            return $this->removeSharedCalendar($calendarId);
        }
    }

    /**
     * Restore calendar. Only the owner can restore a calendar.
     *
     * @param $calendarId
     * @return bool
     */
    public function restoreCalendar($calendarId): bool
    {
        $calendarId = (int)$calendarId;

        if (empty($calendarId)) {
            return false;
        }        
        
        return $this->db->update(
            'xcalendar_calendars',
            ['removed_at' => NULL],
            ['id' => $calendarId, 'user_id' => $this->userId]
        );
    }

    /**
     * Returns an array with dates as keys and sunrise/sunset times as values.
     *
     * @param string $start
     * @param string $end
     * @param bool|int $offset
     * @return array
     * @throws \DateMalformedStringException
     */
    public function getSunData(string $start, string $end, bool|int $offset): array
    {
        if (!$this->rcmail->plugins->get_plugin('xweather')) {
            return [];
        }

        $timeFormat = $this->rcmail->config->get('time_format');
        $latitude = $this->rcmail->config->get('xweather_latitude');
        $longitude = $this->rcmail->config->get('xweather_longitude');
        $showSunrise = (bool)$this->rcmail->config->get('xcalendar_show_sunrise', false);
        $showSunset = (bool)$this->rcmail->config->get('xcalendar_show_sunset', false);

        if (empty($timeFormat) ||
            empty($latitude) || 
            empty($longitude) || 
            !is_string($latitude) || 
            !is_string($longitude) ||
            !is_string($timeFormat) ||
            (!$showSunrise && !$showSunset)
        ) {
            return [];
        }

        try {
            $date = (new \DateTime($start))->setTimeZone(new \DateTimeZone('UTC'));

            if (!($endTime = strtotime($end))) {
                throw new \Exception();
            }
        } catch (\Exception) {
            return [];
        }

        $data = [];
        

        do {
            $items = [];
            $time = $date->getTimestamp();
            $sunInfo = date_sun_info($time, $latitude, $longitude);

            if ($showSunrise && isset($sunInfo['sunrise'])) {
                $sunrise = (new \DateTime())->setTimestamp($sunInfo['sunrise'])->setTimeZone(new \DateTimeZone('UTC'));
                $offset && $sunrise->modify("+$offset seconds");
                $items[] = $sunrise->format($timeFormat);
            }

            if ($showSunset && isset($sunInfo['sunset'])) {
                $sunset = (new \DateTime())->setTimestamp($sunInfo['sunset'])->setTimeZone(new \DateTimeZone('UTC'));
                $offset && $sunset->modify("+$offset seconds");
                $items[] = $sunset->format($timeFormat);
            }

            $data[$date->format('Y-m-d')] = implode(' - ', $items);
            $date->modify('+1 day');

        } while ($time < $endTime);

        return $data;
    }

    /**
     * Outputs
     *
     * @param string $code
     */
    public function getPublishedContent(string $code): void
    {
        try {
            if (!$this->rcmail->config->get('xcalendar_calendar_publish_enabled', true) ||
                !($publishedCalendar = $this->db->row('xcalendar_published', ['code' => explode('.', $code)[0]])) ||
                !($calendar = $this->db->row('xcalendar_calendars', ['id' => $publishedCalendar['calendar_id']]))
            ) {
                throw new \Exception();
            }

            if (empty($events = $this->db->all(
                "SELECT * FROM {xcalendar_events} WHERE calendar_id = ? AND removed_at IS NULL",
                [$publishedCalendar['calendar_id']]
            ))) {
                throw new \Exception();
            }

            try {
                $timezone = new \DateTimeZone($this->rcmail->config->get('timezone', 'UTC'));
            } catch (\Exception) {
                $timezone = new \DateTimeZone('UTC');
            }

            $vcalendar = new \Sabre\VObject\Component\VCalendar();
            $resultEvents = [];
            $resultTimezones = [];

            foreach ($events as $event) {
                // if private event, don't show
                if ($event['visibility'] == "private" ||
                    ($event['visibility'] == 'default' && $calendar['default_event_visibility'] == 'private')
                ) {
                    continue;
                }

                // get the timezones for this event and add to the list of timezones we'll be including as vtimezone
                foreach ([$event['timezone_start'], $event['timezone_end']] as $value) {
                    if ($value && !array_key_exists($value, $resultTimezones) && ($tz = Timezone::getVTimezone($value))) {
                        $resultTimezones[$value] = $tz;
                    }
                }

                if ($publishedCalendar['full']) {
                    // output full event information: take it from the vevent field
                    // if vevent in db includes vcalendar wrapper, need to cut it out
                    if ($i = strpos($event['vevent'], 'END:VCALENDAR')) {
                        if ($j = strpos($event['vevent'], 'BEGIN:VEVENT')) {
                            $resultEvents[] = substr($event['vevent'], $j, $i - $j);
                        }
                    } else {
                        $resultEvents[] = $event['vevent'];
                    }
                } else {
                    // output only busy/available information, create it from scratch
                    $vevent = new \Sabre\VObject\Component\VEvent(
                        $vcalendar,
                        'VEVENT',
                        [
                            'UID' => $event['uid'],
                            'SUMMARY' => '[' . $this->rcmail->gettext('xcalendar.' . 
                                    ((int)$event['busy'] ? 'busy' : 'available')) . ']',
                            'TRANSP' => (int)$event['busy'] ? 'OPAQUE' : 'TRANSPARENT',
                        ]
                    );

                    try {
                        $timezoneStart = empty($event['timezone_start']) ? 
                            $timezone : new \DateTimeZone($event['timezone_start']);
                        $timezoneEnd = empty($event['timezone_end']) ? 
                            $timezone : new \DateTimeZone($event['timezone_end']);
                    } catch (\Exception) {
                        $timezoneStart = $timezone;
                        $timezoneEnd = $timezone;
                    }

                    try {
                        $dtStartDate = new \DateTime($event['start'], $timezoneStart);
                        $dtEndDate = new \DateTime($event['end'], $timezoneEnd);
                    } catch (\Exception) {
                        continue;
                    }

                    if ($event['all_day']) {
                        $dtStart = $vevent->add('DTSTART', $dtStartDate->format('Ymd'));
                        $dtEnd = $vevent->add('DTEND', $dtEndDate->modify('+1 day')->format('Ymd'));
                        $dtStart['VALUE'] = 'DATE';
                        $dtEnd['VALUE'] = 'DATE';
                    } else {
                        $vevent->add('DTSTART', $dtStartDate);
                        $vevent->add('DTEND', $dtEndDate);
                    }

                    $visibility = $event['visibility'] == 'default' ? $calendar['default_event_visibility'] : $event['visibility'];
                    $visibility != 'public' && $vevent->add('CLASS', strtoupper($visibility));
                    $event['repeat_rule'] && $vevent->add('RRULE', $event['repeat_rule']);
                    $resultEvents[] = $vevent->serialize();
                }
            }

            exit(Event::wrapInVCalendar(implode("\n", $resultEvents), implode("\n", $resultTimezones)));
        } catch (\Exception) {
            Utils::exit404();
        }
    }
}