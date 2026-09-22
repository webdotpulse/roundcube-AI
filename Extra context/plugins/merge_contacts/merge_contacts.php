<?php

/**
 * Compatibility alias for merge_and_fix plugin
 */

declare(strict_types=1);

if (!class_exists('merge_and_fix')) {
    $parentPlugin = dirname(__DIR__) . '/merge_and_fix/merge_and_fix.php';
    if (file_exists($parentPlugin)) {
        require_once $parentPlugin;
    }
}

class merge_contacts extends merge_and_fix
{
}
