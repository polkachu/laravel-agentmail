<?php

namespace Polkachu\LaravelAgentmail\DTOs;

use Carbon\Carbon;

readonly class Inbox
{
    public function __construct(
        public string $podId,
        public string $inboxId,
        public Carbon $updatedAt,
        public Carbon $createdAt,
        public ?string $displayName = null,
        public ?string $clientId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            podId: $data['pod_id'],
            inboxId: $data['inbox_id'],
            updatedAt: Carbon::parse($data['updated_at']),
            createdAt: Carbon::parse($data['created_at']),
            displayName: $data['display_name'] ?? null,
            clientId: $data['client_id'] ?? null,
        );
    }
}
