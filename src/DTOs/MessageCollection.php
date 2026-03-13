<?php

namespace Polkachu\LaravelAgentmail\DTOs;

use Illuminate\Support\Collection;

readonly class MessageCollection
{
    public function __construct(
        public Collection $messages,
        public int $count,
        public ?int $limit = null,
        public ?string $nextPageToken = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            messages: collect($data['messages'])->map(fn (array $message) => Message::fromArray($message)),
            count: $data['count'],
            limit: $data['limit'] ?? null,
            nextPageToken: $data['next_page_token'] ?? null,
        );
    }
}
