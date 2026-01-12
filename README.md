# 🚀 SimpleRouter

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-100%2B%20passing-brightgreen.svg)]()
[![Code Coverage](https://img.shields.io/badge/coverage-95%25-brightgreen.svg)]()

A lightweight, enterprise-grade PHP router with **Domain-Driven Design (DDD)** architecture, featuring powerful validation, middleware pipeline, and zero dependencies.

```php
// It's this simple
$router = new Router();

$router->post('/users', function(Request $request) {
    $validated = $request->validated([
        'name' => 'required|min:3|max:50',
        'email' => 'required|email',
        'age' => 'required|integer|min:18'
    ]);
    
    return Response::json($validated, 201);
});

$router->run();
```

## ✨ Features

- 🎯 **Clean Architecture** - Domain-Driven Design (DDD) with clear separation of concerns
- ⚡ **Zero Dependencies** - Pure PHP 8.2+, no external packages required
- 🛡️ **Type Safety** - Full type hints with strict types enabled
- 🔒 **Built-in Validation** - 17+ validation rules with custom messages
- 🔄 **Middleware Pipeline** - Powerful middleware system with built-in CORS, Rate Limiting, and Logging
- 🎨 **Flexible Routing** - RESTful routes, route groups, named routes, and multiple HTTP methods
- 📝 **Smart Parameters** - Type-safe route parameters (int, uuid, slug, alpha, etc.)
- 🧪 **100+ Tests** - Comprehensive test suite with PHPUnit
- 📖 **Rich Documentation** - Complete examples and API reference

## 📦 Installation

```bash
composer require celiovmjr/simplerouter
```

Or download the latest release and include via autoloader:

```php
require_once 'vendor/autoload.php';
```

## 🚀 Quick Start

### Basic Route

```php
use SimpleRouter\Application\Router;
use SimpleRouter\Application\Http\{Request, Response};

$router = new Router();

$router->get('/hello/{name}', function(Request $request) {
    $name = $request->input('name');
    return Response::json(['message' => "Hello, {$name}!"]);
});

$router->run();
```

### With Validation

```php
$router->post('/register', function(Request $request) {
    $validated = $request->validated([
        'name' => 'required|alpha|min:3|max:50',
        'email' => 'required|email|onError("Please provide a valid email")',
        'password' => 'required|min:8',
        'age' => 'required|integer|min:18|onError("You must be 18 or older")'
    ]);
    
    // Create user...
    
    return Response::json([
        'message' => 'User registered successfully',
        'user' => $validated
    ], 201);
});
```

### With Middleware

```php
use SimpleRouter\Application\Middleware\Builtin\{
    CorsMiddleware,
    RateLimitMiddleware,
    LoggingMiddleware
};

$router->group([
    'prefix' => '/api',
    'middleware' => [
        CorsMiddleware::production(['https://app.example.com']),
        RateLimitMiddleware::api(),
        LoggingMiddleware::production(__DIR__ . '/logs/api.log')
    ]
], function($router) {
    $router->get('/users', [UserController::class, 'index']);
    $router->post('/users', [UserController::class, 'store']);
});
```

## 📚 Documentation

- **[Getting Started](docs/GETTING-STARTED.md)** - Installation and first steps
- **[Architecture](docs/ARCHITECTURE.md)** - DDD structure and design principles
- **[Routing Guide](docs/ROUTING.md)** - Complete routing documentation
- **[Validation Guide](docs/VALIDATION.md)** - Validation rules and usage
- **[Middleware Guide](docs/MIDDLEWARE.md)** - Built-in and custom middleware
- **[Testing Guide](docs/TESTING.md)** - Running and writing tests
- **[Examples](examples/)** - Code examples and demos

## 🛣️ Routing

```php
// HTTP Methods
$router->get('/users', $handler);
$router->post('/users', $handler);
$router->put('/users/{id}', $handler);
$router->delete('/users/{id}', $handler);

// Typed Parameters
$router->get('/users/{id:int}', $handler);
$router->get('/posts/{uuid:uuid}', $handler);
$router->get('/blog/{slug:slug}', $handler);

// Route Groups
$router->group(['prefix' => '/api/v1'], function($router) {
    $router->get('/users', $handler);
});

// Named Routes
$router->get('/dashboard', $handler)->withName('dashboard');
$url = $router->route('dashboard');
```

[→ Full Routing Documentation](docs/ROUTING.md)

## ✅ Validation

```php
$validated = $request->validated([
    'email' => 'required|email',
    'age' => 'required|integer|min:18|max:120',
    'username' => 'required|alphanumeric|min:3|max:20',
    'role' => 'required|in:admin,user,guest'
]);
```

**17+ Built-in Rules:**
- `required`, `email`, `url`, `uuid`
- `integer`, `numeric`, `boolean`, `alpha`, `alphanumeric`
- `min`, `max`, `in`, `regex`, `date`

[→ Full Validation Documentation](docs/VALIDATION.md)

## 🛡️ Middleware

### Built-in Middleware

```php
// CORS
CorsMiddleware::production(['https://app.example.com']);

// Rate Limiting
RateLimitMiddleware::api();        // 100 req/min
RateLimitMiddleware::auth();       // 5 req/min
RateLimitMiddleware::perUser(1000, 60);

// Logging
LoggingMiddleware::production('/var/log/api.log');
LoggingMiddleware::debug('/var/log/debug.log');
```

### Custom Middleware

```php
class AuthMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Authentication logic
        return $next($request);
    }
}
```

[→ Full Middleware Documentation](docs/MIDDLEWARE.md)

## 📥📤 Request & Response

### Request

```php
// Input
$request->input('name');
$request->all();
$request->only(['name', 'email']);
$request->validated($rules);

// Headers & Query
$request->header('Authorization');
$request->query('page', 1);

// Request Info
$request->method();
$request->ip();
$request->path();
```

### Response

```php
// JSON
Response::json(['data' => 'value'], 200);

// HTML
Response::html('<h1>Hello</h1>');

// Redirect
Response::redirect('/dashboard');

// Fluent Interface
Response::make('OK')
    ->withStatus(201)
    ->withHeader('X-Custom', 'Value');
```

[→ Full Request/Response Documentation](docs/REQUEST-RESPONSE.md)

## 🧪 Testing

```bash
# Run all tests
./run-tests.sh

# Run specific suite
./run-tests.sh router
./run-tests.sh validation

# Generate coverage
./run-tests.sh coverage
```

**100+ Tests** covering:
- Router functionality
- Validation rules
- Request/Response handling
- Middleware pipeline

[→ Full Testing Documentation](docs/TESTING.md)

## 🏗️ Architecture

SimpleRouter follows **Domain-Driven Design (DDD)**:

```
src/
├── Domain/              # Business logic
│   ├── Contracts/       # Interfaces (Middleware, ValidationRule, Validator)
│   ├── Entities/        # Domain entities (Route, RouteCollection)
│   └── ValueObjects/    # Value objects (HttpMethod, Uri)
└── Application/         # Application logic
    ├── Router.php       # Main router
    ├── Http/            # Request/Response
    ├── Validation/      # Validation system
    │   ├── RequestValidator.php
    │   ├── RuleParser.php
    │   ├── ValidationResult.php
    │   └── Rules/       # Individual rule files (17 rules)
    ├── Middleware/      # Middleware pipeline
    │   ├── MiddlewarePipeline.php
    │   └── Builtin/     # CorsMiddleware, RateLimitMiddleware, LoggingMiddleware
    └── Exceptions/      # Custom exceptions
```

[→ Full Architecture Documentation](docs/ARCHITECTURE.md)

**Design Principles:**
- ✅ SOLID principles
- ✅ Clean Code practices  
- ✅ DDD architecture (Domain/Application layers)
- ✅ Type safety (PHP 8.2+)
- ✅ Zero global state
- ✅ Interface-based design
- ✅ 17 individual validation rule files
- ✅ 3 production-ready built-in middleware

## 📖 Examples

### API with Database

```php
$router->get('/users', function(Request $request) {
    $page = $request->query('page', 1);
    $limit = $request->query('limit', 10);
    
    $users = User::paginate($page, $limit);
    
    return Response::json([
        'data' => $users,
        'page' => $page,
        'total' => User::count()
    ]);
});
```

### Authentication

```php
$router->post('/login', function(Request $request) {
    $validated = $request->validated([
        'email' => 'required|email',
        'password' => 'required|min:8'
    ]);
    
    $token = Auth::attempt($validated);
    
    return Response::json([
        'token' => $token,
        'expires_in' => 3600
    ]);
});
```

### File Upload

```php
$router->post('/upload', function(Request $request) {
    $file = $request->file('document');
    
    if ($file && $file->isValid()) {
        $path = $file->store('uploads');
        return Response::json(['path' => $path], 201);
    }
    
    return Response::json(['error' => 'Invalid file'], 400);
});
```

[→ More Examples](examples/)

## 🤝 Contributing

Contributions welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## 📄 License

MIT License - see [LICENSE](LICENSE) file.

## 🙏 Acknowledgments

- Built with ❤️ by [Celio Junior](https://fb.com/celiojr1994)
- Inspired by Laravel and Symfony
- Architecture based on DDD principles

## 📞 Support

- 📧 Email: profissional.celiojunior@outlook.com
- 🐛 Issues: [GitHub Issues](https://github.com/celiovmjr/simplerouter/issues)
- 📖 Docs: [Documentation](docs/)

---

**Made with ❤️ and PHP 8.2+**
