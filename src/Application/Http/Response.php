<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Http;

/**
 * Represents an HTTP response
 */
final class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private mixed $content = '';

    private const STATUS_TEXTS = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];

    public function __construct(
        mixed $content = '',
        int $status = 200,
        array $headers = []
    ) {
        $this->content = $content;
        $this->statusCode = $status;
        $this->headers = $headers;
    }

    public static function make(
        mixed $content = '',
        int $status = 200,
        array $headers = []
    ): self {
        return new self($content, $status, $headers);
    }

    public static function json(
        mixed $data,
        int $status = 200,
        array $headers = []
    ): self {
        $response = new self('', $status, $headers);
        return $response->withJson($data);
    }

    public static function html(
        string $content,
        int $status = 200,
        array $headers = []
    ): self {
        $response = new self($content, $status, $headers);
        return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public static function redirect(
        string $url,
        int $status = 302
    ): self {
        return new self('', $status, ['Location' => $url]);
    }

    public static function noContent(): self
    {
        return new self('', 204);
    }

    public function withStatus(int $code): self
    {
        $new = clone $this;
        $new->statusCode = $code;
        return $new;
    }

    public function withHeader(string $name, string $value): self
    {
        $new = clone $this;
        $new->headers[$name] = $value;
        return $new;
    }

    public function withHeaders(array $headers): self
    {
        $new = clone $this;
        $new->headers = array_merge($new->headers, $headers);
        return $new;
    }

    public function withContent(mixed $content): self
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    public function withJson(mixed $data, int $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES): self
    {
        $json = json_encode($data, $options);
        
        if ($json === false) {
            throw new \RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }

        $new = clone $this;
        $new->content = $json;
        $new->headers['Content-Type'] = 'application/json; charset=UTF-8';
        
        return $new;
    }

    public function status(): int
    {
        return $this->statusCode;
    }

    public function statusText(): string
    {
        return self::STATUS_TEXTS[$this->statusCode] ?? 'Unknown';
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function content(): mixed
    {
        return $this->content;
    }

    public function send(): void
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    private function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
    }

    private function sendContent(): void
    {
        echo $this->content;
    }

    public function __toString(): string
    {
        return (string) $this->content;
    }
}
