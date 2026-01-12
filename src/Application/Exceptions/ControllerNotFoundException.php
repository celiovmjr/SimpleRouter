<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Exceptions;

use Exception;

/**
 * Exception thrown when a controller or method is not found
 */
final class ControllerNotFoundException extends Exception
{
    public function __construct(
        string $controller,
        ?string $method = null
    ) {
        $message = $method 
            ? "Controller method not found: {$controller}::{$method}"
            : "Controller not found: {$controller}";
            
        parent::__construct($message, 500);
    }
}