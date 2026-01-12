<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Exceptions;

use Exception;

/**
 * Exception thrown when a route is not found
 */
final class RouteNotFoundException extends Exception
{
    public function __construct(
        string $uri,
        string $method
    ) {
        parent::__construct(
            "Route not found: {$method} {$uri}",
            404
        );
    }
}