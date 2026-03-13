<?php

namespace Polkachu\LaravelAgentmail\DTOs;

use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class Message
{
    public function __construct(
        public string $inboxId,
        public string $threadId,
        public string $messageId,
        public array $labels,
        public Carbon $timestamp,
        public string $from,
        public array $to,
        public int $size,
        public Carbon $updatedAt,
        public Carbon $createdAt,
        public ?array $cc = null,
        public ?array $bcc = null,
        public ?array $replyTo = null,
        public ?string $subject = null,
        public ?string $preview = null,
        public ?string $text = null,
        public ?string $html = null,
        public ?string $extractedText = null,
        public ?string $extractedHtml = null,
        public ?string $inReplyTo = null,
        public ?array $references = null,
        public ?Collection $attachments = null,
        public ?array $headers = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $attachments = isset($data['attachments'])
            ? collect($data['attachments'])->map(fn (array $a) => Attachment::fromArray($a))
            : null;

        return new self(
            inboxId: $data['inbox_id'],
            threadId: $data['thread_id'],
            messageId: $data['message_id'],
            labels: $data['labels'],
            timestamp: Carbon::parse($data['timestamp']),
            from: $data['from'],
            to: $data['to'],
            size: $data['size'],
            updatedAt: Carbon::parse($data['updated_at']),
            createdAt: Carbon::parse($data['created_at']),
            cc: $data['cc'] ?? null,
            bcc: $data['bcc'] ?? null,
            replyTo: $data['reply_to'] ?? null,
            subject: $data['subject'] ?? null,
            preview: $data['preview'] ?? null,
            text: $data['text'] ?? null,
            html: $data['html'] ?? null,
            extractedText: $data['extracted_text'] ?? null,
            extractedHtml: $data['extracted_html'] ?? null,
            inReplyTo: $data['in_reply_to'] ?? null,
            references: $data['references'] ?? null,
            attachments: $attachments,
            headers: $data['headers'] ?? null,
        );
    }
}
