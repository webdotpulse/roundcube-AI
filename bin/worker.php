#!/usr/bin/env php
<?php

/**
 * Gemini Executive Assistant — CLI Background Worker Launcher
 *
 * Forwards execution to the background worker located in Extra context/plugins/roundcube_ai/bin/worker.php.
 *
 * @license MIT
 */

$workerScript = __DIR__ . '/../Extra context/plugins/roundcube_ai/bin/worker.php';

if (!file_exists($workerScript)) {
    fwrite(STDERR, "Error: AI worker script not found at {$workerScript}\n");
    exit(1);
}

require $workerScript;
