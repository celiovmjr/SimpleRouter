<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class RegexRule extends AbstractRule
{
    public function __construct(
        private readonly string $pattern
    ) {}

    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return (bool) preg_match($this->pattern, $value);
    }

    protected function defaultMessage(): string
    {
        return 'Invalid format';
    }
}
