<?php
namespace XCalendar;

/**
 * There can be only one birthday calendar per user.
 */
class ClientBirthday
{
    public static function enabled(): bool
    {
        return !empty(xrc()->config->get('xcalendar_birthday_calendar_enabled', true));
    }

    public static function getId(): ?string
    {
        if (!self::enabled()) {
            return null;
        }

        return xdb()->value(
            'id', 
            'xcalendar_calendars', 
            ['user_id' => xrc()->get_user_id(), 'type' => Calendar::BIRTHDAY]
        );
    }

    public static function getEvents(string $startTime, string $endTime): array
    {
        $rcmail = xrc();

        if (!self::enabled()) {
            return [];
        }

        if (!($calendarData = CalendarData::load(self::getId()))) {
            return [];
        }

        try {
            $startDateTime = new \DateTime($startTime);
            $endDateTime = new \DateTime($endTime);
            $startYear = (int) $startDateTime->format("Y");
            $endYear = (int) $endDateTime->format("Y");
        } catch (\Exception) {
            return [];
        }

        // compare on date only (Y-m-d) so a timezone offset between the range bounds and the
        // birthday date can't push a birthday across a midnight boundary
        $startDate = $startDateTime->format('Y-m-d');
        $endDate = $endDateTime->format('Y-m-d');

        $rcubeContacts = new \rcube_contacts($rcmail->db, $rcmail->get_user_id());
        $rcubeContacts->set_pagesize(9999);
        $contacts = $rcubeContacts->list_records(['name', 'birthday'], 0, true);
        $events = [];

        foreach ($contacts as $contact) {
            if (empty($contact['birthday'][0])) {
                continue;
            }

            try {
                $datetime = new \DateTime($contact['birthday'][0]);
            } catch (\Exception) {
                continue;
            }

            $month = (int) $datetime->format('m');
            $day   = (int) $datetime->format('d');
            $date  = false;

            // check every year the requested range spans, not just its first and last
            for ($year = $startYear; $year <= $endYear; $year++) {
                $d = $day;

                // a Feb-29 birthday in a non-leap year would otherwise overflow to Mar 1; pin it to Feb 28
                if ($month === 2 && $d === 29 && !checkdate(2, 29, $year)) {
                    $d = 28;
                }

                $candidate = sprintf('%04d-%02d-%02d', $year, $month, $d);

                if ($candidate >= $startDate && $candidate <= $endDate) {
                    $date = $candidate;
                    break;
                }
            }

            if ($date === false) {
                continue;
            }

            // find the first email
            $email = false;
            foreach ($contact as $key => $val) {
                if (str_starts_with($key, 'email')) {
                    if (is_array($val) && !empty($val[0])) {
                        $email = $val[0];
                        break;
                    }
                }
            }

            $events[] = [
                'id' => 0,
                'type' => Calendar::BIRTHDAY,
                'calendar' => $calendarData->get('name'),
                'calendar_id' => $calendarData->getId(),
                'title' => $rcmail->gettext([
                    'name' => 'xcalendar.birthday_event_title', 
                    'vars' => ['n' => $contact['name']]
                ]),
                'start' => $date,
                'end' => $date,
                'allDay' => true,
                'email' => $email,
                'backgroundColor' => $calendarData->get('bg_color'),
                'textColor' => $calendarData->get('tx_color'),
            ];
        }

        return $events;
    }
}