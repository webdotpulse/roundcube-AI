<?php
namespace XCalendar;

use XFramework\DatabaseGeneric;
use XFramework\Utils;

class CalendarData
{
    private array $data;
    private \rcube $rcmail;
    private ?int $userId;
    private ?string $userEmail;
    private DatabaseGeneric $db;

    /**
     * This constructor is private because we're using static functions to create CalendarData objects: load, etc.
     */
    private function __construct()
    {
        $this->db = xdb();
        $this->rcmail = xrc();
        $this->userId = $this->rcmail->get_user_id();
        $this->userEmail = $this->rcmail->get_user_email();
        $this->data = $this->getEmptyData();
    }

    /**
     * Factory constructor: load an existing calendar from the database.
     *
     * @param $calendarId
     * @return false|CalendarData
     */
    public static function load($calendarId): bool|CalendarData
    {
        if ($data = xdb()->row("xcalendar_calendars", ["id" => $calendarId, "removed_at" => NULL])) {
            $data['properties'] = empty($data['properties']) ? [] : self::decodeProperties($data['properties']);
            $calendarData = new CalendarData();
            $calendarData->setData($data);
            return $calendarData;
        }

        return false;
    }

    /**
     * Factory constructor: load current user's default calendar.
     *
     * @return CalendarData
     */
    public static function loadDefault(): CalendarData
    {
        $rcmail = xrc();
        $db = xdb();
        $userId = $rcmail->get_user_id();
        $calendarData = new CalendarData();

        // try getting data for the default calendar as specified in the config / user preferences
        if (($id = $rcmail->config->get("xcalendar_calendar", 0)) &&
            ($data = $db->row("xcalendar_calendars", ["id" => $id, "user_id" => $userId, "removed_at" => NULL]))
        ) {
            $calendarData->setData($data);
            return $calendarData;
        }

        // if not found, find the first calendar that belongs to this user
        if ($data = $db->row("xcalendar_calendars", ["user_id" => $userId, "type" => Calendar::LOCAL, "removed_at" => NULL])) {
            $calendarData->setData($data);
            $rcmail->config->set("xcalendar_calendar", $data['id']);
            return $calendarData;
        }

        $error = "Unable to load default calendar (4839929)";
        Utils::logError($error);
        exit($error);
    }

    /**
     * Factory constructor: load an empty calendar.
     *
     * @param int $type
     * @return CalendarData
     */
    public static function loadEmpty(int $type = Calendar::LOCAL): CalendarData
    {
        $calendarData = new CalendarData();

        if (in_array($type, [Calendar::LOCAL, Calendar::HOLIDAY, Calendar::GOOGLE, Calendar::CALDAV, Calendar::BIRTHDAY])) {
            $calendarData->set("type", $type);
        }

        return $calendarData;
    }

    public function getSharedCalendarData($calendarId): bool|array
    {
        return $this->db->row(
            "xcalendar_calendars_shared",
            ["email" => $this->userEmail, "calendar_id" => $calendarId, "added" => "1"]
        );
    }

    public function isWritable(): bool
    {
        // if current user is the owner
        if ($this->getUserId() == $this->rcmail->get_user_id()) {
            return match ($this->data["type"]) {
                Calendar::LOCAL => $this->get('enabled'),
                Calendar::CALDAV => $this->get('enabled') && !$this->getProperty('caldav_readonly'),
                default => false,
            };
        }

        // if current user is not the owner, check shared calendars
        return
            ($calendar = $this->getSharedCalendarData($this->data['id'])) &&
            ($permissions = json_decode($calendar['permissions'], true)) &&
            ($permissions['edit_events'] ?? false);
    }

