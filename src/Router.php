<?php

declare(strict_types=1);

namespace SimpleRouter\Application;

use Closure;
use InvalidArgumentException;
use stdClass;

class Router
{
    use RouterTrait;

    private ?string $path = null;
    private ?string $group = null;
    private ?string $lastGroup = null;
    private ?string $method = null;
    private array $routes = [];
    private array $names = [];
    private ?stdClass $error = null;

    public function __construct(
        private Request $request = new Request(),
        private Response $response = new Response()
    ) {}

    public function get(string $router, array|Closure $arguments): self
    {
        $this->method = strtoupper(__FUNCTION__);
        $this->addRoute(
            method: "GET",
            uri: $router,
            handler: $arguments
        );
        return $this;
    }

    public function post(string $router, array|Closure $arguments): self
    {
        $this->method = strtoupper(__FUNCTION__);
        $this->addRoute(
            method: "POST",
            uri: $router,
            handler: $arguments
        );
        return $this;
    }

    public function put(string $router, array|Closure $arguments): self
    {
        $this->method = strtoupper(__FUNCTION__);
        $this->addRoute(
            method: "PUT",
            uri: $router,
            handler: $arguments
        );
        return $this;
    }

    public function patch(string $router, array|Closure $arguments): self
    {
        $this->method = strtoupper(__FUNCTION__);
        $this->addRoute(
            method: "PATCH",
            uri: $router,
            handler: $arguments
        );
        return $this;
    }

    public function delete(string $router, array|Closure $arguments): self
    {
        $this->method = strtoupper(__FUNCTION__);
        $this->addRoute(
            method: "DELETE",
            uri: $router,
            handler: $arguments
        );
        return $this;
    }

    public function name(string $name): self
    {
        if (empty($this->group)) {
            throw new InvalidArgumentException('Cannot name a route outside of a group.');
        }

        $keys = array_keys($this->routes[$this->method]);
        $uri = end($keys);
        $this->names[$this->group][$name] = $uri;

        return $this;
    }

    public function route(string $name): ?string
    {
        foreach ($this->names as $routes) {
            if (empty($routes[$name])) {
                continue;
            }

            return $routes[$name];
        }
        return null;
    }

    public function path(string $path): void
    {
        if (empty($path)) {
            $path = "/";
        }

        if (substr($path, 0, 1) !== "/") {
            $path = "/" . $path;
        }

        if (substr($path, -1) === "/" && strlen($path) > 1) {
            $path = substr($path, 0, -1);
        }

        $this->path = $path;
    }

    public function group(?string $prefix, Closure $handler): void
    {
        if (empty($prefix)) {
            $prefix = "/";
        }

        if (substr($prefix, 0, 1) !== "/") {
            $prefix = "/" . $prefix;
        }

        if (substr($prefix, -1) === "/" && strlen($prefix) > 1) {
            $prefix = substr($prefix, 0, -1);
        }

        $this->group = $prefix;
        $this->lastGroup = $this->group;

        if ($handler instanceof Closure) {
            $handler();
        }
    }

    public function subgroup(?string $prefix, Closure $handler): void
    {
        if (empty($this->group)) {
            $this->handlerError("The group has not been defined.", 0);
            return;
        }

        if ($this->group !== $this->lastGroup) {
            $this->group = $this->lastGroup;
        }

        $this->group = str_replace("//", "/", ($this->group .= $prefix));

        if ($handler instanceof Closure) {
            $handler();
        }
    }

    public function dispatch(): bool
    {
        $method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_SPECIAL_CHARS);
        if (! array_key_exists(strtoupper($this->request->getMethod()), $this->routes)) {
            $this->handlerError("Method Not Allowed", 405);
            return false;
        }

        foreach ($this->routes[strtoupper($this->request->getMethod())] as $uri => $router) {
            if (! $this->uriExists($uri)) {
                continue;
            }

            $this->setParameters($uri, $router);
            return $this->run($router);
        }

        $this->handlerError("Not Found", 404);
        return false;
    }

    public function error(): ?stdClass
    {
        return $this->error;
    }

    private function addRoute(string $method, string $uri, array|Closure $handler, ?string $name = null): void
    {
        preg_match_all('/\{([^}]*)\}/', $uri, $matches);

        if (! empty($matches)) {
            foreach ($matches[1] as $values) {
                $parameters[strstr($values, ":", true)] = substr(strstr($values, ':'), 1);
            }
        }

        if (is_array($handler)) {
            $handler = [
                "controller" => $handler[0],
                "method" => $handler[1]
            ];
        }

        $this->routes[$method][$this->traitUri($uri, $this->path, $this->group)] = [
            "action" => $handler,
            "parameters" => $parameters ?? []
        ];

        if (!empty($this->group) && $name) {
            $this->names[$this->group][$name] = $this->traitUri($uri, $this->path, $this->group);
        }
    }

    private function run(array $router): bool
    {
        if (is_callable($router["action"])) {
            call_user_func($router["action"], $this->request);
            return true;
        }

        if (! $router["action"]["controller"] || ! $router["action"]["method"]) {
            $this->handlerError("Bad request", 400);
            return false;
        }
        
        $controller = $router["action"]["controller"];
        $method = $router["action"]["method"];

        if (! class_exists($controller)) {
            $this->handlerError("Not Found", 404);
            return false;
        }

        if (! method_exists($controller, $method)) {
            $this->handlerError("Not Implemented", 501);
            return false;
        }

        (new $controller())->{$method}($this->request, $this->response);
        return true;
    }
    private function handlerError(string $message, int $code): void
    {
        $this->error = new stdClass();
        $this->error->message = $message;
        $this->error->code = $code;
    }
}
