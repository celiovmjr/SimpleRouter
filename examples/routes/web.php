<?php

/**
 * Web Routes
 * 
 * Routes for web pages (HTML responses)
 * Typically uses session-based authentication
 */

use SimpleRouter\Application\Router;
use SimpleRouter\Application\Http\{Request, Response};
use App\Controllers\Web\{
    HomeController,
    AuthController,
    DashboardController,
    ProfileController,
    PostController
};
use App\Middleware\{SessionAuthMiddleware, CsrfMiddleware};

/** @var Router $router */

// ============================================================================
// Public Web Routes
// ============================================================================

$router->get('/', [HomeController::class, 'index'])
    ->withName('home');

$router->get('/about', [HomeController::class, 'about'])
    ->withName('about');

$router->get('/contact', [HomeController::class, 'contact'])
    ->withName('contact');

$router->post('/contact', [HomeController::class, 'submitContact'])
    ->withMiddleware([CsrfMiddleware::class])
    ->withName('contact.submit');

// ============================================================================
// Blog Routes (Public)
// ============================================================================

$router->group(['prefix' => '/blog'], function($router) {
    
    $router->get('/', [PostController::class, 'index'])
        ->withName('blog.index');
    
    $router->get('/{slug:slug}', [PostController::class, 'show'])
        ->withName('blog.show');
    
    $router->get('/category/{category:slug}', [PostController::class, 'category'])
        ->withName('blog.category');
    
    $router->get('/tag/{tag:slug}', [PostController::class, 'tag'])
        ->withName('blog.tag');
});

// ============================================================================
// Authentication Routes
// ============================================================================

$router->group(['prefix' => '/auth'], function($router) {
    
    // Show login form
    $router->get('/login', [AuthController::class, 'showLogin'])
        ->withName('auth.login');
    
    // Process login
    $router->post('/login', [AuthController::class, 'login'])
        ->withMiddleware([CsrfMiddleware::class])
        ->withName('auth.login.submit');
    
    // Show registration form
    $router->get('/register', [AuthController::class, 'showRegister'])
        ->withName('auth.register');
    
    // Process registration
    $router->post('/register', [AuthController::class, 'register'])
        ->withMiddleware([CsrfMiddleware::class])
        ->withName('auth.register.submit');
    
    // Forgot password
    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword'])
        ->withName('auth.forgot-password');
    
    $router->post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->withMiddleware([CsrfMiddleware::class])
        ->withName('auth.forgot-password.submit');
    
    // Reset password
    $router->get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
        ->withName('auth.reset-password');
    
    $router->post('/reset-password', [AuthController::class, 'resetPassword'])
        ->withMiddleware([CsrfMiddleware::class])
        ->withName('auth.reset-password.submit');
    
    // Logout
    $router->post('/logout', [AuthController::class, 'logout'])
        ->withMiddleware([SessionAuthMiddleware::class, CsrfMiddleware::class])
        ->withName('auth.logout');
});

// ============================================================================
// Protected Web Routes (Authenticated Users)
// ============================================================================

$router->group([
    'middleware' => [SessionAuthMiddleware::class]
], function($router) {
    
    // Dashboard
    $router->get('/dashboard', [DashboardController::class, 'index'])
        ->withName('dashboard');
    
    // Profile routes
    $router->group(['prefix' => '/profile'], function($router) {
        
        $router->get('/', [ProfileController::class, 'show'])
            ->withName('profile.show');
        
        $router->get('/edit', [ProfileController::class, 'edit'])
            ->withName('profile.edit');
        
        $router->post('/update', [ProfileController::class, 'update'])
            ->withMiddleware([CsrfMiddleware::class])
            ->withName('profile.update');
        
        $router->post('/avatar', [ProfileController::class, 'uploadAvatar'])
            ->withMiddleware([CsrfMiddleware::class])
            ->withName('profile.avatar');
        
        $router->get('/settings', [ProfileController::class, 'settings'])
            ->withName('profile.settings');
        
        $router->post('/settings', [ProfileController::class, 'updateSettings'])
            ->withMiddleware([CsrfMiddleware::class])
            ->withName('profile.settings.update');
    });
    
    // User's posts
    $router->group(['prefix' => '/my-posts'], function($router) {
        
        $router->get('/', [PostController::class, 'myPosts'])
            ->withName('posts.mine');
        
        $router->get('/create', [PostController::class, 'create'])
            ->withName('posts.create');
        
        $router->post('/create', [PostController::class, 'store'])
            ->withMiddleware([CsrfMiddleware::class])
            ->withName('posts.store');
        
        $router->get('/{id:int}/edit', [PostController::class, 'edit'])
            ->withName('posts.edit');
        
        $router->post('/{id:int}/update', [PostController::class, 'update'])
            ->withMiddleware([CsrfMiddleware::class])
            ->withName('posts.update');
        
        $router->post('/{id:int}/delete', [PostController::class, 'delete'])
            ->withMiddleware([CsrfMiddleware::class])
            ->withName('posts.delete');
    });
});

// ============================================================================
// Admin Routes (Admin Users Only)
// ============================================================================

$router->group([
    'prefix' => '/admin',
    'middleware' => [
        SessionAuthMiddleware::class,
        new RoleMiddleware(['admin', 'superadmin'])
    ]
], function($router) {
    
    $router->get('/dashboard', [AdminDashboardController::class, 'index'])
        ->withName('admin.dashboard');
    
    // User management
    $router->group(['prefix' => '/users'], function($router) {
        $router->get('/', [AdminUserController::class, 'index'])
            ->withName('admin.users');
        
        $router->get('/{id:int}', [AdminUserController::class, 'show'])
            ->withName('admin.users.show');
        
        $router->post('/{id:int}/ban', [AdminUserController::class, 'ban'])
            ->withMiddleware([CsrfMiddleware::class])
            ->withName('admin.users.ban');
        
        $router->post('/{id:int}/unban', [AdminUserController::class, 'unban'])
            ->withMiddleware([CsrfMiddleware::class])
            ->withName('admin.users.unban');
    });
    
    // Content management
    $router->group(['prefix' => '/posts'], function($router) {
        $router->get('/', [AdminPostController::class, 'index'])
            ->withName('admin.posts');
        
        $router->post('/{id:int}/approve', [AdminPostController::class, 'approve'])
            ->withMiddleware([CsrfMiddleware::class])
            ->withName('admin.posts.approve');
        
        $router->post('/{id:int}/reject', [AdminPostController::class, 'reject'])
            ->withMiddleware([CsrfMiddleware::class])
            ->withName('admin.posts.reject');
    });
    
    // Settings
    $router->get('/settings', [AdminSettingsController::class, 'index'])
        ->withName('admin.settings');
    
    $router->post('/settings', [AdminSettingsController::class, 'update'])
        ->withMiddleware([CsrfMiddleware::class])
        ->withName('admin.settings.update');
});
