<?php declare(strict_types=1);

namespace SimpleRouter\Application;

class Response
{
    protected int $status = 200;
    protected array $headers = [];
    protected mixed $body = '';

    public function __construct()
    {
        $this->status = 200;
        $this->headers = [];
        $this->body = '';
    }

    public function status(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function header(string $header, string $value): self
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function body(array|object $data): self
    {
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $this->header('Content-Type', 'application/json');
        return $this;
    }

    public function send(bool $die = false): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $header => $value) header("$header: $value");
        echo $this->body;
        if ($die) exit(1);
    }
}
