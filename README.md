# SimpleRouter

SimpleRouter is a PHP class for managing routes in a PHP web application in a simple and effective manner.

## Installation

To use SimpleRouter, simply include the `Router.php` file in your PHP project and configure the appropriate autoloading.

```php
<?php
require_once 'Router.php';
```

## Usage Example

### Initial Setup

```php
use SimpleRouter\Application\Router;

$router = new Router();
```

### Route Definition

#### GET and POST Routes

```php
$router->get('/', function($request) {
    echo "Hello, GET request!";
});

$router->post('/post', [MyController::class, 'postMethod']);
```

#### Defining a Route Group

```php
$router->group('/api', function() use ($router) {
    $router->get('/users', [UserController::class, 'getAll']);
    $router->post('/users', [UserController::class, 'create']);
});
```

#### Naming Routes

```php
$router->group('/admin', function() use ($router) {
    $router->get('/dashboard', function($request) {
        echo "Welcome to Admin Dashboard!";
    })->name('admin.dashboard');
});
```

### Resolving Named Routes

```php
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

Feel free to expand this README with additional details such as server requirements, advanced usage examples, or any specific configurations required for your project.
