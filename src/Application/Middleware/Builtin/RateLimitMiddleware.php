<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Middleware\Builtin;

use Closure;
use SimpleRouter\Application\Http\Request;
use SimpleRouter\Application\Http\Response;
use SimpleRouter\Domain\Contracts\Middleware;

/**
 * Rate Limit Middleware
 * 
 * Limits the number of requests a client can make in a given time window.
 * Uses in-memory storage by default (not suitable for production with multiple servers).
 * For production, extend this class and override the storage methods to use Redis/Memcached.
 * 
 * @example
 * // Basic usage: 60 requests per minute
 * $router->get('/api/users', $handler)
 *     ->withMiddleware([new RateLimitMiddleware(60, 1)]);
 * 
 * // 100 requests per hour
 * $router->group([
 *     'middleware' => [new RateLimitMiddleware(100, 60)]
 * ], function($router) {
 *     // API routes
 * });
 * 
 * // Custom identifier (per user instead of per IP)
 * $router->get('/api/data', $handler)
 *     ->withMiddleware([
 *         new RateLimitMiddleware(
 *             maxRequests: 1000,
 *             perMinutes: 60,
 *             keyGenerator: fn($request) => $request->input('user_id')
 *         )
 *     ]);
 */
final class RateLimitMiddleware implements Middleware
{
    /** @var array<string, array<int>> */
    private static array $requests = [];

    /**
     * @param int $maxRequests Maximum number of requests allowed
     * @param int $perMinutes Time window in minutes
     * @param string $keyPrefix Prefix for the rate limit key
     * @param callable|null $keyGenerator Custom key generator function
     */
    public function __construct(
        private int $maxRequests = 60,
        private int $perMinutes = 1,
        private string $keyPrefix = 'rate_limit',
        private $keyGenerator = null
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->getKey($request);
        $now = time();
        $window = $this->perMinutes * 60;

        // Clean old requests
        $this->cleanOldRequests($key, $now, $window);

        // Get current request count
        $requestCount = $this->getRequestCount($key);

        // Calculate remaining requests and reset time
        $remaining = max(0, $this->maxRequests - $requestCount);
        $resetTime = $now + $window;

        // Check if rate limit exceeded
        if ($requestCount >= $this->maxRequests) {
            return $this->rateLimitExceeded($remaining, $resetTime, $window);
        }

        // Add current request
        $this->addRequest($key, $now);

        // Process request and add rate limit headers
        $response = $next($request);

        return $this->addRateLimitHeaders($response, $remaining - 1, $resetTime);
    }

    /**
     * Get the rate limit key for the request
     */
    private function getKey(Request $request): string
    {
        if ($this->keyGenerator !== null) {
            $identifier = ($this->keyGenerator)($request);
        } else {
            // Default: use IP address and path
            $identifier = $request->ip() . ':' . $request->path();
        }

        return $this->keyPrefix . ':' . md5($identifier);
    }

    /**
     * Clean old requests outside the time window
     */
    private function cleanOldRequests(string $key, int $now, int $window): void
    {
        if (!isset(self::$requests[$key])) {
            self::$requests[$key] = [];
            return;
        }

        self::$requests[$key] = array_filter(
            self::$requests[$key],
            fn(int $timestamp) => $timestamp > ($now - $window)
        );
    }

    /**
     * Get the current request count
     */
    private function getRequestCount(string $key): int
    {
        return count(self::$requests[$key] ?? []);
    }

    /**
     * Add a request to the counter
     */
    private function addRequest(string $key, int $timestamp): void
    {
        if (!isset(self::$requests[$key])) {
            self::$requests[$key] = [];
        }

        self::$requests[$key][] = $timestamp;
    }

    /**
     * Return rate limit exceeded response
     */
    private function rateLimitExceeded(int $remaining, int $resetTime, int $retryAfter): Response
    {
        return Response::json([
            'error' => 'Too Many Requests',
            'message' => sprintf(
                'Rate limit exceeded. Maximum %d requests per %d minute(s)',
                $this->maxRequests,
                $this->perMinutes
            ),
            'retry_after' => $retryAfter,
            'reset_at' => date('Y-m-d H:i:s', $resetTime)
        ], 429)
            ->withHeader('X-RateLimit-Limit', (string) $this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', '0')
            ->withHeader('X-RateLimit-Reset', (string) $resetTime)
            ->withHeader('Retry-After', (string) $retryAfter);
    }

    /**
     * Add rate limit headers to response
     */
    private function addRateLimitHeaders(Response $response, int $remaining, int $resetTime): Response
    {
        return $response
            ->withHeader('X-RateLimit-Limit', (string) $this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string) max(0, $remaining))
            ->withHeader('X-RateLimit-Reset', (string) $resetTime);
    }

    /**
     * Clear all rate limit data (useful for testing)
     */
    public static function clear(): void
    {
        self::$requests = [];
    }

    /**
     * Create rate limit for API endpoints (100 requests per minute)
     */
    public static function api(): self
    {
        return new self(
            maxRequests: 100,
            perMinutes: 1
        );
    }

    /**
     * Create rate limit for authentication endpoints (5 requests per minute)
     */
    public static function auth(): self
    {
        return new self(
            maxRequests: 5,
            perMinutes: 1,
            keyPrefix: 'auth_rate_limit'
        );
    }

    /**
     * Create rate limit for public endpoints (1000 requests per hour)
     */
    public static function public(): self
    {
        return new self(
            maxRequests: 1000,
            perMinutes: 60
        );
    }

    /**
     * Create rate limit per user (using user_id from request)
     */
    public static function perUser(int $maxRequests = 1000, int $perMinutes = 60): self
    {
        return new self(
            maxRequests: $maxRequests,
            perMinutes: $perMinutes,
            keyPrefix: 'user_rate_limit',
            keyGenerator: fn(Request $request) => 
                $request->input('user_id') ?? $request->ip()
        );
    }

    /**
     * Create rate limit per API key
     */
    public static function perApiKey(int $maxRequests = 10000, int $perMinutes = 60): self
    {
        return new self(
            maxRequests: $maxRequests,
            perMinutes: $perMinutes,
            keyPrefix: 'api_key_rate_limit',
            keyGenerator: fn(Request $request) => 
                $request->header('X-API-Key') ?? 
                $request->input('api_key') ?? 
                $request->ip()
        );
    }
}
