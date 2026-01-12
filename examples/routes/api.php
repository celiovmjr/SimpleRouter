<?php

/**
 * API Routes
 * 
 * Routes for API endpoints (JSON responses)
 * Typically uses token-based authentication (JWT)
 */

use SimpleRouter\Application\Router;
use SimpleRouter\Application\Http\{Request, Response};
use App\Controllers\Api\{
    AuthController,
    UserController,
    PostController,
    CommentController,
    CategoryController,
    ProductController,
    OrderController
};
use App\Middleware\{
    JwtAuthMiddleware,
    ApiKeyMiddleware,
    CorsMiddleware,
    RateLimitMiddleware,
    LoggingMiddleware,
    ContentNegotiationMiddleware
};

/** @var Router $router */

// ============================================================================
// API v1 - Public & Protected Routes
// ============================================================================

$router->group([
    'prefix' => '/api/v1',
    'middleware' => [
        new CorsMiddleware(['*']), // Allow all origins (configure as needed)
        new LoggingMiddleware(),
        new ContentNegotiationMiddleware()
    ]
], function($router) {
    
    // ========================================================================
    // Authentication Endpoints (Public)
    // ========================================================================
    
    $router->group([
        'prefix' => '/auth',
        'middleware' => [new RateLimitMiddleware(10, 1)] // 10 requests per minute
    ], function($router) {
        
        // Register new user
        $router->post('/register', [AuthController::class, 'register'])
            ->withName('api.auth.register');
        
        // Login
        $router->post('/login', [AuthController::class, 'login'])
            ->withName('api.auth.login');
        
        // Refresh token
        $router->post('/refresh', [AuthController::class, 'refresh'])
            ->withName('api.auth.refresh');
        
        // Forgot password
        $router->post('/forgot-password', [AuthController::class, 'forgotPassword'])
            ->withName('api.auth.forgot-password');
        
        // Reset password
        $router->post('/reset-password', [AuthController::class, 'resetPassword'])
            ->withName('api.auth.reset-password');
    });
    
    // ========================================================================
    // Public Endpoints (No Authentication)
    // ========================================================================
    
    $router->group([
        'prefix' => '/public',
        'middleware' => [new RateLimitMiddleware(100, 1)] // 100 requests per minute
    ], function($router) {
        
        // Health check
        $router->get('/health', function(Request $request) {
            return Response::json([
                'status' => 'healthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0.0'
            ]);
        })->withName('api.health');
        
        // Posts
        $router->group(['prefix' => '/posts'], function($router) {
            $router->get('/', [PostController::class, 'index'])
                ->withName('api.posts.index');
            
            $router->get('/{id:int}', [PostController::class, 'show'])
                ->withName('api.posts.show');
            
            $router->get('/{id:int}/comments', [CommentController::class, 'postComments'])
                ->withName('api.posts.comments');
        });
        
        // Categories
        $router->get('/categories', [CategoryController::class, 'index'])
            ->withName('api.categories.index');
        
        $router->get('/categories/{id:int}', [CategoryController::class, 'show'])
            ->withName('api.categories.show');
        
        // Products
        $router->group(['prefix' => '/products'], function($router) {
            $router->get('/', [ProductController::class, 'index'])
                ->withName('api.products.index');
            
            $router->get('/{id:int}', [ProductController::class, 'show'])
                ->withName('api.products.show');
            
            $router->get('/search', [ProductController::class, 'search'])
                ->withName('api.products.search');
            
            $router->get('/featured', [ProductController::class, 'featured'])
                ->withName('api.products.featured');
        });
    });
    
    // ========================================================================
    // Protected Endpoints (JWT Authentication Required)
    // ========================================================================
    
    $router->group([
        'middleware' => [
            JwtAuthMiddleware::class,
            new RateLimitMiddleware(200, 1) // 200 requests per minute for authenticated users
        ]
    ], function($router) {
        
        // Current user
        $router->get('/me', [UserController::class, 'me'])
            ->withName('api.me');
        
        $router->put('/me', [UserController::class, 'updateMe'])
            ->withName('api.me.update');
        
        $router->post('/me/avatar', [UserController::class, 'uploadAvatar'])
            ->withName('api.me.avatar');
        
        $router->post('/me/change-password', [UserController::class, 'changePassword'])
            ->withName('api.me.change-password');
        
        // Logout
        $router->post('/logout', [AuthController::class, 'logout'])
            ->withName('api.auth.logout');
        
        // Posts (authenticated actions)
        $router->group(['prefix' => '/posts'], function($router) {
            
            $router->post('/', [PostController::class, 'store'])
                ->withName('api.posts.store');
            
            $router->put('/{id:int}', [PostController::class, 'update'])
                ->withName('api.posts.update');
            
            $router->delete('/{id:int}', [PostController::class, 'destroy'])
                ->withName('api.posts.destroy');
            
            // Post likes
            $router->post('/{id:int}/like', [PostController::class, 'like'])
                ->withName('api.posts.like');
            
            $router->delete('/{id:int}/like', [PostController::class, 'unlike'])
                ->withName('api.posts.unlike');
            
            // Post comments
            $router->post('/{id:int}/comments', [CommentController::class, 'store'])
                ->withName('api.comments.store');
        });
        
        // Comments
        $router->group(['prefix' => '/comments'], function($router) {
            
            $router->put('/{id:int}', [CommentController::class, 'update'])
                ->withName('api.comments.update');
            
            $router->delete('/{id:int}', [CommentController::class, 'destroy'])
                ->withName('api.comments.destroy');
        });
        
        // User's orders
        $router->group(['prefix' => '/orders'], function($router) {
            
            $router->get('/', [OrderController::class, 'index'])
                ->withName('api.orders.index');
            
            $router->post('/', [OrderController::class, 'store'])
                ->withName('api.orders.store');
            
            $router->get('/{id:int}', [OrderController::class, 'show'])
                ->withName('api.orders.show');
            
            $router->post('/{id:int}/cancel', [OrderController::class, 'cancel'])
                ->withName('api.orders.cancel');
        });
    });
    
    // ========================================================================
    // Admin Endpoints (Admin Role Required)
    // ========================================================================
    
    $router->group([
        'prefix' => '/admin',
        'middleware' => [
            JwtAuthMiddleware::class,
            new RoleMiddleware(['admin', 'superadmin'])
        ]
    ], function($router) {
        
        // User management
        $router->group(['prefix' => '/users'], function($router) {
            
            $router->get('/', [UserController::class, 'index'])
                ->withName('api.admin.users.index');
            
            $router->get('/{id:int}', [UserController::class, 'show'])
                ->withName('api.admin.users.show');
            
            $router->put('/{id:int}', [UserController::class, 'update'])
                ->withName('api.admin.users.update');
            
            $router->delete('/{id:int}', [UserController::class, 'destroy'])
                ->withName('api.admin.users.destroy');
            
            $router->post('/{id:int}/ban', [UserController::class, 'ban'])
                ->withName('api.admin.users.ban');
            
            $router->post('/{id:int}/unban', [UserController::class, 'unban'])
                ->withName('api.admin.users.unban');
        });
        
        // Post management
        $router->group(['prefix' => '/posts'], function($router) {
            
            $router->get('/pending', [PostController::class, 'pending'])
                ->withName('api.admin.posts.pending');
            
            $router->post('/{id:int}/approve', [PostController::class, 'approve'])
                ->withName('api.admin.posts.approve');
            
            $router->post('/{id:int}/reject', [PostController::class, 'reject'])
                ->withName('api.admin.posts.reject');
        });
        
        // Statistics
        $router->get('/stats', function(Request $request) {
            return Response::json([
                'users' => ['total' => 1234, 'active' => 567],
                'posts' => ['total' => 5678, 'today' => 12],
                'orders' => ['total' => 890, 'pending' => 23]
            ]);
        })->withName('api.admin.stats');
    });
});

// ============================================================================
// API v2 - New Version (Example of versioning)
// ============================================================================

$router->group([
    'prefix' => '/api/v2',
    'middleware' => [
        new CorsMiddleware(['*']),
        new LoggingMiddleware(),
        ApiKeyMiddleware::class // Requires API key for v2
    ]
], function($router) {
    
    $router->get('/users', [UserControllerV2::class, 'index'])
        ->withName('api.v2.users.index');
    
    $router->get('/users/{id:int}', [UserControllerV2::class, 'show'])
        ->withName('api.v2.users.show');
    
    // Add v2-specific endpoints here
});

// ============================================================================
// Webhooks (No Authentication, Signature Verification in Controller)
// ============================================================================

$router->group(['prefix' => '/webhooks'], function($router) {
    
    $router->post('/stripe', [WebhookController::class, 'stripe'])
        ->withName('webhooks.stripe');
    
    $router->post('/paypal', [WebhookController::class, 'paypal'])
        ->withName('webhooks.paypal');
    
    $router->post('/github', [WebhookController::class, 'github'])
        ->withName('webhooks.github');
});
