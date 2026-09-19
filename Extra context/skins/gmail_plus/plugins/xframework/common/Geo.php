<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * This file provides ip to country functions.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

class Geo
{
    /**
     * Gets the geolocation data for the specified ip.
     *
     * @param string|null $ip
     * @return array
     */
    public static function getDataFromIp(?string $ip = null): array
    {
        $data = [
            "ip" => $ip,
            "country_code" => false,
            "country_name" => false,
            "city" => false,
            "latitude" => false,
            "longitude" => false,
        ];

        if ($ip === null) {
            $ip = Utils::getRemoteAddr();
        }

        $rcmail = xrc();
        $maxMindDatabase = $rcmail->config->get('maxmind_database', false);
        $maxMindCity = $rcmail->config->get('maxmind_city', false);

        // check if the data for this configuration has been already retrieved
        $hash = md5($ip . '|' . $maxMindDatabase . '|' . (int)$maxMindCity);

        if (isset($_SESSION["xframework_geo_$hash"])) {
            return $_SESSION["xframework_geo_$hash"];
        }

        // get the geo data from the database
        self::getMaxMindData($ip, $data, $maxMindDatabase, $maxMindCity);
        $data["country_name"] = self::getCountryName((string)$data["country_code"]);
        $_SESSION["xframework_geo_$hash"] = $data;

        return $data;
    }

    /**
     * Returns the array of country names for the specified language taken from the countries directory.
     *
     * @param bool $includeUnknown
     * @param string $language
     * @return array
     */
    public static function getCountryArray(bool $includeUnknown = true, string $language = ""): array
    {
        // get the user's language code
        if (!$language) {
            $rcmail = \rcmail::get_instance();
            $array = explode("_", $rcmail->user->language);
            $language = $array[0];
        }

        $countries = @include(__DIR__ . "/countries/$language.php");

        if (empty($countries) && $language != "en") {
            $countries = include(__DIR__ . "/countries/en.php");
        }

        // @codeCoverageIgnoreStart
        if (empty($countries)) {
            return [];
        }
        // @codeCoverageIgnoreEnd

        if (!$includeUnknown) {
            unset($countries['ZZ']);
        }

        return $countries;
    }

    /**
     * Returns the country name given the country code.
     *
     * @param string $code
     * @return string
     */
    public static function getCountryName(string $code): string
    {
        $countries = self::getCountryArray();

        if (empty($code) || !array_key_exists($code, $countries)) {
            return "-";
        }

        return $countries[$code];
    }

    /**
     * Uses the MaxMind database to get the geo data.
     * http://dev.maxmind.com/geoip/
     *
     * @param string $ip
     * @param array $data
     * @param bool|string $maxMindDatabase
     * @param bool $maxMindCity
     */
    protected static function getMaxMindData(string $ip, array &$data, bool|string $maxMindDatabase = false,
                                             bool $maxMindCity = false): void
    {
        try {
            require_once __DIR__ . "/../vendor/autoload.php";

            // use the local country database unless a different database is specified
            if (!$maxMindDatabase) {
                $maxMindDatabase = __DIR__ . "/geo/GeoLite2-Country.mmdb";
                $maxMindCity = false;
            }

            $reader = new \GeoIp2\Database\Reader($maxMindDatabase);

            if ($maxMindCity) {
                // @codeCoverageIgnoreStart
                $record = $reader->city($ip);
                $data['city'] = $record->city->name;
                $data['latitude'] = $record->location->latitude;
                $data['longitude'] = $record->location->longitude;
                // @codeCoverageIgnoreEnd
            } else {
                $record = $reader->country($ip);
            }

            if (!empty($record->country->isoCode)) {
                $data["country_code"] = $record->country->isoCode;
            }
        } catch (\Exception) {
        }
    }
}