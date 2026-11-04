<?php

declare(strict_types=1);

use Filagora\ForumBugReports\FilagoraForumBugReportsPlugin;
use Filagora\ForumBugReports\Filament\Resources\BugReports\BugReportResource;
use Filagora\ForumBugReports\Models\BugReport;
use Filagora\ForumBugReports\Services\ResolvedBugThreadVisibility;
use Filagora\ForumBugReports\Tests\Support\TestUser;
use Filament\Panel;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

it('lets moderators manage reports and nobody else', function () {
    $report = bugReport(bugThread());
    $staff = moderator();
    $member = TestUser::make('Member');

    foreach (['view', 'update', 'delete'] as $ability) {
        expect(Gate::forUser($staff)->allows($ability, $report))->toBeTrue($ability)
            ->and(Gate::forUser($member)->allows($ability, $report))->toBeFalse($ability);
    }

    foreach (['viewAny', 'create', 'deleteAny'] as $ability) {
        expect(Gate::forUser($staff)->allows($ability, BugReport::class))->toBeTrue($ability)
            ->and(Gate::forUser($member)->allows($ability, BugReport::class))->toBeFalse($ability);
    }
});

it('offers the hide-resolved choice to moderators only, off by default', function () {
    $visibility = app(ResolvedBugThreadVisibility::class);
    $request = Request::create('/');
    $request->setLaravelSession(new Store('test', new ArraySessionHandler(10)));
    $staff = moderator();
    $member = TestUser::make('Member');

    expect($visibility->availableFor($staff))->toBeTrue()
        ->and($visibility->availableFor($member))->toBeFalse()
        ->and($visibility->availableFor(null))->toBeFalse()
        ->and($visibility->hiddenFor($request, $staff))->toBeFalse();

    $visibility->setHidden($request, true);

    expect($visibility->hiddenFor($request, $staff))->toBeTrue()
        ->and($visibility->hiddenFor($request, $member))->toBeFalse();
});

it('registers the staff screen on a panel', function () {
    $panel = Panel::make()->id('admin')->plugin(FilagoraForumBugReportsPlugin::make());

    expect($panel->hasPlugin('filagora-forum-bugreports'))->toBeTrue()
        ->and($panel->getResources())->toContain(BugReportResource::class);
});

it('links to the host\'s thread page only when the host has one', function () {
    expect(BugReportResource::threadUrl(5))->toBeNull();

    Route::get('/threads/{thread}', fn () => '')->name('threads.show');
    app('router')->getRoutes()->refreshNameLookups();

    expect(BugReportResource::threadUrl(5))->toEndWith('/threads/5');
});
