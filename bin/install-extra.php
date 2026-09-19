<?php
/**
 * Roundcube AI Extra Content Installer
 *
 * Automatically installs and synchronizes bundled skins (gmail_plus)
 * and companion plugins (xskin, xframework, customizr, thread_drafts, thunderbird_labels)
 * into the host Roundcube Webmail environment during `composer install` / `composer update`.
 *
 * Can be run via:
 * 1. Roundcube Plugin Installer hook (`extra.roundcube.post-install-script`)
 * 2. Composer lifecycle script (`scripts.post-install-cmd`)
 * 3. Standalone CLI: `php bin/install-extra.php [--roundcube-path=/path/to/roundcube] [--activate] [--dry-run]`
 *
 * @license MIT
 * @author Webdotpulse & LifePrisma Contributors
 */

declare(strict_types=1);

class RoundcubeExtraContentInstaller
{
    private string $pluginDir;
    private ?string $roundcubeDir = null;
    private bool $dryRun = false;
    private bool $activate = false;
    private bool $verbose = false;
    private array $logs = [];

    public function __construct(?string $pluginDir = null, ?string $roundcubeDir = null)
    {
        $this->pluginDir = $pluginDir ? realpath($pluginDir) : realpath(dirname(__DIR__));
        if ($roundcubeDir) {
            $this->roundcubeDir = realpath($roundcubeDir) ?: $roundcubeDir;
        }
    }

    /**
     * Run installer from CLI or include context.
     */
    public static function run(array $argv = []): int
    {
        $installer = new self();
        $installer->parseCliArgs($argv);
        return $installer->execute();
    }

    /**
     * Parse CLI arguments if executed from terminal.
     */
    public function parseCliArgs(array $argv): void
    {
        foreach ($argv as $arg) {
            if ($arg === '--dry-run') {
                $this->dryRun = true;
            } elseif ($arg === '--activate') {
                $this->activate = true;
            } elseif ($arg === '-v' || $arg === '--verbose') {
                $this->verbose = true;
            } elseif (str_starts_with($arg, '--roundcube-path=')) {
                $path = substr($arg, strlen('--roundcube-path='));
                $this->roundcubeDir = realpath($path) ?: $path;
            } elseif (str_starts_with($arg, '--target=')) {
                $path = substr($arg, strlen('--target='));
                $this->roundcubeDir = realpath($path) ?: $path;
            } elseif ($arg === '--help' || $arg === '-h') {
                $this->printHelp();
                exit(0);
            }
        }
    }

