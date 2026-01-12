<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Middleware\Builtin;

use Closure;
use SimpleRouter\Application\Http\Request;
use SimpleRouter\Application\Http\Response;
use SimpleRouter\Domain\Contracts\Middleware;

/**
 * Logging Middleware
 * 
 * Logs HTTP requests and responses with timing and memory usage information.
 * Supports different log levels and custom log handlers.
 * 
 * @example
 * // Basic usage (logs to error_log)
 * $router->get('/api/users', $handler)
 *     ->withMiddleware([new LoggingMiddleware()]);
 * 
 * // Log to file
 * $router->group([
 *     'middleware' => [
 *         new LoggingMiddleware(
 *             logFile: '/var/log/api.log',
 *             logLevel: 'info'
 *         )
 *     ]
 * ], function($router) {
 *     // API routes
 * });
 * 
 * // Custom log handler
 * $router->get('/api/data', $handler)
 *     ->withMiddleware([
 *         new LoggingMiddleware(
 *             logHandler: function($message, $context) {
 *                 // Send to monitoring service
 *                 MonitoringService::log($message, $context);
 *             }
 *         )
 *     ]);
 */
final class LoggingMiddleware implements Middleware
{
    /**
     * @param string|null $logFile Path to log file (null = use error_log)
     * @param string $logLevel Minimum log level (debug, info, warning, error)
     * @param bool $logRequestBody Whether to log request body
     * @param bool $logResponseBody Whether to log response body
     * @param int $maxBodyLength Maximum body length to log
     * @param callable|null $logHandler Custom log handler function(string $message, array $context)
     * @param array<string> $excludePaths Paths to exclude from logging
     * @param array<string> $sensitiveHeaders Headers to mask in logs
     */
    public function __construct(
        private ?string $logFile = null,
        private string $logLevel = 'info',
        private bool $logRequestBody = false,
        private bool $logResponseBody = false,
        private int $maxBodyLength = 1000,
        private $logHandler = null,
        private array $excludePaths = [],
        private array $sensitiveHeaders = ['Authorization', 'X-API-Key', 'Cookie', 'Set-Cookie']
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Check if path should be excluded
        if ($this->shouldExclude($request->path())) {
            return $next($request);
        }

        // Start timing and memory tracking
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Log request
        $this->logRequest($request);

        // Process request
        $response = $next($request);

        // Calculate metrics
        $duration = round((microtime(true) - $startTime) * 1000, 2); // ms
        $memory = round((memory_get_usage() - $startMemory) / 1024 / 1024, 2); // MB

        // Log response
        $this->logResponse($request, $response, $duration, $memory);

        // Add timing headers to response
        return $response
            ->withHeader('X-Response-Time', "{$duration}ms")
            ->withHeader('X-Memory-Usage', "{$memory}MB");
    }

