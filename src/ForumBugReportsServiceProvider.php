<?php

declare(strict_types=1);

namespace Filagora\ForumBugReports;

use Filagora\Core\Modules\Module;
use Filagora\Core\Modules\ModuleRegistry;
use Filagora\Core\Schema\PackageSchema;
use Filagora\Core\Schema\SchemaRegistry;
use Filagora\ForumBugReports\Models\BugReport;
use Filagora\ForumBugReports\Observers\BugReportObserver;
use Filagora\ForumBugReports\Policies\BugReportPolicy;
use Filagora\Forums\Models\Thread;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ForumBugReportsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filagora-forum-bugreports.php', 'filagora-forum-bugreports');
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->register(
            new Module('forum-bugreports', 'Filagora forum bug reports', 'filagora/filagora-forum-bugreports', requires: ['core', 'forums']),
        );

        $this->app->make(SchemaRegistry::class)->register(
            PackageSchema::fromFile('forum-bugreports', __DIR__.'/../database/schema.php', __DIR__.'/../database/migrations'),
        );

        Gate::policy(BugReport::class, BugReportPolicy::class);
        BugReport::observe(BugReportObserver::class);
        Thread::resolveRelationUsing('bugReports', fn (Thread $thread) => $thread->hasMany(BugReport::class, 'thread_id'));

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filagora-forum-bugreports');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filagora-forum-bugreports.php' => config_path('filagora-forum-bugreports.php'),
            ], 'filagora-forum-bugreports-config');
        }
    }
}
