<?php

/**
 * Complete Usage Examples for SimpleRouter
 * 
 * This file demonstrates all features of the refactored router
 */

use SimpleRouter\Application\Router;
use SimpleRouter\Application\Http\Request;
use SimpleRouter\Application\Http\Response;
use SimpleRouter\Application\Middleware\{
    AuthMiddleware,
    CorsMiddleware,
    RateLimitMiddleware,
    JsonMiddleware,
    LoggingMiddleware
};

require 'vendor/autoload.php';

$router = new Router();

// ============================================================================
// EXAMPLE 1: Basic Routes
// ============================================================================

$router->get('/', function(Request $request) {
    return Response::html('<h1>Welcome to SimpleRouter!</h1>');
});

$router->get('/about', function(Request $request) {
    return Response::json([
        'version' => '2.0.0',
        'features' => ['validation', 'middleware', 'DDD', 'clean-code']
    ]);
});

// ============================================================================
// EXAMPLE 2: Routes with Parameters
// ============================================================================

// Integer parameter
$router->get('/users/{id:int}', function(Request $request) {
    $userId = $request->input('id');
    return Response::json([
        'user_id' => $userId,
        'type' => 'integer'
    ]);
});

// UUID parameter
$router->get('/posts/{uuid:uuid}', function(Request $request) {
    $postId = $request->input('uuid');
    return Response::json([
        'post_id' => $postId,
        'type' => 'uuid'
    ]);
});

// Slug parameter
$router->get('/blog/{slug:slug}', function(Request $request) {
    $slug = $request->input('slug');
    return Response::json([
        'slug' => $slug,
        'article' => "Article for: {$slug}"
    ]);
});

// Multiple parameters
$router->get('/categories/{category:slug}/products/{id:int}', function(Request $request) {
    return Response::json([
        'category' => $request->input('category'),
        'product_id' => $request->input('id')
    ]);
});

// ============================================================================
// EXAMPLE 3: Request Validation
// ============================================================================

$router->post('/register', function(Request $request) {
    // Validate with custom error messages
    $validated = $request->validated([
        'username' => 'required|alphanumeric|min:3|max:20|onError("Username must be 3-20 alphanumeric characters")',
        'email' => 'required|email|onError("Please provide a valid email address")',
        'password' => 'required|min:8|onError("Password must be at least 8 characters")',
        'age' => 'required|integer|min:18|onError("You must be 18 or older")',
        'country' => 'required|alpha|onError("Country must contain only letters")',
        'website' => 'url|onError("Please provide a valid URL")'
    ]);
    
    // If we reach here, all data is valid
    return Response::json([
        'message' => 'Registration successful',
        'user' => $validated
    ], 201);
});

// Validation without exceptions
$router->post('/contact', function(Request $request) {
    $result = $request->validate([
        'name' => 'required|min:3',
        'email' => 'required|email',
        'message' => 'required|min:10'
    ], throwOnFailure: false);
    
    if (!$result->isValid()) {
        return Response::json([
            'success' => false,
            'errors' => $result->errors()
        ], 422);
    }
    
    return Response::json([
        'success' => true,
        'message' => 'Message sent successfully'
    ]);
});

// ============================================================================
// EXAMPLE 4: Middleware Usage
// ============================================================================

// Single middleware
$router->get('/protected', function(Request $request) {
    return Response::json(['message' => 'This is a protected route']);
})->withMiddleware(AuthMiddleware::class);

// Multiple middleware
$router->post('/admin/users', function(Request $request) {
    return Response::json(['message' => 'User created']);
})->withMiddleware([
    AuthMiddleware::class,
    new RateLimitMiddleware(10, 1) // 10 requests per minute
]);

// ============================================================================
// EXAMPLE 5: Route Groups
// ============================================================================

// Group with prefix
$router->group(['prefix' => '/api'], function($router) {
    
    $router->get('/status', function(Request $request) {
        return Response::json(['status' => 'operational']);
    });
    
    $router->get('/version', function(Request $request) {
        return Response::json(['version' => '1.0.0']);
    });
});

// Group with middleware
$router->group([
    'middleware' => [new CorsMiddleware(['*'])]
], function($router) {
    
    $router->get('/public/posts', function(Request $request) {
        return Response::json(['posts' => []]);
    });
    
    $router->get('/public/users', function(Request $request) {
        return Response::json(['users' => []]);
    });
});

// Nested groups
$router->group(['prefix' => '/api'], function($router) {
    
    // API v1
    $router->group(['prefix' => '/v1'], function($router) {
        
        $router->get('/users', function(Request $request) {
            return Response::json([
                'version' => 'v1',
                'users' => ['Alice', 'Bob']
            ]);
        });
        
        $router->post('/users', function(Request $request) {
            $validated = $request->validated([
                'name' => 'required|min:3',
                'email' => 'required|email'
            ]);
            
            return Response::json([
                'version' => 'v1',
                'created' => $validated
            ], 201);
        });
    });
    
    // API v2 with authentication
    $router->group([
        'prefix' => '/v2',
        'middleware' => [AuthMiddleware::class]
    ], function($router) {
        
        $router->get('/users', function(Request $request) {
            return Response::json([
                'version' => 'v2',
                'users' => ['Alice', 'Bob', 'Charlie']
            ]);
        });
        
        $router->post('/users', function(Request $request) {
            $validated = $request->validated([
                'name' => 'required|min:3',
                'email' => 'required|email',
                'role' => 'required|in:admin,user,guest'
            ]);
            
            return Response::json([
                'version' => 'v2',
                'created' => $validated
            ], 201);
        });
    });
});

