<?php

namespace Polkachu\LaravelAgentmail\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Polkachu\LaravelAgentmail\AgentmailServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [AgentmailServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('agentmail.api_key', 'test-api-key');
        $app['config']->set('agentmail.base_url', 'https://api.agentmail.to');
    }
}
