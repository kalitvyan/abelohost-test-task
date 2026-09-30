<?php

declare(strict_types=1);

namespace App\Http\Routing;

use App\Http\Exception\MethodNotAllowedException;
use App\Http\Exception\NotFoundException;
use LogicException;

final class Router
{
    /** @var list<Route> */
    private array $routes = [];

    /** @var array<string, Route> */
    private array $named = [];

    /**
     * @param array{class-string, non-empty-string} $handler
     */
    public function get(string $pattern, array $handler, string $name): void
    {
        $this->add('GET', $pattern, $handler, $name);
    }

    /**
     * @param array{class-string, non-empty-string} $handler
     */
    public function add(string $method, string $pattern, array $handler, string $name): void
    {
        if (isset($this->named[$name])) {
            throw new LogicException("Route '{$name}' is already defined");
        }

        $route = new Route(strtoupper($method), $pattern, $handler, $name);

        $this->routes[] = $route;
        $this->named[$name] = $route;
    }

    public function match(string $method, string $path): RouteMatch
    {
        $method = $method === 'HEAD' ? 'GET' : $method;
        $allowed = [];

        foreach ($this->routes as $route) {
            $params = $route->match($path);

            if ($params === null) {
                continue;
            }

            if ($route->method === $method) {
                return new RouteMatch($route, $params);
            }

            $allowed[] = $route->method;
        }

        if ($allowed === []) {
            throw new NotFoundException();
        }

        if (in_array('GET', $allowed, true)) {
            $allowed[] = 'HEAD';
        }

        throw new MethodNotAllowedException(array_values(array_unique($allowed)));
    }

    /**
     * @param array<string, string|int> $params
     * @param array<string, mixed>      $query
     */
    public function url(string $name, array $params = [], array $query = []): string
    {
        $route = $this->named[$name] ?? throw new LogicException("Route '{$name}' is not defined");
        $query = array_filter($query, static fn (mixed $v): bool => $v !== null && $v !== '');

        return $route->path($params) . ($query === [] ? '' : '?' . http_build_query($query));
    }
}
