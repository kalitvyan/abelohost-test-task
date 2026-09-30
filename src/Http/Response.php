<?php

declare(strict_types=1);

namespace App\Http;

final class Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public readonly string $body = '',
        public readonly int $status = 200,
        public readonly array $headers = [],
    ) {
    }

    /**
     * @param array<string, string> $headers
     */
    public static function html(
        string $body,
        int $status = 200,
        array $headers = []
    ): self {
        return new self(
            body: $body,
            status: $status,
            headers: $headers + ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * @param array<string, string> $headers
     */
    public static function text(
        string $body,
        int $status = 200,
        array $headers = []
    ): self {
        return new self(
            body: $body,
            status: $status,
            headers: $headers + ['Content-Type' => 'text/plain; charset=utf-8']
        );
    }

    public static function redirect(string $location, int $status = 302): self
    {
        return new self(
            body: '',
            status: $status,
            headers: ['Location' => $location]
        );
    }

    public function send(bool $withBody = true): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if ($withBody) {
            echo $this->body;
        }
    }
}
