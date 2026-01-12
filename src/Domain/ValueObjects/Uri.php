<?php

declare(strict_types=1);

namespace SimpleRouter\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Represents a URI as a value object
 */
final readonly class Uri
{
    private string $path;

    private function __construct(string $uri)
    {
        $this->path = $this->normalize($uri);
    }

    public static function from(string $uri): self
    {
        return new self($uri);
    }

    public function value(): string
    {
        return $this->path;
    }

    public function equals(Uri $other): bool
    {
        return $this->path === $other->path;
    }

    public function matches(string $pattern): bool
    {
        $pattern = $this->convertToRegex($pattern);
        return (bool) preg_match($pattern, $this->path);
    }

    public function extractParameters(string $pattern): array
    {
        $pattern = $this->convertToRegex($pattern);
        preg_match($pattern, $this->path, $matches);
        
        array_shift($matches); // Remove full match
        
        return $matches;
    }

    private function normalize(string $uri): string
    {
        // Remove query string
        $uri = strtok($uri, '?') ?: $uri;
        
        // Ensure starts with /
        if (!str_starts_with($uri, '/')) {
            $uri = '/' . $uri;
        }

        // Remove trailing slash except for root
        if (strlen($uri) > 1 && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }

    private function convertToRegex(string $pattern): string
    {
        // Replace {param:type} with appropriate regex
        $pattern = preg_replace_callback(
            '/\{([^:}]+)(?::([^}]+))?\}/',
            function ($matches) {
                $paramName = $matches[1];
                $type = $matches[2] ?? 'any';

                return match ($type) {
                    'number', 'int' => '(\d+)',
                    'letter', 'alpha' => '([a-zA-Z]+)',
                    'alphanumeric' => '([a-zA-Z0-9]+)',
                    'uuid' => '([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})',
                    'slug' => '([a-z0-9\-]+)',
                    'any' => '([^/]+)',
                    default => throw new InvalidArgumentException("Invalid parameter type: {$type}")
                };
            },
            $pattern
        );

        return '@^' . $pattern . '$@';
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
