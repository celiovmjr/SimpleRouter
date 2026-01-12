<?php

declare(strict_types=1);

namespace SimpleRouter\Tests;

use PHPUnit\Framework\TestCase;
use SimpleRouter\Application\Router;
use SimpleRouter\Application\Http\{Request, Response};

/**
 * Router Core Functionality Tests
 */
class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    public function testBasicGetRoute(): void
    {
        $this->router->get('/test', function(Request $request) {
            return Response::json(['message' => 'success']);
        });

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test'],
            query: [],
            body: [],
            files: []
        );

        $response = $this->router->dispatch($request);

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('success', $response->content());
    }

    public function testBasicPostRoute(): void
    {
        $this->router->post('/users', function(Request $request) {
            return Response::json(['created' => true], 201);
        });

        $request = new Request(
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/users'],
            query: [],
            body: ['name' => 'John'],
            files: []
        );

        $response = $this->router->dispatch($request);

        $this->assertEquals(201, $response->status());
    }

    public function testRouteWithIntParameter(): void
    {
        $this->router->get('/users/{id:int}', function(Request $request) {
            return Response::json(['id' => $request->input('id')]);
        });

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/users/123'],
            query: [],
            body: [],
            files: []
        );

        $response = $this->router->dispatch($request);

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('123', $response->content());
    }

    public function testRouteWithUuidParameter(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        
        $this->router->get('/posts/{uuid:uuid}', function(Request $request) {
            return Response::json(['uuid' => $request->input('uuid')]);
        });

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/posts/{$uuid}"],
            query: [],
            body: [],
            files: []
        );

        $response = $this->router->dispatch($request);

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString($uuid, $response->content());
    }

    public function testRouteNotFound(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/nonexistent'],
            query: [],
            body: [],
            files: []
        );

        $response = $this->router->dispatch($request);

        $this->assertEquals(404, $response->status());
    }

    public function testMethodNotAllowed(): void
    {
        $this->router->get('/test', function(Request $request) {
            return Response::json(['message' => 'GET only']);
        });

        $request = new Request(
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/test'],
            query: [],
            body: [],
            files: []
        );

        $response = $this->router->dispatch($request);

        $this->assertEquals(404, $response->status());
    }

    public function testRouteGroups(): void
    {
        $this->router->group(['prefix' => '/api'], function($router) {
            $router->get('/users', function(Request $request) {
                return Response::json(['route' => 'api.users']);
            });
        });

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/users'],
            query: [],
            body: [],
            files: []
        );

        $response = $this->router->dispatch($request);

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('api.users', $response->content());
    }

    public function testNestedRouteGroups(): void
    {
        $this->router->group(['prefix' => '/api'], function($router) {
            $router->group(['prefix' => '/v1'], function($router) {
                $router->get('/users', function(Request $request) {
                    return Response::json(['route' => 'api.v1.users']);
                });
            });
        });

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/v1/users'],
            query: [],
            body: [],
            files: []
        );

        $response = $this->router->dispatch($request);

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('api.v1.users', $response->content());
    }

    public function testNamedRoutes(): void
    {
        $this->router->get('/dashboard', function(Request $request) {
            return Response::json(['page' => 'dashboard']);
        })->withName('dashboard');

        $uri = $this->router->route('dashboard');

        $this->assertEquals('/dashboard', $uri);
    }

    public function testMultipleHttpMethods(): void
    {
        $this->router->match(['GET', 'POST'], '/test', function(Request $request) {
            return Response::json(['method' => $request->method()]);
        });

        $getRequest = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test']
        );
        $getResponse = $this->router->dispatch($getRequest);
        $this->assertEquals(200, $getResponse->status());

        // Reset router for second test
        $this->router = new Router();
        $this->router->match(['GET', 'POST'], '/test', function(Request $request) {
            return Response::json(['method' => $request->method()]);
        });

        $postRequest = new Request(
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/test']
        );
        $postResponse = $this->router->dispatch($postRequest);
        $this->assertEquals(200, $postResponse->status());
    }

    public function testControllerRoute(): void
    {
        // Mock controller
        $controller = new class {
            public function index(Request $request): Response {
                return Response::json(['controller' => 'called']);
            }
        };

        $this->router->get('/controller', [get_class($controller), 'index']);

        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/controller']
        );

        $response = $this->router->dispatch($request);

        $this->assertEquals(200, $response->status());
    }
}
