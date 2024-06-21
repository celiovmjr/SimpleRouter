# SimpleRouter

SimpleRouter is a PHP class for managing routes in a PHP web application in a simple and effective manner.

## Installation

To use SimpleRouter, simply include the `Router.php` file in your PHP project and configure the appropriate autoloading.

```php
<?php
require_once 'Router.php';
```

## Example Usage

### Initial Setup

```php
use SimpleRouter\Application\Router;

$router = new Router();
```

### Defining Routes

#### Basic Routes

```php
$router->get('/', function($request) {
    echo "Hello, GET request!";
});

$router->post('/post', [MyController::class, 'postMethod']);
```

#### Route Groups and Subgroups

```php
$router->group('/api', function() use ($router) {
    // Subgroup '/v2'
    $router->subgroup('/v2', function() use ($router) {
        $router->get('/users', [UserController::class, 'getAll']);
        $router->post('/users', [UserController::class, 'create']);
    });

    // You can define more routes directly within the '/api' group
    $router->get('/status', function($request) {
        echo "API Status";
    });
});
```

#### Naming Routes

```php
$router->group('/admin', function() use ($router) {
    $router->get('/dashboard', function($request) {
        echo "Welcome to Admin Dashboard!";
    })->name('admin.dashboard');
});

// Resolving a named route
$route = $router->route('admin.dashboard'); // Returns '/admin/dashboard'
```

### Running the Application

```php
if ($router->dispatch()) {
    // Route found and processed successfully
} else {
    // Error handling
    $error = $router->error();
    echo "Error {$error->code}: {$error->message}";
}
```

## Available Methods

- **get($uri, $handler)**: Defines a GET route.
- **post($uri, $handler)**: Defines a POST route.
- **put($uri, $handler)**: Defines a PUT route.
- **patch($uri, $handler)**: Defines a PATCH route.
- **delete($uri, $handler)**: Defines a DELETE route.
- **group($prefix, $handler)**: Defines a route group with a prefix.
- **subgroup($prefix, $handler)**: Defines a subgroup of routes within an existing group.
- **dispatch()**: Executes routing and processes the corresponding route.
- **route($name)**: Returns the URI associated with a named route.
- **error()**: Returns an stdClass object with error information, if any.

### Benefits

- **Modular Routing**: Easily organize routes into groups and subgroups to maintain a structured application architecture.
- **Flexible Routing**: Supports dynamic and static routes, closures, and controller methods.
- **Error Handling**: Provides error handling for route dispatching failures.

---

This README provides a comprehensive overview of how to use the SimpleRouter class to manage routes in a PHP web application. Customize and expand upon it as needed to fit your specific project requirements, including additional configuration details, advanced usage examples, or server setup instructions.
