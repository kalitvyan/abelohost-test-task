<?php

declare(strict_types=1);

namespace App\Http;

use LogicException;

final class Request
{
    /**
     * @param array<string, mixed>  $query
     * @param array<string, string> $routeParams
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $query = [],
        private readonly array $routeParams = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

        return new self(
            method: strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            path: is_string($path) && $path !== '' ? $path : '/',
            query: $_GET,
        );
    }

    /**
     * @param array<string, string> $params
     */
    public function withRouteParams(array $params): self
    {
        return new self($this->method, $this->path, $this->query, $params);
    }

    public function route(string $key): string
    {
        return $this->routeParams[$key] ?? throw new LogicException("Route parameter '{$key}' is not defined");
    }

    public function query(string $key, ?string $default = null): ?string
    {
        $value = $this->query[$key] ?? null;

        return is_string($value) ? $value : $default;
    }

    public function queryInt(string $key, int $default, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        $value = filter_var($this->query[$key] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $min, 'max_range' => $max],
        ]);

        return $value === false ? $default : $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function queryAll(): array
    {
        return $this->query;
    }

    public function queryString(): string
    {
        return $this->query === [] ? '' : '?' . http_build_query($this->query);
    }
}
