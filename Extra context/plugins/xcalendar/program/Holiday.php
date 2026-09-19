<?php
namespace XCalendar;

/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

class Holiday
{
    const ALLOWED_HOLIDAY_COUNT = 6;

    public static function getAdded(): array
    {
        $result = [];
        $calendars = xdb()->all(
            "SELECT url FROM {xcalendar_calendars} WHERE type = ? AND user_id = ?",
            [Calendar::HOLIDAY, xrc()->get_user_id()]
        );

        foreach ($calendars as $calendar) {
            $result[] = $calendar['url'];
        }

        return $result;
    }

    public static function getList(): array
    {
        $result = [];

        // the language comes from the user's roundcube profile (roundcube validates it), but since it's used to build
        // an include path, guard it against anything other than letters/underscores before using it
        $language = (string)xrc()->user->language;
        $file = preg_match('/^[a-zA-Z_]+$/', $language) ? __DIR__ . "/holiday/$language.php" : "";
        $groups = include($file && file_exists($file) ? $file : __DIR__ . "/holiday/en_US.php");

        foreach ($groups as $group => $items) {
            foreach ($items as $url => $name) {
                $result[$group][] = [
                    "url" => $url,
                    "name" => $name,
                ];
            }
        }

        return $result;
    }

    public static function add($url, $name)
    {
        Color::getRandomColors($txColor, $bgColor);
        $db = xdb();

        if ($db->insert(
            "xcalendar_calendars",
            [
                "user_id" => xrc()->get_user_id(),
                "type" => Calendar::HOLIDAY,
                "url" => substr((string)$url, 0, 250),
                "name" => substr((string)$name, 0, 250),
                'bg_color' => $bgColor,
                "tx_color" => $txColor,
            ]
        )) {
            return $db->lastInsertId("xcalendar_calendars");
        }

        return false;
    }

    public static function remove($id): bool
    {
        return xdb()->remove(
            "xcalendar_calendars",
            [
                "id" => (int)$id,
                "user_id" => xrc()->get_user_id(),
                "type" => Calendar::HOLIDAY,
            ]
        );
    }

}