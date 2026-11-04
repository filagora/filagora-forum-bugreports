<?php

declare(strict_types=1);

namespace Filagora\ForumBugReports\Services;

use Filagora\ForumBugReports\Enums\BugReportStatus;
use Filagora\ForumBugReports\Models\BugReport;
use Filagora\Forums\Models\Thread;
use Filagora\Forums\Services\Subscriptions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Opening and changing bug reports, and the reads a host's thread pages need.
 */
class BugReports
{
    /**
     * An identical report on the same thread from the same reporter inside this window is a double
     * click or a resubmitted form, not a second report. A thread may collect several over time, so
     * only an immediate repeat is dropped.
     */
    private const DUPLICATE_SUBMISSION_WINDOW_SECONDS = 15;

    public function __construct(private readonly Subscriptions $subscriptions) {}

    /**
     * Open a report. Reporters follow their own report so its changes reach them.
     *
     * @return BugReport|null The new report, or null when this was an immediate repeat.
     */
    public function report(Authenticatable $reporter, Thread $thread, ?string $description): ?BugReport
    {
        $description = trim((string) $description) ?: null;

        $isDuplicate = BugReport::query()
            ->where('thread_id', $thread->getKey())
            ->where('reported_by_id', $reporter->getAuthIdentifier())
            ->where('created_at', '>=', now()->subSeconds(self::DUPLICATE_SUBMISSION_WINDOW_SECONDS))
            ->where(fn ($query) => $description === null ? $query->whereNull('description') : $query->where('description', $description))
            ->exists();

        if ($isDuplicate) {
            return null;
        }

        $this->subscriptions->subscribe($reporter, $thread);

        return BugReport::query()->create([
            'thread_id' => $thread->getKey(),
            'reported_by_id' => $reporter->getAuthIdentifier(),
            'status' => BugReportStatus::REPORTED,
            'description' => $description,
        ]);
    }

    /**
     * Change a report's status, assignee and note. An empty note leaves the existing one in place.
     */
    public function update(BugReport $bugReport, BugReportStatus $status, ?int $assignedToId, ?string $note): void
    {
        $note = trim((string) $note);

        $bugReport->status = $status;
        $bugReport->assigned_to_id = $assignedToId ?: null;
        $bugReport->resolution_note = $note !== '' ? $note : $bugReport->resolution_note;
        $bugReport->confirmed_at = $status === BugReportStatus::CONFIRMED ? ($bugReport->confirmed_at ?? now()) : $bugReport->confirmed_at;
        $bugReport->completed_at = $status->isTerminal() ? ($bugReport->completed_at ?? now()) : null;
        $bugReport->save();
    }

    /**
     * A thread's reports, newest first, with the people named on them.
     *
     * @return Collection<int, BugReport>
     */
    public function forThread(Thread $thread): Collection
    {
        return BugReport::query()
            ->with(['reportedBy', 'assignedTo'])
            ->where('thread_id', $thread->getKey())
            ->latest('created_at')
            ->get();
    }

    /**
     * The status of the newest report on each of these threads, keyed by thread id. Threads with no
     * report are left out.
     *
     * @param  iterable<int>  $threadIds
     * @return array<int, BugReportStatus>
     */
    public function latestStatusByThread(iterable $threadIds): array
    {
        return BugReport::query()
            ->whereIn('thread_id', collect($threadIds)->all())
            ->orderByDesc('created_at')
            ->get(['thread_id', 'status'])
            ->groupBy(fn (BugReport $bugReport): int => (int) $bugReport->thread_id)
            ->map(fn ($reports): BugReportStatus => $reports->first()->status)
            ->all();
    }

    /**
     * Leave out threads whose bug reports are all closed out. A thread stays when it has none at
     * all, or one still open.
     *
     * @param  Builder<covariant Thread>  $threads
     */
    public function hideResolved(Builder $threads): void
    {
        // The relation is registered by this package's service provider, which larastan cannot see.
        // @phpstan-ignore-next-line larastan.relationExistence
        $threads->where(fn (Builder $query) => $query
            ->whereDoesntHave('bugReports')
            ->orWhereHas('bugReports', fn (Builder $reports) => $reports->whereNotIn('status', BugReportStatus::terminalValues())));
    }
}
