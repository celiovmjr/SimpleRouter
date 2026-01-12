<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class MinLengthRule extends AbstractRule
{
    public function __construct(
        private readonly int $min
    ) {}

    public function validate(mixed $value): bool
    {
        if (!\is_string($value) && !\is_array($value)) {
            return false;
        }

        $length = \is_string($value) ? mb_strlen($value) : count($value);
        return $length >= $this->min;
    }

    protected function defaultMessage(): string
    {
        return "Must be at least {$this->min} characters";
    }
}