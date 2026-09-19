<?php
namespace XCalendar;

class ClientGoogle
{
    /**
     * @throws \Exception
     */
    public static function getEvents($calendarId, string $startTime, string $endTime): array
    {
        $rcmail = xrc();

        if (empty($rcmail->config->get("xcalendar_google_calendar_key"))) {
            return [];
        }

        $calendarData = CalendarData::load($calendarId);

        // the calendar must belong to the current user (google/holiday calendars are never shared)
        if (empty($calendarData) ||
            $calendarData->getUserId() != $rcmail->get_user_id() ||
            empty($calendarUrl = $calendarData->get("url"))
        ) {
            return [];
        }

        // get the data from the server
        $type = $calendarData->getType();

        try {
            $data = self::getDataFromServer($type, $calendarUrl, $startTime, $endTime);
        } catch (\Exception $e) {
            if ($message = $e->getMessage()) {
                $name = $type == Calendar::GOOGLE ? 'Google' : 'holiday';
                \rcube::write_log("errors", "[xcalendar] Cannot load $name calendar events: " . $message);
            }
            throw new \Exception($rcmail->gettext([
                "name" => "xcalendar." . ($type == Calendar::GOOGLE ? "cannot_load_google_calendar" : "cannot_load_events"),
                "vars" => ["c" => $calendarData->get("name")]
            ]));
        }

        // create and return the list of items
        $events = [];
        foreach ($data['items'] as $event) {
            if (!empty($event['summary'])) {
                $events[] = [
                    "id" => 0,
                    "type" => $type,
                    "calendar" => $calendarData->get("name"),
                    "calendar_id" => $calendarId,
                    "title" => $event['summary'],
                    "start" => $event['start']['date'] ?? $event['start']['dateTime'],
                    "end" => $event['end']['date'] ?? $event['end']['dateTime'],
                    "allDay" => empty($event['start']['dateTime']),
                    "description" => EventData::getDescriptionForPreview((string)($event['description'] ?? "")),
                    "link" => $event['htmlLink'] ?? "",
                    "location" => $event['location'] ?? "",
                    "backgroundColor" => $calendarData->get("bg_color"),
                    "textColor" => $calendarData->get("tx_color"),
                ];
            }
        }

        return $events;
    }

    /**
     * @throws \Exception
     */
    public static function getDataFromServer($type, $calendarUrl, $startTime, $endTime): array
    {
        $rcmail = xrc();
        $language = empty($rcmail->user->data['language']) ? "en" : substr($rcmail->user->data['language'], 0, 2);
        $calendarId = rawurlencode($type == Calendar::HOLIDAY
            ? "$language.$calendarUrl#holiday@group.v.calendar.google.com"
            : $calendarUrl);

        $url = "https://www.googleapis.com/calendar/v3/calendars/$calendarId/events" .
            "?singleEvents=true" .
            "&timeMin=" . gmdate('Y-m-d\TH:i:s\Z', strtotime($startTime)) .
            "&timeMax=" . gmdate('Y-m-d\TH:i:s\Z', strtotime($endTime)) .
            "&key=" . substr($rcmail->config->get("xcalendar_google_calendar_key"), 0, 250);

        if (!function_exists('curl_init')) {
            throw new \Exception('cURL is not installed');
        }

        if (($curl = curl_init($url)) === false) {
            throw new \Exception('Cannot initialize cURL');
        }

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $result = curl_exec($curl);

        if ($result === false) {
            throw new \Exception("cURL error " . curl_errno($curl) . ": " . curl_error($curl));
        }

        $httpCode = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        if ($httpCode >= 400) {
            throw new \Exception("HTTP $httpCode error from Google Calendar API");
        }

        try {
            $data = json_decode(trim($result), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \Exception('Failed to parse JSON from server: ' . $e->getMessage());
        }

        if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
            throw new \Exception('Invalid or unexpected data received from the server.');
        }

        return $data;
    }
}