    /**
     * Main execution entry point.
     */
    public function execute(): int
    {
        $this->info("=================================================");
        $this->info("  Roundcube AI — Extra Content Installer        ");
        $this->info("=================================================");

        // 1. Locate source directory containing extra content
        $sourceDir = $this->findSourceDir();
        if (!$sourceDir) {
            $this->warning("No 'Extra context' or 'extra_content' directory found in {$this->pluginDir}. Nothing to install.");
            return 0;
        }
        $this->info("Found extra content repository at: " . $sourceDir);

        // 2. Locate destination Roundcube installation root
        $targetDir = $this->findRoundcubeDir();
        if (!$targetDir) {
            $this->warning("Could not automatically locate Roundcube root directory.");
            $this->info("To deploy extra content into Roundcube, specify target path:");
            $this->info("  php bin/install-extra.php --roundcube-path=/path/to/roundcube");
            $this->info("Or set the ROUNDCUBE_PATH environment variable.");
            return 0;
        }

        $this->success("Detected Roundcube target at: " . $targetDir);

        if ($this->dryRun) {
            $this->warning("[DRY-RUN MODE ACTIVE] No filesystem modifications will be made.");
        }

        $pluginsTarget = $targetDir . DIRECTORY_SEPARATOR . 'plugins';
        $skinsTarget = $targetDir . DIRECTORY_SEPARATOR . 'skins';

        if (!is_dir($pluginsTarget) || !is_dir($skinsTarget)) {
            $this->error("Invalid Roundcube directory. Expected both 'plugins' and 'skins' directories in {$targetDir}.");
            return 1;
        }

        $installedCount = 0;

        // 3. Install Bundled Skin: GMail+
        $gmailPlusSkinSrc = $this->resolvePath([
            $sourceDir . '/skins/gmail_plus/skins/gmail_plus',
            $sourceDir . '/skins/gmail_plus',
        ]);

        if ($gmailPlusSkinSrc && is_dir($gmailPlusSkinSrc)) {
            $dest = $skinsTarget . DIRECTORY_SEPARATOR . 'gmail_plus';
            $this->installComponent('skin', 'gmail_plus', $gmailPlusSkinSrc, $dest, 'config.inc.php.sample');
            $installedCount++;
        }

        // 4. Install Roundcube Plus Plugins: xskin, xframework
        $xskinSrc = $this->resolvePath([
            $sourceDir . '/skins/gmail_plus/plugins/xskin',
            $sourceDir . '/plugins/xskin',
        ]);
        if ($xskinSrc && is_dir($xskinSrc)) {
            $dest = $pluginsTarget . DIRECTORY_SEPARATOR . 'xskin';
            $this->installComponent('plugin', 'xskin', $xskinSrc, $dest, 'config.inc.php.dist');
            $installedCount++;
        }

        $xframeworkSrc = $this->resolvePath([
            $sourceDir . '/skins/gmail_plus/plugins/xframework',
            $sourceDir . '/plugins/xframework',
        ]);
        if ($xframeworkSrc && is_dir($xframeworkSrc)) {
            $dest = $pluginsTarget . DIRECTORY_SEPARATOR . 'xframework';
            $this->installComponent('plugin', 'xframework', $xframeworkSrc, $dest, null);
            $installedCount++;
        }

        // 5. Install Companion Plugins: customizr, thread_drafts, thunderbird_labels
        $companionPlugins = ['customizr', 'thread_drafts', 'thunderbird_labels'];
        foreach ($companionPlugins as $pluginName) {
            $pluginSrc = $sourceDir . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pluginName;
            if (is_dir($pluginSrc)) {
                $dest = $pluginsTarget . DIRECTORY_SEPARATOR . $pluginName;
                $this->installComponent('plugin', $pluginName, $pluginSrc, $dest, 'config.inc.php.dist');
                $installedCount++;
            }
        }

        // 6. Ensure this AI plugin itself has a compatibility link or is recognized
        $this->ensureAiPluginLinked($pluginsTarget);

        // 7. Check / Assist Roundcube Configuration
        $this->checkRoundcubeConfig($targetDir);

        $this->info("-------------------------------------------------");
        $this->success("Extra content installation complete. ({$installedCount} components processed)");
        $this->info("=================================================");

        return 0;
    }

    /**
     * Install a skin or plugin component to the target destination.
     */
    private function installComponent(string $type, string $name, string $source, string $destination, ?string $sampleConfigFile): void
    {
        $this->info("Installing {$type} [{$name}]...");
        $this->info("  Source: {$source}");
        $this->info("  Target: {$destination}");

        if ($this->dryRun) {
            $this->info("  -> Would copy recursively from {$source} to {$destination}");
        } else {
            $this->copyRecursive($source, $destination);
            $this->success("  -> Installed {$name} to {$destination}");
        }

        // Bootstrap configuration file if sample/dist exists and target config does not
        if ($sampleConfigFile) {
            $targetConfigFile = $destination . DIRECTORY_SEPARATOR . 'config.inc.php';
            $sampleFilePath = $destination . DIRECTORY_SEPARATOR . $sampleConfigFile;

            if (!file_exists($targetConfigFile) && file_exists($sampleFilePath)) {
                if ($this->dryRun) {
                    $this->info("  -> Would initialize default config: {$targetConfigFile} from {$sampleConfigFile}");
                } else {
                    copy($sampleFilePath, $targetConfigFile);
                    $this->success("  -> Initialized default config: {$targetConfigFile}");
                }
            } elseif (file_exists($targetConfigFile)) {
                $this->info("  -> Preserving existing user config at {$targetConfigFile}");
            }
        }
    }

