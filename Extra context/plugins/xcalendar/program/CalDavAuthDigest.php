<?php
namespace XCalendar;

use XFramework\DatabaseGeneric;

require_once __DIR__ . '/../vendor/autoload.php';

class CalDavAuthDigest extends \Sabre\DAV\Auth\Backend\AbstractDigest
{
    private \rcube $rcmail;
    private DatabaseGeneric $db;
    private $principalBackend;

    public function __construct($principalBackend)
    {
        $this->rcmail = xrc();
        $this->db = xdb();
        $this->principalBackend = $principalBackend;
    }

    /**
     * Returns the digest hash for a user.
     *
     * @param string $realm
     * @param string $username
     *
     * @return string|null
     */
    public function getDigestHash($realm, $username): ?string
    {
        // find the sync and while we're at it, get the language of the user the sync belongs to
        if (!($row = $this->db->fetch(
            "SELECT {users}.user_id, {xcalendar_synced}.password, {users}.language FROM {xcalendar_synced} 
            LEFT JOIN {users} ON {xcalendar_synced}.user_id = {users}.user_id 
            WHERE {xcalendar_synced}.username = ? LIMIT 1", [$username]))
        ) {
            CalDavLog::log("ERROR: SYNC NOT FOUND [$realm / $username]");
            return null;
        }

        CalDavLog::log("$realm / $username");

        // set the principal
        $this->principalBackend->setCurrentPrincipal($username);

        // read the calendar texts in the user's language and rewrite them so they're in the format: xcalendar.key
        $texts = $this->rcmail->read_localization(
            RCUBE_INSTALL_PATH . "plugins/xcalendar/localization/",
            $row['language']
        );
        $add = [];

        foreach ($texts as $key => $val) {
            $add["xcalendar." . $key] = $val;
        }

        // load the user's language and add the xcalendar strings
        $this->rcmail->load_language($row['language'], $add);

        // create user object and set it in rcmail - sending mail won't work without it
        $this->rcmail->set_user(new \rcube_user($row['user_id']));

        // return password to successfully authenticate
        return $row['password'];
    }
}

