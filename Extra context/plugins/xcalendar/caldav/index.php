<?php
// if you're using Roundcube via <roundcube>/public_html, you may need to define INSTALL_PATH in your server config to
// point to Roundcube's main directory, otherwise INSTALL_PATH will not point to the correct directory and the require
// statements will not work properly
if (!defined('INSTALL_PATH')) {
    define('INSTALL_PATH', getenv('INSTALL_PATH') ?: dirname($_SERVER['SCRIPT_FILENAME'], 4) . '/');
}
const XCALENDAR_CALDAV = true;

require_once INSTALL_PATH . 'program/include/iniset.php';
require_once INSTALL_PATH . 'vendor/autoload.php';
require_once INSTALL_PATH . 'program/lib/Roundcube/bootstrap.php';
require_once INSTALL_PATH . 'program/lib/Roundcube/rcube.php';
require_once INSTALL_PATH . 'program/lib/Roundcube/rcube_config.php';
require_once INSTALL_PATH . 'program/lib/Roundcube/rcube_charset.php';
require_once INSTALL_PATH . 'program/lib/Roundcube/rcube_utils.php';
require_once INSTALL_PATH . 'program/lib/Roundcube/rcube_db.php';
require_once INSTALL_PATH . 'program/lib/Roundcube/db/mysql.php';
require_once INSTALL_PATH . 'program/lib/Roundcube/db/sqlite.php';
require_once INSTALL_PATH . 'plugins/xframework/common/functions.php';
require_once INSTALL_PATH . 'plugins/xframework/common/Database.php';
require_once INSTALL_PATH . 'plugins/xframework/common/DatabaseMysql.php';
require_once INSTALL_PATH . 'plugins/xframework/common/DatabaseSqlite.php';
require_once INSTALL_PATH . 'plugins/xframework/common/DatabasePostgres.php';
require_once INSTALL_PATH . 'plugins/xframework/common/Format.php';
require_once INSTALL_PATH . 'plugins/xframework/common/Utils.php';
require_once INSTALL_PATH . 'plugins/xcalendar/program/CalDavAuthDigest.php';
require_once INSTALL_PATH . 'plugins/xcalendar/program/CalDavAuthBasic.php';
require_once INSTALL_PATH . 'plugins/xcalendar/program/CalDavCalendar.php';
require_once INSTALL_PATH . 'plugins/xcalendar/program/CalDavSync.php';
require_once INSTALL_PATH . 'plugins/xcalendar/program/CalDavPrincipal.php';
require_once INSTALL_PATH . 'plugins/xcalendar/program/CalDavIMip.php';
require_once INSTALL_PATH . 'plugins/xcalendar/program/Color.php';
require_once INSTALL_PATH . 'plugins/xcalendar/program/CalendarData.php';
require_once INSTALL_PATH . 'plugins/xcalendar/program/Permission.php';
require_once INSTALL_PATH . 'plugins/xcalendar/program/Event.php';
require_once INSTALL_PATH . 'plugins/xcalendar/program/CalDavLog.php';

use XCalendar\CalDavLog;

// load the calendar config
$config = \XFramework\Utils::loadConfigFile(INSTALL_PATH . 'plugins/xcalendar/config.inc.php');

// make sure caldav is enabled
if (empty($config['xcalendar_caldav_server_enabled'])) {
    header("HTTP/1.0 404");
    exit();
}

// enable the caldav log if specified in the config
define('CALDAV_LOG', !empty($config['xcalendar_caldav_debug_log_enabled']));

try {
    if (CALDAV_LOG) {
        CalDavLog::log('request: ' . $_SERVER['REQUEST_URI'] . ' | agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        CalDavLog::log('php://input: ' . file_get_contents('php://input'));

        $authFound = false;
        foreach ($_SERVER as $key => $val) {
            if (stripos($key, 'auth') !== false) {
                CalDavLog::log("AUTH: $key = [REDACTED]");
                $authFound = true;
            }
        }

        if (!$authFound) {
            CalDavLog::log('AUTH HEADER NOT FOUND');
        }
    }

    // add calendar config to the main config
    foreach ($config as $key => $val) {
        xrc()->config->set($key, $val);
    }

    // create the necessary classes: $calendar and $event userId is set in CalDavPrincipal::getPrincipalByPath()
    $event = new \XCalendar\Event();
    $sync = new \XCalendar\CalDavSync();
    $calendarBackend = new \XCalendar\CalDavCalendar($sync, $event);
    $principalBackend = new \XCalendar\CalDavPrincipal($event, $calendarBackend);

    $server = new Sabre\DAV\Server([
        new Sabre\CalDAV\Principal\Collection($principalBackend),
        new Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend),
    ]);

    // add the auth plugin with the supported auth methods (this deprecates the unpublished
    // xcalendar_caldav_authentication_type config option, as it's no longer necessary to choose a single auth method)
    $authPlugin = new Sabre\DAV\Auth\Plugin();
    $authPlugin->addBackend(new \XCalendar\CalDavAuthDigest($principalBackend));
    $authPlugin->addBackend(new \XCalendar\CalDavAuthBasic($principalBackend));
    $server->addPlugin($authPlugin);

    $davacl = new Sabre\DAVACL\Plugin();
    // added to fix this issue: https://github.com/sabre-io/dav/issues/1244#issuecomment-582607462
    $davacl->allowUnauthenticatedAccess = false;
    $server->addPlugin($davacl);

    $server->addPlugin(new Sabre\CalDAV\Plugin());
    $server->addPlugin(new Sabre\CalDAV\Schedule\Plugin());
    $server->addPlugin(new \XCalendar\CalDavIMip('', true));

    // add the browser plugin for debugging if enabled
    if (!empty($config['xcalendar_caldav_enable_browser_plugin'])) {
        $server->addPlugin(new Sabre\DAV\Browser\Plugin(false));
    }

    $server->start();
    CalDavLog::write();
} catch (Throwable $e) {
    CalDavLog::log('EXCEPTION: ' . $e->getMessage());
    CalDavLog::write();
    \XFramework\Utils::logError('CalDAV server error: ' . $e->getMessage());
    http_response_code(500);
    exit('Error: cannot start the CalDAV server.');
}
