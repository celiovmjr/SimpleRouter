<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class InRule extends AbstractRule
{
    public function __construct(
        private readonly array $allowed
    ) {}

    public function validate(mixed $value): bool
    {
        return \in_array($value, $this->allowed, true);
    }

    protected function defaultMessage(): string
    {
        $values = implode(', ', $this->allowed);
        return "Must be one of: {$values}";
    }
}