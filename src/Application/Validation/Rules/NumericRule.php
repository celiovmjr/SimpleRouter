<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class NumericRule extends AbstractRule
{
    public function validate(mixed $value): bool
    {
        return is_numeric($value);
    }

    protected function defaultMessage(): string
    {
        return 'Must be a number';
    }
}