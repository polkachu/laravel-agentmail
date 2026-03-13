<?php

namespace Polkachu\LaravelAgentmail\Resources;

use Illuminate\Http\Client\PendingRequest;
use Polkachu\LaravelAgentmail\DTOs\Message;
use Polkachu\LaravelAgentmail\DTOs\MessageCollection;
use Polkachu\LaravelAgentmail\DTOs\SendMessageResponse;
use Polkachu\LaravelAgentmail\Exceptions\AgentmailException;

class Messages
{
    public function __construct(
        private readonly PendingRequest $http,
        private readonly string $inboxId,
    ) {}

    public function list(
        ?int $limit = null,
        ?string $pageToken = null,
        ?array $labels = null,
        ?string $before = null,
        ?string $after = null,
        ?bool $ascending = null,
        ?bool $includeSpam = null,
        ?bool $includeBlocked = null,
        ?bool $includeTrash = null,
    ): MessageCollection {
        $query = array_filter([
            'limit'           => $limit,
            'page_token'      => $pageToken,
            'labels'          => $labels,
            'before'          => $before,
            'after'           => $after,
            'ascending'       => $ascending,
            'include_spam'    => $includeSpam,
            'include_blocked' => $includeBlocked,
            'include_trash'   => $includeTrash,
        ], fn ($value) => $value !== null);

        $response = $this->http->get("/v0/inboxes/{$this->inboxId}/messages", $query);

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return MessageCollection::fromArray($response->json());
    }

    public function get(string $messageId): Message
    {
        $response = $this->http->get("/v0/inboxes/{$this->inboxId}/messages/{$messageId}");

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return Message::fromArray($response->json());
    }

    public function send(
        string|array|null $to = null,
        string|array|null $cc = null,
        string|array|null $bcc = null,
        ?string $subject = null,
        ?string $text = null,
        ?string $html = null,
        string|array|null $replyTo = null,
        ?array $labels = null,
        ?array $headers = null,
    ): SendMessageResponse {
        $body = array_filter([
            'to'       => $to,
            'cc'       => $cc,
            'bcc'      => $bcc,
            'subject'  => $subject,
            'text'     => $text,
            'html'     => $html,
            'reply_to' => $replyTo,
            'labels'   => $labels,
            'headers'  => $headers,
        ], fn ($value) => $value !== null);

        $response = $this->http->post("/v0/inboxes/{$this->inboxId}/messages", $body);

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return SendMessageResponse::fromArray($response->json());
    }

    public function update(string $messageId, ?array $addLabels = null, ?array $removeLabels = null): Message
    {
        $body = array_filter([
            'add_labels'    => $addLabels,
            'remove_labels' => $removeLabels,
        ], fn ($value) => $value !== null);

        $response = $this->http->patch("/v0/inboxes/{$this->inboxId}/messages/{$messageId}", $body);

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return Message::fromArray($response->json());
    }

    public function reply(
        string $messageId,
        string|array|null $to = null,
        string|array|null $cc = null,
        string|array|null $bcc = null,
        ?string $text = null,
        ?string $html = null,
        string|array|null $replyTo = null,
        ?array $labels = null,
        ?array $headers = null,
        ?bool $replyAll = null,
    ): SendMessageResponse {
        $body = array_filter([
            'to'        => $to,
            'cc'        => $cc,
            'bcc'       => $bcc,
            'text'      => $text,
            'html'      => $html,
            'reply_to'  => $replyTo,
            'labels'    => $labels,
            'headers'   => $headers,
            'reply_all' => $replyAll,
        ], fn ($value) => $value !== null);

        $response = $this->http->post("/v0/inboxes/{$this->inboxId}/messages/{$messageId}/reply", $body);

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return SendMessageResponse::fromArray($response->json());
    }

    public function replyAll(
        string $messageId,
        string|array|null $to = null,
        string|array|null $cc = null,
        string|array|null $bcc = null,
        ?string $text = null,
        ?string $html = null,
        string|array|null $replyTo = null,
        ?array $labels = null,
        ?array $headers = null,
    ): SendMessageResponse {
        $body = array_filter([
            'to'       => $to,
            'cc'       => $cc,
            'bcc'      => $bcc,
            'text'     => $text,
            'html'     => $html,
            'reply_to' => $replyTo,
            'labels'   => $labels,
            'headers'  => $headers,
        ], fn ($value) => $value !== null);

        $response = $this->http->post("/v0/inboxes/{$this->inboxId}/messages/{$messageId}/reply-all", $body);

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return SendMessageResponse::fromArray($response->json());
    }

    public function forward(
        string $messageId,
        string|array|null $to = null,
        string|array|null $cc = null,
        string|array|null $bcc = null,
        ?string $subject = null,
        ?string $text = null,
        ?string $html = null,
        string|array|null $replyTo = null,
        ?array $labels = null,
        ?array $headers = null,
    ): SendMessageResponse {
        $body = array_filter([
            'to'       => $to,
            'cc'       => $cc,
            'bcc'      => $bcc,
            'subject'  => $subject,
            'text'     => $text,
            'html'     => $html,
            'reply_to' => $replyTo,
            'labels'   => $labels,
            'headers'  => $headers,
        ], fn ($value) => $value !== null);

        $response = $this->http->post("/v0/inboxes/{$this->inboxId}/messages/{$messageId}/forward", $body);

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return SendMessageResponse::fromArray($response->json());
    }
}
