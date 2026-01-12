# 📥📤 Request & Response

Complete guide to handling HTTP requests and responses in SimpleRouter.

## Table of Contents

- [Request](#request)
  - [Input Data](#input-data)
  - [Query Parameters](#query-parameters)
  - [Headers](#headers)
  - [Files](#files)
  - [Request Info](#request-info)
- [Response](#response)
  - [JSON Responses](#json-responses)
  - [HTML Responses](#html-responses)
  - [Redirects](#redirects)
  - [Custom Responses](#custom-responses)
- [Best Practices](#best-practices)

## Request

The `Request` class (`src/Application/Http/Request.php`) provides access to all incoming HTTP data.

### Input Data

#### Get Single Input

```php
// Get input value
$name = $request->input('name');

// With default value
$name = $request->input('name', 'Guest');

// Type-safe retrieval
$age = (int) $request->input('age', 18);
```

#### Get All Inputs

```php
// Get all input data (query + body + route params)
$all = $request->all();

// Returns array like:
[
    'name' => 'John',
    'email' => 'john@example.com',
    'page' => '1'
]
```

#### Get Specific Fields

```php
// Get only specific fields
$data = $request->only(['name', 'email']);
// Returns: ['name' => 'John', 'email' => 'john@example.com']

// Get all except specific fields
$data = $request->except(['password', 'token']);
// Returns all fields except password and token
```

#### Check if Input Exists

```php
// Check if field exists (even if empty)
if ($request->has('email')) {
    // Field exists
}

// Check if field is filled (not empty)
if ($request->filled('email')) {
    // Field exists and has a value
}
```

### Query Parameters

Query parameters from the URL (`?page=1&limit=10`):

```php
// Get query parameter
$page = $request->query('page');

// With default value
$page = $request->query('page', 1);
$limit = $request->query('limit', 10);

// Example: /users?page=2&limit=20
$page = $request->query('page');   // '2'
$limit = $request->query('limit'); // '20'
```

### Headers

```php
// Get header
$token = $request->header('Authorization');

// With default value
$contentType = $request->header('Content-Type', 'application/json');

// Get all headers
$headers = $request->headers();

// Common headers
$token = $request->header('Authorization');
$apiKey = $request->header('X-API-Key');
$contentType = $request->header('Content-Type');
$accept = $request->header('Accept');
```

### Files

```php
// Get uploaded file
$file = $request->file('document');

if ($file && $file->isValid()) {
    // Get file info
    $name = $file->getClientOriginalName();
    $size = $file->getSize();
    $mime = $file->getMimeType();
    
    // Store file
    $path = $file->store('uploads');
}

// Multiple files
$files = $request->file('documents'); // Array of files
foreach ($files as $file) {
    if ($file->isValid()) {
        $file->store('uploads');
    }
}
```

### Request Info

#### HTTP Method

```php
// Get method
$method = $request->method(); // 'GET', 'POST', etc.

// Check method
if ($request->isMethod('POST')) {
    // Handle POST request
}

if ($request->isMethod('GET')) {
    // Handle GET request
}
```

#### URI and Path

```php
// Get full URI
$uri = $request->uri(); // '/api/users?page=1'

// Get path only (without query string)
$path = $request->path(); // '/api/users'
```

#### Client Information

```php
// Get client IP
$ip = $request->ip(); // '127.0.0.1'

// Get user agent
$userAgent = $request->userAgent(); // 'Mozilla/5.0...'
```

#### Content Type Detection

```php
// Check if request is JSON
if ($request->isJson()) {
    // Handle JSON request
}

// Check if request expects JSON
if ($request->expectsJson()) {
    // Client expects JSON response
}
```

#### Route Parameters

```php
// For route: /users/{id:int}
$id = $request->routeParameter('id');

// With default value
$id = $request->routeParameter('id', 0);

// All route parameters
$params = $request->routeParameters();
```

## Response

The `Response` class (`src/Application/Http/Response.php`) helps build HTTP responses.

### JSON Responses

```php
// Basic JSON response
Response::json(['message' => 'Success']);

// With status code
Response::json(['user' => $user], 201);

// With custom headers
Response::json(['data' => $data])
    ->withHeader('X-Custom', 'Value');
```

#### Common JSON Patterns

```php
// Success response
Response::json([
    'success' => true,
    'data' => $data
], 200);

// Error response
Response::json([
    'success' => false,
    'error' => 'Not found'
], 404);

// Paginated response
Response::json([
    'data' => $users,
    'page' => $page,
    'per_page' => $limit,
    'total' => $total
]);

// Created resource
Response::json([
    'message' => 'User created',
    'user' => $user
], 201);
```

### HTML Responses

```php
// Basic HTML
Response::html('<h1>Hello World</h1>');

// With status code
Response::html('<h1>Not Found</h1>', 404);

// With template
$html = include 'views/dashboard.php';
Response::html($html);
```

### Redirects

```php
// Temporary redirect (302)
Response::redirect('/dashboard');

// Permanent redirect (301)
Response::redirect('/new-url', 301);

// With query string
Response::redirect('/users?page=1');

// External redirect
Response::redirect('https://example.com');
```

### Custom Responses

#### No Content

```php
// 204 No Content (for DELETE, etc)
Response::noContent();
```

#### Custom Status

```php
// Create response with any status
Response::make('OK', 200);
Response::make('Created', 201);
Response::make('Accepted', 202);
Response::make('Bad Request', 400);
Response::make('Unauthorized', 401);
Response::make('Forbidden', 403);
Response::make('Not Found', 404);
Response::make('Internal Server Error', 500);
```

#### Fluent Interface

```php
// Chain methods
Response::make('Success')
    ->withStatus(201)
    ->withHeader('X-Custom', 'Value')
    ->withHeader('X-Request-ID', uniqid())
    ->withContent('Updated content');

// Build complex response
Response::json([])
    ->withStatus(200)
    ->withHeaders([
        'X-RateLimit-Limit' => '100',
        'X-RateLimit-Remaining' => '95',
        'Cache-Control' => 'no-cache'
    ]);
```

## Complete Examples

### API Endpoint with Validation

```php
$router->post('/users', function(Request $request) {
    // Validate input
    try {
        $validated = $request->validated([
            'name' => 'required|min:3|max:50',
            'email' => 'required|email',
            'age' => 'required|integer|min:18'
        ]);
    } catch (ValidationException $e) {
        return Response::json([
            'success' => false,
            'errors' => $e->errors()
        ], 422);
    }
    
    // Create user
    $user = User::create($validated);
    
    // Return response
    return Response::json([
        'success' => true,
        'message' => 'User created',
        'user' => $user
    ], 201);
});
```

### Paginated API

```php
$router->get('/users', function(Request $request) {
    // Get pagination params
    $page = (int) $request->query('page', 1);
    $limit = (int) $request->query('limit', 10);
    
    // Get filter params
    $search = $request->query('search', '');
    $role = $request->query('role', '');
    
    // Fetch data
    $query = User::query();
    
    if ($search) {
        $query->where('name', 'LIKE', "%{$search}%");
    }
    
    if ($role) {
        $query->where('role', $role);
    }
    
    $total = $query->count();
    $users = $query->limit($limit)
                   ->offset(($page - 1) * $limit)
                   ->get();
    
    // Return paginated response
    return Response::json([
        'data' => $users,
        'pagination' => [
            'page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
});
```

### File Upload

```php
$router->post('/upload', function(Request $request) {
    // Validate file
    $file = $request->file('document');
    
    if (!$file) {
        return Response::json([
            'error' => 'No file uploaded'
        ], 400);
    }
    
    if (!$file->isValid()) {
        return Response::json([
            'error' => 'Invalid file'
        ], 400);
    }
    
    // Check file size (max 5MB)
    if ($file->getSize() > 5 * 1024 * 1024) {
        return Response::json([
            'error' => 'File too large (max 5MB)'
        ], 400);
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($file->getMimeType(), $allowedTypes)) {
        return Response::json([
            'error' => 'Invalid file type'
        ], 400);
    }
    
    // Store file
    $path = $file->store('uploads');
    
    return Response::json([
        'message' => 'File uploaded',
        'path' => $path,
        'name' => $file->getClientOriginalName(),
        'size' => $file->getSize()
    ], 201);
});
```

### Content Negotiation

```php
$router->get('/users/{id:int}', function(Request $request) {
    $id = $request->input('id');
    $user = User::find($id);
    
    if (!$user) {
        // Check what client expects
        if ($request->expectsJson()) {
            return Response::json(['error' => 'User not found'], 404);
        }
        
        return Response::html('<h1>User not found</h1>', 404);
    }
    
    // Return appropriate format
    if ($request->expectsJson()) {
        return Response::json($user);
    }
    
    return Response::html("<h1>{$user->name}</h1>");
});
```

### API with Headers

```php
$router->get('/api/data', function(Request $request) {
    // Check authentication
    $token = $request->header('Authorization');
    
    if (!$token) {
        return Response::json([
            'error' => 'Unauthorized'
        ], 401)
        ->withHeader('WWW-Authenticate', 'Bearer');
    }
    
    // Validate token
    if (!validateToken($token)) {
        return Response::json([
            'error' => 'Invalid token'
        ], 401);
    }
    
    // Get data
    $data = getData();
    
    // Return with custom headers
    return Response::json($data)
        ->withHeaders([
            'X-Total-Count' => count($data),
            'X-Response-Time' => '45ms',
            'Cache-Control' => 'public, max-age=3600'
        ]);
});
```

## Best Practices

### 1. Always Validate Input

```php
// Good
$validated = $request->validated($rules);

// Avoid
$data = $request->all(); // No validation!
```

### 2. Use Appropriate Status Codes

```php
// Good
Response::json($user, 201);        // Created
Response::json($data, 200);        // OK
Response::json(['error'], 404);    // Not Found
Response::noContent();             // 204 for DELETE

// Avoid
Response::json($user, 200);        // Should be 201 for creation
Response::json(['error'], 200);    // Errors should not be 200
```

### 3. Use Type Hints

```php
// Good
$page = (int) $request->query('page', 1);
$active = (bool) $request->input('active');

// Avoid
$page = $request->query('page', 1); // Might be string "1"
```

### 4. Check File Validity

```php
// Good
if ($file && $file->isValid()) {
    // Process file
}

// Avoid
$file->store('uploads'); // Might throw error
```

### 5. Handle Errors Gracefully

```php
// Good
try {
    $validated = $request->validated($rules);
} catch (ValidationException $e) {
    return Response::json(['errors' => $e->errors()], 422);
}

// Avoid
$validated = $request->validated($rules); // Unhandled exception
```

## See Also

- [Routing Guide](ROUTING.md)
- [Validation Guide](VALIDATION.md)
- [Examples](../examples/)

---

**Handle requests like a pro! 📥📤**
