<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class MaxLengthRule extends AbstractRule
{
    public function __construct(
        private readonly int $max
    ) {}

    public function validate(mixed $value): bool
    {
        if (!is_string($value) && !is_array($value)) {
            return false;
        }

        $length = is_string($value) ? mb_strlen($value) : count($value);
        return $length <= $this->max;
    }

    protected function defaultMessage(): string
    {
        return "Must be at most {$this->max} characters";
    }
}
