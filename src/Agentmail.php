<?php

namespace Polkachu\LaravelAgentmail;

use Illuminate\Http\Client\PendingRequest;
use Polkachu\LaravelAgentmail\Resources\Inboxes;

class Agentmail
{
    public function __construct(private readonly PendingRequest $http) {}

    public function inboxes(): Inboxes
    {
        return new Inboxes($this->http);
    }
}
