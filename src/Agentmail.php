<?php

namespace Polkachu\LaravelAgentmail;

use Illuminate\Http\Client\PendingRequest;
use Polkachu\LaravelAgentmail\Resources\Inboxes;
use Polkachu\LaravelAgentmail\Resources\Messages;

class Agentmail
{
    public function __construct(private readonly PendingRequest $http) {}

    public function inboxes(): Inboxes
    {
        return new Inboxes($this->http);
    }

    public function messages(string $inboxId): Messages
    {
        return new Messages($this->http, $inboxId);
    }
}
