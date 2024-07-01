<?php declare(strict_types=1);

namespace SimpleRouter\Application;

class Request
{
    protected string $uri;
    protected string $method;
    protected object $headers;
    protected object $body;

    public function __construct()
    {
        $this->fire();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->body->$key ?? $default;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHeaders(): object
    {
        return $this->headers;
    }

    public function getBody(bool $associative = false): null|object|array
    {
        if (empty($this->body)) {
            return null;
        }
        return (! $associative) ? $this->body : (array) $this->body;
    }

    public function setBody(array|object $data): void
    {
        foreach ($data as $key => $value) {
            $this->body->$key = $value;
        }
    }

    private function parseBody(array $body = []): object
    {
        $json = json_decode(file_get_contents('php://input'), true);
        $data = array_merge($_REQUEST, $json ?? [], $body);

        foreach ($data as $key => &$value) {
            if ($key === "route") {
                unset($data[$key]);
                continue;
            }

            if (!is_array($value) && !is_object($value)) {
                $value = filter_var($value, FILTER_SANITIZE_STRING);
            }
        }

        return (object) $data;
    }
    

    private function fire(): void
    {
        $this->uri = filter_input(INPUT_GET, 'route', FILTER_SANITIZE_SPECIAL_CHARS) ?? "/";
        $this->method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_SPECIAL_CHARS);
        $this->headers = (object) getallheaders();
        $this->body = $this->parseBody();
    }
}
