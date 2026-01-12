<?php

declare(strict_types=1);

namespace SimpleRouter\Domain\Contracts;

/**
 * Contract for validation rules
 */
interface ValidationRule
{
    public function validate(mixed $value): bool;
    
    public function message(): string;
}
