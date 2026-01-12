<?php

declare(strict_types=1);

namespace SimpleRouter\Application;

use Closure;
use SimpleRouter\Application\Exceptions\ControllerNotFoundException;
use SimpleRouter\Application\Exceptions\MethodNotAllowedException;
use SimpleRouter\Application\Exceptions\RouteNotFoundException;
use SimpleRouter\Application\Http\Request;
use SimpleRouter\Application\Http\Response;
use SimpleRouter\Application\Middleware\MiddlewarePipeline;
use SimpleRouter\Domain\Contracts\Middleware;
use SimpleRouter\Domain\Entities\Route;
use SimpleRouter\Domain\Entities\RouteCollection;
use SimpleRouter\Domain\ValueObjects\HttpMethod;
use SimpleRouter\Domain\ValueObjects\Uri;

/**
 * Main router class with DDD principles
 */
final class Router
{
    private RouteCollection $routes;
    private string $groupPrefix = '';
    private array $groupMiddleware = [];
    private ?Route $currentRoute = null;

    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    /**
     * Register a GET route
     */
    public function get(string $uri, Closure|array $handler): Route
    {
        return $this->addRoute(HttpMethod::get(), $uri, $handler);
    }

    /**
     * Register a POST route
     */
    public function post(string $uri, Closure|array $handler): Route
    {
        return $this->addRoute(HttpMethod::post(), $uri, $handler);
    }

    /**
     * Register a PUT route
     */
    public function put(string $uri, Closure|array $handler): Route
    {
        return $this->addRoute(HttpMethod::put(), $uri, $handler);
    }

    /**
     * Register a PATCH route
     */
    public function patch(string $uri, Closure|array $handler): Route
    {
        return $this->addRoute(HttpMethod::patch(), $uri, $handler);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $uri, Closure|array $handler): Route
    {
        return $this->addRoute(HttpMethod::delete(), $uri, $handler);
    }

    /**
     * Register multiple routes with the same handler
     */
    public function match(array $methods, string $uri, Closure|array $handler): array
    {
        $routes = [];
        
        foreach ($methods as $method) {
            $routes[] = $this->addRoute(
                HttpMethod::from($method),
                $uri,
                $handler
            );
        }

        return $routes;
    }

    /**
     * Register a route for all HTTP methods
     */
    public function any(string $uri, Closure|array $handler): array
    {
        return $this->match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $handler);
    }

    /**
     * Create a route group with shared attributes
     */
    public function group(array $attributes, Closure $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        // Apply group prefix
        if (isset($attributes['prefix'])) {
            $this->groupPrefix = $this->normalizePrefix($previousPrefix . $attributes['prefix']);
        }

        // Apply group middleware
        if (isset($attributes['middleware'])) {
            $middleware = is_array($attributes['middleware']) 
                ? $attributes['middleware'] 
                : [$attributes['middleware']];
            
            $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        }

        // Execute the group callback
        $callback($this);

        // Restore previous state
        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    /**
     * Find route by name
     */
    public function route(string $name): ?string
    {
        return $this->routes->findByName($name);
    }

    /**
     * Dispatch the current request
     */
    public function dispatch(?Request $request = null): Response
    {
        $request = $request ?? Request::capture();
        
        try {
            $uri = Uri::from($request->path());
            $method = HttpMethod::from($request->method());

            $route = $this->routes->match($uri, $method);

            if ($route === null) {
                throw new RouteNotFoundException($uri->value(), $method->value());
            }

            // Extract and set route parameters
            if ($route->hasParameters()) {
                $parameters = $route->extractParameters($uri);
                $request->setRouteParameters($parameters);
            }

            // Execute middleware pipeline
            return $this->runMiddlewarePipeline($route, $request);

        } catch (RouteNotFoundException $e) {
            return $this->handleException($e);
        } catch (MethodNotAllowedException $e) {
            return $this->handleException($e);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        $response = $this->dispatch();
        $response->send();
    }

    private function addRoute(HttpMethod $method, string $uri, Closure|array $handler): Route
    {
        $fullUri = $this->groupPrefix . $this->normalizeUri($uri);
        
        $route = Route::create($method, $fullUri, $handler);

        // Apply group middleware
        if (!empty($this->groupMiddleware)) {
            $route->withMiddleware($this->groupMiddleware);
        }

        $this->routes->add($route);
        $this->currentRoute = $route;

        return $route;
    }

    private function runMiddlewarePipeline(Route $route, Request $request): Response
    {
        $pipeline = new MiddlewarePipeline();

        // Add route-specific middleware
        foreach ($route->middleware() as $middleware) {
            if (is_string($middleware)) {
                $middleware = new $middleware();
            }
            $pipeline->pipe($middleware);
        }

        // Execute the pipeline with the route handler as destination
        return $pipeline->process($request, function (Request $request) use ($route) {
            return $this->executeHandler($route, $request);
        });
    }

    private function executeHandler(Route $route, Request $request): Response
    {
        $handler = $route->handler();

        // Handle closure
        if ($handler instanceof Closure) {
            $result = $handler($request);
            return $this->normalizeResponse($result);
        }

        // Handle controller array [Controller::class, 'method']
        if (is_array($handler)) {
            [$controller, $method] = $handler;

            if (!class_exists($controller)) {
                throw new ControllerNotFoundException($controller);
            }

            $instance = new $controller();

            if (!method_exists($instance, $method)) {
                throw new ControllerNotFoundException($controller, $method);
            }

            $result = $instance->{$method}($request);
            return $this->normalizeResponse($result);
        }

        throw new \RuntimeException('Invalid route handler');
    }

    private function normalizeResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result) || is_object($result)) {
            return Response::json($result);
        }

        if (is_string($result)) {
            return Response::html($result);
        }

        if ($result === null) {
            return Response::noContent();
        }

        return Response::make((string) $result);
    }

    private function handleException(\Throwable $e): Response
    {
        $statusCode = method_exists($e, 'getCode') && $e->getCode() >= 400 && $e->getCode() < 600
            ? $e->getCode()
            : 500;

        return Response::json([
            'error' => true,
            'message' => $e->getMessage(),
            'code' => $statusCode
        ], $statusCode);
    }

    private function normalizeUri(string $uri): string
    {
        if (empty($uri)) {
            return '/';
        }

        if (!str_starts_with($uri, '/')) {
            $uri = '/' . $uri;
        }

        if (strlen($uri) > 1 && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }

    private function normalizePrefix(string $prefix): string
    {
        if (empty($prefix)) {
            return '';
        }

        $prefix = '/' . trim($prefix, '/');
        
        return $prefix === '/' ? '' : $prefix;
    }
}