    /**
     * Ensure this AI plugin is also available under expected directory names in plugins/
     * (e.g. lifeprisma_ai and roundcube_ai).
     */
    private function ensureAiPluginLinked(string $pluginsTarget): void
    {
        $pluginBaseName = basename($this->pluginDir);

        // If installed inside Roundcube plugins directory (e.g. plugins/lifeprisma_ai or plugins/roundcube_ai)
        $lifeprismaTarget = $pluginsTarget . DIRECTORY_SEPARATOR . 'lifeprisma_ai';
        $roundcubeAiTarget = $pluginsTarget . DIRECTORY_SEPARATOR . 'roundcube_ai';

        if ($this->pluginDir === realpath($roundcubeAiTarget) && !file_exists($lifeprismaTarget)) {
            if (!$this->dryRun) {
                @symlink('roundcube_ai', $lifeprismaTarget);
                $this->info("Created compatibility symlink: {$lifeprismaTarget} -> roundcube_ai");
            }
        } elseif ($this->pluginDir === realpath($lifeprismaTarget) && !file_exists($roundcubeAiTarget)) {
            if (!$this->dryRun) {
                @symlink('lifeprisma_ai', $roundcubeAiTarget);
                $this->info("Created compatibility symlink: {$roundcubeAiTarget} -> lifeprisma_ai");
            }
        }
    }

    /**
     * Inspect and optionally update Roundcube config.inc.php.
     */
    private function checkRoundcubeConfig(string $targetDir): void
    {
        $configFile = $targetDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php';
        $sampleFile = $targetDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php.sample';

        if (!file_exists($configFile) && file_exists($sampleFile) && $this->activate) {
            if (!$this->dryRun) {
                copy($sampleFile, $configFile);
                $this->success("Initialized Roundcube main config: {$configFile}");
            }
        }

        if (!file_exists($configFile)) {
            $this->info("Note: Roundcube config not yet initialized ({$configFile}).");
            $this->info("When configuring Roundcube, activate these plugins in \$config['plugins']:");
            $this->info("  'xskin', 'customizr', 'thread_drafts', 'thunderbird_labels', 'lifeprisma_ai'");
            $this->info("And set the active skin in \$config['skin'] = 'gmail_plus';");
            return;
        }

        $configContent = file_get_contents($configFile);
        if ($configContent === false) {
            return;
        }

        $recommendedPlugins = ['xskin', 'customizr', 'thread_drafts', 'thunderbird_labels', 'lifeprisma_ai'];
        $missingPlugins = [];

        foreach ($recommendedPlugins as $p) {
            if (!preg_match("/['\"]" . preg_quote($p, '/') . "['\"]/", $configContent)) {
                $missingPlugins[] = $p;
            }
        }

        $hasGmailPlusSkin = preg_match("/\\\$config\\['skin'\\]\\s*=\\s*['\"]gmail_plus['\"]/", $configContent);

        if (!empty($missingPlugins) || !$hasGmailPlusSkin) {
            $this->info("Roundcube Configuration Status:");
            if (!empty($missingPlugins)) {
                $this->info("  Plugins available to activate in \$config['plugins']: " . implode(', ', $missingPlugins));
            }
            if (!$hasGmailPlusSkin) {
                $this->info("  Skin available to activate: \$config['skin'] = 'gmail_plus';");
            }

            if ($this->activate && is_writable($configFile)) {
                $this->updateRoundcubeConfig($configFile, $configContent, $missingPlugins, !$hasGmailPlusSkin);
            } else {
                $this->info("  (Run with --activate to automatically enable them in config.inc.php)");
            }
        } else {
            $this->success("Roundcube configuration already has gmail_plus skin and companion plugins enabled!");
        }
    }

