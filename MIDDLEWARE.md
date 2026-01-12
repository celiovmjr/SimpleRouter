# 🛡️ Built-in Middleware Documentation

SimpleRouter v2.0 includes three production-ready middleware classes located in `src/Application/Middleware/Builtin/`.

## 📦 Available Middleware

All built-in middleware are in: `src/Application/Middleware/Builtin/`

1. **CorsMiddleware.php** - Handle Cross-Origin Resource Sharing
2. **RateLimitMiddleware.php** - Limit request rates to prevent abuse  
3. **LoggingMiddleware.php** - Log requests and responses with metrics

## 🌐 CorsMiddleware

Handle CORS (Cross-Origin Resource Sharing) headers for cross-origin requests.

### Basic Usage

```php
use SimpleRouter\Application\Middleware\Builtin\CorsMiddleware;

// Allow all origins (development)
$router->get('/api/users', $handler)
    ->withMiddleware([CorsMiddleware::allowAll()]);

// Allow specific origins (production)
$router->group([
    'middleware' => [
        CorsMiddleware::production([
            'https://app.example.com',
            'https://admin.example.com'
        ])
    ]
], function($router) {
    // Your routes here
});
```

### Configuration Options

```php
new CorsMiddleware(
    allowedOrigins: ['*'],              // Origins to allow or ['*'] for all
    allowedMethods: ['GET', 'POST'],    // HTTP methods to allow
    allowedHeaders: ['Content-Type'],   // Headers to allow
    allowCredentials: false,            // Allow credentials (cookies, auth)
    maxAge: 86400,                     // Preflight cache time (seconds)
    exposedHeaders: ['X-Total-Count']   // Headers to expose to client
)
```

### Factory Methods

```php
// Development (very permissive)
CorsMiddleware::development();

// Production (specific origins)
CorsMiddleware::production(['https://example.com']);

// Allow all (any origin)
CorsMiddleware::allowAll();
```

### Features

✅ Handles preflight OPTIONS requests automatically
✅ Supports wildcard origins (e.g., `https://*.example.com`)
✅ Configurable methods, headers, and credentials
✅ Adds `Vary: Origin` header when needed
✅ Production-ready and RFC-compliant

### Example: Custom CORS Configuration

```php
$router->group([
    'prefix' => '/api',
    'middleware' => [
        new CorsMiddleware(
            allowedOrigins: [
                'https://app.example.com',
                'https://*.example.com'  // Wildcard subdomain
            ],
            allowedMethods: ['GET', 'POST', 'PUT', 'DELETE'],
            allowedHeaders: [
                'Content-Type',
                'Authorization',
                'X-Custom-Header'
            ],
            allowCredentials: true,
            maxAge: 7200, // 2 hours
            exposedHeaders: [
                'X-Total-Count',
                'X-Page-Number'
            ]
        )
    ]
], function($router) {
    // API routes
});
```

### Response Headers Added

```
Access-Control-Allow-Origin: https://app.example.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Allow-Credentials: true
Access-Control-Max-Age: 7200
Access-Control-Expose-Headers: X-Total-Count, X-Page-Number
Vary: Origin
```

## ⏱️ RateLimitMiddleware

Limit the number of requests a client can make in a given time window.

### Basic Usage

```php
use SimpleRouter\Application\Middleware\Builtin\RateLimitMiddleware;

// 60 requests per minute (default)
$router->get('/api/users', $handler)
    ->withMiddleware([new RateLimitMiddleware(60, 1)]);

// 100 requests per hour
$router->group([
    'middleware' => [new RateLimitMiddleware(100, 60)]
], function($router) {
    // API routes
});
```

### Configuration Options

```php
new RateLimitMiddleware(
    maxRequests: 60,        // Maximum requests allowed
    perMinutes: 1,          // Time window in minutes
    keyPrefix: 'rate_limit', // Storage key prefix
    keyGenerator: null      // Custom key generator function
)
```

### Factory Methods

```php
// API endpoints (100 requests per minute)
RateLimitMiddleware::api();

// Authentication endpoints (5 requests per minute)
RateLimitMiddleware::auth();

// Public endpoints (1000 requests per hour)
RateLimitMiddleware::public();

// Per user (using user_id from request)
RateLimitMiddleware::perUser(1000, 60);

// Per API key
RateLimitMiddleware::perApiKey(10000, 60);
```

