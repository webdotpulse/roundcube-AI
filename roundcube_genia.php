<?php
/**
 * Compatibility wrapper for Roundcube Plugin Installer (lifeprisma/roundcube-genia).
 *
 * Allows Roundcube to load this plugin seamlessly when installed under
 * plugins/roundcube_genia without requiring directory renaming.
 *
 * @license MIT
 * @author Webdotpulse & LifePrisma Contributors
 */

require_once __DIR__ . '/lifeprisma_ai.php';

if (!class_exists('roundcube_genia', false)) {
    class roundcube_genia extends lifeprisma_ai
    {
    }
}
