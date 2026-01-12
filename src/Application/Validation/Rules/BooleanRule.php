<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class BooleanRule extends AbstractRule
{
    public function validate(mixed $value): bool
    {
        return is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false'], true);
    }

    protected function defaultMessage(): string
    {
        return 'Must be a boolean value';
    }
}
