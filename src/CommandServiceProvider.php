<?php

namespace JordanPartridge\ClaudeToolsGithub;

use Illuminate\Support\ServiceProvider;
use JordanPartridge\ClaudeToolsGithub\Commands\ReposListCommand;

class CommandServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ReposListCommand::class,
            ]);
        }
    }

    public function register()
    {
        // Register any dependencies
    }
}