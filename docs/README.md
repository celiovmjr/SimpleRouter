# 📚 SimpleRouter Documentation

Complete documentation for SimpleRouter v2.0.

## 📖 Documentation Index

### Getting Started

- **[Getting Started](GETTING-STARTED.md)** - Installation, setup, and first route
  - Requirements
  - Installation (Composer & manual)
  - Server configuration
  - Your first route
  - Project structure

### Core Concepts

- **[Architecture](ARCHITECTURE.md)** - DDD structure and design principles
  - Project structure
  - Domain-Driven Design
  - SOLID principles
  - Component details
  - Request flow

- **[Routing](ROUTING.md)** - Complete routing guide
  - HTTP methods
  - Route parameters (typed)
  - Route groups & nesting
  - Named routes
  - Controllers
  - Advanced patterns

- **[Validation](VALIDATION.md)** - Validation system
  - 17 validation rules
  - Custom error messages
  - Smart min/max (context-aware)
  - Creating custom rules

- **[Request & Response](REQUEST-RESPONSE.md)** - HTTP handling
  - Request methods
  - Input data & validation
  - Query parameters & headers
  - File uploads
  - Response types (JSON, HTML, redirects)
  - Status codes

- **[Middleware](MIDDLEWARE.md)** - Middleware system
  - Built-in middleware:
    - CorsMiddleware
    - RateLimitMiddleware
    - LoggingMiddleware
  - Creating custom middleware
  - Middleware pipeline

### Reference

- **[API Reference](API-REFERENCE.md)** - Complete API documentation
  - Router API
  - Request API
  - Response API
  - Route API
  - Validation API
  - Middleware API
  - Exceptions

- **[Examples](EXAMPLES.md)** - Practical code examples
  - Basic examples
  - Authentication (login, JWT, registration)
  - Database operations (CRUD, pagination, search)
  - File uploads
  - RESTful APIs
  - Real-world applications

### Testing & Development

- **[Testing](TESTING.md)** - Testing guide
  - Running tests
  - Test coverage
  - Writing tests
  - Test examples

### Additional Documentation

- **[Bug Fixes](BUGFIX.md)** - Bug fixes and corrections
- **[Validation Fix](VALIDATION-FIX.md)** - Smart min/max validation

## 🎯 Quick Links

### By Topic

**Routing**
- [Basic Routing](ROUTING.md#basic-routing)
- [Route Parameters](ROUTING.md#route-parameters)
- [Route Groups](ROUTING.md#route-groups)
- [Named Routes](ROUTING.md#named-routes)

**Validation**
- [Available Rules](VALIDATION.md#available-rules)
- [Custom Messages](VALIDATION.md#custom-error-messages)
- [Context-Aware min/max](VALIDATION.md#smart-minmax-rules)

**HTTP**
- [Request Input](REQUEST-RESPONSE.md#input-data)
- [Response Types](REQUEST-RESPONSE.md#response)
- [File Uploads](REQUEST-RESPONSE.md#files)

**Middleware**
- [CORS](MIDDLEWARE.md#corsmiddleware)
- [Rate Limiting](MIDDLEWARE.md#ratelimitmiddleware)
- [Logging](MIDDLEWARE.md#loggingmiddleware)

**Examples**
- [Authentication](EXAMPLES.md#authentication)
- [Database Operations](EXAMPLES.md#database-operations)
- [File Uploads](EXAMPLES.md#file-uploads)
- [RESTful API](EXAMPLES.md#api-examples)

## 📦 Structure Overview

```
docs/
├── README.md (this file)     # Documentation index
├── GETTING-STARTED.md        # Installation & setup
├── ARCHITECTURE.md           # DDD structure
├── ROUTING.md               # Routing guide
├── VALIDATION.md            # Validation system
├── REQUEST-RESPONSE.md      # HTTP handling
├── MIDDLEWARE.md            # Middleware guide
├── TESTING.md               # Testing guide
├── API-REFERENCE.md         # Complete API
├── EXAMPLES.md              # Code examples
├── BUGFIX.md                # Bug fixes
└── VALIDATION-FIX.md        # Validation fixes
```

## 🚀 Learning Path

### Beginner

1. [Getting Started](GETTING-STARTED.md) - Set up your first route
2. [Routing Basics](ROUTING.md#basic-routing) - Learn routing
3. [Request & Response](REQUEST-RESPONSE.md) - Handle HTTP
4. [Validation](VALIDATION.md) - Validate inputs

### Intermediate

5. [Route Groups](ROUTING.md#route-groups) - Organize routes
6. [Controllers](ROUTING.md#controllers) - Use controllers
7. [Middleware](MIDDLEWARE.md) - Add middleware
8. [Examples](EXAMPLES.md#database-operations) - Database ops

### Advanced

9. [Architecture](ARCHITECTURE.md) - Understand DDD
10. [Custom Middleware](MIDDLEWARE.md#custom-middleware) - Build middleware
11. [Testing](TESTING.md) - Test your app
12. [API Reference](API-REFERENCE.md) - Complete API

## 🎓 Use Cases

### Building a Web Application

1. [Getting Started](GETTING-STARTED.md)
2. [Routing](ROUTING.md)
3. [Controllers](ROUTING.md#controllers)
4. [Validation](VALIDATION.md)
5. [Authentication Example](EXAMPLES.md#authentication)

### Building a REST API

1. [Getting Started](GETTING-STARTED.md)
2. [Route Groups](ROUTING.md#route-groups)
3. [JSON Responses](REQUEST-RESPONSE.md#json-responses)
4. [Middleware](MIDDLEWARE.md) - CORS, Rate Limit
5. [RESTful API Example](EXAMPLES.md#restful-api)

### Adding Authentication

1. [Authentication Examples](EXAMPLES.md#authentication)
2. [Custom Middleware](MIDDLEWARE.md#custom-middleware)
3. [Route Groups](ROUTING.md#route-groups) - Protect routes

### File Uploads

1. [File Upload Guide](REQUEST-RESPONSE.md#files)
2. [Validation](VALIDATION.md) - Validate files
3. [File Upload Examples](EXAMPLES.md#file-uploads)

## 💡 Common Tasks

### How do I...

**...create a route?**
→ [Basic Routing](ROUTING.md#basic-routing)

**...validate request data?**
→ [Validation Guide](VALIDATION.md)

**...add middleware?**
→ [Middleware Guide](MIDDLEWARE.md)

**...handle file uploads?**
→ [File Uploads](REQUEST-RESPONSE.md#files)

**...return JSON?**
→ [JSON Responses](REQUEST-RESPONSE.md#json-responses)

**...group routes?**
→ [Route Groups](ROUTING.md#route-groups)

**...use typed parameters?**
→ [Route Parameters](ROUTING.md#typed-parameters)

**...create custom validation rules?**
→ [Validation](VALIDATION.md#creating-custom-rules)

**...write tests?**
→ [Testing Guide](TESTING.md)

## 🆘 Need Help?

- 📧 Email: profissional.celiojunior@outlook.com
- 🐛 Issues: [GitHub Issues](https://github.com/celiovmjr/simplerouter/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/celiovmjr/simplerouter/discussions)

## 🤝 Contributing

Want to improve the documentation?

- [Contributing Guide](../CONTRIBUTING.md)
- [Architecture](ARCHITECTURE.md) - Understand the structure
- [API Reference](API-REFERENCE.md) - API details

## 📄 License

MIT License - see [LICENSE](../LICENSE)

---

**Happy learning! 📚🚀**
