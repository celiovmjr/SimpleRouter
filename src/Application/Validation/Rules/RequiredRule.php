<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class RequiredRule extends AbstractRule
{
    public function validate(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        
        return $value !== null && $value !== [];
    }

    protected function defaultMessage(): string
    {
        return 'This field is required';
    }
}