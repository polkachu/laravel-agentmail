<?php

namespace Polkachu\LaravelAgentmail\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Polkachu\LaravelAgentmail\Resources\Inboxes inboxes()
 *
 * @see \Polkachu\LaravelAgentmail\Agentmail
 */
class Agentmail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Polkachu\LaravelAgentmail\Agentmail::class;
    }
}
