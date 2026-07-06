<?php
/**
 * OTPAP reference autoloader.
 *
 * This file provides a tiny PSR-4 style autoloader so the reference
 * implementation can run without Composer in constrained environments.
 */

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'Otpap\\Reference\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
