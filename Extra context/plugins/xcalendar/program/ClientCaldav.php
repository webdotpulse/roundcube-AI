<?php
namespace XCalendar;

use Sabre\DAV\Client;
use Sabre\Xml\Service as XmlService;
use XFramework\Utils;

class ClientCaldav
{
    public static function enabled(): bool
    {
        return (bool)xrc()->config->get('xcalendar_caldav_client_enabled', true);
    }

    /**
     * @throws \Exception
     */
    public static function create($calendarId): array
    {
        if (!self::enabled()) {
            throw new \Exception('CalDAV client functionality disabled. (289112)');
        }

        if (empty($calendarData = CalendarData::load($calendarId))) {
            throw new \Exception();
        }

        // the calendar must belong to the current user -- caldav calendars are never shared, so any other user's id is
        // unauthorized; without this an authenticated user could pass a victim's calendar id to
        // getRemoteEventList/removeEvent and read or delete the victim's events using the victim's stored credentials
        if ($calendarData->getUserId() != xrc()->get_user_id()) {
            throw new \Exception('Calendar access denied. (2819939)');
        }

        $properties = $calendarData->get('properties');

        if ($calendarData->getType() != Calendar::CALDAV ||
            empty($calendarData->getId()) ||
            !self::verifyProperties($properties)
        ) {
            throw new \Exception(
                xrc()->gettext([
                    'name' => 'xcalendar.cannot_connect_to_caldav_server',
                    'vars' => ['n' => $calendarData->get('name')]]) .
                ' (2819938)'
            );
        }

        self::assertSafeUrl(self::resolveServerUrl($properties));

        return [
            self::createClient(
                $properties['caldav_server_url'],
                $properties['caldav_username'],
                $properties['caldav_password']
            ),
            $properties['caldav_calendar_url'],
            $calendarData,
        ];
    }

    public static function verifyProperties($properties): bool
    {
        return is_array($properties) &&
            !empty($properties['caldav_server_url']) &&
            !empty($properties['caldav_calendar_url']) &&
            !empty($properties['caldav_username']) &&
            !empty($properties['caldav_password']);
    }

    public static function ensureProperties($properties): array
    {
        is_array($properties) || ($properties = []);
        isset($properties['caldav_server_url']) || ($properties['caldav_server_url'] = '');
        isset($properties['caldav_calendar_url']) || ($properties['caldav_calendar_url'] = '');
        isset($properties['caldav_username']) || ($properties['caldav_username'] = '');
        isset($properties['caldav_password']) || ($properties['caldav_password'] = '');

        return $properties;
    }

