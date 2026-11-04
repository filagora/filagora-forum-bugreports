<?php

declare(strict_types=1);

namespace Filagora\ForumBugReports\Events;

use Filagora\ForumBugReports\Models\BugReport;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A bug report was opened or changed, from the host's pages or the staff screen. The package sends
 * no mail of its own: the host decides who hears of it and how.
 */
final class BugReportChanged
{
    use Dispatchable;

    /**
     * @param  list<string>  $changes  What changed, one translated line each.
     */
    public function __construct(
        public readonly BugReport $bugReport,
        public readonly array $changes,
        public readonly int|string|null $actorId,
    ) {}
}