    public function getDataForEditing(): bool|array
    {
        // caldav/google/holiday/birthday calendars are never shared, so for an existing calendar the
        // caller must be the owner; local calendars fall through to the owner/shared check below.
        // Without this, getCalendarData leaks another user's calendar details (e.g. caldav url+username).
        if (!empty($this->data['id']) &&
            $this->data['user_id'] != $this->userId &&
            $this->data['type'] != Calendar::LOCAL
        ) {
            return false;
        }

        $data = [
            "id" => $this->data['id'],
            "type" => $this->data['type'],
            "name" => $this->data['name'],
            "description" => $this->data['description'],
            "url" => $this->data['url'],
            "timezone" => $this->data['timezone'],
            "bg_color" => $this->data['bg_color'],
            "tx_color" => $this->data['tx_color'],
            "enabled" => $this->data['enabled'],
            "readonly" => "0",
            "default_event_visibility" => $this->data['default_event_visibility'],
        ];

        if ($data['type'] == Calendar::CALDAV) {
            $properties = $this->get("properties");

            if (!empty($properties['caldav_readonly'])) {
                $data['readonly'] = "1";
            }

            if ($data['id']) {
                $properties = ClientCaldav::ensureProperties($properties);
                $data['url'] = ClientCaldav::resolveServerUrl($properties);
                $data['username'] = $properties['caldav_username'];
                $data['password'] = Calendar::PASSWORD_PLACEHOLDER;
            } else {
                $data['caldav_calendars'] = [];
                $data['url'] = "";
                $data['username'] = "";
                $data['password'] = "";
            }

            $data['show_remove'] = (bool)$data['id'];
            $data['permissions'] = Permission::getDefaultCalendarPermissions();
            return $data;
        }

        if ($data['type'] == Calendar::BIRTHDAY) {
            if (!$data['id']) {
                $data['name'] = $this->rcmail->gettext("xcalendar.contact_birthdays");
            }
            $data['show_remove'] = (bool)$data['id'];
            $data['permissions'] = Permission::getDefaultCalendarPermissions();
            return $data;
        }

        if (in_array($data['type'], [Calendar::GOOGLE, Calendar::HOLIDAY])) {
            $data['show_remove'] = (bool)$data['id'];
            $data['permissions'] = Permission::getDefaultCalendarPermissions();
            return $data;
        }

        // if shared calendar, include share-specific data: permissions, name and colors that are specific to this user
        if ($this->data['user_id'] == $this->userId) {
            $data['owner'] = true;
            $data['ownerEmail'] = false;
            $data['permissions'] = Permission::getCalendarOwnerPermissions();
            $data['show_remove'] = $data['id'] && Calendar::getCalendarCount(Calendar::LOCAL) > 1;
        } else {
            if (!($shared = $this->db->row(
                "xcalendar_calendars_shared",
                ["email" => $this->userEmail, "calendar_id" => $data['id']]
            ))) {
                return false;
            }

            $data['owner'] = false;
            $data['name'] = $shared['name'];
            $data['description'] = $shared['description'];
            $data['bg_color'] = $shared['bg_color'];
            $data['tx_color'] = $shared['tx_color'];
            $data['enabled'] = $shared['enabled'];
            $data['permissions'] = Permission::decodeCalendarPermissions($shared['permissions']);
            $data['show_remove'] = true;

            // get owner email
            if (!($data['ownerEmail'] = $this->db->value("username", "users", ["user_id" => $this->data['user_id']]))) {
                return false;
            }
        }

        if ($data['permissions']->publish_calendar) {
            $data['publish'] = Calendar::getCalendarPublishData((int)$data['id']);
        }

        // if the user is permitted to share the calendar, add the share list
        if ($data['permissions']->share_calendar) {
            $data['shares'] = $this->db->all(
                "SELECT email, permissions, added, created_at FROM {xcalendar_calendars_shared} WHERE calendar_id = ?",
                $data['id']
            );

            foreach ($data['shares'] as $key => $val) {
                $data['shares'][$key]['permissions'] = Permission::decodeCalendarPermissions($val['permissions']);
                $data['shares'][$key]['added'] = (bool)$val['added'];
            }
        } else {
            $data['shares'] = [];
        }

        if ($data['owner']) {
            $data['syncs'] = (new CalDavSync())->getDisplayList($data['id']);
        } else {
            $data['syncs'] = [];
        }

        return $data;
    }

