<?php

namespace Polkachu\LaravelAgentmail\DTOs;

readonly class Attachment
{
    public function __construct(
        public string $attachmentId,
        public int $size,
        public ?string $filename = null,
        public ?string $contentType = null,
        public ?string $contentDisposition = null,
        public ?string $contentId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            attachmentId: $data['attachment_id'],
            size: $data['size'],
            filename: $data['filename'] ?? null,
            contentType: $data['content_type'] ?? null,
            contentDisposition: $data['content_disposition'] ?? null,
            contentId: $data['content_id'] ?? null,
        );
    }
}
