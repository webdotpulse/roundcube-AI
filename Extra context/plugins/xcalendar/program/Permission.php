<?php
namespace XCalendar;

require_once __DIR__ . '/Calendar.php';

class Permission
{
    public static function getDefaultCalendarPermissions(): object
    {
        return (object)[
            'publish_calendar' => false,
            'share_calendar' => false,
            'edit_events' => false,
            'see_details' => false,
        ];
    }

    /**
     * Returns the permission array with all values set to true.
     *
     * @return object
     */
    public static function getCalendarOwnerPermissions(): object
    {
        $permissions = self::getDefaultCalendarPermissions();
        foreach ($permissions as $key => $val) {
            $permissions->$key = true;
        }
        return $permissions;
    }

    /**
     * Decodes the permissions from the json string as they're stored in the db into an array.
     *
     * @param string $permissionJsonString
     * @return object
     */
    public static function decodeCalendarPermissions(string $permissionJsonString): object
    {
        $object = json_decode($permissionJsonString ?: '{}');
        is_object($object) || $object = new \stdClass();
        $permissions = self::getDefaultCalendarPermissions();

        foreach ($permissions as $key => $val) {
            if (!empty($object->$key)) {
                $permissions->$key = true;
            }
        }

        return $permissions;
    }

    /**
     * Returns the array of permissions for a calendar.
     *
     * @param $calendarId
     * @param $userId
     * @param $userEmail
     * @return object
     */
    public static function getCalendarPermissions($calendarId, $userId, $userEmail): object
    {
        $calendarId = (int)$calendarId;
        $userId = (int)$userId;

        if (!$calendarId || !$userId) {
            return self::getDefaultCalendarPermissions();
        }

        // if the current user is the owner of the calendar, he or she has all the rights
        if ($calendar = xdb()->row('xcalendar_calendars', ['id' => $calendarId, 'user_id' => $userId])) {
            if (in_array($calendar['type'], [Calendar::LOCAL, Calendar::CALDAV])) {
                return self::getCalendarOwnerPermissions();
            } else {
                return self::getDefaultCalendarPermissions();
            }
        }

        if (!$userEmail || !is_string($userEmail)) {
            return self::getDefaultCalendarPermissions();
        }

        // the calendar is a shared copy, get the permissions
        $permissions = xdb()->value(
            'permissions',
            'xcalendar_calendars_shared',
            ['email' => $userEmail, 'calendar_id' => $calendarId]
        );

        return $permissions ? self::decodeCalendarPermissions($permissions) : self::getDefaultCalendarPermissions();
    }

    /**
     * Checks if a user has the specified permission for the calendar. If more permissions need to be checked
     * in one function, it's better to use getCalendarPermissions and check the permission in the resulting array
     * to minimize db reads.
     *
     * @param $calendarId
     * @param string $permission
     * @param $userId
     * @param $userEmail
     * @return bool
     */
    public static function hasCalendarPermission($calendarId, string $permission, $userId, $userEmail): bool
    {
        $calendarId = (int)$calendarId;
        $userId = (int)$userId;

        if (!$calendarId || !$userId) {
            return false;
        }

        $permissions = self::getCalendarPermissions($calendarId, $userId, $userEmail);
        return !empty($permissions->$permission);
    }

    /**
     * Checks if the specified user is the owner of the calendar.
     *
     * @param $calendarId
     * @param $userId
     * @return bool
     */
    public static function isCalendarOwner($calendarId, $userId): bool
    {
        $calendarId = (int)$calendarId;
        $userId = (int)$userId;

        if (!$calendarId || !$userId) {
            return false;
        }

        return (bool)xdb()->row("xcalendar_calendars", ["id" => $calendarId, "user_id" => $userId]);
    }
}