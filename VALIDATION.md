# 🔧 Validation System

## Overview

SimpleRouter includes a powerful validation system with **17 individual validation rules**, each in its own file following the Single Responsibility Principle.

## Architecture

### File Structure

```
src/Application/Validation/
├── RequestValidator.php      # Main validator
├── RuleParser.php            # Parses rule strings
├── ValidationResult.php      # Validation results
└── Rules/                    # Individual rule files
    ├── AbstractRule.php      # Base class for all rules
    ├── RequiredRule.php
    ├── EmailRule.php
    ├── UuidRule.php
    ├── IntegerRule.php
    ├── NumericRule.php
    ├── AlphaRule.php
    ├── AlphaNumericRule.php
    ├── MinLengthRule.php     # For strings
    ├── MaxLengthRule.php     # For strings
    ├── MinValueRule.php      # For numbers
    ├── MaxValueRule.php      # For numbers
    ├── UrlRule.php
    ├── BooleanRule.php
    ├── InRule.php
    ├── RegexRule.php
    └── DateRule.php
```

### How It Works

1. User calls `$request->validated($rules)`
2. `RequestValidator` receives rules array
3. `RuleParser` converts rule strings to rule objects
4. Each field is validated by its rule objects
5. Returns `ValidationResult` with success/errors

## Problem

Previously, `min` and `max` rules always validated **string length**, even for numeric fields:

```php
// ❌ Problem: age validation checked string length, not value
$request->validated([
    'age' => 'integer|min:18'  // This checked if "18" has 18+ characters!
]);

// Value "19" has only 2 characters, so it failed!
```

## Solution

Now, `min` and `max` are **context-aware**:

- **String fields**: Validates **length** (number of characters)
- **Numeric fields**: Validates **value** (numeric comparison)

### Automatic Detection

The validation system automatically detects if a field is numeric by checking for `integer`, `int`, or `numeric` rules:

```php
// ✅ String validation (checks length)
$request->validated([
    'username' => 'required|min:3|max:20'  // 3-20 characters
]);

// ✅ Numeric validation (checks value)
$request->validated([
    'age' => 'required|integer|min:18|max:120'  // Value between 18-120
]);

// ✅ Also works with 'numeric' rule
$request->validated([
    'price' => 'required|numeric|min:0|max:999.99'  // Value between 0-999.99
]);
```

## How It Works

### Internal Implementation

1. **RuleParser** scans all rules to detect `integer`, `int`, or `numeric`
2. If found, sets `$isNumericField = true`
3. When creating `min`/`max` rules:
   - **Numeric field** → Uses `MinValueRule` / `MaxValueRule`
   - **String field** → Uses `MinLengthRule` / `MaxLengthRule`

### Rules Created

| Field Type | Rule | Validates |
|------------|------|-----------|
| String | `MinLengthRule` | String length ≥ X |
| String | `MaxLengthRule` | String length ≤ X |
| Numeric | `MinValueRule` | Numeric value ≥ X |
| Numeric | `MaxValueRule` | Numeric value ≤ X |

## Complete Examples

### Age Validation (Numeric)

```php
// ✅ Correct: validates numeric value
$validated = $request->validated([
    'age' => 'required|integer|min:18|max:120|onError("Age must be between 18 and 120")'
]);

// Input: "19" → ✅ PASS (19 >= 18 and 19 <= 120)
// Input: "17" → ❌ FAIL (17 < 18)
// Input: "150" → ❌ FAIL (150 > 120)
```

### Price Validation (Numeric with Decimals)

```php
// ✅ Works with decimal numbers
$validated = $request->validated([
    'price' => 'required|numeric|min:0.01|max:999999.99'
]);

// Input: "99.99" → ✅ PASS
// Input: "0" → ❌ FAIL (0 < 0.01)
// Input: "1000000" → ❌ FAIL (> 999999.99)
```

### Username Validation (String Length)

```php
// ✅ Validates string length (no numeric rule)
$validated = $request->validated([
    'username' => 'required|alphanumeric|min:3|max:20'
]);

// Input: "john" → ✅ PASS (4 characters)
// Input: "jo" → ❌ FAIL (only 2 characters)
// Input: "verylongusernamethatexceeds" → ❌ FAIL (too many characters)
```

### Mixed Validation

```php
// ✅ Correctly handles both types in same request
$validated = $request->validated([
    'username' => 'required|min:3|max:20',           // String length
    'age' => 'required|integer|min:18|max:120',      // Numeric value
    'bio' => 'max:500',                              // String length
    'salary' => 'numeric|min:1000|max:100000'        // Numeric value
]);
```

## Error Messages

### Default Messages

**String fields:**
- `min`: "Must be at least X characters"
- `max`: "Must not exceed X characters"

**Numeric fields:**
- `min`: "Must be at least X"
- `max`: "Must not exceed X"

### Custom Messages

```php
$validated = $request->validated([
    // Custom message on string field
    'username' => 'required|min:3|onError("Username too short")',
    
    // Custom message on numeric field
    'age' => 'required|integer|min:18|onError("You must be 18 or older")',
    
    // Custom message on max
    'bio' => 'max:500|onError("Bio is too long")',
    
    // Custom message on numeric max
    'price' => 'numeric|max:1000|onError("Price cannot exceed $1000")'
]);
```

## Testing

### Test Cases

```php
// Test 1: Numeric field with min/max
$data = ['age' => '25'];
$rules = ['age' => 'integer|min:18|max:120'];
// Result: ✅ PASS (25 is between 18 and 120)

// Test 2: String field with min/max
$data = ['username' => 'john'];
$rules = ['username' => 'min:3|max:20'];
// Result: ✅ PASS (4 characters is between 3 and 20)

// Test 3: Numeric field fails min
$data = ['age' => '15'];
$rules = ['age' => 'integer|min:18'];
// Result: ❌ FAIL "Must be at least 18"

// Test 4: String field fails max
$data = ['username' => 'verylongusernamethatexceedsmaximum'];
$rules = ['username' => 'max:20'];
// Result: ❌ FAIL "Must not exceed 20 characters"
```

## Backward Compatibility

⚠️ **Breaking Change**: If you were using `min`/`max` with numeric fields in v1.x, the behavior has changed.

### Migration

```php
// OLD (v1.x) - Was checking string length
'age' => 'min:18'  // Checked if "18" string has 18+ characters (always failed!)

// NEW (v2.0) - Need to add integer/numeric rule
'age' => 'integer|min:18'  // Now correctly checks if value >= 18
```

**Migration steps:**

1. Find all numeric fields using `min` or `max`
2. Add `integer` or `numeric` rule before `min`/`max`
3. Test your validation

```php
// ❌ Before (incorrect)
$rules = [
    'age' => 'min:18',
    'price' => 'min:0|max:1000'
];

// ✅ After (correct)
$rules = [
    'age' => 'integer|min:18',
    'price' => 'numeric|min:0|max:1000'
];
```

## Summary

✅ **String fields**: `min`/`max` check **length** (characters)  
✅ **Numeric fields**: `min`/`max` check **value** (numeric comparison)  
✅ **Automatic detection**: Just add `integer` or `numeric` rule  
✅ **Works with decimals**: Supports `float` values  
✅ **Custom messages**: Works with `onError()`  

---

**Smart validation that does what you expect! 🎯**