    /**
     * Automatically update config/config.inc.php to enable plugins and skin.
     */
    private function updateRoundcubeConfig(string $configFile, string $content, array $missingPlugins, bool $enableSkin): void
    {
        $modified = false;

        if ($enableSkin) {
            if (preg_match("/(\\\$config\\['skin'\\]\\s*=\\s*)[^;]+;/", $content)) {
                $content = preg_replace("/(\\\$config\\['skin'\\]\\s*=\\s*)[^;]+;/", "\$1'gmail_plus';", $content);
                $modified = true;
            } else {
                $content .= "\n// Default skin set by Roundcube AI installer\n\$config['skin'] = 'gmail_plus';\n";
                $modified = true;
            }
            $this->success("  -> Set \$config['skin'] = 'gmail_plus' in config.inc.php");
        }

        if (!empty($missingPlugins)) {
            // Check if $config['plugins'] exists
            if (preg_match('/(\$config\[[\'"]plugins[\'"]\]\s*=\s*\[)([^\]]*)(\];)/s', $content, $m)) {
                $currentPluginsText = $m[2];
                $newEntries = "";
                foreach ($missingPlugins as $plugin) {
                    $newEntries .= "    '{$plugin}',\n";
                }
                $replacement = $m[1] . "\n" . $currentPluginsText . (str_ends_with(trim($currentPluginsText), ',') ? "\n" : ",\n") . $newEntries . $m[3];
                // Clean up duplicate empty commas or formatting
                $replacement = preg_replace('/,\s*,\s*/', ",\n", $replacement);
                $content = str_replace($m[0], $replacement, $content);
                $modified = true;
                $this->success("  -> Added missing plugins (" . implode(', ', $missingPlugins) . ") to \$config['plugins']");
            }
        }

        if ($modified && !$this->dryRun) {
            file_put_contents($configFile, $content);
            $this->success("Successfully updated Roundcube configuration file at {$configFile}");
        }
    }

    /**
     * Find directory containing the bundled extra content.
     */
    private function findSourceDir(): ?string
    {
        $candidates = [
            $this->pluginDir . DIRECTORY_SEPARATOR . 'Extra context',
            $this->pluginDir . DIRECTORY_SEPARATOR . 'extra_content',
            $this->pluginDir . DIRECTORY_SEPARATOR . 'extra-content',
            $this->pluginDir . DIRECTORY_SEPARATOR . 'extra',
        ];

        foreach ($candidates as $cand) {
            if (is_dir($cand)) {
                return realpath($cand);
            }
        }

        return null;
    }

    /**
     * Automatically discover the target Roundcube root directory.
     */
    private function findRoundcubeDir(): ?string
    {
        if ($this->roundcubeDir && is_dir($this->roundcubeDir)) {
            return $this->roundcubeDir;
        }

        // Priority 1: INSTALL_PATH constant defined by roundcube/plugin-installer
        if (defined('INSTALL_PATH')) {
            $path = realpath(INSTALL_PATH);
            if ($path && is_dir($path)) {
                return $path;
            }
        }

        // Priority 2: ROUNDCUBE_PATH environment variable
        $envPath = getenv('ROUNDCUBE_PATH') ?: getenv('ROUNDCUBE_DIR');
        if ($envPath && is_dir($envPath)) {
            return realpath($envPath);
        }

        // Priority 3: Check if plugin is located in <roundcube>/plugins/<plugin_dir>
        $parentDir = dirname($this->pluginDir);
        if (basename($parentDir) === 'plugins') {
            $grandParentDir = dirname($parentDir);
            if ($this->isRoundcubeRoot($grandParentDir)) {
                return realpath($grandParentDir);
            }
        }

        // Priority 4: Check current working directory
        $cwd = getcwd();
        if ($cwd && $this->isRoundcubeRoot($cwd)) {
            return realpath($cwd);
        }

        // Priority 5: Check parent of cwd
        if ($cwd) {
            $cwdParent = dirname($cwd);
            if ($this->isRoundcubeRoot($cwdParent)) {
                return realpath($cwdParent);
            }
        }

        // Priority 6: Standard local development paths
        $localPaths = [
            '/home/koen/Downloads/Roundcube/roundcubemail-1.7.4',
            '/var/www/html/roundcube',
            '/var/www/roundcube',
            '/usr/share/roundcube',
        ];
        foreach ($localPaths as $lp) {
            if ($this->isRoundcubeRoot($lp)) {
                return realpath($lp);
            }
        }

        return null;
    }