    /**
     * Saves the calendar data to the database. Returns the saved calendar's id or false on error.
     *
     * @return bool|int
     */
    public function save(): bool|int
    {
        $data = $this->data;
        $data['user_id'] = $this->userId;
        $data['properties'] = $this->encodeProperties($data['properties']);
        $columns = $this->db->getColumns("xcalendar_calendars");

        foreach ($data as $key => $val) {
            if (!in_array($key, $columns)) {
                unset($data[$key]);
            }
        }

        if (empty($data['id'])) {
            unset($data['id']); // in case it's null, otherwise it'll throw a db error on postgres
            return $this->db->insert("xcalendar_calendars", $data) ? $this->db->lastInsertId("xcalendar_calendars") : false;
        }

        // only the owner may update an existing calendar; without this an authenticated user could pass
        // another user's calendar id to a save*Post() method and overwrite/hijack it (the update is keyed
        // by id and forces user_id to the current user)
        if (!$this->db->row("xcalendar_calendars", ["id" => $data['id'], "user_id" => $this->userId])) {
            Utils::logError("Refused to save calendar {$data['id']} not owned by user $this->userId");
            return false;
        }

        $data['modified_at'] = date("Y-m-d H:i:s");
        return $this->db->update("xcalendar_calendars", $data, ["id" => $data['id']]) ? $data['id'] : false;
    }

    /**
     * @throws \Exception
     */
    public static function saveCaldavPost(): int
    {
        $data = \XFramework\Input::instance()->fill(
            ["id", "name", "username", "password", "bg_color", "tx_color", "default_event_visibility"]
        );

        if (empty($data['id']) || empty($data['name']) || empty($data['username']) || empty($data['password'])) {
            throw new \Exception(xrc()->gettext("xcalendar.caldav_client_error_server_info"));
        }

        if (!($calendarData = self::load($data['id'])) ||
            $calendarData->getUserId() != xrc()->get_user_id()
        ) {
            throw new \Exception(xrc()->gettext("xcalendar.unable_to_save_calendar_data") . " (3881994)");
        }

        $calendarData->set("name", substr($data['name'], 0, 250));
        empty($data['bg_color']) || $calendarData->set("bg_color", $data['bg_color']);
        empty($data['tx_color']) || $calendarData->set("tx_color", $data['tx_color']);
        empty($data['default_event_visibility']) || $calendarData->set("default_event_visibility", $data['default_event_visibility']);

        $calendarData->setProperty("caldav_username", substr($data['username'], 0, 250));

        if ($data['password'] != Calendar::PASSWORD_PLACEHOLDER) {
            $calendarData->setProperty("caldav_password", substr($data['password'], 0, 250));
        }

        // check caldav password
        if (!ClientCaldav::checkPassword(
            $calendarData->getProperty("caldav_server_url"),
            $calendarData->getProperty("caldav_username"),
            $calendarData->getProperty("caldav_password"),
        )) {
            throw new \Exception(xrc()->gettext("xcalendar.caldav_client_error_credentials"));
        }

        if (!($calendarId = $calendarData->save())) {
            throw new \Exception(xrc()->gettext('xcalendar.unable_to_save_calendar_data'));
        }

        return $calendarId;
    }

    /**
     * @throws \Exception
     */
    public static function saveGooglePost(): int
    {
        if (empty(xrc()->config->get("xcalendar_google_calendar_key"))) {
            throw new \Exception();
        }

        $data = \XFramework\Input::instance()->fill(["id", "name", "url", "bg_color", "tx_color"]);

        if (empty($data['url'])) {
            throw new \Exception(xrc()->gettext("xcalendar.specify_google_calendar_id"));
        }

        if (!strpos($data['url'], "@")) {
            throw new \Exception(xrc()->gettext("xcalendar.calendar_id_format_error"));
        }

        if (empty($data['id'])) {
            $calendarData = self::loadEmpty(Calendar::GOOGLE);
        } else {
            if (!($calendarData = self::load($data['id']))) {
                throw new \Exception(xrc()->gettext("xcalendar.unable_to_save_calendar_data") . " (3881994)");
            }
        }

        $calendarData->set("name", $data['name'] ? substr($data['name'], 0, 250) : xrc()->gettext("xcalendar.new_google_calendar"));
        $calendarData->set("url", substr($data['url'], 0, 250));
        empty($data['bg_color']) || $calendarData->set("bg_color", $data['bg_color']);
        empty($data['tx_color']) || $calendarData->set("tx_color", $data['tx_color']);

        // check server connection
        try {
            ClientGoogle::getDataFromServer(Calendar::GOOGLE, $calendarData->get("url"), date("Y-m-d"), date("Y-m-d"));
        } catch (\Exception) {
            throw new \Exception(xrc()->gettext([
                "name" => "xcalendar.cannot_load_google_calendar",
                "vars" => ["c" => $calendarData->get("name")]
            ]));
        }

        if (!($calendarId = $calendarData->save())) {
            throw new \Exception(xrc()->gettext('xcalendar.unable_to_save_calendar_data'));
        }

        return $calendarId;
    }

