<?php

declare(strict_types=1);

namespace SimpleRouter\Domain\Entities;

use Closure;
use SimpleRouter\Domain\ValueObjects\HttpMethod;
use SimpleRouter\Domain\ValueObjects\Uri;

/**
 * Represents a route entity in the domain
 */
final class Route
{
    private array $middleware = [];
    private ?string $name = null;
    private array $parameters = [];

    public function __construct(
        private readonly HttpMethod $method,
        private readonly string $pattern,
        private readonly Closure|array $handler
    ) {}

    public static function create(
        HttpMethod $method,
        string $pattern,
        Closure|array $handler
    ): self {
        return new self($method, $pattern, $handler);
    }

    public function method(): HttpMethod
    {
        return $this->method;
    }

    public function pattern(): string
    {
        return $this->pattern;
    }

    public function handler(): Closure|array
    {
        return $this->handler;
    }

    public function withMiddleware(array|string $middleware): self
    {
        $middlewareArray = is_array($middleware) ? $middleware : [$middleware];
        $this->middleware = array_merge($this->middleware, $middlewareArray);
        return $this;
    }

    public function middleware(): array
    {
        return $this->middleware;
    }

    public function withName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function matches(Uri $uri, HttpMethod $method): bool
    {
        return $this->method->equals($method) && $uri->matches($this->pattern);
    }

    public function extractParameters(Uri $uri): array
    {
        if (empty($this->parameters)) {
            $this->extractParameterNames();
        }

        $values = $uri->extractParameters($this->pattern);
        
        return array_combine($this->parameters, $values) ?: [];
    }

    private function extractParameterNames(): void
    {
        preg_match_all('/\{([^:}]+)(?::[^}]+)?\}/', $this->pattern, $matches);
        $this->parameters = $matches[1] ?? [];
    }

    public function hasParameters(): bool
    {
        return str_contains($this->pattern, '{');
    }
}
