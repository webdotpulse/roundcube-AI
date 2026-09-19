<?php
namespace XCalendar;

require_once __DIR__ . '/../vendor/autoload.php';

use Sabre\DAV;
use Sabre\DAV\MkCol;
use XFramework\DatabaseGeneric;

class CalDavPrincipal extends \Sabre\DAVACL\PrincipalBackend\AbstractBackend
{
    private $principalByPath = null;
    private \rcube $rcmail;
    private DatabaseGeneric $db;
    private $event;
    private $calendarBackend;
    private $currentPrincipalName;

    public function __construct($event, $calendarBackend)
    {
        $this->rcmail = xrc();
        $this->db = xdb();
        $this->event = $event;
        $this->calendarBackend = $calendarBackend;
    }

    public function setCurrentPrincipal($name): void
    {
        CalDavLog::log($name);
        $this->currentPrincipalName = $name;
    }

    /**
     * Returns a list of principals based on a prefix.
     *
     * This prefix will often contain something like 'principals'. You are only
     * expected to return principals that are in this base path.
     *
     * You are expected to return at least a 'uri' for every user, you can
     * return any additional properties if you wish so. Common properties are:
     *   {DAV:}displayname
     *   {http://sabredav.org/ns}email-address - This is a custom SabreDAV
     *     field that's actualy injected in a number of other properties. If
     *     you have an email address, use this property.
     *
     * @param string $prefixPath
     *
     * @return array
     */
    public function getPrincipalsByPrefix($prefixPath): array
    {
        CalDavLog::log($prefixPath);

        // return only the currently logged in principal
        return [['uri' => 'principals/' . $this->currentPrincipalName]];
    }

    /**
     * Returns a specific principal, specified by its path.
     * The returned structure should be the exact same as from
     * getPrincipalsByPrefix.
     *
     * @param string $path
     *
     * @return array
     */
    public function getPrincipalByPath($path): array
    {
        // if already got this during this call, return it
        if (is_array($this->principalByPath)) {
            CalDavLog::log("[CACHED] $path: " . json_encode($this->principalByPath));
            return $this->principalByPath;
        }

        $username = basename($path);
        if (!($syncData = $this->db->row('xcalendar_synced', ['username' => $username]))) {
            CalDavLog::log("ERROR: SYNC NOT FOUND [$path]");
            return [];
        }

        if (!($user = $this->db->row('users', ['user_id' => $syncData['user_id']]))) {
            CalDavLog::log("ERROR: USER NOT FOUND [{$syncData['user_id']} / $path]");
            return [];
        }

        // pass current sync data to calendar backend
        $this->calendarBackend->setSyncData($syncData);

        // update access time in db (at the most once a day)
        if (substr($syncData['connected_at'] ?? '', 0, 10) != date('Y-m-d')) {
            $this->db->update('xcalendar_synced', ['connected_at' => date('Y-m-d H:i:s')], ['id' => $syncData['id']]);
        }

        // set the event user id so all the permission functions work properly
        // using caldav we always act as if the user is the owner of the calendar and all the events
        // we set this here, this is the first place we can deduct the user id
        $this->event->setUserId($user['user_id']);

        // overwrite the default config with users preferences
        if (!empty($user['preferences'])) {
            foreach ((array)unserialize($user['preferences'], ['allowed_classes' => false]) as $key => $val) {
                $this->rcmail->config->set($key, $val);
            }
        }

        // get identity email
        if ($result = $this->db->fetch("SELECT email, name FROM {identities} WHERE user_id = ? AND del = 0 ".
            "ORDER BY standard DESC, name ASC, email ASC, identity_id ASC LIMIT 1",
            $user['user_id']
        )) {
            $email = $result['email'];
            $name = $result['name'];
        } else {
            $email = $user['username'];
            $name = $user['username'];
        }

        $this->principalByPath = [
            'id' => $syncData['id'],
            'uri' => $path,
            '{DAV:}displayname' => $name,
            '{http://sabredav.org/ns}email-address' => $email,
        ];

        CalDavLog::log("$path / " . json_encode($this->principalByPath));

        return $this->principalByPath;
    }

    /**
     * Updates one or more webdav properties on a principal.
     *
     * The list of mutations is stored in a Sabre\DAV\PropPatch object.
     * To do the actual updates, you must tell this object which properties
     * you're going to process with the handle() method.
     *
     * Calling the handle method is like telling the PropPatch object "I
     * promise I can handle updating this property".
     *
     * Read the PropPatch documentation for more info and examples.
     *
     * @param string $path
     */
    public function updatePrincipal($path, DAV\PropPatch $propPatch): bool
    {
        CalDavLog::log($path);
        return false;
    }

    /**
     * This method is used to search for principals matching a set of
     * properties.
     *
     * This search is specifically used by RFC3744's principal-property-search
     * REPORT.
     *
     * The actual search should be a unicode-non-case-sensitive search. The
     * keys in searchProperties are the WebDAV property names, while the values
     * are the property values to search on.
     *
     * By default, if multiple properties are submitted to this method, the
     * various properties should be combined with 'AND'. If $test is set to
     * 'anyof', it should be combined using 'OR'.
     *
     * This method should simply return an array with full principal uri's.
     *
     * If somebody attempted to search on a property the backend does not
     * support, you should simply return 0 results.
     *
     * You can also just return 0 results if you choose to not support
     * searching at all, but keep in mind that this may stop certain features
     * from working.
     *
     * @param string $prefixPath
     * @param array $searchProperties
     * @param string $test
     *
     * @return array
     */
    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof'): array
    {
        CalDavLog::log($prefixPath);
        return [];
    }

    /**
     * Finds a principal by its URI.
     *
     * This method may receive any type of uri, but mailto: addresses will be
     * the most common.
     *
     * Implementation of this API is optional. It is currently used by the
     * CalDAV system to find principals based on their email addresses. If this
     * API is not implemented, some features may not work correctly.
     *
     * This method must return a relative principal path, or null, if the
     * principal was not found or you refuse to find it.
     *
     * @param string $uri
     * @param string $principalPrefix
     *
     * @return string|null
     */
    public function findByUri($uri, $principalPrefix): ?string
    {
        CalDavLog::log("$uri / $principalPrefix");
        return null;
    }

    /**
     * Returns the list of members for a group-principal.
     *
     * @param string $principal
     *
     * @return array
     */
    public function getGroupMemberSet($principal): array
    {
        CalDavLog::log();
        return [];
    }

    /**
     * Returns the list of groups a principal is a member of.
     *
     * @param string $principal
     *
     * @return array
     */
    public function getGroupMembership($principal): array
    {
        return [];
    }

    /**
     * Updates the list of group members for a group principal.
     *
     * The principals should be passed as a list of uri's.
     *
     * @param string $principal
     */
    public function setGroupMemberSet($principal, array $members): bool
    {
        CalDavLog::log();
        return false;
    }

    /**
     * Creates a new principal.
     *
     * This method receives a full path for the new principal. The mkCol object
     * contains any additional webdav properties specified during the creation
     * of the principal.
     *
     * @param $path
     * @param MkCol $mkCol
     * @return bool
     */
    public function createPrincipal($path, MkCol $mkCol): bool
    {
        CalDavLog::log($path);
        return false;
    }

}
