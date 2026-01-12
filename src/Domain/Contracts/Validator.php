<?php

declare(strict_types=1);

namespace SimpleRouter\Domain\Contracts;

use SimpleRouter\Application\Validation\ValidationResult;

/**
 * Contract for validators
 */
interface Validator
{
    public function validate(array $data, array $rules): ValidationResult;
}
