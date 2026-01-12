<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class UrlRule extends AbstractRule
{
    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return (bool) filter_var($value, FILTER_VALIDATE_URL);
    }

    protected function defaultMessage(): string
    {
        return 'Must be a valid URL';
    }
}