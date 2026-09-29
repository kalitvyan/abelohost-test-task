<?php

declare(strict_types=1);

namespace App;

use App\Database\Database;

final class Container
{
    private ?Database $database = null;

    public function __construct(private readonly string $basePath)
    {
    }

    public function path(string $relative = ''): string
    {
        return rtrim($this->basePath . '/' . ltrim($relative, '/'), '/');
    }

    public function database(): Database
    {
        /** @var array{host: string, port: int, database: string, username: string, password: string} $config */
        $config = $this->config('database');

        return $this->database ??= Database::connect($config);
    }

    /**
     * @return array<string, mixed>
     */
    public function config(string $name): array
    {
        return require $this->path("config/{$name}.php");
    }
}
