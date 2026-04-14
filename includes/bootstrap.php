<?php

declare(strict_types=1);

// Fall back to a local writable session directory when the PHP default
// session path is unavailable in the current environment.
$configuredSessionPath = (string) ini_get('session.save_path');
$sessionPath = trim(explode(';', $configuredSessionPath)[count(explode(';', $configuredSessionPath)) - 1] ?? '');

if ($sessionPath === '' || !is_dir($sessionPath) || !is_writable($sessionPath)) {
    $localSessionPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';

    if (!is_dir($localSessionPath)) {
        mkdir($localSessionPath, 0777, true);
    }

    session_save_path($localSessionPath);
}

session_start();

require_once __DIR__ . '/functions.php';
