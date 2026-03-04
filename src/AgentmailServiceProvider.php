<?php

namespace Polkachu\LaravelAgentmail;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AgentmailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/agentmail.php', 'agentmail');

        $this->app->singleton(Agentmail::class, function () {
            $http = Http::baseUrl(config('agentmail.base_url'))
                ->withToken(config('agentmail.api_key'))
                ->asJson()
                ->acceptJson();

            return new Agentmail($http);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/agentmail.php' => config_path('agentmail.php'),
        ], 'agentmail');
    }
}