    /**
     * @throws \Exception
     */
    public static function saveBirthdayPost(): int
    {
        if (!ClientBirthday::enabled()) {
            throw new \Exception("Birthday calendar disabled in config. (3711883)");
        }

        $rcmail = xrc();
        $data = \XFramework\Input::instance()->fill(["name", "bg_color", "tx_color"]);

        if (empty($data['name'])) {
            throw new \Exception($rcmail->gettext("xcalendar.calendar_name_cannot_be_empty"));
        }

        $calendarId = ClientBirthday::getId();


        if (!($calendarData = $calendarId ? self::load($calendarId) : self::loadEmpty())) {
            throw new \Exception($rcmail->gettext("xcalendar.unable_to_save_calendar_data") . " (177823)");
        }

        $calendarData->set("name", substr($data['name'], 0, 250));
        empty($data['bg_color']) || $calendarData->set("bg_color", $data['bg_color']);
        empty($data['tx_color']) || $calendarData->set("tx_color", $data['tx_color']);
        $calendarData->set("type", Calendar::BIRTHDAY);

        if (!($calendarId = $calendarData->save())) {
            throw new \Exception(xrc()->gettext('xcalendar.unable_to_save_calendar_data'));
        }

        return $calendarId;
    }

    /**
     * @throws \Exception
     */
    public static function saveHolidayPost(): int
    {
        $rcmail = xrc();

        if (empty($rcmail->config->get("xcalendar_google_calendar_key"))) {
            throw new \Exception();
        }

        $data = \XFramework\Input::instance()->fill(["id", "name", "url", "bg_color", "tx_color"]);

        if (empty($data['url'])) {
            throw new \Exception("Invalid calendar url (28900)");
        }

        if (empty($data['name'])) {
            throw new \Exception($rcmail->gettext("xcalendar.calendar_name_cannot_be_empty"));
        }

        if (!($calendarData = self::load($data['id']))) {
            throw new \Exception($rcmail->gettext("xcalendar.unable_to_save_calendar_data") . " (177823)");
        }

        $calendarData->set("name", substr($data['name'], 0, 250));
        empty($data['bg_color']) || $calendarData->set("bg_color", $data['bg_color']);
        empty($data['tx_color']) || $calendarData->set("tx_color", $data['tx_color']);

        // check server connection
        try {
            ClientGoogle::getDataFromServer(Calendar::HOLIDAY, $calendarData->get("url"), date("Y-m-d"), date("Y-m-d"));
        } catch (\Exception) {
            throw new \Exception($rcmail->gettext("xcalendar.cannot_load_holidays"));
        }

        if (!($calendarId = $calendarData->save())) {
            throw new \Exception(xrc()->gettext('xcalendar.unable_to_save_calendar_data'));
        }

        return $calendarId;
    }

