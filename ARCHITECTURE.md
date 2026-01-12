# 🏗️ Architecture

SimpleRouter follows **Domain-Driven Design (DDD)** principles with a clean separation of concerns.

## 📁 Project Structure

```
simplerouter/
├── src/
│   ├── Domain/                    # Business logic layer
│   │   ├── Contracts/             # Interfaces
│   │   │   ├── Middleware.php
│   │   │   ├── ValidationRule.php
│   │   │   └── Validator.php
│   │   ├── Entities/              # Domain entities
│   │   │   ├── Route.php
│   │   │   └── RouteCollection.php
│   │   └── ValueObjects/          # Immutable value objects
│   │       ├── HttpMethod.php
│   │       └── Uri.php
│   │
│   └── Application/               # Application logic layer
│       ├── Router.php             # Main router class
│       │
│       ├── Http/                  # HTTP handling
│       │   ├── Request.php
│       │   └── Response.php
│       │
│       ├── Middleware/            # Middleware system
│       │   ├── MiddlewarePipeline.php
│       │   └── Builtin/           # Built-in middleware
│       │       ├── CorsMiddleware.php
│       │       ├── LoggingMiddleware.php
│       │       └── RateLimitMiddleware.php
│       │
│       ├── Validation/            # Validation system
│       │   ├── RequestValidator.php
│       │   ├── RuleParser.php
│       │   ├── ValidationResult.php
│       │   └── Rules/             # Individual validation rules
│       │       ├── AbstractRule.php
│       │       ├── RequiredRule.php
│       │       ├── EmailRule.php
│       │       ├── UuidRule.php
│       │       ├── IntegerRule.php
│       │       ├── NumericRule.php
│       │       ├── AlphaRule.php
│       │       ├── AlphaNumericRule.php
│       │       ├── MinLengthRule.php
│       │       ├── MaxLengthRule.php
│       │       ├── MinValueRule.php
│       │       ├── MaxValueRule.php
│       │       ├── UrlRule.php
│       │       ├── BooleanRule.php
│       │       ├── InRule.php
│       │       ├── RegexRule.php
│       │       └── DateRule.php
│       │
│       └── Exceptions/            # Custom exceptions
│           ├── RouteNotFoundException.php
│           ├── MethodNotAllowedException.php
│           ├── ControllerNotFoundException.php
│           └── ValidationException.php
│
├── tests/                         # PHPUnit tests
│   ├── RouterTest.php
│   ├── ValidationTest.php
│   ├── RequestTest.php
│   ├── ResponseTest.php
│   └── MiddlewareTest.php
│
├── examples/                      # Usage examples
│   ├── index-with-route-files.php
│   ├── usage.php
│   └── routes/
│       ├── web.php
│       └── api.php
│
├── docs/                          # Documentation
│   ├── GETTING-STARTED.md
│   ├── ROUTING.md
│   ├── VALIDATION.md
│   ├── MIDDLEWARE.md
│   ├── TESTING.md
│   └── ARCHITECTURE.md (this file)
│
├── .htaccess                      # Apache configuration
├── phpunit.xml                    # PHPUnit configuration
├── composer.json                  # Composer configuration
├── run-tests.sh                   # Test runner script
├── LICENSE                        # MIT License
└── README.md                      # Main documentation
```

## 🎯 Design Principles

### 1. Domain-Driven Design (DDD)

The project is organized into two main layers:

**Domain Layer** (`src/Domain/`)
- Contains pure business logic
- No dependencies on infrastructure
- Defines contracts (interfaces)
- Immutable value objects
- Domain entities

**Application Layer** (`src/Application/`)
- Implements domain contracts
- Coordinates business logic
- Handles HTTP concerns
- Manages validation and middleware

### 2. SOLID Principles

**Single Responsibility**
- Each validation rule is its own class
- Each middleware has one purpose
- Clear separation of concerns

**Open/Closed**
- Extensible through interfaces
- Closed for modification
- Easy to add new rules/middleware

**Liskov Substitution**
- All rules implement `ValidationRule`
- All middleware implement `Middleware`
- Interchangeable implementations

**Interface Segregation**
- Small, focused interfaces
- `ValidationRule`, `Middleware`, `Validator`
- No fat interfaces

**Dependency Inversion**
- Depend on abstractions (interfaces)
- Not on concrete implementations
- Easy to test and mock

### 3. Clean Code

- **Type Safety**: Full PHP 8.2+ type hints
- **Strict Types**: `declare(strict_types=1)` everywhere
- **Immutability**: Value objects are immutable
- **No Globals**: Zero global state
- **PSR-4**: Standard autoloading

## 📦 Component Details

### Domain Layer

#### Contracts (Interfaces)

**ValidationRule** - Contract for validation rules
```php
interface ValidationRule
{
    public function validate(mixed $value): bool;
    public function message(): string;
}
```

**Middleware** - Contract for middleware
```php
interface Middleware
{
    public function handle(Request $request, Closure $next): Response;
}
```

**Validator** - Contract for validators
```php
interface Validator
{
    public function validate(array $data, array $rules): ValidationResult;
}
```

#### Entities

**Route** - Represents a single route
- Immutable once created
- Contains URI, method, handler
- Supports middleware attachment