    /**
     * Makes a simple call to the server to verify the username and password.
     *
     * @param $serverUrl
     * @param $username
     * @param $password
     * @return bool
     */
    public static function checkPassword($serverUrl, $username, $password): bool
    {
        try {
            $result = self::createClient($serverUrl, $username, $password)
                ->propFind('', ['{DAV:}current-user-principal']);

            return !empty($result['{DAV:}current-user-principal'][0]['value']);
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Retrieves events from the calendar server and converts them to a usable array format.
     *
     * @param $calendarId
     * @param string $startTime
     * @param string $endTime
     * @return array
     * @throws \Exception
     */
    public static function getEvents($calendarId, string $startTime, string $endTime): array
    {
        list($client, $calendarUrl, $calendarData) = self::create($calendarId);
        $calendarName = $calendarData->get('name');
        $startTime = date("Ymd\THis\Z", strtotime($startTime));
        $endTime = date("Ymd\THis\Z", strtotime($endTime));

        $response = $client->request(
            'REPORT',
            $calendarUrl,
            "<c:calendar-query xmlns:d='DAV:' xmlns:c='urn:ietf:params:xml:ns:caldav'>
                <d:prop>
                    <d:getetag />
                    <c:calendar-data />
                </d:prop>
                <c:filter>
                    <c:comp-filter name='VCALENDAR'>
                        <c:comp-filter name='VEVENT'>
                            <c:time-range start='$startTime' end='$endTime' />
                        </c:comp-filter>
                    </c:comp-filter>
                </c:filter>                
            </c:calendar-query>",
            ["Depth" => "1", "Content-Type" => "application/xml; charset=utf-8"]
        );

        // create the resulting events array
        $veventArray = [];
        $hrefString = '';

        try {
            $responseItems = self::processResponse($response);
        } catch (\Exception $e) {
            throw new \Exception("Cannot retrieve events for calendar: $calendarName. " .
                $e->getMessage() . ' (299384)');
        }

        foreach ($responseItems as $item) {
            if (!empty($item['value']) && ($href = $item['value']->getHref())) {
                $properties = $item['value']->getResponseProperties();
                $vcalendar = $properties[200]["{urn:ietf:params:xml:ns:caldav}calendar-data"] ?? false;
                $etag = $properties[200]["{DAV:}getetag"] ?? "";

                if ($vcalendar) {
                    $veventArray[$href] = ["vcalendar" => $vcalendar, "etag" => $etag];
                } else if ($etag && str_ends_with($href, ".ics")) {
                    $hrefString .= "<d:href>$href</d:href>";
                }
            }
        }

        // some calendars, like zoho, don't return vcalendar in the previous step, we need to run another request, passing
        // all the events hrefs to get their vcalendars
        if (!empty($hrefString)) {
            $response = $client->request(
                'REPORT',
                $calendarUrl,
                "<c:calendar-multiget xmlns:d='DAV:' xmlns:c='urn:ietf:params:xml:ns:caldav'>
                    <d:prop>
                        <d:getetag/>
                        <c:calendar-data></c:calendar-data>
                    </d:prop>
                    $hrefString
                </c:calendar-multiget>",
                ["Depth" => "1", "Content-Type" => "application/xml; charset=utf-8"]
            );

            try {
                $responseItems = self::processResponse($response);
            } catch (\Exception $e) {
                throw new \Exception("Cannot retrieve events for calendar: $calendarName. " .
                    $e->getMessage() . ' (299385)');
            }

            foreach ($responseItems as $item) {
                if (!empty($item['value']) && ($href = $item['value']->getHref())) {
                    $properties = $item['value']->getResponseProperties();
                    $vcalendar = $properties[200]["{urn:ietf:params:xml:ns:caldav}calendar-data"] ?? false;
                    $etag = $properties[200]["{DAV:}getetag"] ?? "";

                    if ($vcalendar) {
                        $veventArray[$href] = ["vcalendar" => $vcalendar, "etag" => $etag];
                    }
                }
            }
        }

        // convert the vcalendar items to event arrays that we need to display in the frontend
        $event = new Event();
        $result = [];

        // get the current user's emails (login email and identity emails)
        $userEmails = Utils::getUserEmails();
        $categories = Event::getCategories(true);
        $useBorders = xrc()->config->get('xcalendar_event_border', Event::getDefaultSettings()['event_border']);
        $readonly = $calendarData->getProperty('caldav_readonly');

        foreach ($veventArray as $href => $item) {
            foreach ($event->vEventToDataArray((string)$item['vcalendar'], false) as $eventArray) {
                if (!empty($eventArray['title'])) {
                    // add repeated events
                    $eventArray['repeat_rule'] = $eventArray['repeat_rule_orig'];
                    $array = array_merge([$eventArray], $event->getRepeatedEvents($eventArray, $startTime, $endTime));

                    if ($useBorders && array_key_exists($eventArray['category'], $categories)) {
                        $borderColor = $categories[$eventArray['category']];
                    } else {
                        $borderColor = 'transparent';
                    }

                    foreach ($array as $val) {
                        $data = [
                            'href' => $href,
                            'id' => $val['uid'],
                            'etag' => $item['etag'],
                            'type' => Calendar::CALDAV,
                            'calendar' => $calendarData->get('name'),
                            'calendar_id' => $calendarId,
                            'title' => $val['title'],
                            'start' => $val['start'],
                            'end' => $val['end'],
                            'allDay' => (bool)(int)$val['all_day'],
                            'description' => EventData::getDescriptionForPreview((string)($val['description'] ?? '')),
                            'link' => $val['url'] ?? '',
                            'location' => $val['location'] ?? '',
                            'backgroundColor' => $calendarData->get('bg_color'),
                            'textColor' => $calendarData->get('tx_color'),
                            'borderColor' => $borderColor,
                            'has_attendees' => !empty($val['attendees']),
                            'attendance' => [0, 0, 0, 0, 0],
                            'vcalendar' => (string)$item['vcalendar'],
                            'canEdit' => !$readonly,
                            'editable' => !$readonly,
                        ];

                        // add attendance information (will be used in preview)
                        foreach ($val['attendees'] as $attendee) {
                            $data['attendance'][$attendee['status'] ?? 0]++;

                            // if current user is an attendee show the yes/no/maybe/more response options
                            if (in_array($attendee['email'], $userEmails)) {
                                $data['show_attendance_response'] = true;
                                $data['attendance_status'] = (int)$attendee['status'];
                            }
                        }

                        $result[] = $data;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public static function removeEvent($calendarId, $href): void
    {
        list($client, , $calendarData) = self::create($calendarId);
        self::assertHrefHostAllowed($href, $calendarData);
        $response = $client->request('DELETE', $href);
        self::processResponse($response);
    }

    /**
     * @throws \Exception
     */
    public static function saveEvent($calendarId, array $data): void
    {
        $rcmail = xrc();

        try {
            list($client, $calendarUrl, $calendarData) = self::create($calendarId);

            $data['repeat_rule'] = empty($data['repeat_rule']) ? "" : EventData::encodeRRule($data['repeat_rule']);

            $vevent = EventData::createVEvent($data);
            $timezoneString = "";

            // if not all day, extract timezones to add to vcalendar text
            if (empty($data['all_day'])) {
                Timezone::extractZonesFromVEvent(
                    $vevent, 
                    $rcmail->config->get("timezone", "UTC"), 
                    $timezoneStart, 
                    $timezoneEnd
                );
                $timezoneString = Timezone::getVTimezone($timezoneStart) .
                    ($timezoneStart != $timezoneEnd ? Timezone::getVTimezone($timezoneEnd) : "");
            }

            // href and etag will be set when editing events; href might be different from uid, that's why we keep track
            // of href throughout the life of the event; etag is needed when editing event; when creating event we use a
            // newly created uid as href - uid is generated when creating EventData()
            // we're appending .ics to the urls of the newly created events because iCloud CalDAV servers require it and
            // throw 400 errors if the url doesn't end with .ics, it doesn't seem to negatively affect any other servers
            if (!empty($data['href'])) {
                self::assertHrefHostAllowed($data['href'], $calendarData);
            }

            $response = $client->request(
                'PUT',
                $data['href'] ?: ($calendarUrl . $data['uid'] . '.ics'),
                Event::wrapInVCalendar($vevent, $timezoneString),
                [
                    'Content-Type' => 'text/calendar; charset=utf-8',
                    'If-Match' => $data['etag'],
                ]
            );

            self::processResponse($response);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                Utils::logError("Unable to save CalDAV event: $message");
            }
            throw new \Exception($rcmail->gettext('xcalendar.caldav_client_error_save_event'));
        }
    }

    /**
     * Connects to the CalDAV server and returns the list of available calendars. This is a static function that doesn't require
     * $calendarUrl, since at this point in time we don't have it.
     *
     * @param string $serverUrl
     * @param string $username
     * @param string $password
     * @return array
     * @throws \Exception
     */
    public static function findCalendars(string $serverUrl, string $username, string $password): array
    {
        $calendars = [];
        $userId = xrc()->get_user_id();
        $client = self::createClient($serverUrl, $username, $password);

        // get the principal url
        $result = $client->propFind('', ['{DAV:}current-user-principal']);

        if (!empty($result['{DAV:}current-user-principal'][0]['value'])) {
            // get the calendar url
            $result = $client->propFind(
                $result['{DAV:}current-user-principal'][0]['value'],
                ['{urn:ietf:params:xml:ns:caldav}calendar-home-set']
            );

            if (!empty($result['{urn:ietf:params:xml:ns:caldav}calendar-home-set'][0]['value'])) {
                // get the available calendars
                $url = $result['{urn:ietf:params:xml:ns:caldav}calendar-home-set'][0]['value'];
                $properties = [
                    '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set',
                    '{http://calendarserver.org/ns/}getctag',
                    '{DAV:}current-user-privilege-set',
                    '{DAV:}displayname',
                    '{DAV:}acl',
                ];

                try {
                    $result = $client->propFind($url, $properties, 1);
                } catch (\Exception $e) {
                    // if getting 501 Not Implemented, try running without ACL (ACL is used as a backup property for
                    // determining whether the calendar is read-only in isCalendarReadOnly() below). We don't know which
                    // property is not supported, but ACL is the likely candidate (it's not supported on SOGo)
                    if ($e->getCode() == 501) {
                        $result = $client->propFind($url, array_diff($properties, ['{DAV:}acl']), 1);
                    } else {
                        Utils::logError($e->getMessage() . " (839923)");
                        throw $e;
                    }
                }

                // iterate and collect the calendars into an array
                foreach ($result as $calendarUrl => $data) {
                    if (!empty($data['{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set'][0])) {
                        $array = $data['{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set'][0];

                        if (empty($array['name']) ||
                            empty($array['attributes']['name']) ||
                            empty($data['{DAV:}displayname']) ||
                            $array['name'] != '{urn:ietf:params:xml:ns:caldav}comp' ||
                            $array['attributes']['name'] != 'VEVENT'
                        ) {
                            continue;
                        }

                        $uniqueId = md5(self::resolveServerUrl([
                            'caldav_server_url' => $serverUrl,
                            'caldav_calendar_url' => $calendarUrl
                        ]));

                        // check if the calendar is already added and disable the checkbox if it is
                        $disabled = xdb()->row(
                            'xcalendar_calendars',
                            ['user_id' => $userId, 'type' => Calendar::CALDAV, 'url' => $uniqueId]
                        );

                        $calendars[] = [
                            'id' => 'caldav-calendar-$uniqueId',
                            'url' => $calendarUrl,
                            'name' => $data['{DAV:}displayname'],
                            'ctag' => $data['{http://calendarserver.org/ns/}getctag'] ?? false,
                            'checked' => !$disabled,
                            'disabled' => $disabled,
                            'readonly' => self::isCalendarReadOnly($client, $data, $calendarUrl),
                        ];
                    }
                }
            }
        }

        return $calendars;
    }

    /**
     * Checks if the calendar is read-only by trying to create/delete an event.
     *
     * @param $client
     * @param array $data
     * @param string $calendarUrl
     * @return bool
     */
    public static function isCalendarReadOnly($client, array $data, string $calendarUrl): bool
    {
        // check if privilege set exists and if it grants write rights
        if (!empty($data['{DAV:}current-user-privilege-set'])) {
            return !self::findPropertyName($data['{DAV:}current-user-privilege-set'], '{DAV:}write');
        }

        // if not, check if acl exists and grants write rights
        if (!empty($data['{DAV:}acl'])) {
            return !self::findPropertyName($data['{DAV:}acl'], '{DAV:}write');
        }

        // if not, try creating a test event
        $readonly = true;
        $eventUid = 'rcp_test_' . Utils::uuid();
        $eventUrl = $calendarUrl . $eventUid . '.ics';

        try {
            // create a test event
            $response = $client->request(
                'PUT',
                $eventUrl,
                "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\nUID:$eventUid\r\nSUMMARY:Test\r\n".
                "DTSTART:20200101T000000Z\r\nDTEND:20200101T010000Z\r\nEND:VEVENT\r\nEND:VCALENDAR",
                ['Content-Type' => 'text/calendar; charset=utf-8',]
            );
            self::processResponse($response);
            $readonly = false;

            // try deleting the test event by its url, if it fails, we'll try by href (event's url and href might not be the same)
            $response = $client->request('DELETE', $eventUrl);
            self::processResponse($response);
        } catch (\Exception) {
            if (!$readonly) {
                // if the event has been created, but deleting by url has failed, retrieve all events, find the test event
                // get its href, and try deleting by href
                foreach ($client->propFind($calendarUrl, ["{DAV:}href"], 1) as $href => $val) {
                    if (str_contains($href, $eventUid)) {
                        $client->request("DELETE", $href);
                        break;
                    }
                }
            }
        }

        return $readonly;
    }

    /**
     * Iterates a sabredav property array looking for a value of the name key. Returns true if the name is found.
     *
     * @param array $array
     * @param string $name
     * @return bool
     */
    public static function findPropertyName(array $array, string $name): bool
    {
        foreach ($array as $item) {
            if (!empty($item['name'])) {
                if ($item['name'] == $name) {
                    return true;
                }

                if (!empty($item['value']) && is_array($item['value'])) {
                    if (self::findPropertyName($item['value'], $name)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function resolveServerUrl(array $properties): string
    {
        if (empty($properties['caldav_server_url']) || empty($properties['caldav_calendar_url'])) {
            return "";
        }

        try {
            return \Sabre\Uri\resolve($properties['caldav_server_url'], $properties['caldav_calendar_url']);
        } catch (\Exception) {
            return $properties['caldav_server_url'] . $properties['caldav_calendar_url'];
        }
    }

    /**
     * @throws \Exception
     */
    protected static function processResponse(array $response): array
    {
        // parse the body regardless of whether status reports error or not
        if (!isset($response['body'])) {
            throw new \Exception('Invalid CalDAV server response');
        }

        if (empty($response['body'])) {
            $data = [];
        } else {
            try {
                $xml = new XmlService();
                $xml->elementMap['{DAV:}response'] = "Sabre\DAV\Xml\Element\Response";
                $data = $xml->parse($response['body']);
            } catch (\Exception) {
                throw new \Exception('Cannot parse CalDAV server response');
            }

            if (!is_array($data)) {
                $data = [];
            }
        }

        // if error, get the error message: first try "message" and if it doesn't exist, try "exception"
        if (empty($response['statusCode']) || $response['statusCode'] < 200 || $response['statusCode'] > 207) {
            $message = "";

            foreach ($data as $item) {
                if (!empty($item['name']) && 
                    !empty($item['value']) && 
                    $item['name'] == '{http://sabredav.org/ns}message'
                ) {
                    $message = $item['value'];
                }
            }

            if (empty($message)) {
                foreach ($data as $item) {
                    if (!empty($item['name']) && 
                        !empty($item['value']) && 
                        $item['name'] == '{http://sabredav.org/ns}exception'
                    ) {
                        $message = $item['value'];
                    }
                }
            }

            if (empty($message)) {
                foreach ($data as $item) {
                    if (!empty($item['value']) && is_array($item['value'])) {
                        foreach ($item['value'] as $val) {
                            if (!empty($val['value']) && is_string($val['value'])) {
                                $message .= $val['value'] . ' / ';
                            }
                        }
                    }
                }
            }

            throw new \Exception(trim($message));
        }

        return $data;
    }

    /**
     * @throws \Exception
     */
    protected static function createClient($serverUrl, $username, $password): Client
    {
        $rcmail = xrc();
        $username = substr(trim((string)$username), 0, 250);
        $password = substr((string)$password, 0, 250);
        $settings = [
            'baseUri' => substr(trim((string)$serverUrl), 0, 250),
            'userName' => $username === '%u' ? $rcmail->get_user_name() : $username,
            'password' => $password === '%p' ? $rcmail->get_user_password() : $password,
        ];

        // reject urls that resolve to internal/reserved addresses (SSRF protection)
        self::assertSafeUrl($settings['baseUri']);

        // if zlib support is available in curl, add encodings to curl header (identity + deflate + gzip)
        $info = curl_version();
        if ($info && $info['features'] & CURL_VERSION_LIBZ) {
            $settings['encoding'] = Client::ENCODING_ALL;
        }

        return new Client($settings);
    }

    /**
     * Validates an outbound CalDAV server url before the server connects to it.
     *
     * Always enforced (no functionality cost): the scheme must be http/https and a host must be
     * present. This blocks dangerous SSRF primitives such as file://, gopher://, dict://.
     *
     * IP-range filtering (rejecting loopback / link-local / private / reserved addresses — the part
     * that protects cloud-metadata endpoints and internal services) is OPT-IN, because most
     * deployments are self-hosted and legitimately run their CalDAV server on a private/internal
     * address or on 127.0.0.1. Multi-tenant / hosted providers, where mailbox users are not trusted,
     * should turn it on with xcalendar_caldav_block_private_hosts = true.
     *
     * @param string $url
     * @throws \Exception
     */
    protected static function assertSafeUrl(string $url): void
    {
        $rc = xrc();
        $parts = parse_url($url);

        if (empty($parts['scheme']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            throw new \Exception($rc->gettext('xcalendar.invalid_caldav_url') . ' (472884)');
        }

        if (empty($host = $parts['host'] ?? '')) {
            throw new \Exception($rc->gettext('xcalendar.invalid_caldav_url') . ' (472885)');
        }

        // IP-range filtering is opt-in so we don't break on-prem CalDAV servers on internal networks.
        if (!$rc->config->get("xcalendar_caldav_block_private_hosts", false)) {
            return;
        }

        // strict mode: resolve the host to its ip(s) (v4 + v6)
        $ips = [];

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips[] = $host;
        } else {
            foreach ((array)@dns_get_record($host, DNS_A | DNS_AAAA) as $record) {
                empty($record['ip'])   || $ips[] = $record['ip'];
                empty($record['ipv6']) || $ips[] = $record['ipv6'];
            }
        }

        if (empty($ips)) {
            throw new \Exception($rc->gettext('xcalendar.cannot_resolve_caldav_host') . ' (472886)');
        }

        // reject private (10/8, 172.16/12, 192.168/16, fc00::/7) and reserved (incl. 127/8, 169.254/16, ::1) ranges
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new \Exception($rc->gettext('xcalendar.private_caldav_addresses_not_allowed') . ' (472887)');
            }
        }
    }

    /**
     * Rejects an event href that carries its own host different from the calendar's (already validated)
     * server host. Relative hrefs (the normal case) carry no host and pass through untouched, so this adds
     * no DNS lookups on the hot path.
     *
     * @throws \Exception
     */
    protected static function assertHrefHostAllowed($href, CalendarData $calendarData): void
    {
        $parts = parse_url((string)$href);

        if (!empty($parts['host'])) {
            $serverHost = parse_url((string)$calendarData->getProperty("caldav_server_url"), PHP_URL_HOST);

            if (empty($serverHost) || strcasecmp($parts['host'], $serverHost) !== 0) {
                throw new \Exception('Disallowed event href host.');
            }
        }
    }
}