    /**
     * @throws \Exception
     */
    public static function saveLocalPost(): int
    {
        $rcmail = xrc();
        $db = xdb();
        $userId = $rcmail->get_user_id();
        $userEmail = $rcmail->get_user_email();

        $fields = ["id", "type", "url", "name", "description", "bg_color", "tx_color", "default_event_visibility"];
        $data = \XFramework\Input::instance()->fill(
            $rcmail->config->get("xcalendar_calendar_share_enabled", true) ? array_merge($fields, ['shares']) : $fields
        );

        $calendarId = $data['id'] ?? false;
        $newCalendar = !$calendarId;

        if ($calendarId) {
            $owner = Permission::isCalendarOwner($calendarId, $userId);
            $permissions = Permission::getCalendarPermissions($calendarId, $userId, $userEmail);
        } else {
            $owner = true;
            $permissions = Permission::getCalendarOwnerPermissions();
        }

        // publish is not saved here, it's saved right away when user clicks create/reset/remove
        unset($data['publish']);

        Color::getRandomColors($txColor, $bgColor);
        $data['name'] = empty($data['name']) ? $rcmail->gettext("xcalendar.new_calendar") : substr($data['name'], 0, 250);
        $data['description'] = empty($data['description']) ? "" : substr($data['description'], 0, 1000);
        $data['url'] = empty($data['url']) ? "" : substr($data['url'], 0, 180);
        $data['bg_color'] = empty($data['bg_color']) ? $bgColor : $data['bg_color'];
        $data['tx_color'] = empty($data['tx_color']) ? $txColor : $data['tx_color'];
        $data['default_event_visibility'] =
            empty($data['default_event_visibility']) || !in_array($data['default_event_visibility'], ["public", "private"]) ?
            "public" : $data['default_event_visibility'];

        $db->beginTransaction();

        try {
            // if saving a shared copy, save the name and colors in xcalendar_calendars_shared and unset those properties
            // from the data to be saved in xcalendar_calendars
            if ($owner) {
                $calendarData = $calendarId ? self::load($calendarId) : self::loadEmpty();
                $calendarData->set("name", $data['name']);
                $calendarData->set("description", $data['description']);
                $calendarData->set("url", $data['url']);
                $calendarData->set("bg_color", $data['bg_color']);
                $calendarData->set("tx_color", $data['tx_color']);
                $calendarData->set("default_event_visibility", $data['default_event_visibility']);

                if (!($calendarId = $calendarData->save())) {
                    Utils::logError("Cannot save calendar data (18570)");
                    throw new \Exception();
                }
            } else {
                if (!$db->query(
                    "UPDATE {xcalendar_calendars_shared} SET name = ?, description = ?, bg_color = ?, tx_color = ? " .
                    "WHERE email = ? AND calendar_id = ?",
                    [$data['name'], $data['description'], $data['bg_color'], $data['tx_color'], $userEmail, $calendarId]
                )) {
                    Utils::logError("Cannot save calendar data (18571)");
                    throw new \Exception();
                }
            }

            if (!$newCalendar && $permissions->share_calendar) {
                self::saveShares($data, $owner);
            }

            $db->commit();
            return $calendarId;

        } catch (\Exception $e) {
            $db->rollBack();
            throw new \Exception($e->getMessage() ?: $rcmail->gettext("xcalendar.unable_to_save_calendar_data"));
        }
    }

