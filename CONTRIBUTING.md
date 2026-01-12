# 🤝 Contributing to SimpleRouter

Thank you for your interest in contributing to SimpleRouter! This document provides guidelines for contributing.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [How to Contribute](#how-to-contribute)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Pull Request Process](#pull-request-process)

## 📜 Code of Conduct

- Be respectful and inclusive
- Welcome newcomers and beginners
- Focus on constructive feedback
- No harassment or discrimination

## 🚀 Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Git

### Fork and Clone

```bash
# Fork the repository on GitHub
# Then clone your fork
git clone https://github.com/celiovmjr/simplerouter.git
cd simplerouter

# Add upstream remote
git remote add upstream https://github.com/celiovmjr/simplerouter.git
```

## 🛠️ Development Setup

```bash
# Install dependencies
composer install

# Run tests
./run-tests.sh

# Run static analysis (if available)
composer analyse
```

## 💡 How to Contribute

### Reporting Bugs

1. Check if the bug is already reported in [Issues](https://github.com/celiovmjr/simplerouter/issues)
2. If not, create a new issue with:
   - Clear title and description
   - Steps to reproduce
   - Expected vs actual behavior
   - PHP version and environment details
   - Code samples if applicable

### Suggesting Features

1. Check existing [Issues](https://github.com/celiovmjr/simplerouter/issues) for similar suggestions
2. Create a new issue with:
   - Clear description of the feature
   - Use cases and benefits
   - Possible implementation approach

### Contributing Code

#### Adding a New Validation Rule

1. Create file in `src/Application/Validation/Rules/YourRule.php`
2. Extend `AbstractRule`
3. Implement required methods
4. Add to `RuleParser::RULE_MAP`
5. Write tests in `tests/ValidationTest.php`
6. Update documentation

Example:

```php
<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

final class YourRule extends AbstractRule
{
    public function validate(mixed $value): bool
    {
        // Your validation logic
        return true;
    }
    
    protected function defaultMessage(): string
    {
        return 'Your error message';
    }
}
```

#### Adding Custom Middleware

1. Create file in `src/Application/Middleware/Builtin/YourMiddleware.php`
2. Implement `Middleware` interface
3. Add factory methods if useful
4. Write tests in `tests/MiddlewareTest.php`
5. Update `docs/MIDDLEWARE.md`

Example:

```php
<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Middleware\Builtin;

use Closure;
use SimpleRouter\Application\Http\{Request, Response};
use SimpleRouter\Domain\Contracts\Middleware;

final class YourMiddleware implements Middleware
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

## 📏 Coding Standards

### PHP Standards

- **PHP 8.2+** features
- **Strict types**: `declare(strict_types=1);` in every file
- **Type hints**: All parameters and return types
- **Final classes**: Use `final` by default unless designed for inheritance
- **Readonly**: Use `readonly` for immutable properties
- **PSR-4**: Autoloading standard
- **PSR-12**: Coding style (mostly)

### Naming Conventions

**Classes**
```php
// Good
final class EmailRule extends AbstractRule {}
final class CorsMiddleware implements Middleware {}

// Avoid
class email_rule {}
class corsMiddleware {}
```

**Methods**
```php
// Good
public function validateEmail(string $email): bool {}
private function parseRuleString(string $rule): array {}

// Avoid
public function validate_email(string $email): bool {}
private function Parse_Rule_String(string $rule): array {}
```

**Variables**
```php
// Good
$userName = 'John';
$isValid = true;

// Avoid
$user_name = 'John';
$IsValid = true;
```

### Code Organization

**Single Responsibility**
- One class = one responsibility
- One method = one thing
- Keep methods short (< 20 lines ideally)

**File Structure**
```php
<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

use SimpleRouter\Domain\Contracts\ValidationRule;

/**
 * Brief description
 */
final class YourRule extends AbstractRule
{
    // Properties
    
    // Constructor
    
    // Public methods
    
    // Protected methods
    
    // Private methods
}
```

### Documentation

**PHPDoc blocks**
```php
/**
 * Validates email addresses
 * 
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
public function validate(string $email): bool
{
    // ...
}
```

**Inline comments**
- Explain "why", not "what"
- Complex logic only
- Don't state the obvious

```php
// Good
// Use bcrypt for security (OWASP recommendation)
$hash = password_hash($password, PASSWORD_BCRYPT);

// Avoid
// Hash the password
$hash = password_hash($password, PASSWORD_BCRYPT);
```

## 🧪 Testing

### Writing Tests

All new features must include tests.

```php
public function testYourFeature(): void
{
    // Arrange
    $router = new Router();
    
    // Act
    $result = $router->someMethod();
    
    // Assert
    $this->assertEquals($expected, $result);
}
```

### Running Tests

```bash
# All tests
./run-tests.sh

# Specific test file
./run-tests.sh validation

# With coverage
./run-tests.sh coverage
```

### Test Coverage

- Aim for 90%+ coverage
- Test happy paths and edge cases
- Test error conditions

## 🔄 Pull Request Process

### Before Submitting

1. **Create a branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**
   - Follow coding standards
   - Add tests
   - Update documentation

3. **Run tests**
   ```bash
   ./run-tests.sh
   ```

4. **Commit your changes**
   ```bash
   git add .
   git commit -m "Add: your feature description"
   ```

### Commit Messages

Follow conventional commits:

```
Add: New email validation rule
Fix: CORS middleware preflight handling
Update: Documentation for routing
Refactor: Simplify RuleParser logic
Test: Add tests for MinValueRule
Docs: Update CONTRIBUTING.md
```

### Submitting PR

1. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

2. **Create Pull Request** on GitHub

3. **PR Description should include:**
   - What changed
   - Why it changed
   - How to test
   - Related issues (if any)

4. **PR Checklist:**
   - [ ] Tests pass
   - [ ] New tests added
   - [ ] Documentation updated
   - [ ] Code follows standards
   - [ ] No merge conflicts

### Review Process

1. Maintainer reviews your PR
2. Feedback provided (if needed)
3. Make requested changes
4. PR approved and merged

## 📝 Documentation

When adding features:

1. **Update README.md** if it affects main usage
2. **Update relevant docs/** files
3. **Add examples** to `examples/` if helpful
4. **Add inline comments** for complex logic

## 🎯 Areas for Contribution

### Priority Areas

- [ ] Additional validation rules
- [ ] More built-in middleware
- [ ] Performance optimizations
- [ ] Documentation improvements
- [ ] More examples

### Good First Issues

Look for issues tagged with `good first issue` on GitHub.

## 💬 Questions?

- 📧 Email: profissional.celiojunior@outlook.com
- 🐛 Issues: [GitHub Issues](https://github.com/celiovmjr/simplerouter/issues)
- 📖 Docs: [Documentation](docs/)

## 🙏 Thank You!

Every contribution helps make SimpleRouter better. Thank you for taking the time to contribute!

---

**Happy coding! 🚀**
