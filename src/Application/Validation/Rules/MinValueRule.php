<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class MinValueRule extends AbstractRule
{
    public function __construct(
        private readonly int|float $min
    ) {}

    public function validate(mixed $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        return (float) $value >= $this->min;
    }

    protected function defaultMessage(): string
    {
        return "Must be at least {$this->min}";
    }
}