    /**
     * @throws \Exception
     */
    protected static function saveShares(array $data, bool $owner): void
    {
        $rcmail = xrc();
        $db = xdb();
        $userId = $rcmail->get_user_id();
        $calendarId = $data['id'] ?? false;

        if (isset($data['shares'])) {
            $shares = is_array($data['shares']) ? $data['shares'] : [];
            unset($data['shares']);
        } else {
            $shares = [];
        }

        // find the owner id of this calendar
        if (!empty($shares)) {
            if ($owner) {
                $ownerId = $userId;
            } else {
                $ownerId = $db->value("user_id", "xcalendar_calendars", ["id" => $calendarId]);
            }

            $ownerUsername = $ownerId ? $db->value("username", "users", ["user_id" => $ownerId]) : false;
        } else {
            $ownerId = false;
            $ownerUsername = false;
        }

        // create an array of shared email addresses
        $sharedEmails = [];
        $unableToShare = [];
        $verifyLocal = (bool)$rcmail->config->get("xcalendar_verify_shared_emails");
        $allowedDomains = $rcmail->config->get("xcalendar_allowed_share_domains");

        foreach ($shares as $key => $share) {
            $share['email'] = trim(strtolower($share['email']));

            // check if sharing the calendar with the calendar owner
            if ($ownerId && $share['email'] == $ownerUsername) {
                if ($ownerId == $userId) {
                    throw new \Exception($rcmail->gettext("xcalendar.cannot_share_with_yourself"));
                } else {
                    throw new \Exception($rcmail->gettext([
                            'name' => 'xcalendar.cannot_share_with_calendar_owner',
                            'vars' => ['e' => $share['email']]]
                    ));
                }
            }

            $shares[$key]['email'] = $share['email'];

            if ($verifyLocal && !self::isLocalEmailAddress($share['email'])) {
                $unableToShare[] = $share['email'];
            }

            if (!empty($allowedDomains)) {
                $found = false;
                foreach ($allowedDomains as $domain) {
                    if ($domain == "<current>") {
                        $domain = Utils::getUrl('', true);
                    }

                    $parts = explode('@', $share['email']);
                    if (count($parts) == 2 && $parts[1] === strtolower($domain)) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $unableToShare[] = $share['email'];
                }
            }

            $sharedEmails[] = $share['email'];
        }

        if (!empty($unableToShare)) {
            Utils::logError("Cannot save calendar data (18572)");
            throw new \Exception($rcmail->gettext("xcalendar.unable_to_share") . " " .
                implode(", ", $unableToShare));
        }

        // delete the shares that have been removed from the list
        $markers = ["?"];
        $param = [$calendarId, ""];
        foreach ($sharedEmails as $email) {
            $markers[] = "?";
            $param[] = $email;
        }

        if (!$db->query(
            "DELETE FROM {xcalendar_calendars_shared} WHERE calendar_id = ? AND email NOT IN (" . implode(",", $markers) . ")",
            $param
        )) {
            Utils::logError("Cannot delete shared calendar data (18573)");
            throw new \Exception();
        }

        // get the array of the emails of the existing users this calendar is shared with
        $array = $db->all(
            "SELECT email FROM {xcalendar_calendars_shared} WHERE calendar_id = ?",
            $calendarId
        );

        $existing = [];
        if (is_array($array)) {
            foreach ($array as $val) {
                $existing[] = $val['email'];
            }
        }

        $newShares = array_diff($sharedEmails, $existing);

        if (!empty($newShares)) {
            foreach ($shares as $key => $share) {
                if (in_array($share['email'], $newShares)) {
                    $shares[$key]['add_code'] = bin2hex(openssl_random_pseudo_bytes(20));
                }
            }
        }

        // insert/update the shares in the db
        foreach ($shares as $share) {
            $savePermissions = json_encode($share['permissions']);

            if ($db->value(
                "permissions",
                "xcalendar_calendars_shared",
                ["email" => $share['email'], "calendar_id" => $calendarId]
            )) {
                if (!$db->update(
                    "xcalendar_calendars_shared",
                    ["permissions" => $savePermissions],
                    ["email" => $share['email'], "calendar_id" => $calendarId]
                )) {
                    Utils::logError("Cannot update shared calendar data (18575)");
                    throw new \Exception();
                }
            } else {
                // creating the shared calendar with the original colors
                if (!$db->insert(
                    "xcalendar_calendars_shared",
                    [
                        "email" => $share['email'],
                        "calendar_id" => $calendarId,
                        "permissions" => $savePermissions,
                        "add_code" => $share['add_code'] ?: "",
                        "name" => $data['name'],
                        "description" => $data['description'] ?: "",
                        "bg_color" => $data['bg_color'],
                        "tx_color" => $data['tx_color'],
                        "created_at" => date("Y-m-d H:i:s"),
                    ]
                )) {
                    Utils::logError("Cannot insert shared calendar data (18576)");
                    throw new \Exception();
                }
            }
        }

        // send emails to new calendar users
        if (!empty($newShares)) {
            foreach ($shares as $share) {
                if (!empty($share['notify'])) {
                    self::sendSharedEmailNotification($share, $data['name']);
                }
            }
        }
    }

