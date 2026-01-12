<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Exceptions;

use Exception;

/**
 * Exception thrown when HTTP method is not allowed
 */
final class MethodNotAllowedException extends Exception
{
    public function __construct(
        string $method,
        array $allowedMethods = []
    ) {
        $allowed = implode(', ', $allowedMethods);
        parent::__construct(
            "Method {$method} not allowed. Allowed methods: {$allowed}",
            405
        );
    }
}
