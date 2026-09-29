<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class Env
{
    public static function get(string $key, ?string $default = null): string
    {
        $value = getenv($key);

        if ($value === false) {
            return $default ?? throw new RuntimeException("Environment variable {$key} is not set");
        }

        return $value;
    }

    public static function int(string $key, ?int $default = null): int
    {
        $value = filter_var(self::get($key, $default === null ? null : (string) $default), FILTER_VALIDATE_INT);

        return $value === false ? throw new RuntimeException("Environment variable {$key} must be int") : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return filter_var(self::get($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOL);
    }
}