    /**
     * Validate if a directory is a Roundcube root.
     */
    private function isRoundcubeRoot(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }

        $hasIniset = file_exists($path . '/program/include/iniset.php');
        $hasIndex = file_exists($path . '/index.php');
        $hasPlugins = is_dir($path . '/plugins');
        $hasSkins = is_dir($path . '/skins');

        return ($hasIniset || $hasIndex) && $hasPlugins && $hasSkins;
    }

    /**
     * Resolves the first existing path from an array of candidates.
     */
    private function resolvePath(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return realpath($candidate);
            }
        }
        return null;
    }

    /**
     * Recursively copy files and directories preserving permissions.
     */
    private function copyRecursive(string $src, string $dst): void
    {
        if (is_link($src)) {
            if (file_exists($dst) || is_link($dst)) {
                unlink($dst);
            }
            @symlink(readlink($src), $dst);
            return;
        }

        if (is_file($src)) {
            $dir = dirname($dst);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            copy($src, $dst);
            chmod($dst, fileperms($src) & 0777);
            return;
        }

        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }

        $dir = opendir($src);
        if (!$dir) {
            return;
        }

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $srcItem = $src . DIRECTORY_SEPARATOR . $file;
            $dstItem = $dst . DIRECTORY_SEPARATOR . $file;

            if (is_dir($srcItem)) {
                $this->copyRecursive($srcItem, $dstItem);
            } else {
                // If target file is config.inc.php and already exists, do not overwrite!
                if ($file === 'config.inc.php' && file_exists($dstItem)) {
                    continue;
                }
                copy($srcItem, $dstItem);
                chmod($dstItem, fileperms($srcItem) & 0777);
            }
        }
        closedir($dir);
    }

    private function info(string $msg): void
    {
        $this->log("\033[0;34m[INFO]\033[0m " . $msg);
    }

    private function success(string $msg): void
    {
        $this->log("\033[0;32m[SUCCESS]\033[0m " . $msg);
    }

    private function warning(string $msg): void
    {
        $this->log("\033[0;33m[WARN]\033[0m " . $msg);
    }

    private function error(string $msg): void
    {
        $this->log("\033[0;31m[ERROR]\033[0m " . $msg);
    }

    private function log(string $msg): void
    {
        if (php_sapi_name() === 'cli' || defined('STDIN')) {
            echo $msg . PHP_EOL;
        }
        $this->logs[] = $msg;
    }

    private function printHelp(): void
    {
        echo <<<HELP
Roundcube AI Extra Content Installer

Usage:
  php bin/install-extra.php [options]

Options:
  --roundcube-path=DIR   Specify target Roundcube root directory
  --target=DIR           Alias for --roundcube-path
  --activate             Automatically enable plugins and skin in config/config.inc.php
  --dry-run              Simulate installation without making filesystem changes
  --verbose, -v          Verbose output
  --help, -h             Display this help message

HELP;
    }
}

// Execution handling:
// Check if running directly as CLI script or included by Roundcube PluginInstaller
if (php_sapi_name() === 'cli' || defined('INSTALL_PATH')) {
    $exitCode = RoundcubeExtraContentInstaller::run($argv ?? []);
    if (php_sapi_name() === 'cli' && !defined('INSTALL_PATH')) {
        exit($exitCode);
    }
}