    public static function sendSharedEmailNotification($share, $calendarName): bool
    {
        $rcmail = xrc();
        $userEmail = $rcmail->get_user_email();
        $error = false;

        if (Utils::isCpanel()) {
            $body = $rcmail->gettext([
                "name" => "xcalendar.shared_email_body_cpanel",
                "vars" => ["u" => \rcube::Q($userEmail), "c" => \rcube::Q($calendarName)]
            ]);
            $link = "";
        } else {
            $body = $rcmail->gettext([
                "name" => "xcalendar.shared_email_body",
                "vars" => ["u" => \rcube::Q($userEmail), "c" => \rcube::Q($calendarName)]
            ]);
            $url = Utils::getUrl() . "?_task=xcalendar&_action=ac&c=" . $share['add_code'];
            $link = "<a href='$url'>" . $rcmail->gettext("xcalendar.add_shared_calendar") . "</a>";
        }

        return \XFramework\Plugin::sendHtmlEmail(
            $share['email'],
            $rcmail->gettext(["name" => "xcalendar.shared_email_subject", "vars" => ["u" => $userEmail]]),
            \XFramework\Plugin::view("elastic", "xcalendar.shared_email", ["body" => $body, "link" => $link]),
            $error
        );
    }

    /**
     * Sets calendar data from an array. Loads the default data first, and then overwrites the values if they exist in $data.
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data): void
    {
        foreach ($this->data as $key => $val) {
            if (array_key_exists($key, $data)) {
                $this->data[$key] = $data[$key];
            }
        }

        if (!is_array($this->data['properties'])) {
            $this->data['properties'] = $this->decodeProperties((string)$this->data['properties']);
        }
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function getUserId()
    {
        return $this->data['user_id'];
    }

    public function getType()
    {
        return $this->data['type'];
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function getProperty(string $key)
    {
        return $this->data['properties'][$key] ?? null;
    }

    public function setProperty(string $key, $value): void
    {
        if (empty($this->data['properties'])) {
            $this->data['properties'] = [];
        }

        $this->data['properties'][$key] = $value;
    }

    /**
     * Creates a local calendar if the user doesn't already have one.
     *
     * @return void
     */
    public static function createDefaultCalendar(): void
    {
        if (($userId = xrc()->get_user_id()) &&
            !xdb()->row("xcalendar_calendars", ["user_id" => $userId, "type" => Calendar::LOCAL, "removed_at" => NULL])
        ) {
            $calendarData = self::loadEmpty();
            $calendarData->set("bg_color", "#a9d5fc");
            $calendarData->set("tx_color", "#333");

            if (!$calendarData->save()) {
                $error = "Unable to create default calendar (382991)";
                Utils::logError($error);
                exit($error);
            }
        }
    }

    public static function decodeProperties(string $properties): array
    {
        if (empty($properties) || empty($array = json_decode($properties, true))) {
            return [];
        }

        if (!empty($array['caldav_password'])) {
            $array['caldav_password'] = xrc()->decrypt($array['caldav_password']);
        }

        return $array;
    }

    protected function encodeProperties(array $properties): string
    {
        if (empty($properties)) {
            return "";
        }

        if (!empty($properties['caldav_password'])) {
            $properties['caldav_password'] = $this->rcmail->encrypt($properties['caldav_password']);
        }

        return json_encode($properties);
    }

    protected function getEmptyData(): array
    {
        Color::getRandomColors($txColor, $bgColor);

        return [
            "id" => null,
            "user_id" => $this->userId,
            "type" => Calendar::LOCAL,
            "name" => $this->rcmail->gettext("xcalendar.new_calendar"),
            "description" => "",
            "url" => "",
            "timezone" => "",
            "bg_color" => $bgColor,
            "tx_color" => $txColor,
            "enabled" => 1,
            "default_event_visibility" => "public",
            "properties" => [],
        ];
    }

    protected static function isLocalEmailAddress(string $email): bool
    {
        return (bool)xdb()->row("users", ["username" => $email]);
    }
}