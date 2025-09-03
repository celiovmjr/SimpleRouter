<?php declare(strict_types=1);

namespace SimpleRouter\Application;

use RuntimeException;

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

    /**
     * Converte todas as strings de um array/objeto para UTF-8.
     *
     * Converts all strings in an array/object to UTF-8.
     */
    private function utf8ize(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([$this, 'utf8ize'], $data);
        }

        if (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->utf8ize($value);
            }
            return $data;
        }

        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }

        return $data;
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
        $data = $this->utf8ize($data);
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($this->body === false) {
            throw new RuntimeException('Erro ao codificar JSON: ' . json_last_error_msg());
        }

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
