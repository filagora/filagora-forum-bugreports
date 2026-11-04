<?php

declare(strict_types=1);

namespace Filagora\ForumBugReports\Filament\Resources\BugReports\Pages;

use Filagora\ForumBugReports\Filament\Resources\BugReports\BugReportResource;
use Filament\Resources\Pages\ListRecords;

class ListBugReports extends ListRecords
{
    protected static string $resource = BugReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
