<?php

declare(strict_types=1);

use Filagora\ForumBugReports\Enums\BugReportStatus;
use Filagora\ForumBugReports\Events\BugReportChanged;
use Filagora\ForumBugReports\Models\BugReport;
use Filagora\ForumBugReports\Services\BugReports;
use Filagora\ForumBugReports\Tests\Support\TestUser;
use Filagora\Forums\Models\Thread;
use Filagora\Forums\Services\Subscriptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

afterEach(fn () => Carbon::setTestNow());

it('opens a report and subscribes the reporter to the thread', function () {
    $thread = bugThread();
    $staff = moderator();

    $report = app(BugReports::class)->report($staff, $thread, '  It breaks  ');

    expect($report->status)->toBe(BugReportStatus::REPORTED)
        ->and($report->description)->toBe('It breaks')
        ->and($report->reported_by_id)->toBe($staff->id)
        ->and(app(Subscriptions::class)->isSubscribed($staff, $thread))->toBeTrue();
});

it('treats a blank description as none', function () {
    expect(app(BugReports::class)->report(moderator(), bugThread(), '   ')->description)->toBeNull();
});

it('drops an identical report inside the window and keeps one after it', function () {
    Carbon::setTestNow('2026-11-04 12:00:00');
    $thread = bugThread();
    $staff = moderator();
    $reports = app(BugReports::class);

    expect($reports->report($staff, $thread, 'Same'))->not->toBeNull()
        ->and($reports->report($staff, $thread, 'Same'))->toBeNull()
        ->and($reports->report($staff, $thread, 'Different'))->not->toBeNull();

    Carbon::setTestNow('2026-11-04 12:00:16');

    expect($reports->report($staff, $thread, 'Same'))->not->toBeNull()
        ->and(BugReport::query()->count())->toBe(3);
});

it('stamps when a report is confirmed and completed, and reopens it', function () {
    $report = bugReport(bugThread());
    $reports = app(BugReports::class);

    $reports->update($report, BugReportStatus::CONFIRMED, null, null);
    $confirmedAt = $report->confirmed_at;
    expect($confirmedAt)->not->toBeNull()->and($report->completed_at)->toBeNull();

    $reports->update($report, BugReportStatus::COMPLETED, null, null);
    expect($report->completed_at)->not->toBeNull()->and($report->confirmed_at)->toEqual($confirmedAt);

    $reports->update($report, BugReportStatus::REPORTED, null, null);
    expect($report->completed_at)->toBeNull();
});

it('keeps the existing note when an update leaves it empty', function () {
    $report = bugReport(bugThread(), attributes: ['resolution_note' => 'Fixed in 2.1']);

    app(BugReports::class)->update($report, BugReportStatus::CONFIRMED, null, '  ');

    expect($report->resolution_note)->toBe('Fixed in 2.1');
});

it('lists a thread\'s reports newest first', function () {
    $thread = bugThread();
    $older = bugReport($thread);
    $older->forceFill(['created_at' => now()->subDay()])->save();
    $newer = bugReport($thread);
    bugReport(bugThread());

    expect(app(BugReports::class)->forThread($thread)->pluck('id')->all())->toBe([$newer->id, $older->id]);
});

it('gives the newest status on each thread and leaves out threads with no report', function () {
    $thread = bugThread();
    $old = bugReport($thread, BugReportStatus::COMPLETED);
    $old->forceFill(['created_at' => now()->subDay()])->save();
    bugReport($thread, BugReportStatus::CONFIRMED);
    $none = bugThread();

    expect(app(BugReports::class)->latestStatusByThread([$thread->id, $none->id]))->toBe([$thread->id => BugReportStatus::CONFIRMED]);
});

it('hides only threads whose reports are all closed out', function () {
    $noReports = bugThread();
    $open = bugThread();
    bugReport($open, BugReportStatus::COMPLETED);
    bugReport($open, BugReportStatus::REPORTED);
    $closed = bugThread();
    bugReport($closed, BugReportStatus::COMPLETED);
    bugReport($closed, BugReportStatus::WONT_FIX);

    $query = Thread::query();
    app(BugReports::class)->hideResolved($query);

    expect($query->pluck('id')->all())->toEqualCanonicalizing([$noReports->id, $open->id]);
});

it('deletes a thread\'s reports with the thread', function () {
    $thread = bugThread();
    bugReport($thread);

    $thread->forceDelete();

    expect(BugReport::query()->count())->toBe(0);
});

it('announces a new report', function () {
    Event::fake([BugReportChanged::class]);
    $staff = moderator();
    $this->actingAs($staff);

    $report = bugReport(bugThread());

    Event::assertDispatched(BugReportChanged::class, fn (BugReportChanged $event): bool => $event->bugReport->is($report)
        && $event->changes === ['This thread was reported as a bug (Reported).']
        && $event->actorId === $staff->id);
});

it('announces each change in one event', function () {
    $report = bugReport(bugThread());
    $assignee = TestUser::make('Dana');
    Event::fake([BugReportChanged::class]);

    $report->update(['status' => BugReportStatus::CONFIRMED, 'assigned_to_id' => $assignee->id, 'resolution_note' => 'On it']);

    Event::assertDispatched(BugReportChanged::class, fn (BugReportChanged $event): bool => $event->changes === [
        'Status changed from Reported to Confirmed.',
        'Assigned to Dana.',
        'Note: On it',
    ]);
});

it('announces an unassignment', function () {
    $report = bugReport(bugThread(), attributes: ['assigned_to_id' => TestUser::make('Dana')->id]);
    Event::fake([BugReportChanged::class]);

    $report->update(['assigned_to_id' => null]);

    Event::assertDispatched(BugReportChanged::class, fn (BugReportChanged $event): bool => $event->changes === ['Unassigned.']);
});

it('names the assignee by the configured attribute', function () {
    config(['filagora-forum-bugreports.user_attribute' => 'email']);
    $assignee = TestUser::make('Dana');
    $report = bugReport(bugThread());
    Event::fake([BugReportChanged::class]);

    $report->update(['assigned_to_id' => $assignee->id]);

    Event::assertDispatched(BugReportChanged::class, fn (BugReportChanged $event): bool => $event->changes === ["Assigned to {$assignee->email}."]);
});

it('says nothing when no announced field changed', function () {
    $report = bugReport(bugThread());
    Event::fake([BugReportChanged::class]);

    $report->update(['description' => 'Edited']);

    Event::assertNotDispatched(BugReportChanged::class);
});
