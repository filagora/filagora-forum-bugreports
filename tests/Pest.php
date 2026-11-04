<?php

declare(strict_types=1);

use Filagora\ForumBugReports\Enums\BugReportStatus;
use Filagora\ForumBugReports\Models\BugReport;
use Filagora\ForumBugReports\Tests\Support\HostAccess;
use Filagora\ForumBugReports\Tests\Support\TestUser;
use Filagora\ForumBugReports\Tests\TestCase;
use Filagora\Forums\Enums\ForumPostingPolicy;
use Filagora\Forums\Enums\ForumVisibility;
use Filagora\Forums\Models\Forum;
use Filagora\Forums\Models\ForumGroup;
use Filagora\Forums\Models\Thread;
use Filagora\Forums\Support\Abilities;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');

function bugThread(?TestUser $author = null): Thread
{
    $author ??= TestUser::make('Author'.uniqid());
    $group = ForumGroup::query()->firstOrCreate(['slug' => 'main'], ['name' => 'Main']);
    $forum = Forum::create(['forum_group_id' => $group->id, 'name' => 'F'.uniqid(), 'slug' => 'f-'.uniqid(), 'visibility' => ForumVisibility::PUBLIC, 'posting_policy' => ForumPostingPolicy::REGISTERED]);

    return Thread::create(['forum_id' => $forum->id, 'author_id' => $author->id, 'title' => 'Thread', 'slug' => 't-'.uniqid()]);
}

function moderator(): TestUser
{
    $user = TestUser::make('Staff'.uniqid());
    HostAccess::$granted[$user->id] = [Abilities::MODERATE];

    return $user;
}

function bugReport(Thread $thread, BugReportStatus $status = BugReportStatus::REPORTED, array $attributes = []): BugReport
{
    return BugReport::query()->create([
        'thread_id' => $thread->id,
        'reported_by_id' => $thread->author_id,
        'status' => $status,
        ...$attributes,
    ]);
}
