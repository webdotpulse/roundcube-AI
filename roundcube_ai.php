<?php
/**
 * Compatibility wrapper for Roundcube Plugin Installer (webdotpulse/roundcube-ai).
 *
 * Allows Roundcube to load this plugin seamlessly when installed under
 * plugins/roundcube_ai without requiring directory renaming.
 *
 * @license MIT
 * @author Webdotpulse & LifePrisma Contributors
 */

require_once __DIR__ . '/lifeprisma_ai.php';

if (!class_exists('roundcube_ai', false)) {
    class roundcube_ai extends lifeprisma_ai
    {
    }
}