    /**
     * Check if path should be excluded from logging
     */
    private function shouldExclude(string $path): bool
    {
        foreach ($this->excludePaths as $excludePath) {
            if (str_starts_with($path, $excludePath)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Log incoming request
     */
    private function logRequest(Request $request): void
    {
        $context = [
            'type' => 'request',
            'method' => $request->method(),
            'path' => $request->path(),
            'query' => $request->query(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $this->maskSensitiveHeaders($request->headers()),
        ];

        // Add request body if enabled
        if ($this->logRequestBody && in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $body = json_encode($request->all());
            $context['body'] = $this->truncateBody($body);
        }

        $message = sprintf(
            '[%s] %s %s - IP: %s',
            date('Y-m-d H:i:s'),
            $request->method(),
            $request->path(),
            $request->ip()
        );

        $this->log('info', $message, $context);
    }

    /**
     * Log outgoing response
     */
    private function logResponse(
        Request $request,
        Response $response,
        float $duration,
        float $memory
    ): void {
        $context = [
            'type' => 'response',
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->status(),
            'duration_ms' => $duration,
            'memory_mb' => $memory,
            'headers' => $this->maskSensitiveHeaders($response->headers()),
        ];

        // Add response body if enabled
        if ($this->logResponseBody) {
            $context['body'] = $this->truncateBody($response->content());
        }

        // Determine log level based on status code
        $level = $this->getLogLevelForStatus($response->status());

        $message = sprintf(
            '[%s] %s %s - Status: %d - Duration: %sms - Memory: %sMB',
            date('Y-m-d H:i:s'),
            $request->method(),
            $request->path(),
            $response->status(),
            $duration,
            $memory
        );

        $this->log($level, $message, $context);
    }

    /**
     * Mask sensitive headers
     */
    private function maskSensitiveHeaders(array $headers): array
    {
        $masked = [];
        
        foreach ($headers as $key => $value) {
            if (in_array($key, $this->sensitiveHeaders, true)) {
                $masked[$key] = '***MASKED***';
            } else {
                $masked[$key] = $value;
            }
        }

        return $masked;
    }

    /**
     * Truncate body to maximum length
     */
    private function truncateBody(string $body): string
    {
        if (strlen($body) <= $this->maxBodyLength) {
            return $body;
        }

        return substr($body, 0, $this->maxBodyLength) . '... (truncated)';
    }

    /**
     * Get appropriate log level based on HTTP status code
     */
    private function getLogLevelForStatus(int $status): string
    {
        return match (true) {
            $status >= 500 => 'error',
            $status >= 400 => 'warning',
            default => 'info'
        };
    }

    /**
     * Write log message
     */
    private function log(string $level, string $message, array $context = []): void
    {
        // Check if we should log this level
        if (!$this->shouldLog($level)) {
            return;
        }

        // Use custom handler if provided
        if ($this->logHandler !== null) {
            ($this->logHandler)($message, $context);
            return;
        }

        // Format log message
        $formattedMessage = $this->formatLogMessage($level, $message, $context);

        // Write to file or error_log
        if ($this->logFile !== null) {
            $this->writeToFile($formattedMessage);
        } else {
            error_log($formattedMessage);
        }
    }

    /**
     * Check if we should log this level
     */
    private function shouldLog(string $level): bool
    {
        $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];
        
        $currentLevel = $levels[$this->logLevel] ?? 1;
        $messageLevel = $levels[$level] ?? 1;

        return $messageLevel >= $currentLevel;
    }

    /**
     * Format log message
     */
    private function formatLogMessage(string $level, string $message, array $context): string
    {
        $formatted = sprintf('[%s] %s', strtoupper($level), $message);

        // Add context as JSON if present
        if (!empty($context)) {
            $formatted .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $formatted;
    }

    /**
     * Write to log file
     */
    private function writeToFile(string $message): void
    {
        if ($this->logFile === null) {
            return;
        }

        // Ensure directory exists
        $directory = dirname($this->logFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Write to file
        file_put_contents(
            $this->logFile,
            $message . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Create logging middleware with debug level
     */
    public static function debug(?string $logFile = null): self
    {
        return new self(
            logFile: $logFile,
            logLevel: 'debug',
            logRequestBody: true,
            logResponseBody: true
        );
    }

    /**
     * Create logging middleware for production (minimal logging)
     */
    public static function production(string $logFile): self
    {
        return new self(
            logFile: $logFile,
            logLevel: 'warning',
            logRequestBody: false,
            logResponseBody: false,
            excludePaths: ['/health', '/ping', '/metrics']
        );
    }

    /**
     * Create logging middleware for API monitoring
     */
    public static function api(string $logFile): self
    {
        return new self(
            logFile: $logFile,
            logLevel: 'info',
            logRequestBody: true,
            logResponseBody: false,
            maxBodyLength: 500
        );
    }

    /**
     * Create logging middleware with custom handler
     */
    public static function custom(callable $handler): self
    {
        return new self(
            logHandler: $handler,
            logLevel: 'info'
        );
    }
}
