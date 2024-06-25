<?php declare(strict_types=1);

namespace SimpleRouter\Application;

class Response
{
    protected int $statusCode;
    protected array $headers;
    protected mixed $body;

    public function __construct()
    {
        $this->statusCode = 200;
        $this->headers = [];
        $this->body = '';
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function setHeader(string $header, string $value): self
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function body(array|object $data): self
    {
        $this->body = json_encode($data);
        $this->setHeader('Content-Type', 'application/json');
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $header => $value) {
            header("$header: $value");
        }

        echo $this->body;
    }
}
