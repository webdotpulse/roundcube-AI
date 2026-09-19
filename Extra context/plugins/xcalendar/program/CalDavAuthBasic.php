<?php
namespace XCalendar;

use XFramework\DatabaseGeneric;

require_once __DIR__ . '/../vendor/autoload.php';

class CalDavAuthBasic extends \Sabre\DAV\Auth\Backend\AbstractBasic
{
    private \rcube $rcmail;
    private DatabaseGeneric $db;
    private CalDavPrincipal $principalBackend;

    public function __construct(CalDavPrincipal $principalBackend)
    {
        $this->rcmail = xrc();
        $this->db = xdb();
        $this->principalBackend = $principalBackend;
    }

    protected function validateUserPass($username, $password): bool
    {
        // find the sync and while we're at it, get the language of the user the sync belongs to
        if (!($row = $this->db->fetch(
            "SELECT {users}.user_id, {xcalendar_synced}.password, {users}.language FROM {xcalendar_synced} 
            LEFT JOIN {users} ON {xcalendar_synced}.user_id = {users}.user_id 
            WHERE {xcalendar_synced}.username = ? LIMIT 1", [$username]))
        ) {
            CalDavLog::log("ERROR: SYNC NOT FOUND [$username]");
            return false;
        }

        CalDavLog::log("AuthBasic: validating $username");

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

        // check password
        return hash_equals((string)$row['password'], md5($username . ":" . RCP_CALDAV_REALM . ":" . $password));
    }
}

