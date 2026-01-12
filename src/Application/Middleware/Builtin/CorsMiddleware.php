<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Middleware\Builtin;

use Closure;
use SimpleRouter\Application\Http\Request;
use SimpleRouter\Application\Http\Response;
use SimpleRouter\Domain\Contracts\Middleware;

/**
 * CORS (Cross-Origin Resource Sharing) Middleware
 * 
 * Handles CORS headers for cross-origin requests.
 * Supports preflight OPTIONS requests and configurable origins, methods, and headers.
 * 
 * @example
 * // Allow all origins
 * $router->get('/api/users', $handler)
 *     ->withMiddleware([new CorsMiddleware()]);
 * 
 * // Custom configuration
 * $router->group([
 *     'middleware' => [
 *         new CorsMiddleware(
 *             allowedOrigins: ['https://example.com', 'https://app.example.com'],
 *             allowedMethods: ['GET', 'POST', 'PUT', 'DELETE'],
 *             allowedHeaders: ['Content-Type', 'Authorization', 'X-API-Key'],
 *             allowCredentials: true,
 *             maxAge: 86400
 *         )
 *     ]
 * ], function($router) {
 *     // Your routes here
 * });
 */
final class CorsMiddleware implements Middleware
{
    /**
     * @param array<string> $allowedOrigins List of allowed origins or ['*'] for all
     * @param array<string> $allowedMethods List of allowed HTTP methods
     * @param array<string> $allowedHeaders List of allowed request headers
     * @param bool $allowCredentials Whether to allow credentials (cookies, auth headers)
     * @param int $maxAge How long the preflight response can be cached (in seconds)
     * @param array<string> $exposedHeaders Headers to expose to the client
     */
    public function __construct(
        private array $allowedOrigins = ['*'],
        private array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        private array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With', 'X-API-Key', 'Accept'],
        private bool $allowCredentials = false,
        private int $maxAge = 86400, // 24 hours
        private array $exposedHeaders = ['Content-Length', 'X-JSON']
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS request
        if ($request->method() === 'OPTIONS') {
            return $this->handlePreflightRequest($request);
        }

        // Process the actual request
        $response = $next($request);

        // Add CORS headers to the response
        return $this->addCorsHeaders($request, $response);
    }

    /**
     * Handle preflight OPTIONS request
     */
    private function handlePreflightRequest(Request $request): Response
    {
        $response = Response::noContent();

        // Get the origin from request
        $origin = $this->getAllowedOrigin($request);

        // Add preflight headers
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods))
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders))
            ->withHeader('Access-Control-Max-Age', (string) $this->maxAge);

        // Add credentials header if needed
        if ($this->allowCredentials && $origin !== '*') {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        // Add vary header
        if (!in_array('*', $this->allowedOrigins, true)) {
            $response = $response->withHeader('Vary', 'Origin');
        }

        return $response;
    }

    /**
     * Add CORS headers to the actual response
     */
    private function addCorsHeaders(Request $request, Response $response): Response
    {
        $origin = $this->getAllowedOrigin($request);

        $response = $response
            ->withHeader('Access-Control-Allow-Origin', $origin);

        // Add credentials header if needed
        if ($this->allowCredentials && $origin !== '*') {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        // Add exposed headers
        if (!empty($this->exposedHeaders)) {
            $response = $response->withHeader(
                'Access-Control-Expose-Headers',
                implode(', ', $this->exposedHeaders)
            );
        }

        // Add vary header
        if (!in_array('*', $this->allowedOrigins, true)) {
            $response = $response->withHeader('Vary', 'Origin');
        }

        return $response;
    }

    /**
     * Get the allowed origin for the request
     */
    private function getAllowedOrigin(Request $request): string
    {
        // If all origins are allowed
        if (in_array('*', $this->allowedOrigins, true)) {
            return '*';
        }

        // Get the origin from the request
        $requestOrigin = $request->header('Origin', '');

        // Check if the origin is in the allowed list
        if (in_array($requestOrigin, $this->allowedOrigins, true)) {
            return $requestOrigin;
        }

        // Check for wildcard patterns (e.g., "https://*.example.com")
        foreach ($this->allowedOrigins as $allowedOrigin) {
            if ($this->matchesWildcard($allowedOrigin, $requestOrigin)) {
                return $requestOrigin;
            }
        }

        // If no match, return the first allowed origin
        // This is safer than returning the request origin for non-matching cases
        return $this->allowedOrigins[0] ?? '*';
    }

    /**
     * Check if origin matches wildcard pattern
     */
    private function matchesWildcard(string $pattern, string $origin): bool
    {
        // Convert wildcard pattern to regex
        // Example: "https://*.example.com" -> "^https://[^/]+\.example\.com$"
        $regex = '/^' . str_replace(
            ['\*', '\.'],
            ['[^/]+', '\.'],
            preg_quote($pattern, '/')
        ) . '$/';

        return (bool) preg_match($regex, $origin);
    }

    /**
     * Create CORS middleware with common presets
     */
    public static function allowAll(): self
    {
        return new self(
            allowedOrigins: ['*'],
            allowedMethods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            allowedHeaders: ['*'],
            allowCredentials: false
        );
    }

    /**
     * Create CORS middleware for development (very permissive)
     */
    public static function development(): self
    {
        return new self(
            allowedOrigins: ['*'],
            allowedMethods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'],
            allowedHeaders: ['*'],
            allowCredentials: true,
            maxAge: 3600 // 1 hour
        );
    }

    /**
     * Create CORS middleware for production with specific origins
     * 
     * @param array<string> $origins
     */
    public static function production(array $origins): self
    {
        return new self(
            allowedOrigins: $origins,
            allowedMethods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With'],
            allowCredentials: true,
            maxAge: 86400 // 24 hours
        );
    }
}
