<?php

declare(strict_types=1);

namespace Filagora\ForumBugReports;

use Filagora\ForumBugReports\Filament\Resources\BugReports\BugReportResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilagoraForumBugReportsPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filagora-forum-bugreports';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([BugReportResource::class]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
