<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * This class handles the date/time formats and their conversions to the formats used by different systems/components.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

require_once __DIR__ . '/Singleton.php';

class Format
{
    use Singleton;

    const DECIMAL_SEPARATOR_SYMBOL = 0;
    const GROUPING_SEPARATOR_SYMBOL = 1;
    const MONETARY_SEPARATOR_SYMBOL = 2;
    const MONETARY_GROUPING_SEPARATOR_SYMBOL = 3;
    private \rcube $rcmail;
    private array $dateFormats = [];
    private array $timeFormats = [];
    private array $dmFormats = [];

    private array $separators = [
        'sq_AL' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'ar' => [0 => '٫', 1 => '٬', 2 => '٫', 3 => '٬'],
        'ar_SA' => [0 => '٫', 1 => '٬', 2 => '٫', 3 => '٬'],
        'hy_AM' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'ast' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'az_AZ' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'eu_ES' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'be_BE' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'bn_BD' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'bs_BA' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'br' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'bg_BG' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'ca_ES' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'zh_CN' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'zh_TW' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'hr_HR' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'cs_CZ' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'da_DK' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'fa_AF' => [0 => '٫', 1 => '٬', 2 => '٫', 3 => '٬'],
        'de_DE' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'de_CH' => [0 => '.', 1 => "'", 2 => '.', 3 => "'"],
        'nl_NL' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'en_CA' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'en_GB' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'en_US' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'eo' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'et_EE' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'fo_FO' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'fi_FI' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'nl_BE' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'fr_FR' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'gl_ES' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'ka_GE' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'el_GR' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'he_IL' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'hi_IN' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'hu_HU' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'is_IS' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'id_ID' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'ia' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'ga_IE' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'it_IT' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'ja_JP' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'km_KH' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'kn_IN' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'ko_KR' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'ku' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'lv_LV' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'lt_LT' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'lb_LU' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'mk_MK' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'mn_MN' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'ms_MY' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'ml_IN' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'mr_IN' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'ne_NP' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'nb_NO' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'nn_NO' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'ps' => [0 => '٫', 1 => '٬', 2 => '٫', 3 => '٬'],
        'fa_IR' => [0 => '٫', 1 => '٬', 2 => '٫', 3 => '٬'],
        'pl_PL' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'pt_BR' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'pt_PT' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'ro_RO' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'ru_RU' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'sr_CS' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'si_LK' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'sk_SK' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'sl_SI' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'es_AR' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'es_ES' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'es_419' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'sv_SE' => [0 => ',', 1 => ' ', 2 => ':', 3 => ' '],
        'ta_IN' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'ti' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'th_TH' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'tr_TR' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'uk_UA' => [0 => ',', 1 => ' ', 2 => ',', 3 => ' '],
        'ur_PK' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'vi_VN' => [0 => ',', 1 => '.', 2 => ',', 3 => '.'],
        'cy_GB' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
        'fy_NL' => [0 => '.', 1 => ',', 2 => '.', 3 => ','],
    ];

    /**
     * Format constructor
     *
     * @codeCoverageIgnore
     */
    public function __construct() {
        $this->rcmail = xrc();
        // $this->rcmail might be null when running some /bin scripts
        $this->rcmail && $this->loadFormats();
    }

    /**
     * Returns the user date format.
     *
     * @param string $type
     * @return string
     */
    public function getDateFormat(string $type = "php"): string
    {
        $valid = in_array($type, ["php", "moment", "datepicker", "flatpickr"]);
        return $valid ? $this->dateFormats[$type] : "";
    }

    /**
     * Returns the user's time format.
     *
     * @param string $type
     * @return string
     */
    public function getTimeFormat(string $type = "php"): string
    {
        $valid = in_array($type, ["php", "moment", "flatpickr"]);
        return $valid ? $this->timeFormats[$type] : "";
    }

    /**
     * Returns the user's date and time format.
     *
     * @param string $type
     * @return string
     */
    public function getDateTimeFormat(string $type = "php"): string
    {
        return $this->getDateFormat($type) . " " . $this->getTimeFormat($type);
    }

    public function getDateFormats(): array
    {
        return $this->dateFormats;
    }

    public function getTimeFormats(): array
    {
        return $this->timeFormats;
    }

    public function getDmFormats(): array
    {
        return $this->dmFormats;
    }

