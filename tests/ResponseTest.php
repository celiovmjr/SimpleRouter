<?php

declare(strict_types=1);

namespace SimpleRouter\Tests;

use PHPUnit\Framework\TestCase;
use SimpleRouter\Application\Http\Response;

/**
 * HTTP Response Tests
 */
class ResponseTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $response = Response::make('Hello World');

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Hello World', $response->content());
    }

    public function testCreateResponseWithStatus(): void
    {
        $response = Response::make('Created', 201);

        $this->assertEquals(201, $response->status());
        $this->assertEquals('Created', $response->content());
    }

    public function testJsonResponse(): void
    {
        $data = ['message' => 'success', 'code' => 200];
        $response = Response::json($data);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('application/json; charset=UTF-8', $response->header('Content-Type'));
        
        $decoded = json_decode($response->content(), true);
        $this->assertEquals('success', $decoded['message']);
        $this->assertEquals(200, $decoded['code']);
    }

    public function testJsonResponseWithStatus(): void
    {
        $data = ['message' => 'created'];
        $response = Response::json($data, 201);

        $this->assertEquals(201, $response->status());
    }

    public function testHtmlResponse(): void
    {
        $html = '<h1>Hello World</h1>';
        $response = Response::html($html);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('text/html; charset=UTF-8', $response->header('Content-Type'));
        $this->assertEquals($html, $response->content());
    }

    public function testRedirectResponse(): void
    {
        $response = Response::redirect('/dashboard');

        $this->assertEquals(302, $response->status());
        $this->assertEquals('/dashboard', $response->header('Location'));
    }

    public function testRedirectResponseWithStatus(): void
    {
        $response = Response::redirect('/dashboard', 301);

        $this->assertEquals(301, $response->status());
        $this->assertEquals('/dashboard', $response->header('Location'));
    }

    public function testNoContentResponse(): void
    {
        $response = Response::noContent();

        $this->assertEquals(204, $response->status());
        $this->assertEquals('', $response->content());
    }

    public function testWithStatus(): void
    {
        $response = Response::make('OK')
            ->withStatus(201);

        $this->assertEquals(201, $response->status());
    }

    public function testWithHeader(): void
    {
        $response = Response::make('OK')
            ->withHeader('X-Custom-Header', 'CustomValue');

        $this->assertEquals('CustomValue', $response->header('X-Custom-Header'));
    }

    public function testWithHeaders(): void
    {
        $response = Response::make('OK')
            ->withHeaders([
                'X-Header-1' => 'Value1',
                'X-Header-2' => 'Value2'
            ]);

        $this->assertEquals('Value1', $response->header('X-Header-1'));
        $this->assertEquals('Value2', $response->header('X-Header-2'));
    }

    public function testWithContent(): void
    {
        $response = Response::make('Original')
            ->withContent('Updated');

        $this->assertEquals('Updated', $response->content());
    }

    public function testWithJson(): void
    {
        $data = ['key' => 'value'];
        $response = Response::make('')
            ->withJson($data);

        $this->assertEquals('application/json; charset=UTF-8', $response->header('Content-Type'));
        
        $decoded = json_decode($response->content(), true);
        $this->assertEquals('value', $decoded['key']);
    }

    public function testFluentInterface(): void
    {
        $response = Response::make('OK')
            ->withStatus(201)
            ->withHeader('X-Custom', 'Value')
            ->withContent('Created');

        $this->assertEquals(201, $response->status());
        $this->assertEquals('Value', $response->header('X-Custom'));
        $this->assertEquals('Created', $response->content());
    }

    public function testStatusText(): void
    {
        $response200 = Response::make('OK', 200);
        $this->assertEquals('OK', $response200->statusText());

        $response404 = Response::make('', 404);
        $this->assertEquals('Not Found', $response404->statusText());

        $response500 = Response::make('', 500);
        $this->assertEquals('Internal Server Error', $response500->statusText());
    }

    public function testHeadersMethod(): void
    {
        $response = Response::make('OK')
            ->withHeader('X-Header-1', 'Value1')
            ->withHeader('X-Header-2', 'Value2');

        $headers = $response->headers();

        $this->assertIsArray($headers);
        $this->assertArrayHasKey('X-Header-1', $headers);
        $this->assertArrayHasKey('X-Header-2', $headers);
    }

    public function testToString(): void
    {
        $response = Response::make('Hello World');

        $this->assertEquals('Hello World', (string) $response);
    }

    public function testJsonWithSpecialCharacters(): void
    {
        $data = [
            'message' => 'Olá Mundo',
            'special' => 'João & José'
        ];
        
        $response = Response::json($data);
        $content = $response->content();

        // Should not escape unicode or slashes
        $this->assertStringContainsString('Olá Mundo', $content);
        $this->assertStringContainsString('João & José', $content);
    }

    public function testImmutability(): void
    {
        $original = Response::make('Original');
        $modified = $original->withStatus(201);

        // Original should remain unchanged
        $this->assertEquals(200, $original->status());
        $this->assertEquals(201, $modified->status());
    }
}
