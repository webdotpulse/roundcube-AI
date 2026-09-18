<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * This class provides the basis for plugin unit testing.
 *
 * Make sure @backupGlobals is set to disabled, otherwise you'll get the error:
 * "PDOException: You cannot serialize or unserialize PDO instances"
 * https://blogs.kent.ac.uk/webdev/2011/07/14/phpunit-and-unserialized-pdo-instances/
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

ini_set('error_reporting', E_ERROR);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once __DIR__ . "/DatabaseMysql.php";
require_once __DIR__ . "/Input.php";
require_once __DIR__ . "/Format.php";

class Test extends \PHPUnit\Framework\TestCase
{
    public $rcmail = false;
    protected $input = false;
    protected $db = false;
    protected $userId = false;
    protected $class = false;
    protected $assets = [];
    protected $rcVersion;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->dbProvider = $_SERVER['ROUNDCUBE_TEST_DATABASE'];
        $this->rcVersion = $_SERVER['ROUNDCUBE_TEST_VERSION'] ?? false;

        if (empty($this->rcVersion)) {
            exit("Error: Roundcube version not specified.");
        }

        // start the session to prevent the headers already sent error during tests
        session_start();
        $_SESSION['x_unit_testing'] = true;

        // set the server variables and include the roundcube framework
        $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

        // create the rcmail instance that will use config-test.inc.php
        // all the calls to xrc() will use this same instance
        $this->rcmail = \rcmail::get_instance(0, "test");

        if (!str_contains($this->rcmail->config->get("db_dsnw"), "roundcube_test")) {
            exit("Error loading test config file. Link config-test.inc.php to the roundcube $this->rcVersion config directory.");
        }

        $this->callMethod($this->rcmail, "startup");
        $_SERVER["HTTP_X_CSRF_TOKEN"] = $this->rcmail->get_request_token();

        $this->input = Input::instance();
        $this->db = xdb();
        $this->format = xformat();

        if (!($user = $this->db->row("users", ["user_id" => 1]))) {
            exit("Error loading user (33948554).");
        }

        // emulate a logged in user
        $user['password'] = "";
        $user['active'] = "1";

        $this->rcmail->set_user(new \rcube_user(null, $user));
        $this->userId = $this->rcmail->get_user_id();

        parent::__construct($name, $data, $dataName);

        // create and initialize the plugin class
        if (str_starts_with(get_class($this), "X")) {
            // convert camelcase to underscores, so XNewsFeedTest becomes xnews_feed
            $className = "x" . strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', substr(get_class($this), 1, -4)));
            $this->class = $this->getPluginClass($className);
        }
    }

    public function getPluginClass(string $className)
    {
        require_once __DIR__ . "/../../$className/$className.php";
        $class = new $className(\rcube_plugin_api::get_instance());
        $this->setProperty($class, "unitTest", true);
        $class->init();
        return $class;
    }

    /**
     * Calls a protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function callMethod($object, string $methodName, array $parameters = [])
    {
        is_array($parameters) || $parameters = [$parameters];
        $reflection = new \ReflectionClass(get_class($object));
        try {
            $method = $reflection->getMethod($methodName);
            $method->setAccessible(true);
            return $method->invokeArgs($object, $parameters);
        } catch (\ReflectionException $e) {
            exit("Reflection exception (28849932) " . $e->getMessage());
        }
    }

    /**
     * Sets the value of a protected class property.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     */
    public function setProperty($object, $property, $value)
    {
        $reflection = new \ReflectionObject($object);
        try {
            $refProperty = $reflection->getProperty($property);
            $refProperty->setAccessible(true);
            $refProperty->setValue($object, $value);
        } catch (\ReflectionException $e) {
            exit("Reflection exception (589085324) " . $e->getMessage());
        }
    }

    /**
     * Returns the value of a protected class property.
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    public function getProperty($object, $property)
    {
        $reflection = new \ReflectionObject($object);
        try {
            $refProperty = $reflection->getProperty($property);
            $refProperty->setAccessible(true);
            return $refProperty->getValue($object);
        } catch (\ReflectionException $e) {
            exit("Reflection exception (38908523) " . $e->getMessage());
        }
    }

    protected function hasAsset($partialAssetString): bool
    {
        $assets = $this->class->rcmail->output->get_env("xassets");

        if (!is_array($assets) || empty($assets)) {
            return false;
        }

        foreach ($assets as $asset) {
            if (str_contains($asset, $partialAssetString)) {
                return true;
            }
        }

        return false;
    }

    protected function assertIncludesArray($fullArray, $partialArray)
    {
        $this->assertTrue(is_array($fullArray));
        $this->assertTrue(is_array($partialArray));
        $this->assertTrue(!empty($fullArray));
        $this->assertTrue(!empty($partialArray));

        foreach ($partialArray as $key => $val) {
            $this->assertTrue(isset($fullArray[$key]));
            $this->assertEquals((string)$fullArray[$key], (string)$val);

        }
    }

    protected function assertPluginFiles()
    {
        $path = realpath(__DIR__ . "/../../" . get_class($this->class));

        $this->assertFileExists("$path/CHANGE_LOG");
        $this->assertFileExists("$path/composer.json");
        $this->assertFileExists("$path/LICENSE");
        $this->assertFileExists("$path/README");

        if ($this->getProperty($this->class, "hasConfig")) {
            $this->assertFileExists("$path/config.inc.php.dist");
        }

        if ($this->getProperty($this->class, "hasLocalization")) {
            $this->assertLocalizationFiles();
        }
    }

    protected function assertLocalizationFiles()
    {
        $dir = __DIR__ . "/../localization";
        $this->assertTrue(is_array($files = scandir($dir)));

        foreach (array_diff($files, ["..", "."]) as $file) {
            $labels = false;
            include("$dir/$file");
            $this->assertTrue(is_array($labels));
        }
    }

    protected function has($needle, $haystack): bool
    {
        return str_contains($haystack, $needle);
    }
}