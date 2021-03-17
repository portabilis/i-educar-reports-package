<?php

namespace iEducar\Community\Reports\Providers;

use iEducar\Community\Reports\Commands\CommunityReportsLinkCommand;
use Illuminate\Support\ServiceProvider;

class ReportsServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

            $this->commands([
                CommunityReportsLinkCommand::class,
            ]);
        }
    }
}
