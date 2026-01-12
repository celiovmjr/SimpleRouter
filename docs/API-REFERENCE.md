# 📖 API Reference

Complete API reference for SimpleRouter.

## Table of Contents

- [Router](#router)
- [Request](#request)
- [Response](#response)
- [Route](#route)
- [Validation](#validation)
- [Middleware](#middleware)

## Router

**Class:** `SimpleRouter\Application\Router`

Main router class for registering and dispatching routes.

### Methods

#### Route Registration

##### `get(string $uri, callable|array $handler): Route`

Register a GET route.

```php
$router->get('/users', function(Request $request) {
    return Response::json(['users' => []]);
});
```

##### `post(string $uri, callable|array $handler): Route`

Register a POST route.

```php
$router->post('/users', [UserController::class, 'store']);
```

##### `put(string $uri, callable|array $handler): Route`

Register a PUT route.

```php
$router->put('/users/{id:int}', function(Request $request) {
    // Update user
});
```

##### `patch(string $uri, callable|array $handler): Route`

Register a PATCH route.

```php
$router->patch('/users/{id:int}', $handler);
```

##### `delete(string $uri, callable|array $handler): Route`

Register a DELETE route.

```php
$router->delete('/users/{id:int}', $handler);
```

##### `options(string $uri, callable|array $handler): Route`

Register an OPTIONS route.

```php
$router->options('/users', $handler);
```

##### `match(array $methods, string $uri, callable|array $handler): Route`

Register a route for multiple HTTP methods.

```php
$router->match(['GET', 'POST'], '/contact', $handler);
```

##### `any(string $uri, callable|array $handler): Route`

Register a route for all HTTP methods.

```php
$router->any('/webhook', $handler);
```

#### Route Groups

##### `group(array $attributes, Closure $callback): void`

Create a route group.

**Attributes:**
- `prefix` (string): URI prefix
- `middleware` (array): Middleware classes

```php
$router->group([
    'prefix' => '/api',
    'middleware' => [AuthMiddleware::class]
], function($router) {
    $router->get('/users', $handler);
});
```

#### Named Routes

##### `route(string $name): string`

Get URL for named route.

```php
$url = $router->route('dashboard'); // Returns: /dashboard
```

#### Dispatching

##### `run(): void`

Run the router (process current request).

```php
$router->run();
```

##### `dispatch(Request $request): Response`

Dispatch a specific request.

```php
$response = $router->dispatch($request);
```

---

## Request

**Class:** `SimpleRouter\Application\Http\Request`

HTTP request wrapper.

### Properties

- `server` (array): Server variables
- `query` (array): Query parameters
- `body` (array): Request body
- `files` (array): Uploaded files

### Methods

#### Input

##### `input(string $key, mixed $default = null): mixed`

Get input value.

```php
$name = $request->input('name');
$age = $request->input('age', 18);
```

##### `all(): array`

Get all input data.

```php
$data = $request->all();
```

##### `only(array $keys): array`

Get only specific keys.

```php
$data = $request->only(['name', 'email']);
```

##### `except(array $keys): array`

Get all except specific keys.

```php
$data = $request->except(['password']);
```

##### `has(string $key): bool`

Check if input exists.

```php
if ($request->has('email')) { }
```

##### `filled(string $key): bool`

Check if input is filled (not empty).

```php
if ($request->filled('email')) { }
```

#### Query Parameters

##### `query(string $key = null, mixed $default = null): mixed`

Get query parameter.

```php
$page = $request->query('page', 1);
```

#### Headers

##### `header(string $key, mixed $default = null): mixed`

Get header value.

```php
$token = $request->header('Authorization');
```

##### `headers(): array`

Get all headers.

```php
$headers = $request->headers();
```

#### Files

##### `file(string $key): ?UploadedFile`

Get uploaded file.

```php
$file = $request->file('document');
```

#### Request Info

##### `method(): string`

Get HTTP method.

```php
$method = $request->method(); // 'GET', 'POST', etc.
```

##### `isMethod(string $method): bool`

Check HTTP method.

```php
if ($request->isMethod('POST')) { }
```

##### `uri(): string`

Get request URI.

```php
$uri = $request->uri(); // '/users?page=1'
```

##### `path(): string`

Get path without query string.

```php
$path = $request->path(); // '/users'
```

##### `ip(): string`

Get client IP address.

```php
$ip = $request->ip();
```

##### `userAgent(): string`

Get user agent.

```php
$ua = $request->userAgent();
```

##### `isJson(): bool`

Check if request content type is JSON.

```php
if ($request->isJson()) { }
```

##### `expectsJson(): bool`

Check if client expects JSON response.

```php
if ($request->expectsJson()) { }
```

#### Route Parameters

##### `routeParameter(string $key, mixed $default = null): mixed`

Get route parameter.

```php
$id = $request->routeParameter('id');
```

##### `routeParameters(): array`

Get all route parameters.

```php
$params = $request->routeParameters();
```

##### `setRouteParameters(array $parameters): void`

Set route parameters (internal use).

```php
$request->setRouteParameters(['id' => 123]);
```

#### Validation

##### `validated(array $rules): array`

Validate and return validated data.

```php
$validated = $request->validated([
    'name' => 'required|min:3',
    'email' => 'required|email'
]);
```

**Throws:** `ValidationException` if validation fails

---

## Response

**Class:** `SimpleRouter\Application\Http\Response`

HTTP response builder.

### Static Methods

##### `make(string $content = '', int $status = 200): Response`

Create a new response.

```php
Response::make('Hello World', 200);
```

##### `json(array $data, int $status = 200): Response`

Create JSON response.

```php
Response::json(['message' => 'Success'], 200);
```

##### `html(string $content, int $status = 200): Response`

Create HTML response.

```php
Response::html('<h1>Hello</h1>', 200);
```

##### `redirect(string $url, int $status = 302): Response`

Create redirect response.

```php
Response::redirect('/dashboard', 302);
```

##### `noContent(): Response`

Create 204 No Content response.

```php
Response::noContent();
```

### Instance Methods

##### `withStatus(int $status): Response`

Set status code.

```php
$response->withStatus(201);
```

##### `withHeader(string $name, string $value): Response`

Add header.

```php
$response->withHeader('X-Custom', 'Value');
```

##### `withHeaders(array $headers): Response`

Add multiple headers.

```php
$response->withHeaders([
    'X-Header-1' => 'Value1',
    'X-Header-2' => 'Value2'
]);
```

##### `withContent(string $content): Response`

Set content.

```php
$response->withContent('Updated content');
```

##### `withJson(array $data): Response`

Set JSON content.

```php
$response->withJson(['data' => 'value']);
```

##### `status(): int`

Get status code.

```php
$status = $response->status();
```

##### `statusText(): string`

Get status text.

```php
$text = $response->statusText(); // 'OK', 'Not Found', etc.
```

##### `content(): string`

Get response content.

```php
$content = $response->content();
```

##### `header(string $name): ?string`

Get header value.

```php
$value = $response->header('Content-Type');
```

##### `headers(): array`

Get all headers.

```php
$headers = $response->headers();
```

##### `send(): void`

Send response to client.

```php
$response->send();
```

---

## Route

**Class:** `SimpleRouter\Domain\Entities\Route`

Represents a single route.

### Methods

##### `withName(string $name): Route`

Set route name.

```php
$router->get('/dashboard', $handler)->withName('dashboard');
```

##### `withMiddleware(array|Middleware $middleware): Route`

Add middleware to route.

```php
$router->get('/admin', $handler)->withMiddleware([AuthMiddleware::class]);
```

---

## Validation

### ValidationRule Interface

**Interface:** `SimpleRouter\Domain\Contracts\ValidationRule`

```php
interface ValidationRule
{
    public function validate(mixed $value): bool;
    public function message(): string;
}
```

### Available Rules

All rules in `SimpleRouter\Application\Validation\Rules\`:

| Rule | Class | Usage |
|------|-------|-------|
| required | `RequiredRule` | `'field' => 'required'` |
| email | `EmailRule` | `'field' => 'email'` |
| uuid | `UuidRule` | `'field' => 'uuid'` |
| integer | `IntegerRule` | `'field' => 'integer'` |
| numeric | `NumericRule` | `'field' => 'numeric'` |
| alpha | `AlphaRule` | `'field' => 'alpha'` |
| alphanumeric | `AlphaNumericRule` | `'field' => 'alphanumeric'` |
| min | `MinLengthRule` / `MinValueRule` | `'field' => 'min:3'` |
| max | `MaxLengthRule` / `MaxValueRule` | `'field' => 'max:20'` |
| url | `UrlRule` | `'field' => 'url'` |
| boolean | `BooleanRule` | `'field' => 'boolean'` |
| in | `InRule` | `'field' => 'in:a,b,c'` |
| regex | `RegexRule` | `'field' => 'regex:/pattern/'` |
| date | `DateRule` | `'field' => 'date:Y-m-d'` |

### Custom Error Messages

```php
'field' => 'required|email|onError("Custom error message")'
```

### ValidationException

**Class:** `SimpleRouter\Application\Exceptions\ValidationException`

Thrown when validation fails.

##### `errors(): array`

Get validation errors.

```php
try {
    $validated = $request->validated($rules);
} catch (ValidationException $e) {
    $errors = $e->errors();
}
```

##### `firstError(string $field): ?string`

Get first error for field.

```php
$error = $e->firstError('email');
```

---

## Middleware

### Middleware Interface

**Interface:** `SimpleRouter\Domain\Contracts\Middleware`

```php
interface Middleware
{
    public function handle(Request $request, Closure $next): Response;
}
```

### Built-in Middleware

#### CorsMiddleware

**Class:** `SimpleRouter\Application\Middleware\Builtin\CorsMiddleware`

**Constructor:**
```php
new CorsMiddleware(
    array $allowedOrigins = ['*'],
    array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With'],
    bool $allowCredentials = false,
    int $maxAge = 86400,
    array $exposedHeaders = ['Content-Length', 'X-JSON']
)
```

**Static Methods:**
- `allowAll(): CorsMiddleware`
- `development(): CorsMiddleware`
- `production(array $origins): CorsMiddleware`

#### RateLimitMiddleware

**Class:** `SimpleRouter\Application\Middleware\Builtin\RateLimitMiddleware`

**Constructor:**
```php
new RateLimitMiddleware(
    int $maxRequests = 60,
    int $perMinutes = 1,
    string $keyPrefix = 'rate_limit',
    callable $keyGenerator = null
)
```

**Static Methods:**
- `api(): RateLimitMiddleware`
- `auth(): RateLimitMiddleware`
- `public(): RateLimitMiddleware`
- `perUser(int $max, int $mins): RateLimitMiddleware`
- `perApiKey(int $max, int $mins): RateLimitMiddleware`

#### LoggingMiddleware

**Class:** `SimpleRouter\Application\Middleware\Builtin\LoggingMiddleware`

**Constructor:**
```php
new LoggingMiddleware(
    ?string $logFile = null,
    string $logLevel = 'info',
    bool $logRequestBody = false,
    bool $logResponseBody = false,
    int $maxBodyLength = 1000,
    callable $logHandler = null,
    array $excludePaths = [],
    array $sensitiveHeaders = ['Authorization', 'X-API-Key', 'Cookie']
)
```

**Static Methods:**
- `debug(?string $file): LoggingMiddleware`
- `production(string $file): LoggingMiddleware`
- `api(string $file): LoggingMiddleware`
- `custom(callable $handler): LoggingMiddleware`

---

## Exceptions

### RouteNotFoundException

**Class:** `SimpleRouter\Application\Exceptions\RouteNotFoundException`

Thrown when route is not found (404).

### MethodNotAllowedException

**Class:** `SimpleRouter\Application\Exceptions\MethodNotAllowedException`

Thrown when HTTP method is not allowed (405).

### ControllerNotFoundException

**Class:** `SimpleRouter\Application\Exceptions\ControllerNotFoundException`

Thrown when controller class is not found.

### ValidationException

**Class:** `SimpleRouter\Application\Exceptions\ValidationException`

Thrown when validation fails (422).

---

## Type Definitions

### Route Parameters

Typed route parameters:

| Type | Pattern | Example |
|------|---------|---------|
| `int` | `\d+` | `{id:int}` |
| `uuid` | UUID v4 | `{uuid:uuid}` |
| `slug` | URL slug | `{slug:slug}` |
| `alpha` | Letters only | `{name:alpha}` |
| `alphanum` | Alphanumeric | `{code:alphanum}` |
| `any` | Any chars | `{path:any}` |

### HTTP Status Codes

Common status codes:

| Code | Text | Usage |
|------|------|-------|
| 200 | OK | Success |
| 201 | Created | Resource created |
| 204 | No Content | Success, no body |
| 301 | Moved Permanently | Permanent redirect |
| 302 | Found | Temporary redirect |
| 400 | Bad Request | Invalid request |
| 401 | Unauthorized | Auth required |
| 403 | Forbidden | No permission |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit |
| 500 | Internal Server Error | Server error |

---

## See Also

- [Getting Started](GETTING-STARTED.md)
- [Routing Guide](ROUTING.md)
- [Validation Guide](VALIDATION.md)
- [Middleware Guide](MIDDLEWARE.md)
- [Request & Response](REQUEST-RESPONSE.md)

---

**Complete API reference! 📖**
