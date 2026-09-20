<?php
/**
 * Roundcube AI Extra Content Installer
 *
 * Automatically installs and synchronizes bundled skins (gmail_plus)
 * and companion plugins (xskin, xframework, customizr, thread_drafts, thunderbird_labels, xcalendar, roundcube_loader, xmultibox, xsignature)
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
        $this->pluginDir = $pluginDir ? rtrim($pluginDir, '/\\') : dirname(__DIR__);
        if ($roundcubeDir) {
            $this->roundcubeDir = rtrim($roundcubeDir, '/\\');
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
        if (defined('INSTALL_PATH') && !in_array('--no-activate', $argv, true)) {
            $this->activate = true;
        }

        foreach ($argv as $arg) {
            if ($arg === '--dry-run') {
                $this->dryRun = true;
            } elseif ($arg === '--activate') {
                $this->activate = true;
            } elseif ($arg === '--no-activate') {
                $this->activate = false;
            } elseif ($arg === '-v' || $arg === '--verbose') {
                $this->verbose = true;
            } elseif (str_starts_with($arg, '--roundcube-path=')) {
                $path = substr($arg, strlen('--roundcube-path='));
                $this->roundcubeDir = rtrim($path, '/\\');
            } elseif (str_starts_with($arg, '--target=')) {
                $path = substr($arg, strlen('--target='));
                $this->roundcubeDir = rtrim($path, '/\\');
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
            $this->warning("No 'Extra context' directory found in {$this->pluginDir}. Nothing to install.");
            return 0;
        }
        $this->info("Found extra content source at: {$sourceDir}");

        // 2. Locate destination Roundcube installation root
        $targetDir = $this->findRoundcubeDir();
        if (!$targetDir) {
            $this->warning("Could not automatically locate Roundcube root directory.");
            $this->info("To deploy extra content into Roundcube, run:");
            $this->info("  php " . __FILE__ . " --roundcube-path=/path/to/roundcube");
            return 0;
        }

        $this->success("Target Roundcube root: {$targetDir}");

        if ($this->dryRun) {
            $this->warning("[DRY-RUN MODE ACTIVE] No filesystem changes will be made.");
        }

        $pluginsTarget = $targetDir . DIRECTORY_SEPARATOR . 'plugins';
        $skinsTarget = $targetDir . DIRECTORY_SEPARATOR . 'skins';

        if (!is_dir($pluginsTarget) || !is_dir($skinsTarget)) {
            $this->error("Invalid Roundcube directory. Expected both 'plugins' and 'skins' directories in {$targetDir}.");
            return 1;
        }

        $installedCount = 0;

        // 3. Install all skins from Extra context/skins/ into <roundcube>/skins/
        $skinsSrcDir = $sourceDir . DIRECTORY_SEPARATOR . 'skins';
        if (is_dir($skinsSrcDir)) {
            $skinItems = scandir($skinsSrcDir) ?: [];
            foreach ($skinItems as $skinName) {
                if ($skinName === '.' || $skinName === '..' || $skinName === '.git') {
                    continue;
                }
                $src = $skinsSrcDir . DIRECTORY_SEPARATOR . $skinName;
                if (!is_dir($src)) {
                    continue;
                }
                // Handle legacy nested structure if Extra context/skins/gmail_plus/skins/gmail_plus exists
                if (is_dir($src . DIRECTORY_SEPARATOR . 'skins' . DIRECTORY_SEPARATOR . $skinName)) {
                    $src = $src . DIRECTORY_SEPARATOR . 'skins' . DIRECTORY_SEPARATOR . $skinName;
                }
                $dest = $skinsTarget . DIRECTORY_SEPARATOR . $skinName;
                $this->installComponent('skin', $skinName, $src, $dest);
                $installedCount++;
            }
        }

        // 4. Install all plugins from Extra context/plugins/ into <roundcube>/plugins/
        $pluginsSrcDir = $sourceDir . DIRECTORY_SEPARATOR . 'plugins';
        if (is_dir($pluginsSrcDir)) {
            $pluginItems = scandir($pluginsSrcDir) ?: [];
            foreach ($pluginItems as $pluginName) {
                if ($pluginName === '.' || $pluginName === '..' || $pluginName === '.git') {
                    continue;
                }
                $src = $pluginsSrcDir . DIRECTORY_SEPARATOR . $pluginName;
                if (!is_dir($src)) {
                    continue;
                }
                $dest = $pluginsTarget . DIRECTORY_SEPARATOR . $pluginName;
                $this->installComponent('plugin', $pluginName, $src, $dest);
                $installedCount++;
            }
        }

        // 5. Fallback for legacy nested plugins in skins/gmail_plus/plugins
        $legacyPluginsDir = $skinsSrcDir . DIRECTORY_SEPARATOR . 'gmail_plus' . DIRECTORY_SEPARATOR . 'plugins';
        if (is_dir($legacyPluginsDir)) {
            foreach (['xskin', 'xframework'] as $lp) {
                $src = $legacyPluginsDir . DIRECTORY_SEPARATOR . $lp;
                $dest = $pluginsTarget . DIRECTORY_SEPARATOR . $lp;
                if (is_dir($src) && !is_dir($dest)) {
                    $this->installComponent('plugin', $lp, $src, $dest);
                    $installedCount++;
                }
            }
        }

        // 6. Ensure this AI plugin itself has compatibility link in plugins/
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
    private function installComponent(string $type, string $name, string $source, string $destination): void
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

        // Bootstrap configuration file if .sample or .dist exists and config.inc.php does not
        $targetConfigFile = $destination . DIRECTORY_SEPARATOR . 'config.inc.php';
        if (!file_exists($targetConfigFile)) {
            $sampleCandidates = [
                $destination . DIRECTORY_SEPARATOR . 'config.inc.php.sample',
                $destination . DIRECTORY_SEPARATOR . 'config.inc.php.dist',
            ];
            foreach ($sampleCandidates as $sampleFilePath) {
                if (file_exists($sampleFilePath)) {
                    if ($this->dryRun) {
                        $this->info("  -> Would initialize default config: {$targetConfigFile} from " . basename($sampleFilePath));
                    } else {
                        @copy($sampleFilePath, $targetConfigFile);
                        $this->success("  -> Initialized default config: {$targetConfigFile}");
                    }
                    break;
                }
            }
        } else {
            $this->info("  -> Preserving existing user config at {$targetConfigFile}");
        }

        // Special post-install routine for xcalendar
        if ($type === 'plugin' && $name === 'xcalendar') {
            $this->postInstallXcalendar($destination, $this->roundcubeDir);
        }

        // Special post-install routine for xsignature
        if ($type === 'plugin' && $name === 'xsignature') {
            $this->postInstallXsignature($destination, $this->roundcubeDir);
        }

        // Special post-install routine for xmultibox
        if ($type === 'plugin' && $name === 'xmultibox') {
            $this->postInstallXmultibox($destination, $this->roundcubeDir);
        }
    }

    /**
     * Special post-installation setup, requirements verification, and guidance for xsignature.
     */
    private function postInstallXsignature(string $destination, ?string $roundcubeDir): void
    {
        $this->info("--- Configuring xsignature plugin ---");

        // 1. Requirements verification (PHP 8.0+ and GD extension)
        if (PHP_VERSION_ID < 80000) {
            $this->warning("  [!] xsignature requires PHP 8.0 or higher. Current PHP version: " . PHP_VERSION);
        } else {
            $this->success("  [✓] PHP version " . PHP_VERSION . " meets xsignature requirement (>= 8.0).");
        }

        if (!extension_loaded('gd')) {
            $this->warning("  [!] PHP GD extension is NOT loaded. xsignature requires GD (GD2) for signature image generation.");
        } else {
            $this->success("  [✓] PHP GD extension is loaded.");
        }

        // 2. Default signature logo directory setup
        $targetDir = $roundcubeDir ?? $this->findRoundcubeDir();
        if ($targetDir) {
            $logoDir = $targetDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'xsignature';
            if (!is_dir($logoDir)) {
                if ($this->dryRun) {
                    $this->info("  -> Would create signature logos directory: {$logoDir}");
                } else {
                    if (@mkdir($logoDir, 0775, true)) {
                        $this->success("  -> Created signature logos directory: {$logoDir}");
                    }
                }
            } else {
                $this->info("  -> Preserving existing signature logos directory at {$logoDir}");
            }
        }

        // 3. Database & license guidance
        $this->info("  [i] Database: xsignature columns in 'identities' table are created automatically on first access to Settings > Identities.");
        $this->info("  [i] Signature Builder: Available under Settings > Identities > [Identity] > 'Enable Signature Builder'.");
        $this->info("  [i] License Key & Branding: Configured automatically in config/config.inc.php (\$config['license_key'] = 'RCPLUSFREE20266u'; \$config['remove_vendor_branding'] = true;).");
    }

    /**
     * Special post-installation setup, requirements verification, and guidance for xmultibox.
     */
    private function postInstallXmultibox(string $destination, ?string $roundcubeDir): void
    {
        $this->info("--- Configuring xmultibox plugin ---");

        // 1. Requirements verification (PHP 8.0+)
        if (PHP_VERSION_ID < 80000) {
            $this->warning("  [!] xmultibox requires PHP 8.0 or higher. Current PHP version: " . PHP_VERSION);
        } else {
            $this->success("  [✓] PHP version " . PHP_VERSION . " meets xmultibox requirement (>= 8.0).");
        }

        // 2. Database & multi-identity guidance
        $this->info("  [i] Database: xmultibox columns in 'identities' table are created automatically on first access to Settings > Identities.");
        $this->info("  [i] Multi-account: Navigate to Settings > Identities to configure custom IMAP/SMTP servers.");
        $this->info("  [i] License Key & Branding: Configured automatically in config/config.inc.php (\$config['license_key'] = 'RCPLUSFREE20266u'; \$config['remove_vendor_branding'] = true;).");
    }

    /**
     * Special post-installation setup, requirements verification, and guidance for xcalendar.
     */
    private function postInstallXcalendar(string $destination, ?string $roundcubeDir): void
    {
        $this->info("--- Configuring xcalendar plugin ---");

        // 1. Requirements verification (PHP 8.0+ and cURL)
        if (PHP_VERSION_ID < 80000) {
            $this->warning("  [!] xcalendar requires PHP 8.0 or higher. Current PHP version: " . PHP_VERSION);
        } else {
            $this->success("  [✓] PHP version " . PHP_VERSION . " meets xcalendar requirement (>= 8.0).");
        }

        if (!extension_loaded('curl')) {
            $this->warning("  [!] PHP cURL extension is NOT loaded. xcalendar requires cURL for CalDAV synchronization and remote calendars.");
        } else {
            $this->success("  [✓] PHP cURL extension is loaded.");
        }

        // 2. Event attachments directory setup
        $attachmentsDir = $destination . DIRECTORY_SEPARATOR . 'attachments';
        if (!is_dir($attachmentsDir)) {
            if ($this->dryRun) {
                $this->info("  -> Would create secure event attachments directory: {$attachmentsDir}");
            } else {
                if (@mkdir($attachmentsDir, 0775, true)) {
                    $htaccess = $attachmentsDir . DIRECTORY_SEPARATOR . '.htaccess';
                    @file_put_contents(
                        $htaccess,
                        "# Protect event attachments from direct web access\nOrder Deny,Allow\nDeny from all\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n"
                    );
                    $this->success("  -> Created event attachments directory: {$attachmentsDir} (protected by .htaccess)");
                }
            }
        } else {
            $this->info("  -> Preserving existing attachments directory at {$attachmentsDir}");
        }

        // 3. Output database & cron job guidance from xcalendar_install.txt
        $this->info("  [i] Database: xcalendar tables ('xcalendar_*') will be created automatically on first calendar access.");
        $cronScript = $destination . DIRECTORY_SEPARATOR . 'cron.php';
        $this->info("  [i] Email Notifications Cron Job (run every 1 minute):");
        $this->info("        Option 1 (URL):  * * * * * wget -q -O - <roundcube_url>/index.php?xcalendar-cron=1 >/dev/null 2>&1");
        $this->info("        Option 2 (CLI):  * * * * * php {$cronScript}");
        $this->info("  [i] License Key & Branding: Configured automatically in config/config.inc.php (\$config['license_key'] = 'RCPLUSFREE20266u'; \$config['remove_vendor_branding'] = true;).");
    }

    /**
     * Ensure this AI plugin is also available under expected directory names in plugins/
     * (e.g. lifeprisma_ai and roundcube_ai).
     */
    private function ensureAiPluginLinked(string $pluginsTarget): void
    {
        $lifeprismaTarget = $pluginsTarget . DIRECTORY_SEPARATOR . 'lifeprisma_ai';
        $roundcubeAiTarget = $pluginsTarget . DIRECTORY_SEPARATOR . 'roundcube_ai';

        if ($this->isSamePath($this->pluginDir, $roundcubeAiTarget) && !file_exists($lifeprismaTarget)) {
            if (!$this->dryRun) {
                @symlink('roundcube_ai', $lifeprismaTarget);
                $this->info("Created compatibility symlink: {$lifeprismaTarget} -> roundcube_ai");
            }
        } elseif ($this->isSamePath($this->pluginDir, $lifeprismaTarget) && !file_exists($roundcubeAiTarget)) {
            if (!$this->dryRun) {
                @symlink('lifeprisma_ai', $roundcubeAiTarget);
                $this->info("Created compatibility symlink: {$roundcubeAiTarget} -> lifeprisma_ai");
            }
        }
    }

    /**
     * Compare two paths safely.
     */
    private function isSamePath(string $p1, string $p2): bool
    {
        $r1 = realpath($p1) ?: rtrim($p1, '/\\');
        $r2 = realpath($p2) ?: rtrim($p2, '/\\');
        return $r1 === $r2;
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
                @copy($sampleFile, $configFile);
                $this->success("Initialized Roundcube main config: {$configFile}");
            }
        }

        if (!file_exists($configFile)) {
            $this->info("Note: Roundcube config not yet initialized ({$configFile}).");
            $this->info("When configuring Roundcube, activate these plugins in \$config['plugins']:");
            $this->info("  'xskin', 'customizr', 'thread_drafts', 'thunderbird_labels', 'xcalendar', 'roundcube_loader', 'xmultibox', 'xsignature', 'lifeprisma_ai'");
            $this->info("And set the active skin: \$config['skin'] = 'gmail_plus';");
            return;
        }

        $configContent = @file_get_contents($configFile);
        if ($configContent === false) {
            return;
        }

        $recommendedPlugins = ['xskin', 'customizr', 'thread_drafts', 'thunderbird_labels', 'xcalendar', 'roundcube_loader', 'xmultibox', 'xsignature', 'lifeprisma_ai'];
        $missingPlugins = [];

        foreach ($recommendedPlugins as $p) {
            if ($p === 'lifeprisma_ai' && preg_match("/['\"]roundcube_ai['\"]/", $configContent)) {
                continue;
            }
            if (!preg_match("/['\"]" . preg_quote($p, '/') . "['\"]/", $configContent)) {
                $missingPlugins[] = $p;
            }
        }

        // Parse existing plugins from $config['plugins'] array to verify ordering
        $existingPlugins = [];
        $hasPluginsArray = false;
        if (preg_match('/\$config\[[\'"]plugins[\'"]\]\s*=\s*(?:array\s*\((.*?)\)|\[(.*?)\])\s*;/is', $configContent, $pm)) {
            $hasPluginsArray = true;
            $inner = ($pm[1] !== '') ? $pm[1] : ($pm[2] ?? '');
            if (preg_match_all("/['\"]([a-zA-Z0-9_\-]+)['\"]/", $inner, $matches)) {
                $existingPlugins = $matches[1] ?? [];
            }
        }

        // 'xskin' must be placed at the beginning of $config['plugins'] whenever plugins exist
        $xskinIsFirst = (!empty($existingPlugins) && $existingPlugins[0] === 'xskin');
        $needsXskinAtBeginning = !empty($existingPlugins) && !$xskinIsFirst;

        $hasGmailPlusSkin = preg_match("/\\\$config\\['skin'\\]\\s*=\\s*['\"]gmail_plus['\"]/", $configContent);
        $hasEmptyLicenseKey = preg_match("/\\\$config\\[['\"]license_key['\"]\\]\\s*=\\s*['\"]['\"];/", $configContent);
        $hasLicenseKey = preg_match("/\\\$config\\[['\"]license_key['\"]\\]/", $configContent) && !$hasEmptyLicenseKey;
        $hasRemoveVendorBranding = preg_match("/\\\$config\\[['\"]remove_vendor_branding['\"]\\]/", $configContent);

        $needsConfigUpdate = !empty($missingPlugins) || $needsXskinAtBeginning || !$hasGmailPlusSkin || !$hasLicenseKey || !$hasRemoveVendorBranding;

        if ($needsConfigUpdate) {
            $this->info("Roundcube Configuration Status:");
            if ($needsXskinAtBeginning) {
                $this->info("  Plugin 'xskin' must be placed at the beginning of \$config['plugins'] (found: " . implode(', ', $existingPlugins) . ")");
            }
            if (!empty($missingPlugins)) {
                $this->info("  Plugins available to activate in \$config['plugins']: " . implode(', ', $missingPlugins));
            }
            if (!$hasGmailPlusSkin) {
                $this->info("  Skin available to activate: \$config['skin'] = 'gmail_plus';");
            }
            if (!$hasLicenseKey) {
                $this->info("  License key setting missing or empty: \$config['license_key'] = 'RCPLUSFREE20266u';");
            }
            if (!$hasRemoveVendorBranding) {
                $this->info("  Vendor branding setting missing: \$config['remove_vendor_branding'] = true;");
            }

            if ($this->activate && is_writable($configFile)) {
                $this->updateRoundcubeConfig(
                    $configFile,
                    $configContent,
                    $missingPlugins,
                    !$hasGmailPlusSkin,
                    !$hasLicenseKey,
                    !$hasRemoveVendorBranding,
                    $needsXskinAtBeginning
                );
            } else {
                $this->info("  (Run with --activate to automatically enable them in config.inc.php)");
            }
        } else {
            $this->success("Roundcube configuration already has gmail_plus skin, companion plugins (with 'xskin' first), and license settings configured!");
        }
    }

    /**
     * Automatically update config/config.inc.php to enable plugins, skin, license key, and branding settings.
     * Ensures 'xskin' is always placed at the very beginning of $config['plugins'].
     */
    private function updateRoundcubeConfig(
        string $configFile,
        string $content,
        array $missingPlugins,
        bool $enableSkin,
        bool $addLicenseKey = false,
        bool $addRemoveVendorBranding = false,
        bool $forceXskinFirst = false
    ): void {
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

        // Configure plugins array, ensuring 'xskin' is placed at the beginning
        if (!empty($missingPlugins) || $forceXskinFirst) {
            if (preg_match('/(\$config\[[\'"]plugins[\'"]\]\s*=\s*)(array\s*\((.*?)\)|\[(.*?)\])(\s*;)/is', $content, $m)) {
                $openPrefix = $m[1];
                $arrayExpr = $m[2];
                $innerContent = ($m[3] !== '') ? $m[3] : ($m[4] ?? '');
                $semicolon = $m[5];

                $usesArrayKeyword = (stripos(ltrim($arrayExpr), 'array') === 0);
                $isMultiLine = (strpos($arrayExpr, "\n") !== false);

                preg_match_all("/['\"]([a-zA-Z0-9_\-]+)['\"]/", $innerContent, $pm);
                $existingPlugins = $pm[1] ?? [];

                // Filter out 'xskin' from existing plugins so it is always placed first (index 0)
                $otherExisting = array_values(array_filter($existingPlugins, fn($p) => $p !== 'xskin'));

                // 'xskin' is ALWAYS at the beginning
                $finalPlugins = ['xskin'];

                // Append all other existing plugins, preserving their original order
                foreach ($otherExisting as $p) {
                    if (!in_array($p, $finalPlugins, true)) {
                        $finalPlugins[] = $p;
                    }
                }

                // Append any missing companion plugins to be activated
                foreach ($missingPlugins as $p) {
                    if ($p === 'xskin') {
                        continue;
                    }
                    if ($p === 'lifeprisma_ai' && (in_array('roundcube_ai', $finalPlugins, true) || in_array('lifeprisma_ai', $finalPlugins, true))) {
                        continue;
                    }
                    if ($p === 'roundcube_ai' && (in_array('lifeprisma_ai', $finalPlugins, true) || in_array('roundcube_ai', $finalPlugins, true))) {
                        continue;
                    }
                    if (!in_array($p, $finalPlugins, true)) {
                        $finalPlugins[] = $p;
                    }
                }

                // Format the array (single-line or multiline matching existing style)
                if ($usesArrayKeyword) {
                    if ($isMultiLine || count($finalPlugins) > 4) {
                        $newArray = "array(\n";
                        foreach ($finalPlugins as $p) {
                            $newArray .= "    '{$p}',\n";
                        }
                        $newArray .= ")";
                    } else {
                        $newArray = "array('" . implode("', '", $finalPlugins) . "')";
                    }
                } else {
                    if ($isMultiLine || count($finalPlugins) > 4) {
                        $newArray = "[\n";
                        foreach ($finalPlugins as $p) {
                            $newArray .= "    '{$p}',\n";
                        }
                        $newArray .= "]";
                    } else {
                        $newArray = "['" . implode("', '", $finalPlugins) . "']";
                    }
                }

                $replacement = $openPrefix . $newArray . $semicolon;
                if ($content !== str_replace($m[0], $replacement, $content)) {
                    $content = str_replace($m[0], $replacement, $content);
                    $modified = true;
                    $this->success("  -> Configured \$config['plugins'] with 'xskin' at the beginning: " . implode(', ', $finalPlugins));
                }
            } elseif (!preg_match('/\$config\[[\'"]plugins[\'"]\]\s*=/i', $content)) {
                // $config['plugins'] was completely missing from config
                $finalPlugins = ['xskin'];
                foreach ($missingPlugins as $p) {
                    if ($p !== 'xskin' && !in_array($p, $finalPlugins, true)) {
                        $finalPlugins[] = $p;
                    }
                }
                $content .= "\n// Default plugins configured by Roundcube AI installer (xskin must be loaded first)\n";
                $content .= "\$config['plugins'] = array(\n";
                foreach ($finalPlugins as $p) {
                    $content .= "    '{$p}',\n";
                }
                $content .= ");\n";
                $modified = true;
                $this->success("  -> Initialized \$config['plugins'] with 'xskin' at the beginning: " . implode(', ', $finalPlugins));
            }
        }

        if ($addLicenseKey && !preg_match("/\\\$config\\[['\"]license_key['\"]\\]/", $content)) {
            $content .= "\n// Roundcube Plus license key (valid key for legacy checks; unneeded in patched plugins)\n\$config['license_key'] = 'RCPLUSFREE20266u';\n";
            $modified = true;
            $this->success("  -> Added \$config['license_key'] = 'RCPLUSFREE20266u' to config.inc.php");
        } elseif (preg_match("/\\\$config\\[['\"]license_key['\"]\\]\\s*=\\s*['\"]['\"];/", $content)) {
            $content = preg_replace("/\\\$config\\[['\"]license_key['\"]\\]\\s*=\\s*['\"]['\"];/", "\$config['license_key'] = 'RCPLUSFREE20266u';", $content);
            $modified = true;
            $this->success("  -> Updated \$config['license_key'] = 'RCPLUSFREE20266u' in config.inc.php");
        }

        if ($addRemoveVendorBranding && !preg_match("/\\\$config\\[['\"]remove_vendor_branding['\"]\\]/", $content)) {
            $content .= "\n// Remove Roundcube Plus vendor branding from login screen\n\$config['remove_vendor_branding'] = true;\n";
            $modified = true;
            $this->success("  -> Added \$config['remove_vendor_branding'] = true to config.inc.php");
        }

        if ($modified && !$this->dryRun) {
            @file_put_contents($configFile, $content);
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
                return $cand;
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
            $path = rtrim(INSTALL_PATH, '/\\');
            if (is_dir($path)) {
                return $path;
            }
        }

        // Priority 2: ROUNDCUBE_PATH environment variable
        $envPath = getenv('ROUNDCUBE_PATH') ?: getenv('ROUNDCUBE_DIR');
        if ($envPath && is_dir($envPath)) {
            return rtrim($envPath, '/\\');
        }

        // Priority 3: Check if plugin is located in <roundcube>/plugins/<plugin_dir>
        $parentDir = dirname($this->pluginDir);
        if (basename($parentDir) === 'plugins') {
            $grandParentDir = dirname($parentDir);
            if ($this->isRoundcubeRoot($grandParentDir)) {
                return $grandParentDir;
            }
        }

        // Priority 4: Check current working directory
        $cwd = getcwd();
        if ($cwd && $this->isRoundcubeRoot($cwd)) {
            return $cwd;
        }

        // Priority 5: Check parent of cwd
        if ($cwd) {
            $cwdParent = dirname($cwd);
            if ($this->isRoundcubeRoot($cwdParent)) {
                return $cwdParent;
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
     * Recursively copy files and directories preserving permissions.
     */
    private function copyRecursive(string $src, string $dst): void
    {
        if (is_link($src)) {
            if (file_exists($dst) || is_link($dst)) {
                @unlink($dst);
            }
            @symlink(readlink($src), $dst);
            return;
        }

        if (is_file($src)) {
            $dir = dirname($dst);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @copy($src, $dst);
            @chmod($dst, fileperms($src) & 0777);
            return;
        }

        if (!is_dir($dst)) {
            @mkdir($dst, 0755, true);
        }

        $dir = opendir($src);
        if (!$dir) {
            return;
        }

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..' || $file === '.git') {
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
                @copy($srcItem, $dstItem);
                @chmod($dstItem, fileperms($srcItem) & 0777);
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
  --activate             Automatically enable plugins, skin, license_key and branding removal in config/config.inc.php
  --dry-run              Simulate installation without making filesystem changes
  --verbose, -v          Verbose output
  --help, -h             Display this help message

HELP;
    }
}

// Execution handling:
// Check if running directly as CLI script or included by Roundcube PluginInstaller
$isDirectCli = (php_sapi_name() === 'cli' && !empty($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__));
if ($isDirectCli || defined('INSTALL_PATH')) {
    $exitCode = RoundcubeExtraContentInstaller::run($argv ?? []);
    if ($isDirectCli && !defined('INSTALL_PATH')) {
        exit($exitCode);
    }
}
