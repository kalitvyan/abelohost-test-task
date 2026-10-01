<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

// Warnings/notices become exceptions
set_error_handler(static function (
    int $severity,
    string $message,
    string $file,
    int $line,
): bool {
    if ((error_reporting() & $severity) === 0) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

return new App\Container(dirname(__DIR__));
