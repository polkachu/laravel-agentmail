<?php

namespace Polkachu\LaravelAgentmail\DTOs;

readonly class SendMessageResponse
{
    public function __construct(
        public string $messageId,
        public string $threadId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            messageId: $data['message_id'],
            threadId: $data['thread_id'],
        );
    }
}
