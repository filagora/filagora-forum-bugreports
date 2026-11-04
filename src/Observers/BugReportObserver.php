<?php

declare(strict_types=1);

namespace Filagora\ForumBugReports\Observers;

use Filagora\Core\Support\UserModel;
use Filagora\ForumBugReports\Enums\BugReportStatus;
use Filagora\ForumBugReports\Events\BugReportChanged;
use Filagora\ForumBugReports\Models\BugReport;

/**
 * Sits on the model so the host's pages and the staff screen both announce a change.
 */
class BugReportObserver
{
    private const TRANSLATIONS = 'filagora-forum-bugreports::messages.changes.';

    public function created(BugReport $bugReport): void
    {
        $this->announce($bugReport, [__(self::TRANSLATIONS.'reported', ['status' => $bugReport->status->label()])]);
    }

    public function updated(BugReport $bugReport): void
    {
        $changes = [];

        if ($bugReport->wasChanged('status')) {
            $previous = $bugReport->getOriginal('status');
            $changes[] = __(self::TRANSLATIONS.'status', [
                'from' => $previous instanceof BugReportStatus ? $previous->label() : (string) $previous,
                'to' => $bugReport->status->label(),
            ]);
        }

        if ($bugReport->wasChanged('assigned_to_id')) {
            $assignee = $bugReport->assignedTo;
            $changes[] = $assignee === null
                ? __(self::TRANSLATIONS.'unassigned')
                : __(self::TRANSLATIONS.'assigned', ['name' => $assignee->getAttribute(BugReport::userAttribute()) ?? $assignee->getAttribute(UserModel::titleAttribute())]);
        }

        if ($bugReport->wasChanged('resolution_note') && filled($bugReport->resolution_note)) {
            $changes[] = __(self::TRANSLATIONS.'note', ['note' => $bugReport->resolution_note]);
        }

        if ($changes !== []) {
            $this->announce($bugReport, $changes);
        }
    }

    /**
     * @param  list<string>  $changes
     */
    private function announce(BugReport $bugReport, array $changes): void
    {
        BugReportChanged::dispatch($bugReport, $changes, auth()->id());
    }
}