    /**
     * Converts string to time taking into consideration the user's date/time format. It fixes the problem of
     * strtotime() or DateTime() not working properly with formats day/month/year and converting considering them
     * month/day/year.
     *
     * @param string $dateTimeString
     * @param string $type
     * @return bool|int
     */
    public function stringToTimeWithFormat(string $dateTimeString, string $type = "php"): bool|int
    {
        $dateTimeString = trim($dateTimeString);
        $format = $this->getDateFormat($type);

        if (str_contains($dateTimeString, " ")) {
            $format .= " " . $this->getTimeFormat($type);
        }

        if (!($dateTime = \DateTime::createFromFormat($format, $dateTimeString))) {
            return false;
        }

        return $dateTime->getTimestamp();
    }

    /**
     * Formats the date according to the format specified in the user's config.
     *
     * @param $date
     * @param bool|string $textOnEmpty
     * @return string|bool
     */
    public function formatDate($date, bool|string $textOnEmpty = false): bool|string
    {
        if (empty($date)) {
            return $textOnEmpty;
        }

        return date($this->getDateFormat(), is_numeric($date) ? $date : strtotime($date));
    }

    /**
     * Formats the time according to the format specified in the user's config.
     *
     * @param $time
     * @param bool|string $textOnEmpty
     * @return string|bool
     */
    public function formatTime($time, bool|string $textOnEmpty = false): bool|string
    {
        if (empty($time)) {
            return $textOnEmpty;
        }

        return date($this->getTimeFormat(), is_numeric($time) ? $time : strtotime($time));
    }

    /**
     * Formats the date and time according to the format specified in the user's config.
     *
     * @param $date - string or integer representation of date
     * @param bool|string $textOnEmpty
     * @return string|bool
     */
    public function formatDateTime($date, bool|string $textOnEmpty = false): bool|string
    {
        if (empty($date)) {
            return $textOnEmpty;
        }

        return date($this->getDateFormat() . " " . $this->getTimeFormat(), is_numeric($date) ? $date : strtotime($date));
    }

    /**
     * Formats a currency number using the locale-specific separators.
     *
     * @param $number
     * @param bool|int $decimals
     * @param bool|string $locale
     * @return string
     */
    public function formatCurrency($number, bool|int $decimals = false, bool|string $locale = false): string
    {
        return $this->formatNumberOrCurrency("monetary", $number, $decimals, $locale);
    }

    /**
     * Formats a regular number using the locale-specific separators.
     *
     * @param $number
     * @param bool|int $decimals
     * @param bool|string $locale
     * @return string
     */
    public function formatNumber($number, bool|int $decimals = false, bool|string $locale = false): string
    {
        return $this->formatNumberOrCurrency("decimal", $number, $decimals, $locale);
    }

    /**
     * Returns the locale specific number formatting separatators.
     *
     * @param bool|string $locale
     * @return array
     */
    public function getSeparators(bool|string $locale = false): array
    {
        if (!$locale) {
            $locale = $this->rcmail->user->language;
        }

        if (!array_key_exists($locale, $this->separators)) {
            $locale = 'en_US';
        }

        return $this->separators[$locale];
    }

    /**
     * Converts a float to string without regard for the locale. PHP automatically changes the delimiter used depending
     * on the locale set using setlocale(), so depending on the language selected by the user we might end up with
     * 3,1415 when converting floats to strings. This function leaves the dot delimiter intact when converting to
     * string.
     *
     * @param $float
     * @return string
     */
    public static function floatToString($float): string
    {
        if (!is_float($float)) {
            return $float;
        }

        $conv = localeconv();
        return str_replace($conv['decimal_point'], '.', $float);
    }

    /**
     * Returns a formatted number, either decimal or currency.
     *
     * @param string $type Specify 'monetary' or 'decimal'.
     * @param $number
     * @param bool|int $decimals
     * @param bool|string $locale
     * @return string
     */
    protected function formatNumberOrCurrency(string $type, $number, bool|int $decimals = false,
                                              bool|string $locale = false): string
    {
        if ($type == "monetary") {
            $separator = Format::MONETARY_SEPARATOR_SYMBOL;
            $groupingSeparator = Format::MONETARY_GROUPING_SEPARATOR_SYMBOL;
        } else {
            $separator = Format::DECIMAL_SEPARATOR_SYMBOL;
            $groupingSeparator = Format::GROUPING_SEPARATOR_SYMBOL;
        }

        if (!$decimals) {
            // uncomment to trim the trailing zeros from decimals: 2.1 instead of 2.10
            //$decimals = strlen(trim((string)(($number - round($number)) * 100), 0));
            $decimals = $number - round($number) == 0 ? 0 : 2;
        }

        if (!$locale) {
            $locale = $this->rcmail->user->language;
        }

        if (!array_key_exists($locale, $this->separators)) {
            $locale = 'en_US';
        }

        return number_format(
            $number,
            $decimals,
            $this->separators[$locale][$separator],
            $this->separators[$locale][$groupingSeparator]
        );
    }

