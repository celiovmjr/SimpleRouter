<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Http;

use SimpleRouter\Application\Validation\RequestValidator;
use SimpleRouter\Application\Validation\ValidationResult;
use SimpleRouter\Application\Exceptions\ValidationException;

/**
 * HTTP Request
 *
 * Immutable HTTP request representation with safe lifecycle,
 * single-pass body parsing and stable public API.
 *
 * PHP 8.4
 */
final class Request
{
    private string $uri;
    private string $method;
    private array $headers = [];
    private array $query = [];
    private array $body = [];
    private array $files = [];
    private array $server = [];
    private array $routeParameters = [];
    private string $rawBody = '';

    public function __construct(
        ?array $server = null,
        ?array $query = null,
        ?array $body = null,
        ?array $files = null
    ) {
        $this->server = $server ?? $_SERVER;
        $this->query  = $query ?? $_GET;
        $this->files  = $files ?? $_FILES;

        $this->initialize();

        $this->rawBody = file_get_contents('php://input') ?: '';
        $this->body = $body ?? $this->parseBody();
    }

    public static function capture(): self
    {
        return new self();
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function path(): string
    {
        return parse_url($this->uri, PHP_URL_PATH) ?? '/';
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null
            ? $this->query
            : $this->query[$key] ?? $default;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $data = array_merge($this->query, $this->body, $this->routeParameters);

        return $key === null
            ? $data
            : $data[$key] ?? $default;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->input($key, $default);
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body, $this->routeParameters);
    }

    public function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            if ($this->has($key)) {
                $data[$key] = $this->input($key);
            }
        }

        return $data;
    }

    public function except(array $keys): array
    {
        $data = $this->all();

        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function filled(string $key): bool
    {
        $value = $this->input($key);

        if ($value === null) {
            return false;
        }

        return is_string($value)
            ? trim($value) !== ''
            : !empty($value);
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $key = str_replace('-', '_', strtoupper($key));

        return $this->headers[$key]
            ?? $this->headers["HTTP_{$key}"]
            ?? $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function file(string $key): mixed
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        $file = $this->file($key);

        return is_array($file)
            && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    public function ip(): ?string
    {
        return $this->server['REMOTE_ADDR'] ?? null;
    }

    public function userAgent(): ?string
    {
        return $this->header('User-Agent');
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    public function isJson(): bool
    {
        return str_contains(
            (string) $this->header('Content-Type', ''),
            'application/json'
        );
    }

    public function expectsJson(): bool
    {
        return str_contains(
            (string) $this->header('Accept', ''),
            'application/json'
        );
    }

    public function setRouteParameters(array $parameters): void
    {
        $this->routeParameters = $parameters;
    }

    public function routeParameter(string $key, mixed $default = null): mixed
    {
        return $this->routeParameters[$key] ?? $default;
    }

    public function validate(array $rules, bool $throwOnFailure = true): ValidationResult
    {
        $validator = new RequestValidator();
        $result = $validator->validate($this->all(), $rules);

        if ($throwOnFailure && !$result->isValid()) {
            throw new ValidationException($result);
        }

        return $result;
    }

    public function validated(array $rules): array
    {
        if ($rules === []) {
            return $this->all();
        }

        $this->validate($rules);

        return $this->only(array_keys($rules));
    }

    private function initialize(): void
    {
        $this->method  = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        $this->uri     = $this->parseUri();
        $this->headers = $this->parseHeaders();
    }

    private function parseUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';

        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        return $uri;
    }

    private function parseHeaders(): array
    {
        $headers = [];

        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif ($key === 'CONTENT_TYPE' || $key === 'CONTENT_LENGTH') {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    private function parseBody(): array
    {
        if ($this->isJson()) {
            $data = json_decode($this->rawBody, true);
            return is_array($data) ? $this->sanitize($data) : [];
        }

        if ($this->method === 'POST') {
            return $this->sanitize($_POST);
        }

        return [];
    }

    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitize($value);
                continue;
            }

            if (is_string($value)) {
                $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }

        return $data;
    }
}