// ============================================================================
// EXAMPLE 6: Named Routes
// ============================================================================

$router->get('/dashboard', function(Request $request) {
    return Response::json(['page' => 'dashboard']);
})->withName('dashboard');

$router->get('/user/profile', function(Request $request) {
    return Response::json(['page' => 'profile']);
})->withName('user.profile');

$router->get('/admin/settings', function(Request $request) {
    return Response::json(['page' => 'settings']);
})->withName('admin.settings');

// Get route by name
$router->get('/routes', function(Request $request) use ($router) {
    return Response::json([
        'dashboard' => $router->route('dashboard'),
        'profile' => $router->route('user.profile'),
        'settings' => $router->route('admin.settings')
    ]);
});

// ============================================================================
// EXAMPLE 7: Complete RESTful API
// ============================================================================

$router->group([
    'prefix' => '/api/v1',
    'middleware' => [
        new CorsMiddleware(['*']),
        new RateLimitMiddleware(100, 1), // 100 requests per minute
        new LoggingMiddleware()
    ]
], function($router) {
    
    // Public endpoints
    $router->post('/auth/login', function(Request $request) {
        $validated = $request->validated([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        
        // Authenticate user logic here
        
        return Response::json([
            'token' => 'fake-jwt-token-here',
            'user' => [
                'email' => $validated['email']
            ]
        ]);
    });
    
    // Protected endpoints
    $router->group(['middleware' => [AuthMiddleware::class]], function($router) {
        
        // Users CRUD
        $router->get('/users', function(Request $request) {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            
            return Response::json([
                'data' => [],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => 0
                ]
            ]);
        })->withName('api.users.index');
        
        $router->get('/users/{id:int}', function(Request $request) {
            $id = $request->input('id');
            
            return Response::json([
                'id' => $id,
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ]);
        })->withName('api.users.show');
        
        $router->post('/users', function(Request $request) {
            $validated = $request->validated([
                'name' => 'required|alpha|min:3|max:50',
                'email' => 'required|email',
                'age' => 'integer|min:18|max:120',
                'role' => 'required|in:admin,user,guest'
            ]);
            
            return Response::json([
                'message' => 'User created',
                'user' => $validated
            ], 201);
        })->withName('api.users.store');
        
        $router->put('/users/{id:int}', function(Request $request) {
            $id = $request->input('id');
            
            $validated = $request->validated([
                'name' => 'alpha|min:3|max:50',
                'email' => 'email',
                'age' => 'integer|min:18|max:120'
            ]);
            
            return Response::json([
                'message' => 'User updated',
                'user' => array_merge(['id' => $id], $validated)
            ]);
        })->withName('api.users.update');
        
        $router->delete('/users/{id:int}', function(Request $request) {
            $id = $request->input('id');
            
            return Response::noContent();
        })->withName('api.users.destroy');
    });
});

// ============================================================================
// EXAMPLE 8: Different Response Types
// ============================================================================

$router->get('/html', function(Request $request) {
    return Response::html('<h1>HTML Response</h1><p>This is HTML content</p>');
});

$router->get('/json', function(Request $request) {
    return Response::json(['type' => 'json', 'message' => 'JSON response']);
});

$router->get('/redirect', function(Request $request) {
    return Response::redirect('/dashboard');
});

$router->get('/no-content', function(Request $request) {
    return Response::noContent();
});

$router->get('/custom', function(Request $request) {
    return Response::make('Custom content')
        ->withStatus(200)
        ->withHeader('X-Custom-Header', 'custom-value')
        ->withHeader('Cache-Control', 'no-cache');
});

// ============================================================================
// EXAMPLE 9: Request Data Handling
// ============================================================================

$router->post('/data-handling', function(Request $request) {
    return Response::json([
        // Get single input
        'title' => $request->input('title', 'Default Title'),
        
        // Get all inputs
        'all' => $request->all(),
        
        // Get only specific fields
        'only' => $request->only(['title', 'description']),
        
        // Get all except specific fields
        'except' => $request->except(['password', '_token']),
        
        // Check existence
        'has_title' => $request->has('title'),
        'filled_title' => $request->filled('title'),
        
        // Query parameters
        'page' => $request->query('page', 1),
        
        // Headers
        'user_agent' => $request->userAgent(),
        'ip' => $request->ip(),
        
        // Request info
        'method' => $request->method(),
        'path' => $request->path(),
        'is_json' => $request->isJson()
    ]);
});

// ============================================================================
// EXAMPLE 10: Error Handling
// ============================================================================

$router->get('/error-example', function(Request $request) {
    // Validation will throw ValidationException if it fails
    // The router will automatically catch it and return 422 response
    
    $validated = $request->validated([
        'email' => 'required|email|onError("Invalid email format")'
    ]);
    
    return Response::json(['validated' => $validated]);
});

// ============================================================================
// Run the application
// ============================================================================

$router->run();
