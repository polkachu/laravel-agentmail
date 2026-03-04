<?php

namespace Polkachu\LaravelAgentmail\DTOs;

use Illuminate\Support\Collection;

readonly class InboxCollection
{
    public function __construct(
        public Collection $inboxes,
        public int $count,
        public ?int $limit = null,
        public ?string $nextPageToken = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            inboxes: collect($data['inboxes'])->map(fn (array $inbox) => Inbox::fromArray($inbox)),
            count: $data['count'],
            limit: $data['limit'] ?? null,
            nextPageToken: $data['next_page_token'] ?? null,
        );
    }
}
