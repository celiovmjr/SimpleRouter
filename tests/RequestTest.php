<?php

declare(strict_types=1);

namespace SimpleRouter\Tests;

use PHPUnit\Framework\TestCase;
use SimpleRouter\Application\Http\Request;

/**
 * HTTP Request Tests
 */
class RequestTest extends TestCase
{
    public function testCreateRequest(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test'],
            query: [],
            body: [],
            files: []
        );

        $this->assertEquals('GET', $request->method());
        $this->assertEquals('/test', $request->uri());
    }

    public function testGetMethod(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );

        $this->assertEquals('GET', $request->method());
        $this->assertTrue($request->isMethod('GET'));
        $this->assertFalse($request->isMethod('POST'));
    }

    public function testPostMethod(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/']
        );

        $this->assertEquals('POST', $request->method());
        $this->assertTrue($request->isMethod('POST'));
    }

    public function testQueryParameters(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test'],
            query: ['page' => '1', 'limit' => '10']
        );

        $this->assertEquals('1', $request->query('page'));
        $this->assertEquals('10', $request->query('limit'));
        $this->assertEquals('default', $request->query('nonexistent', 'default'));
    }

    public function testBodyData(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/'],
            query: [],
            body: ['name' => 'John', 'email' => 'john@example.com']
        );

        $this->assertEquals('John', $request->input('name'));
        $this->assertEquals('john@example.com', $request->input('email'));
    }

    public function testInputWithDefault(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']
        );

        $this->assertEquals('default', $request->input('nonexistent', 'default'));
    }

    public function testAllInputs(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/'],
            query: ['page' => '1'],
            body: ['name' => 'John']
        );

        $all = $request->all();

        $this->assertArrayHasKey('page', $all);
        $this->assertArrayHasKey('name', $all);
        $this->assertEquals('1', $all['page']);
        $this->assertEquals('John', $all['name']);
    }

    public function testOnlyMethod(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/'],
            query: [],
            body: ['name' => 'John', 'email' => 'john@example.com', 'password' => 'secret']
        );

        $only = $request->only(['name', 'email']);

        $this->assertArrayHasKey('name', $only);
        $this->assertArrayHasKey('email', $only);
        $this->assertArrayNotHasKey('password', $only);
    }

    public function testExceptMethod(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/'],
            query: [],
            body: ['name' => 'John', 'email' => 'john@example.com', 'password' => 'secret']
        );

        $except = $request->except(['password']);

        $this->assertArrayHasKey('name', $except);
        $this->assertArrayHasKey('email', $except);
        $this->assertArrayNotHasKey('password', $except);
    }

    public function testHasMethod(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/'],
            query: [],
            body: ['name' => 'John', 'email' => '']
        );

        $this->assertTrue($request->has('name'));
        $this->assertTrue($request->has('email'));
        $this->assertFalse($request->has('nonexistent'));
    }

    public function testFilledMethod(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/'],
            query: [],
            body: ['name' => 'John', 'email' => '', 'age' => '0']
        );

        $this->assertTrue($request->filled('name'));
        $this->assertFalse($request->filled('email'));
        $this->assertTrue($request->filled('age')); // 0 is filled
    }

    public function testHeaders(): void
    {
        $request = new Request(
            server: [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer token123'
            ]
        );

        $this->assertEquals('application/json', $request->header('Content-Type'));
        $this->assertEquals('Bearer token123', $request->header('Authorization'));
        $this->assertEquals('default', $request->header('Nonexistent', 'default'));
    }

    public function testUserAgent(): void
    {
        $request = new Request(
            server: [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'HTTP_USER_AGENT' => 'Mozilla/5.0'
            ]
        );

        $this->assertEquals('Mozilla/5.0', $request->userAgent());
    }

    public function testIpAddress(): void
    {
        $request = new Request(
            server: [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'REMOTE_ADDR' => '127.0.0.1'
            ]
        );

        $this->assertEquals('127.0.0.1', $request->ip());
    }

    public function testPathMethod(): void
    {
        $request = new Request(
            server: [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/users/123?page=1'
            ]
        );

        $this->assertEquals('/users/123', $request->path());
    }

    public function testIsJsonMethod(): void
    {
        $jsonRequest = new Request(
            server: [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/',
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $this->assertTrue($jsonRequest->isJson());

        $htmlRequest = new Request(
            server: [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/',
                'CONTENT_TYPE' => 'text/html'
            ]
        );

        $this->assertFalse($htmlRequest->isJson());
    }

    public function testExpectsJsonMethod(): void
    {
        $request = new Request(
            server: [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'HTTP_ACCEPT' => 'application/json'
            ]
        );

        $this->assertTrue($request->expectsJson());
    }

    public function testSetRouteParameters(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/users/123']
        );

        $request->setRouteParameters(['id' => '123', 'type' => 'user']);

        $this->assertEquals('123', $request->routeParameter('id'));
        $this->assertEquals('user', $request->routeParameter('type'));
        $this->assertEquals('default', $request->routeParameter('nonexistent', 'default'));
    }

    public function testRouteParametersInInput(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/users/123'],
            query: ['page' => '1']
        );

        $request->setRouteParameters(['id' => '123']);

        // Route parameters should be accessible via input()
        $this->assertEquals('123', $request->input('id'));
        $this->assertEquals('1', $request->input('page'));
    }
}
