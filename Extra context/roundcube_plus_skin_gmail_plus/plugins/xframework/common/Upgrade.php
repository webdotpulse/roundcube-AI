<?php
namespace XFramework;

class Upgrade
{
    CONST LOCK_KEY = 'rcp_upgrade_lock';
    CONST LOCK_TIMEOUT = 120; // seconds
    protected \rcube $rcmail;
    protected \rcube_config $config;
    protected DatabaseGeneric $db;

    public function __construct()
    {
        $this->rcmail = xrc();
        $this->db = xdb();
    }

    /**
     * Runs the database upgrade process for all the RC+ plugins.
     *
     * @return void
     */
    public function upgradeDatabase(): void
    {
        // check if there are any plugins that need upgrading
        if (empty($this->getUpgradeData())) {
            return;
        }

        // try to acquire the upgrade lock, if can't, the upgrade is already in progress in some other instance
        if (!$this->acquireUpgradeLock()) {
            return;
        }

        // get the upgrade data again to make sure it's up to date (needed on sqlite)
        $upgradeData = $this->getUpgradeData();
        $errors = [];

        try {
            foreach ($upgradeData as $plugin => $data) {
                if (!$this->upgradePlugin($plugin, $data['current'], $data['latest'])) {
                    $errors[] = $plugin;
                }
            }
        } finally {
            xdata()->set('upgrade_disabled_plugins', $errors);
            $this->releaseUpgradeLock();
        }
    }

    protected function getUpgradeData(): array
    {
        $data = [];
        $latestVersions = $this->getLatestVersions();
        $dbVersions = $this->getDbVersions();

        foreach ($latestVersions as $name => $version) {
            if (!array_key_exists($name, $dbVersions) || $dbVersions[$name] < $version) {
                $data[$name] = [
                    'current' => (string)($dbVersions[$name] ?? 0),
                    'latest' => (string)$version,
                ];
            }
        }

        return $data;
    }

    /**
     * Upgrades the database tables for the specified plugin. Updates $dbVersions to the latest successfully executed
     * file. Ideally we'd run this in a transaction so the modifications from all the files get rolled back if any error
     * is encountered but commands like ALTER don't work in transactions in older versions of mysql or in sqlite.
     *
     * @param string $plugin
     * @param string $currentVersion
     * @param string $latestVersion
     * @return bool
     */
    protected function upgradePlugin(string $plugin, string $currentVersion, string $latestVersion): bool
    {
        // read the version files and loop over them
        $versions = [];
        $sqlDir = "$plugin/SQL/" . $this->db->getProvider() . '/';
        $dir = __DIR__ . "/../../$sqlDir";

        try {
            if (!file_exists($dir)) {
                $this->log("SQL directory doesn't exist ($plugin).");
                return false;
            }

            // get all the available version files that are newer than the current version
            $dh = opendir($dir);
            if ($dh === false) {
                $this->log("Cannot open SQL directory ($plugin).");
                return false;
            }

            while ($file = readdir($dh)) {
                if (preg_match('/^([0-9]+)\.sql$/', $file, $matches) && $matches[1] > $currentVersion) {
                    $versions[] = $matches[1];
                }
            }
            closedir($dh);
            sort($versions, SORT_NUMERIC);

            if (!in_array($latestVersion, $versions)) {
                $this->log("Latest version not found in SQL files for $plugin.");
                return false;
            }

            foreach ($versions as $version) {
                // files should never be empty
                if ($sql = file_get_contents("$dir$version.sql")) {
                    // don't write to log files on errors
                    $this->db->setOption('ignore_errors', true);
                    try {
                        if (!$this->db->script($sql)) {
                            throw new \Exception("$version.sql > " . $this->db->lastError());
                        }
                    } finally {
                        $this->refreshLock();
                        $this->db->setOption('ignore_errors', false);
                    }

                    // set the plugin's version in the db to the latest successfully upgraded version
                    $dbVersions = $this->getDbVersions();
                    $dbVersions[$plugin] = $version;
                    $this->setDbVersions($dbVersions);
                } else {
                    throw new \Exception("File $version.sql does not exist or cannot be open.");
                }
            }

            $this->log("$plugin: success");

            return true;

        } catch (\Exception $e) {
            $this->resetConnection();
            $this->log("$plugin error: " . $e->getMessage() . " -- Plugin $plugin has been disabled.");
            return false;
        }
    }

