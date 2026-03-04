<?php

namespace Polkachu\LaravelAgentmail\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

class AgentmailException extends RuntimeException
{
    public function __construct(
        public readonly string $errorName,
        string $message,
        int $statusCode = 0,
    ) {
        parent::__construct($message, $statusCode);
    }

    public static function fromResponse(Response $response): self
    {
        $body = $response->json();

        return new self(
            errorName: $body['name'] ?? 'UnknownError',
            message: $body['message'] ?? 'An unknown error occurred.',
            statusCode: $response->status(),
        );
    }
}
