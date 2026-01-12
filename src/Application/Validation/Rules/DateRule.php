<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class DateRule extends AbstractRule
{
    public function __construct(
        private readonly string $format = 'Y-m-d'
    ) {}

    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $date = \DateTime::createFromFormat($this->format, $value);
        return $date && $date->format($this->format) === $value;
    }

    protected function defaultMessage(): string
    {
        return "Must be a valid date in format {$this->format}";
    }
}
