<?php
namespace XCalendar;

// do not change this value, it's hashed in saved sync passwords
define('RCP_CALDAV_REALM', 'SabreDAV');

require_once __DIR__ . '/../../xframework/common/Utils.php';

use XFramework\DatabaseGeneric;
use XFramework\Utils;

class CalDavSync {
    const USERNAME_LENGTH = 10;
    const PASSWORD_LENGTH = 10;
    private \rcube $rcmail;
    private DatabaseGeneric $db;
    private \XFramework\Format $format;

    public function __construct()
    {
        $this->rcmail = xrc();
        $this->db = xdb();
        $this->format = xformat();
    }

    /**
     * Return a record from the syncs db table.
     *
     * @param $id
     * @return array|bool
     */
    public function getById($id): bool|array
    {
        $id = (int)$id;

        if (!$id) {
            return false;
        }

        $result = $this->db->row('xcalendar_synced', ['id' => $id]);
        CalDavLog::log($result);
        return $result;
    }

    public function getByUsername($username): bool|array
    {
        $result = $this->db->row('xcalendar_synced', ['username' => $username]);
        CalDavLog::log($result);
        return $result;
    }

    public function getByUrl($url): bool|array
    {
        $result = $this->db->row('xcalendar_synced', ['url' => $url]);
        CalDavLog::log($result);
        return $result;
    }

    public function getDisplayList($calendarId): array
    {
        $records = $this->db->all("SELECT * FROM {xcalendar_synced} WHERE calendar_id = ?", $calendarId);
        $result = [];

        if (empty($records)) {
            return $result;
        }

        foreach ($records as $record) {
            $this->getSyncUrls($record['username'], $record['url'], $primarySyncUrl, $alternateSyncUrl);
            $createdAt = $record['created_at'] ?? '';

            $result[] = [
                'id' => $record['id'],
                'name' => $record['name'],
                'username' => $record['username'],
                'url' => $primarySyncUrl,
                'alternateUrl' => $alternateSyncUrl,
                'read_only' => (bool)$record['read_only'],
                'created_at' => $this->format->formatDate($createdAt),
                'connected_at' => $this->format->formatDate($createdAt, $this->rcmail->gettext('xcalendar.never')),
                'default_visible' => false,
            ];
        }

        return $result;
    }

    public function add($userId, $calendarId, $name, $password, $readOnly, &$error = false): bool|array
    {
        if (empty($calendarId)) {
            return false;
        }

        if (empty($name)) {
            $error = $this->rcmail->gettext('nonamewarning');
            return false;
        }

        if (strlen($password) < self::PASSWORD_LENGTH) {
            $error = $this->rcmail->gettext('xcalendar.password_too_short');
            return false;
        }

        // check if the user is the calendar owner
        if (!$this->db->row('xcalendar_calendars', ['id' => $calendarId, 'user_id' => $userId])) {
            return false;
        }

        $username = $this->createUniqueString('username', 'xcalendar_synced');
        $url = $this->createUniqueString('url', 'xcalendar_synced');

        $this->db->beginTransaction();

        try {
            if (!$this->db->insert(
                'xcalendar_synced',
                [
                    'user_id' => $userId,
                    'calendar_id' => $calendarId,
                    'name' => $name,
                    'username' => $username,
                    'url' => $url,
                    'password' => md5($username . ':' . RCP_CALDAV_REALM . ':' . $password),
                    // see https://bugzilla.mozilla.org/show_bug.cgi?id=799184
                    //"password" => md5($username . ":" . RCP_CALDAV_REALM . " (" . $name. "):" . $password),
                    'read_only' => (bool)$readOnly,
                ]
            )) {
                throw new \Exception('Cannot insert sync record to database');
            }

            if (!($id = $this->db->lastInsertId('xcalendar_synced'))) {
                throw new \Exception('Sync record not written to database');
            }

            $this->db->commit();

            $this->getSyncUrls($username, $url, $primarySyncUrl, $alternateSyncUrl);

            return [
                'id' => $id,
                'name' => $name,
                'username' => $username,
                'url' => $primarySyncUrl,
                'alternateUrl' => $alternateSyncUrl,
                'read_only' => (bool)$readOnly,
                'created_at' => $this->format->formatDate(time()),
                'connected_at' => $this->format->formatDate('', $this->rcmail->gettext('xcalendar.never')),
                'details_visible' => true,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            Utils::logError($e->getMessage() . ' (28199)');
            return false;
        }
    }

    public function remove($id, $userId, &$error): bool
    {
        // check if the user is the connection owner
        if ($this->db->row('xcalendar_synced', ['id' => $id, 'user_id' => $userId]) &&
            $this->db->remove('xcalendar_synced', ['id' => $id])) {
            return true;
        }

        $error = $this->rcmail->gettext('xcalendar.remove_sync_error');
        return false;
    }

    public function createUniqueString($targetField, $targetTable): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $charactersLength = strlen($characters);

        do {
            $result = '';

            for ($i = 0; $i < 10; $i++) {
                $result .= $characters[mt_rand(0, $charactersLength - 1)];
            }
        } while ($this->db->value($targetField, $targetTable, [$targetField => $result]));

        return $result;
    }

    /**
     * Creates the primary and alternate sync urls. It replaces the asterisk (if exists) in the server domain with the
     * username
     * @param $username
     * @param $url
     * @param $primaryUrl
     * @param $alternateUrl
     */
    public function getSyncUrls($username, $url, &$primaryUrl, &$alternateUrl): void
    {
        $primaryUrl = str_replace(
            '*',
            $username,
            Utils::addSlash($this->rcmail->config->get('xcalendar_caldav_server_domain'))
        );

        // Version 1.9.2 modification
        // Removed index.php from the alternate url path to make caldav easier to set up on nginx.
        // Apache with the standard rewrite rule included in htaccess is not affected by this change.
        // Without index.php the primary and alternate urls are exactly the same when browsing
        // the calendar and principal data.
        //$alternateUrl = $primaryUrl . "index.php/calendars/$username/$url/";
        $alternateUrl = $primaryUrl . "calendars/$username/$url/";
    }
}