    /**
     * Different components use different formats for date and time, we're creating an array of converted formats
     * that can be used in javascript.
     */
    protected function loadFormats(): void
    {
        // date format
        $dateFormat = $this->rcmail->config->get("date_format", "m/d/Y");

        $this->dateFormats = [
            "php" => $dateFormat,
            "moment" => $this->getMomentDateFormat($dateFormat),
            "datepicker" => $this->getDatepickerDateFormat($dateFormat),
            "flatpickr" => $dateFormat, // doesn't need any conversion
        ];

        // time format
        $timeFormat = $this->rcmail->config->get("time_format", "H:i");

        $this->timeFormats = [
            "php" => $timeFormat,
            "moment" => $this->getMomentTimeFormat($timeFormat),
            "datepicker" => $this->getDatepickerTimeFormat($timeFormat),
            "flatpickr" => $this->getFlatpickrTimeFormat($timeFormat),
        ];

        // day/month format
        $dmFormat = trim(str_replace("Y", "", $dateFormat), " /.-");

        $this->dmFormats = [
            "php" => $dmFormat,
            "moment" => $this->getMomentDateFormat($dmFormat),
            "datepicker" => $this->getDatepickerDateFormat($dmFormat),
            "flatpickr" => $dmFormat, // doesn't need any conversion
        ];

        // set js variables
        if (!empty($this->rcmail->output)) {
            $this->rcmail->output->set_env("dateFormats", $this->dateFormats);
            $this->rcmail->output->set_env("dmFormats", $this->dmFormats);
            $this->rcmail->output->set_env("timeFormats", $this->timeFormats);
        }
    }

    /**
     * Returns the user php date format converted to the javascript moment format.
     *
     * @param string $format
     * @return string
     */
    protected function getMomentDateFormat(string $format): string
    {
        $replace = [
            "D" => "*1",
            "d" => "DD",
            "l" => "dddd",
            "j" => "D",
            "*1" => "ddd",
            "n" => "*2",
            "M" => "MMM",
            "m" => "MM",
            "F" => "MMMM",
            "*2" => "M",
            "Y" => "YYYY",
            "y" => "YY",
        ];

        return str_replace(array_keys($replace), array_values($replace), $format);
    }

    /**
     * Returns the user php time format converted to the javascript moment format.
     *
     * @param string $format
     * @return string
     */
    protected function getMomentTimeFormat(string $format): string
    {
        $replace = [
            "H" => "HH",
            "G" => "H",
            "h" => "hh",
            "g" => "h",
            "i" => "mm",
            "s" => "ss",
        ];

        return str_replace(array_keys($replace), array_values($replace), $format);
    }

    /**
     * Returns the user php date format converted to the jquery ui datepicker format.
     *
     * @param string $format
     * @return string
     */
    protected function getDatepickerDateFormat(string $format): string
    {
        $replace = [
            "d" => "dd",
            "j" => "d",
            "m" => "mm",
            "n" => "m",
            "F" => "MM",
            "Y" => "yy",
        ];

        return str_replace(array_keys($replace), array_values($replace), $format);
    }

    /**
     * Returns the user php date format converted to the jquery ui datepicker format.
     *
     * @param string $format
     * @return string
     */
    protected function getDatepickerTimeFormat(string $format): string
    {
        $replace = [
            "H" => "HH",
            "G" => "H",
            "h" => "hh",
            "g" => "h",
            "i" => "mm",
            "s" => "ss",
            "a" => "tt",
            "A" => "TT",
        ];

        return str_replace(array_keys($replace), array_values($replace), $format);
    }

    /**
     * Returns the user php time format converted to the flatpickr format.
     * (There's no getFlatpickrDateFormat() function because flatpickr uses the PHP format for dates one-to-one.)
 *
     * https://www.php.net/manual/en/datetime.format.php
     * https://flatpickr.js.org/formatting
     *
     * @param string $format
     * @return string
     */
    protected function getFlatpickrTimeFormat(string $format): string
    {
        $replace = [
            "a" => "K",
            "A" => "K",
            "G" => "H",
            "h" => "G",
            "g" => "h",
            //"H" => "H", -- doesn't need converting
            //"i" => "i", -- doesn't need converting
            "s" => "S",
        ];

        return str_replace(array_keys($replace), array_values($replace), $format);
    }
}