### Features

✅ Automatic rate limiting with configurable windows
✅ Returns 429 status when limit exceeded
✅ Adds rate limit headers to all responses
✅ Supports custom key generators (per user, per API key, etc.)
✅ Thread-safe with proper cleanup

### Response Headers Added

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1704470400
```

### Example: Per-User Rate Limiting

```php
$router->group([
    'prefix' => '/api',
    'middleware' => [
        // Rate limit based on user_id from authenticated request
        RateLimitMiddleware::perUser(1000, 60)
    ]
], function($router) {
    $router->get('/dashboard', function(Request $request) {
        // User is identified by user_id in request
        // (typically set by auth middleware)
        return Response::json(['data' => '...']);
    });
});
```

### Example: Custom Key Generator

```php
$router->get('/api/data', $handler)
    ->withMiddleware([
        new RateLimitMiddleware(
            maxRequests: 500,
            perMinutes: 30,
            keyGenerator: fn($request) => 
                $request->header('X-Client-ID') ?? $request->ip()
        )
    ]);
```

### Rate Limit Exceeded Response

```json
{
    "error": "Too Many Requests",
    "message": "Rate limit exceeded. Maximum 100 requests per 1 minute(s)",
    "retry_after": 60,
    "reset_at": "2024-01-05 14:30:00"
}
```

### Production Notes

⚠️ **Important**: The default implementation uses in-memory storage. For production with multiple servers, extend this class and override the storage methods to use Redis or Memcached.

```php
class RedisRateLimitMiddleware extends RateLimitMiddleware
{
    private Redis $redis;
    
    // Override storage methods to use Redis
    // ...
}
```

## 📝 LoggingMiddleware

Log HTTP requests and responses with timing and memory usage information.

### Basic Usage

```php
use SimpleRouter\Application\Middleware\Builtin\LoggingMiddleware;

// Log to error_log (default)
$router->get('/api/users', $handler)
    ->withMiddleware([new LoggingMiddleware()]);

// Log to file
$router->group([
    'middleware' => [
        new LoggingMiddleware(logFile: '/var/log/api.log')
    ]
], function($router) {
    // API routes
});
```

### Configuration Options

```php
new LoggingMiddleware(
    logFile: null,              // Path to log file (null = error_log)
    logLevel: 'info',           // Minimum level: debug, info, warning, error
    logRequestBody: false,      // Log request body
    logResponseBody: false,     // Log response body
    maxBodyLength: 1000,        // Max body length to log
    logHandler: null,           // Custom log handler function
    excludePaths: [],           // Paths to exclude from logging
    sensitiveHeaders: [         // Headers to mask in logs
        'Authorization',
        'X-API-Key',
        'Cookie'
    ]
)
```

### Factory Methods

```php
// Debug mode (logs everything)
LoggingMiddleware::debug('/var/log/debug.log');

// Production mode (warnings and errors only)
LoggingMiddleware::production('/var/log/production.log');

// API monitoring (logs requests but not responses)
LoggingMiddleware::api('/var/log/api.log');