    /**
     * Returns an array of RC+ plugins that use the database, along with their latest db version as reported in the
     * class variables.
     *
     * @return array
     */
    protected function getLatestVersions(): array
    {
        $versions = [];
        $plugins = $this->rcmail->config->get('plugins', []);

        if (!is_array($plugins)) {
            $plugins = [];
        }

        foreach ($plugins as $name) {
            $plugin = $this->rcmail->plugins->get_plugin($name);
            if (!empty($plugin) &&
                method_exists($plugin, 'getLatestDbVersion') &&
                ($dbVersion = $plugin->getLatestDbVersion())
            ) {
                $versions[$name] = $dbVersion;
            }
        }

        return $versions;
    }

    /**
     * Reads and decodes the array of plugin db versions from the database.
     *
     * @return array
     */
    protected function getDbVersions(): array
    {
        $versions = json_decode(
            $this->db->value("value", "system", ["name" => "xframework_db_versions"]) ?: '[]',
            true
        );

        return is_array($versions) ? $versions : [];
    }

    /**
     * Encodes and saves the versions array in the database.
     *
     * @param array $versions
     * @return void
     */
    protected function setDbVersions(array $versions): void
    {
        $versions = json_encode($versions);

        if ($versions === false) {
            $versions = '[]';
        }

        if ($this->db->value("value", "system", ["name" => "xframework_db_versions"])) {
            $this->db->update("system", ["value" => $versions], ["name" => "xframework_db_versions"]);
        } else {
            $this->db->insert("system", ["name" => "xframework_db_versions", "value" => $versions]);
        }
    }

    /**
     * This function tries to insert a row to the system table, which will serve as a lock, informing all the
     * other running instances that the upgrade has already started. We're using this method because insert it's an
     * atomic operation that can only be performed one-at-a-time, so it's a reliable way of managing concurrency.
     * If the lock row already exists, the insert function will fail.
     *
     * @return bool
     */
    protected function acquireUpgradeLock(): bool
    {
        $time = time();

        // try to insert the lock row, this will fail if the row already exists
        try {
            if ($this->db->insert('system', ['name' => self::LOCK_KEY, 'value' => $time], false)) {
                $this->log("Lock acquired");
                return true;
            }
        } catch (\Exception $e) {
            $this->resetConnection();
        }

        // if insert failed, check if the existing lock is expired
        if ($value = $this->db->value('value', 'system', ['name' => self::LOCK_KEY])) {
            if (is_numeric($value) && $time - $value > self::LOCK_TIMEOUT) {
                // the lock is expired, remove it
                $this->db->remove('system', ['name' => self::LOCK_KEY]);
                $this->log("Expired lock removed");

                // try to acquire the lock again
                try {
                    if ($this->db->insert('system', ['name' => self::LOCK_KEY, 'value' => $time], false)) {
                        $this->log("Lock acquired (2)");
                        return true;
                    }
                } catch (\Exception $e) {
                    $this->resetConnection();
                    $this->log("Lock re-acquisition failed: " . $e->getMessage());
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Removes the upgrade lock row from the database.
     *
     * @return void
     */
    protected function releaseUpgradeLock(): void
    {
        $this->db->remove('system', ['name' => self::LOCK_KEY]);
        $this->log("Lock released");
    }

    /**
     * Updates the time of the lock record, this is done after every upgrade file operation to make sure the lock
     * timeout doesn't expire while the upgrade is in progress.
     *
     * @return void
     */
    protected function refreshLock(): void
    {
        if ($this->db->value('value', 'system', ['name' => self::LOCK_KEY])) {
            $this->db->update('system', ['value' => time()], ['name' => self::LOCK_KEY]);
        }
    }

    /**
     * Custom log function for convenience.
     *
     * @param string $text
     * @return void
     */
    protected function log(string $text): void
    {
        \rcube::write_log('errors', "[RC+ DATABASE UPGRADE] " . $text);
    }

    /**
     * Resets the database connection after an error has been logged. If we don't do this, the error will persist and
     * all the subsequent queries will fail and Roundcube will show its fatal error page.
     *
     * @return void
     */
    protected function resetConnection(): void
    {
        // without this all subsequent connections will fail, but it doesn't reset the db_error variable
        $this->db->closeConnection();

        // to reset the db_error variable and prevent roundcube's fatal error page, we need to run a query and reconnect
        // yes, there will most likely be queries as the program continues running, but just in case
        $this->db->value("value", "system", ["name" => "xframework_db_versions"]);
    }
}