<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Middleware;

use Closure;
use SimpleRouter\Application\Http\Request;
use SimpleRouter\Application\Http\Response;
use SimpleRouter\Domain\Contracts\Middleware;

/**
 * Executes a chain of middleware
 */
final class MiddlewarePipeline
{
    /** @var array<Middleware|array> */
    private array $middleware = [];

    public function pipe(Middleware|array $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function process(Request $request, Closure $destination): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        return $pipeline($request);
    }

    private function carry(): Closure
    {
        return function (Closure $next, Middleware|array $middleware): Closure {
            return function (Request $request) use ($next, $middleware): Response {
                // Handle array notation [MiddlewareClass::class, 'method']
                if (is_array($middleware)) {
                    [$class, $method] = $middleware;
                    $instance = new $class();
                    return $instance->{$method}($request, $next);
                }

                return $middleware->handle($request, $next);
            };
        };
    }

    private function prepareDestination(Closure $destination): Closure
    {
        return function (Request $request) use ($destination): Response {
            return $destination($request);
        };
    }
}
