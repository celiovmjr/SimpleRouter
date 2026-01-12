<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class IntegerRule extends AbstractRule
{
    public function validate(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function defaultMessage(): string
    {
        return 'Must be an integer';
    }
}