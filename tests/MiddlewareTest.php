<?php

declare(strict_types=1);

namespace SimpleRouter\Tests;

use Closure;
use PHPUnit\Framework\TestCase;
use SimpleRouter\Application\Router;
use SimpleRouter\Application\Http\{Request, Response};
use SimpleRouter\Domain\Contracts\Middleware;

/**
 * Middleware System Tests
 */
class MiddlewareTest extends TestCase
{
    public function testMiddlewareExecution(): void
    {
        $router = new Router();
        
        // Create test middleware
        $middleware = new class implements Middleware {
            public function handle(Request $request, Closure $next): Response
            {
                $response = $next($request);
                return $response->withHeader('X-Middleware', 'Executed');
            }
        };

        $router->get('/test', function(Request $request) {
            return Response::json(['message' => 'success']);
        })->withMiddleware($middleware);

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test']
        );

        $response = $router->dispatch($request);

        $this->assertEquals('Executed', $response->header('X-Middleware'));
    }

    public function testMultipleMiddleware(): void
    {
        $router = new Router();
        
        $middleware1 = new class implements Middleware {
            public function handle(Request $request, Closure $next): Response
            {
                $response = $next($request);
                return $response->withHeader('X-Middleware-1', 'First');
            }
        };

        $middleware2 = new class implements Middleware {
            public function handle(Request $request, Closure $next): Response
            {
                $response = $next($request);
                return $response->withHeader('X-Middleware-2', 'Second');
            }
        };

        $router->get('/test', function(Request $request) {
            return Response::json(['message' => 'success']);
        })->withMiddleware([$middleware1, $middleware2]);

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test']
        );

        $response = $router->dispatch($request);

        $this->assertEquals('First', $response->header('X-Middleware-1'));
        $this->assertEquals('Second', $response->header('X-Middleware-2'));
    }

    public function testMiddlewareCanModifyRequest(): void
    {
        $router = new Router();
        
        $middleware = new class implements Middleware {
            public function handle(Request $request, Closure $next): Response
            {
                // Add data to request
                $request->setRouteParameters(['middleware_added' => 'value']);
                return $next($request);
            }
        };

        $router->get('/test', function(Request $request) {
            return Response::json([
                'middleware_value' => $request->input('middleware_added')
            ]);
        })->withMiddleware($middleware);

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test']
        );

        $response = $router->dispatch($request);
        $data = json_decode($response->content(), true);

        $this->assertEquals('value', $data['middleware_value']);
    }

    public function testMiddlewareCanBlockRequest(): void
    {
        $router = new Router();
        
        $middleware = new class implements Middleware {
            public function handle(Request $request, Closure $next): Response
            {
                // Block request - don't call $next()
                return Response::json(['error' => 'Blocked by middleware'], 403);
            }
        };

        $router->get('/test', function(Request $request) {
            return Response::json(['message' => 'This should not be reached']);
        })->withMiddleware($middleware);

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test']
        );

        $response = $router->dispatch($request);

        $this->assertEquals(403, $response->status());
        $data = json_decode($response->content(), true);
        $this->assertEquals('Blocked by middleware', $data['error']);
    }

    public function testGroupMiddleware(): void
    {
        $router = new Router();
        
        $middleware = new class implements Middleware {
            public function handle(Request $request, Closure $next): Response
            {
                $response = $next($request);
                return $response->withHeader('X-Group-Middleware', 'Applied');
            }
        };

        $router->group([
            'prefix' => '/api',
            'middleware' => [$middleware]
        ], function($router) {
            $router->get('/users', function(Request $request) {
                return Response::json(['users' => []]);
            });
        });

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/users']
        );

        $response = $router->dispatch($request);

        $this->assertEquals('Applied', $response->header('X-Group-Middleware'));
    }

    public function testNestedGroupMiddleware(): void
    {
        $router = new Router();
        
        $middleware1 = new class implements Middleware {
            public function handle(Request $request, Closure $next): Response
            {
                $response = $next($request);
                return $response->withHeader('X-Outer', 'Outer');
            }
        };

        $middleware2 = new class implements Middleware {
            public function handle(Request $request, Closure $next): Response
            {
                $response = $next($request);
                return $response->withHeader('X-Inner', 'Inner');
            }
        };

        $router->group([
            'prefix' => '/api',
            'middleware' => [$middleware1]
        ], function($router) use ($middleware2) {
            $router->group([
                'prefix' => '/v1',
                'middleware' => [$middleware2]
            ], function($router) {
                $router->get('/users', function(Request $request) {
                    return Response::json(['users' => []]);
                });
            });
        });

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/v1/users']
        );

        $response = $router->dispatch($request);

        // Both middleware should be applied
        $this->assertEquals('Outer', $response->header('X-Outer'));
        $this->assertEquals('Inner', $response->header('X-Inner'));
    }

    public function testMiddlewareExecutionOrder(): void
    {
        $router = new Router();
        $executionOrder = [];
        
        $middleware1 = new class($executionOrder) implements Middleware {
            private array $order;
            
            public function __construct(array &$order) {
                $this->order = &$order;
            }
            
            public function handle(Request $request, Closure $next): Response
            {
                $this->order[] = 'before-1';
                $response = $next($request);
                $this->order[] = 'after-1';
                return $response;
            }
        };

        $middleware2 = new class($executionOrder) implements Middleware {
            private array $order;
            
            public function __construct(array &$order) {
                $this->order = &$order;
            }
            
            public function handle(Request $request, Closure $next): Response
            {
                $this->order[] = 'before-2';
                $response = $next($request);
                $this->order[] = 'after-2';
                return $response;
            }
        };

        $router->get('/test', function(Request $request) use (&$executionOrder) {
            $executionOrder[] = 'handler';
            return Response::json(['message' => 'success']);
        })->withMiddleware([$middleware1, $middleware2]);

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test']
        );

        $router->dispatch($request);

        // Expected order: before-1, before-2, handler, after-2, after-1
        $this->assertEquals([
            'before-1',
            'before-2',
            'handler',
            'after-2',
            'after-1'
        ], $executionOrder);
    }
}
