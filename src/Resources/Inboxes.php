<?php

namespace Polkachu\LaravelAgentmail\Resources;

use Illuminate\Http\Client\PendingRequest;
use Polkachu\LaravelAgentmail\DTOs\Inbox;
use Polkachu\LaravelAgentmail\DTOs\InboxCollection;
use Polkachu\LaravelAgentmail\Exceptions\AgentmailException;

class Inboxes
{
    public function __construct(private readonly PendingRequest $http) {}

    public function list(?int $limit = null, ?string $pageToken = null): InboxCollection
    {
        $query = array_filter([
            'limit' => $limit,
            'page_token' => $pageToken,
        ], fn ($value) => $value !== null);

        $response = $this->http->get('/v0/inboxes', $query);

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return InboxCollection::fromArray($response->json());
    }

    public function create(
        ?string $username = null,
        ?string $domain = null,
        ?string $displayName = null,
        ?string $clientId = null,
    ): Inbox {
        $body = array_filter([
            'username' => $username,
            'domain' => $domain,
            'display_name' => $displayName,
            'client_id' => $clientId,
        ], fn ($value) => $value !== null);

        $response = $this->http->post('/v0/inboxes', $body);

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return Inbox::fromArray($response->json());
    }

    public function get(string $inboxId): Inbox
    {
        $response = $this->http->get("/v0/inboxes/{$inboxId}");

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return Inbox::fromArray($response->json());
    }

    public function update(string $inboxId, string $displayName): Inbox
    {
        $response = $this->http->patch("/v0/inboxes/{$inboxId}", [
            'display_name' => $displayName,
        ]);

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return Inbox::fromArray($response->json());
    }

    public function delete(string $inboxId): bool
    {
        $response = $this->http->delete("/v0/inboxes/{$inboxId}");

        if ($response->failed()) {
            throw AgentmailException::fromResponse($response);
        }

        return true;
    }
}
