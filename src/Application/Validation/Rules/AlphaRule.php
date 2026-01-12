<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class AlphaRule extends AbstractRule
{
    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return (bool) preg_match('/^[a-zA-ZÀ-ÿ\s]+$/u', $value);
    }

    protected function defaultMessage(): string
    {
        return 'Must contain only letters';
    }
}