<?php

namespace iEducar\Community\Reports\Providers;

use iEducar\Community\Reports\Commands\CommunityReportsCompileCommand;
use iEducar\Community\Reports\Commands\CommunityReportsInstallCommand;
use iEducar\Community\Reports\Commands\CommunityReportsLinkCommand;
use iEducar\Reports\Contracts\TeacherReportCard;
use Illuminate\Support\ServiceProvider;
use TeacherReportCardReport;

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
                CommunityReportsCompileCommand::class,
                CommunityReportsInstallCommand::class,
                CommunityReportsLinkCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../../ieducar/Assets' => public_path('vendor/legacy/Reports/Assets')
            ], ['reports-assets']);
        }
    }

    public function register()
    {
        $this->app->bind(TeacherReportCard::class, TeacherReportCardReport::class);
    }
}
