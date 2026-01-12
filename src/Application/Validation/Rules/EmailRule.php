<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class EmailRule extends AbstractRule
{
    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    protected function defaultMessage(): string
    {
        return 'Must be a valid email address';
    }
}