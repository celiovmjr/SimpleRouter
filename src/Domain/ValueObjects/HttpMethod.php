<?php

declare(strict_types=1);

namespace SimpleRouter\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Represents an HTTP method as a value object
 */
final readonly class HttpMethod
{
    private const ALLOWED_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];

    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    public static function from(string $method): self
    {
        return new self(strtoupper($method));
    }

    public static function get(): self
    {
        return new self('GET');
    }

    public static function post(): self
    {
        return new self('POST');
    }

    public static function put(): self
    {
        return new self('PUT');
    }

    public static function patch(): self
    {
        return new self('PATCH');
    }

    public static function delete(): self
    {
        return new self('DELETE');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(HttpMethod $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(): void
    {
        if (!in_array($this->value, self::ALLOWED_METHODS, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid HTTP method: %s', $this->value)
            );
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
