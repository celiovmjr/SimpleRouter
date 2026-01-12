<?php

declare(strict_types=1);

namespace SimpleRouter\Domain\Entities;

use SimpleRouter\Domain\ValueObjects\HttpMethod;
use SimpleRouter\Domain\ValueObjects\Uri;

/**
 * Collection of routes
 */
final class RouteCollection
{
    /** @var Route[] */
    private array $routes = [];

    /** @var array<string, string> */
    private array $namedRoutes = [];

    public function add(Route $route): void
    {
        $this->routes[] = $route;

        if ($name = $route->name()) {
            $this->namedRoutes[$name] = $route->pattern();
        }
    }

    public function match(Uri $uri, HttpMethod $method): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($uri, $method)) {
                return $route;
            }
        }

        return null;
    }

    public function findByName(string $name): ?string
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * @return Route[]
     */
    public function all(): array
    {
        return $this->routes;
    }

    public function count(): int
    {
        return count($this->routes);
    }
}
