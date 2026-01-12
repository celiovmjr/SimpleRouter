<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Exceptions;

use Exception;
use SimpleRouter\Application\Validation\ValidationResult;

/**
 * Exception thrown when validation fails
 */
final class ValidationException extends Exception
{
    public function __construct(
        private readonly ValidationResult $validationResult,
        string $message = 'Validation failed',
        int $code = 422
    ) {
        parent::__construct($message, $code);
    }

    public function errors(): array
    {
        return $this->validationResult->errors();
    }

    public function validationResult(): ValidationResult
    {
        return $this->validationResult;
    }
}