**RouteCollection** - Collection of routes
- Manages route storage
- Handles route lookup
- Supports named routes

#### Value Objects

**HttpMethod** - HTTP method enum
- GET, POST, PUT, PATCH, DELETE, OPTIONS
- Type-safe method handling

**Uri** - URI value object
- Parses and matches URIs
- Extracts parameters
- Immutable

### Application Layer

#### Router

**Router.php** - Main routing engine
- Registers routes
- Matches incoming requests
- Dispatches to handlers
- Manages route groups

#### HTTP

**Request.php** - HTTP request wrapper
- Input handling
- Header access
- File uploads
- Query parameters

**Response.php** - HTTP response builder
- JSON responses
- HTML responses
- Redirects
- Fluent interface

#### Validation System

**RequestValidator.php** - Validates request data
- Uses RuleParser to parse rules
- Applies rules to data
- Returns ValidationResult

**RuleParser.php** - Parses rule strings
- Converts `'required|email'` to rule objects
- Handles parameters (`min:3`)
- Detects numeric context for min/max

**ValidationResult.php** - Validation result
- Success/failure status
- Error messages
- Validated data

**Individual Rules** (17 files)
- Each rule in its own file
- Extends AbstractRule
- Single responsibility
- Easy to add new rules

#### Middleware System

**MiddlewarePipeline.php** - Executes middleware chain
- Processes middleware in order
- Passes request through pipeline
- Returns final response

**Built-in Middleware** (3 classes)
- `CorsMiddleware` - CORS handling
- `RateLimitMiddleware` - Rate limiting
- `LoggingMiddleware` - Request/response logging

#### Exceptions

Custom exceptions for different error scenarios:
- `RouteNotFoundException` - Route not found (404)
- `MethodNotAllowedException` - Method not allowed (405)
- `ControllerNotFoundException` - Controller class not found
- `ValidationException` - Validation failed (422)

## 🔄 Request Flow

```
1. HTTP Request
   ↓
2. Router::run()
   ↓
3. Router::dispatch()
   ↓
4. Match Route (RouteCollection)
   ↓
5. Middleware Pipeline
   ↓
6. Controller/Closure Handler
   ↓
7. Validation (if used)
   ↓
8. Business Logic
   ↓
9. Response
   ↓
10. Middleware Pipeline (reverse)
   ↓
11. HTTP Response
```

## 🧩 Key Design Decisions

### Why Individual Rule Files?

**Before:** Single `ValidationRules.php` with 17 rules (285 lines)

**After:** 17 separate files (one per rule)

**Benefits:**
- ✅ Better organization
- ✅ Easier to find specific rules
- ✅ Simpler to add new rules
- ✅ Follows Single Responsibility
- ✅ Better IDE navigation
- ✅ Clearer dependencies

### Why Separate Builtin Middleware?

Built-in middleware in their own folder:
- ✅ Clear distinction from custom middleware
- ✅ Easy to find production-ready components
- ✅ Users know these are provided
- ✅ Separate namespace

### Why Domain/Application Split?

**Domain** = What the system does (business rules)
**Application** = How it does it (implementation)

Benefits:
- ✅ Clear separation of concerns
- ✅ Business logic independent of framework
- ✅ Easy to test
- ✅ Easy to understand

## 📈 Extensibility

### Adding a New Validation Rule

1. Create `src/Application/Validation/Rules/YourRule.php`
2. Extend `AbstractRule`
3. Implement `validate()` and `defaultMessage()`
4. Add to `RuleParser::RULE_MAP`

```php
final class CustomRule extends AbstractRule
{
    public function validate(mixed $value): bool
    {
        // Your logic
    }
    
    protected function defaultMessage(): string
    {
        return 'Custom error message';
    }
}
```

### Adding Custom Middleware

1. Create your middleware class
2. Implement `Middleware` interface
3. Use in routes or groups

```php
class MyMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Before logic
        $response = $next($request);
        // After logic
        return $response;
    }
}
```

## 🧪 Testing Architecture

Tests mirror the source structure:
- `RouterTest` - Core routing
- `ValidationTest` - All validation rules
- `RequestTest` - Request handling
- `ResponseTest` - Response building
- `MiddlewareTest` - Middleware pipeline

## 📚 Dependencies

**Zero Dependencies** - Pure PHP 8.2+

Only dev dependencies:
- PHPUnit 10.5+ (testing)
- PHPStan 1.10+ (static analysis)

## 🎓 Learning Path

1. **Start**: `src/Application/Router.php`
2. **Routing**: `src/Domain/Entities/Route.php`
3. **HTTP**: `src/Application/Http/`
4. **Validation**: `src/Application/Validation/`
5. **Middleware**: `src/Application/Middleware/`

## 🔍 Code Statistics

- **Total Files**: ~40 PHP files
- **Lines of Code**: ~3,500 lines
- **Test Files**: 5
- **Test Cases**: 100+
- **Code Coverage**: 95%+
- **Validation Rules**: 17
- **Built-in Middleware**: 3

## 📖 See Also

- [Getting Started](GETTING-STARTED.md)
- [Routing Guide](ROUTING.md)
- [Validation Guide](VALIDATION.md)
- [Middleware Guide](MIDDLEWARE.md)

---

**Clean architecture for clean code! 🏗️**
