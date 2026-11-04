<?php

declare(strict_types=1);

namespace Filagora\ForumBugReports\Filament\Resources\BugReports;

use BackedEnum;
use Filagora\Core\Navigation\NavigationGroupRegistry;
use Filagora\ForumBugReports\Enums\BugReportStatus;
use Filagora\ForumBugReports\Filament\Resources\BugReports\Pages\EditBugReport;
use Filagora\ForumBugReports\Filament\Resources\BugReports\Pages\ListBugReports;
use Filagora\ForumBugReports\Filament\Resources\BugReports\Schemas\BugReportForm;
use Filagora\ForumBugReports\Filament\Resources\BugReports\Tables\BugReportsTable;
use Filagora\ForumBugReports\Models\BugReport;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use UnitEnum;

class BugReportResource extends Resource
{
    protected static ?string $model = BugReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBugAnt;

    protected static ?int $navigationSort = 40;

    public static function getNavigationLabel(): string
    {
        return __('filagora-forum-bugreports::messages.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filagora-forum-bugreports::messages.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filagora-forum-bugreports::messages.plural_model_label');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getOpenReportCount();

        return $count > 0 ? static::formatNavigationBadgeCount($count) : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = static::getOpenReportCount();

        if ($count === 0) {
            return null;
        }

        return trans_choice('filagora-forum-bugreports::messages.badge_tooltip', $count, ['count' => $count]);
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return BugReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BugReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBugReports::route('/'),
            'edit' => EditBugReport::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    protected static function getOpenReportCount(): int
    {
        return self::rememberRequestMetric('open_report_count', static fn (): int => BugReport::query()
            ->where('status', BugReportStatus::REPORTED)
            ->count());
    }

    protected static function formatNavigationBadgeCount(int $count): string
    {
        return $count > 99 ? '99+' : (string) $count;
    }

    private static function rememberRequestMetric(string $suffix, callable $resolver): int
    {
        $request = app()->bound('request') ? app(Request::class) : null;

        if (! $request instanceof Request) {
            return (int) $resolver();
        }

        $key = sprintf('filament.navigation_metric.%s.%s', static::class, $suffix);

        if ($request->attributes->has($key)) {
            return (int) $request->attributes->get($key);
        }

        $value = (int) $resolver();
        $request->attributes->set($key, $value);

        return $value;
    }

    /**
     * The host's page for a thread, when it has one.
     */
    public static function threadUrl(int $threadId): ?string
    {
        $route = config('filagora-forum-bugreports.thread_route');

        return is_string($route) && Route::has($route) ? route($route, $threadId) : null;
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return app(NavigationGroupRegistry::class)->label('queues');
    }
}