// Custom handler (send to monitoring service)
LoggingMiddleware::custom(function($message, $context) {
    MonitoringService::log($message, $context);
});
```

### Features

✅ Logs request and response with timing
✅ Tracks memory usage
✅ Masks sensitive headers (Authorization, API keys, etc.)
✅ Configurable log levels
✅ Custom log handlers for external services
✅ Adds `X-Response-Time` and `X-Memory-Usage` headers
✅ Excludes health check endpoints from logs

### Log Format

```
[INFO] [2024-01-05 14:30:15] GET /api/users - IP: 127.0.0.1
[INFO] [2024-01-05 14:30:15] GET /api/users - Status: 200 - Duration: 45.23ms - Memory: 2.15MB
```

### JSON Context (when enabled)

```json
{
    "type": "request",
    "method": "GET",
    "path": "/api/users",
    "query": {"page": "1"},
    "ip": "127.0.0.1",
    "user_agent": "Mozilla/5.0...",
    "headers": {
        "Authorization": "***MASKED***",
        "Content-Type": "application/json"
    }
}
```

### Example: Debug Logging

```php
$router->group([
    'prefix' => '/api',
    'middleware' => [
        LoggingMiddleware::debug(__DIR__ . '/logs/debug.log')
    ]
], function($router) {
    // Logs everything including request/response bodies
});
```

### Example: Production Logging

```php
$router->group([
    'prefix' => '/api',
    'middleware' => [
        LoggingMiddleware::production(__DIR__ . '/logs/production.log')
    ]
], function($router) {
    // Only logs warnings and errors
    // Excludes /health, /ping, /metrics
});
```

### Example: Custom Log Handler

```php
$router->group([
    'middleware' => [
        LoggingMiddleware::custom(function($message, $context) {
            // Send to monitoring service
            Sentry::captureMessage($message, $context);
            
            // Or send to logging service
            LogService::send([
                'message' => $message,
                'context' => $context,
                'environment' => 'production'
            ]);
        })
    ]
], function($router) {
    // Your routes
});
```

### Response Headers Added

```
X-Response-Time: 45.23ms
X-Memory-Usage: 2.15MB
```

## 🔗 Combining Middleware

Use all three middleware together for a production-ready API:

```php
use SimpleRouter\Application\Middleware\Builtin\{
    CorsMiddleware,
    RateLimitMiddleware,
    LoggingMiddleware
};

$router->group([
    'prefix' => '/api/v1',
    'middleware' => [
        // CORS: Production configuration
        CorsMiddleware::production([
            'https://app.example.com',
            'https://admin.example.com'
        ]),
        
        // Rate Limit: API key based
        RateLimitMiddleware::perApiKey(10000, 60),
        
        // Logging: Production mode
        LoggingMiddleware::production(__DIR__ . '/logs/api.log')
    ]
], function($router) {
    // Your API routes here
});
```

## 📊 Complete Example

See `examples/middleware-demo.php` for a complete working example with:

- ✅ Basic API with all middleware
- ✅ Production API with custom CORS
- ✅ Authentication endpoints with strict rate limiting
- ✅ Per-user rate limiting
- ✅ Custom CORS configuration
- ✅ Custom logging handler
- ✅ Interactive rate limit testing page

### Run the Example

```bash
php -S localhost:8000 examples/middleware-demo.php
```

Visit:
- `http://localhost:8000/api/v1/users` - Basic API
- `http://localhost:8000/auth/login` - Auth endpoint
- `http://localhost:8000/test/rate-limit` - Test rate limiting

## 🎓 Best Practices

### 1. CORS in Production

```php
// ❌ Don't allow all origins in production
CorsMiddleware::allowAll();

// ✅ Specify exact origins
CorsMiddleware::production([
    'https://app.example.com',
    'https://admin.example.com'
]);
```

### 2. Rate Limiting

```php
// ❌ Too permissive for auth endpoints
RateLimitMiddleware::api(); // 100/min

// ✅ Strict for authentication
RateLimitMiddleware::auth(); // 5/min
```

### 3. Logging

```php
// ❌ Don't log everything in production
LoggingMiddleware::debug('/var/log/api.log');

// ✅ Only warnings and errors
LoggingMiddleware::production('/var/log/api.log');
```

### 4. Middleware Order

Order matters! Apply middleware in this sequence:

1. **CORS** - Handle preflight requests first
2. **Rate Limiting** - Block excessive requests early
3. **Logging** - Log what actually gets processed
4. **Authentication** - After logging for security audits

```php
$router->group([
    'middleware' => [
        CorsMiddleware::production($origins),  // 1. CORS
        RateLimitMiddleware::api(),           // 2. Rate limit
        LoggingMiddleware::api($logFile),     // 3. Logging
        JwtAuthMiddleware::class              // 4. Auth
    ]
], function($router) {
    // Protected API routes
});
```

## 🚀 Performance Tips

1. **Use Redis for rate limiting** in production with multiple servers
2. **Exclude health checks** from logging
3. **Limit body logging** in production (use `maxBodyLength`)
4. **Cache CORS headers** by setting a high `maxAge`
5. **Use log rotation** for log files

## 📚 See Also

- [Middleware System](../README.md#middleware)
- [Complete Example](../examples/middleware-demo.php)
- [Testing Guide](../TESTING.md)

---

**Ready-to-use, production-tested middleware! 🛡️**
