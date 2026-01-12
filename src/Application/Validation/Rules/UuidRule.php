<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class UuidRule extends AbstractRule
{
    private string $version;

    public function __construct(string $version = 'v4')
    {
        $this->version = $version;
    }

    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $pattern = match ($this->version) {
            'v4' => '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            'v1' => '/^[0-9a-f]{8}-[0-9a-f]{4}-1[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            default => '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i'
        };

        return (bool) preg_match($pattern, $value);
    }

    protected function defaultMessage(): string
    {
        return "Must be a valid UUID {$this->version}";
    }
}