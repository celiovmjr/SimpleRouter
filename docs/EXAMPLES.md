# 💡 Examples

Practical examples for common use cases.

## Table of Contents

- [Basic Examples](#basic-examples)
- [Authentication](#authentication)
- [Database Operations](#database-operations)
- [File Uploads](#file-uploads)
- [API Examples](#api-examples)
- [Real-World Applications](#real-world-applications)

## Basic Examples

### Hello World

```php
<?php

require_once 'vendor/autoload.php';

use SimpleRouter\Application\Router;
use SimpleRouter\Application\Http\{Request, Response};

$router = new Router();

$router->get('/', function(Request $request) {
    return Response::html('<h1>Hello, World!</h1>');
});

$router->run();
```

### JSON API

```php
$router->get('/api/users', function(Request $request) {
    return Response::json([
        'users' => [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ]
    ]);
});
```

### Route Parameters

```php
$router->get('/users/{id:int}', function(Request $request) {
    $id = $request->input('id');
    
    return Response::json([
        'user' => [
            'id' => $id,
            'name' => 'User ' . $id
        ]
    ]);
});
```

## Authentication

### Simple Login

```php
$router->post('/login', function(Request $request) {
    $validated = $request->validated([
        'email' => 'required|email',
        'password' => 'required|min:8'
    ]);
    
    // Check credentials (example)
    $user = User::where('email', $validated['email'])->first();
    
    if (!$user || !password_verify($validated['password'], $user->password)) {
        return Response::json([
            'error' => 'Invalid credentials'
        ], 401);
    }
    
    // Generate session/token
    $_SESSION['user_id'] = $user->id;
    
    return Response::json([
        'message' => 'Login successful',
        'user' => $user
    ]);
});
```

### JWT Authentication

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Login - Generate JWT
$router->post('/auth/login', function(Request $request) {
    $validated = $request->validated([
        'email' => 'required|email',
        'password' => 'required'
    ]);
    
    $user = User::where('email', $validated['email'])->first();
    
    if (!$user || !password_verify($validated['password'], $user->password)) {
        return Response::json(['error' => 'Invalid credentials'], 401);
    }
    
    $payload = [
        'iss' => 'your-app',
        'sub' => $user->id,
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // 24 hours
    ];
    
    $jwt = JWT::encode($payload, 'your-secret-key', 'HS256');
    
    return Response::json([
        'token' => $jwt,
        'expires_in' => 86400
    ]);
});

// Protected Route with JWT
$router->get('/profile', function(Request $request) {
    $token = $request->header('Authorization');
    
    if (!$token || !str_starts_with($token, 'Bearer ')) {
        return Response::json(['error' => 'Unauthorized'], 401);
    }
    
    $token = substr($token, 7);
    
    try {
        $decoded = JWT::decode($token, new Key('your-secret-key', 'HS256'));
        $userId = $decoded->sub;
        
        $user = User::find($userId);
        
        return Response::json($user);
        
    } catch (\Exception $e) {
        return Response::json(['error' => 'Invalid token'], 401);
    }
});
```

### Registration

```php
$router->post('/register', function(Request $request) {
    $validated = $request->validated([
        'name' => 'required|alpha|min:3|max:50',
        'email' => 'required|email',
        'password' => 'required|min:8',
        'age' => 'required|integer|min:18'
    ]);
    
    // Check if email exists
    if (User::where('email', $validated['email'])->exists()) {
        return Response::json([
            'error' => 'Email already registered'
        ], 422);
    }
    
    // Create user
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => password_hash($validated['password'], PASSWORD_BCRYPT),
        'age' => $validated['age']
    ]);
    
    return Response::json([
        'message' => 'Registration successful',
        'user' => $user
    ], 201);
});
```

## Database Operations

### List with Pagination

```php
$router->get('/users', function(Request $request) {
    $page = (int) $request->query('page', 1);
    $limit = (int) $request->query('limit', 10);
    
    $offset = ($page - 1) * $limit;
    
    $users = DB::table('users')
        ->limit($limit)
        ->offset($offset)
        ->get();
    
    $total = DB::table('users')->count();
    
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

### Search and Filter

```php
$router->get('/products', function(Request $request) {
    $query = DB::table('products');
    
    // Search
    if ($search = $request->query('search')) {
        $query->where('name', 'LIKE', "%{$search}%");
    }
    
    // Filter by category
    if ($category = $request->query('category')) {
        $query->where('category', $category);
    }
    
    // Filter by price range
    if ($minPrice = $request->query('min_price')) {
        $query->where('price', '>=', $minPrice);
    }
    
    if ($maxPrice = $request->query('max_price')) {
        $query->where('price', '<=', $maxPrice);
    }
    
    // Sort
    $sortBy = $request->query('sort_by', 'name');
    $sortOrder = $request->query('sort_order', 'asc');
    $query->orderBy($sortBy, $sortOrder);
    
    $products = $query->get();
    
    return Response::json($products);
});
```

### Create with Validation

```php
$router->post('/products', function(Request $request) {
    $validated = $request->validated([
        'name' => 'required|min:3|max:100',
        'description' => 'required|max:500',
        'price' => 'required|numeric|min:0',
        'category' => 'required|in:electronics,clothing,books',
        'stock' => 'required|integer|min:0'
    ]);
    
    $product = DB::table('products')->insert($validated);
    
    return Response::json([
        'message' => 'Product created',
        'product' => $product
    ], 201);
});
```

### Update

```php
$router->put('/products/{id:int}', function(Request $request) {
    $id = $request->input('id');
    
    $validated = $request->validated([
        'name' => 'min:3|max:100',
        'price' => 'numeric|min:0',
        'stock' => 'integer|min:0'
    ]);
    
    $product = DB::table('products')->where('id', $id)->first();
    
    if (!$product) {
        return Response::json(['error' => 'Product not found'], 404);
    }
    
    DB::table('products')->where('id', $id)->update($validated);
    
    return Response::json([
        'message' => 'Product updated',
        'product' => $validated
    ]);
});
```

### Delete

```php
$router->delete('/products/{id:int}', function(Request $request) {
    $id = $request->input('id');
    
    $deleted = DB::table('products')->where('id', $id)->delete();
    
    if (!$deleted) {
        return Response::json(['error' => 'Product not found'], 404);
    }
    
    return Response::noContent();
});
```

## File Uploads

### Single File Upload

```php
$router->post('/upload', function(Request $request) {
    $file = $request->file('document');
    
    if (!$file || !$file->isValid()) {
        return Response::json(['error' => 'No valid file uploaded'], 400);
    }
    
    // Validate file
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file->getSize() > $maxSize) {
        return Response::json(['error' => 'File too large'], 400);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($file->getMimeType(), $allowedTypes)) {
        return Response::json(['error' => 'Invalid file type'], 400);
    }
    
    // Generate unique filename
    $extension = $file->getClientOriginalExtension();
    $filename = uniqid() . '.' . $extension;
    
    // Store file
    $path = 'uploads/' . $filename;
    $file->move('uploads', $filename);
    
    // Save to database
    DB::table('files')->insert([
        'filename' => $filename,
        'original_name' => $file->getClientOriginalName(),
        'path' => $path,
        'size' => $file->getSize(),
        'mime_type' => $file->getMimeType(),
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    return Response::json([
        'message' => 'File uploaded',
        'file' => [
            'filename' => $filename,
            'path' => $path
        ]
    ], 201);
});
```

### Multiple Files Upload

```php
$router->post('/upload-multiple', function(Request $request) {
    $files = $request->file('documents');
    
    if (!$files || !is_array($files)) {
        return Response::json(['error' => 'No files uploaded'], 400);
    }
    
    $uploaded = [];
    
    foreach ($files as $file) {
        if (!$file->isValid()) {
            continue;
        }
        
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move('uploads', $filename);
        
        $uploaded[] = [
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName()
        ];
    }
    
    return Response::json([
        'message' => count($uploaded) . ' files uploaded',
        'files' => $uploaded
    ], 201);
});
```

## API Examples

### RESTful API

```php
// List all
$router->get('/api/posts', function(Request $request) {
    $posts = Post::all();
    return Response::json($posts);
});

// Get one
$router->get('/api/posts/{id:int}', function(Request $request) {
    $post = Post::find($request->input('id'));
    
    if (!$post) {
        return Response::json(['error' => 'Post not found'], 404);
    }
    
    return Response::json($post);
});

// Create
$router->post('/api/posts', function(Request $request) {
    $validated = $request->validated([
        'title' => 'required|min:3|max:200',
        'content' => 'required',
        'status' => 'required|in:draft,published'
    ]);
    
    $post = Post::create($validated);
    
    return Response::json($post, 201);
});

// Update
$router->put('/api/posts/{id:int}', function(Request $request) {
    $post = Post::find($request->input('id'));
    
    if (!$post) {
        return Response::json(['error' => 'Post not found'], 404);
    }
    
    $validated = $request->validated([
        'title' => 'min:3|max:200',
        'content' => '',
        'status' => 'in:draft,published'
    ]);
    
    $post->update($validated);
    
    return Response::json($post);
});

// Delete
$router->delete('/api/posts/{id:int}', function(Request $request) {
    $post = Post::find($request->input('id'));
    
    if (!$post) {
        return Response::json(['error' => 'Post not found'], 404);
    }
    
    $post->delete();
    
    return Response::noContent();
});
```

### API with Middleware

```php
use SimpleRouter\Application\Middleware\Builtin\{
    CorsMiddleware,
    RateLimitMiddleware,
    LoggingMiddleware
};

$router->group([
    'prefix' => '/api/v1',
    'middleware' => [
        CorsMiddleware::allowAll(),
        RateLimitMiddleware::api(),
        LoggingMiddleware::api(__DIR__ . '/logs/api.log')
    ]
], function($router) {
    // All routes here have CORS, rate limiting, and logging
    
    $router->get('/users', function(Request $request) {
        return Response::json(User::all());
    });
});
```

## Real-World Applications

### Blog System

```php
// Public routes
$router->get('/blog', [BlogController::class, 'index']);
$router->get('/blog/{slug:slug}', [BlogController::class, 'show']);

// Admin routes
$router->group([
    'prefix' => '/admin/blog',
    'middleware' => [AuthMiddleware::class, RoleMiddleware::class]
], function($router) {
    $router->get('/', [BlogController::class, 'admin']);
    $router->post('/', [BlogController::class, 'store']);
    $router->put('/{id:int}', [BlogController::class, 'update']);
    $router->delete('/{id:int}', [BlogController::class, 'destroy']);
});
```

### E-commerce API

```php
$router->group(['prefix' => '/api'], function($router) {
    // Products
    $router->get('/products', [ProductController::class, 'index']);
    $router->get('/products/{id:int}', [ProductController::class, 'show']);
    
    // Cart (protected)
    $router->group(['middleware' => [AuthMiddleware::class]], function($router) {
        $router->get('/cart', [CartController::class, 'show']);
        $router->post('/cart/items', [CartController::class, 'addItem']);
        $router->delete('/cart/items/{id:int}', [CartController::class, 'removeItem']);
        
        // Checkout
        $router->post('/checkout', [OrderController::class, 'create']);
        $router->get('/orders', [OrderController::class, 'index']);
        $router->get('/orders/{id:int}', [OrderController::class, 'show']);
    });
});
```

## See Also

- [Routing Guide](ROUTING.md)
- [Validation Guide](VALIDATION.md)
- [Request & Response](REQUEST-RESPONSE.md)
- [Middleware Guide](MIDDLEWARE.md)

---

**Learn by example! 💡**
