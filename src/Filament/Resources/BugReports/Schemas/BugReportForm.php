<?php

declare(strict_types=1);

namespace Filagora\ForumBugReports\Filament\Resources\BugReports\Schemas;

use Filagora\ForumBugReports\Enums\BugReportStatus;
use Filagora\ForumBugReports\Models\BugReport;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BugReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('thread.title')
                    ->label(__('filagora-forum-bugreports::messages.form.thread_id'))
                    ->content(fn ($record): string => (string) ($record->thread->title ?? '-')),
                Placeholder::make('reportedBy.'.BugReport::userAttribute())
                    ->label(__('filagora-forum-bugreports::messages.form.reported_by'))
                    ->content(fn ($record): string => (string) ($record?->reportedBy?->getAttribute(BugReport::userAttribute()) ?? $record->reportedBy->email ?? __('filagora-forum-bugreports::messages.unknown'))),
                Textarea::make('description')
                    ->rows(4)
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Select::make('status')
                    ->required()
                    ->options(collect(BugReportStatus::cases())->mapWithKeys(
                        fn (BugReportStatus $status): array => [$status->value => $status->label()]
                    )),
                Select::make('assigned_to_id')
                    ->label(__('filagora-forum-bugreports::messages.table.assigned_to'))
                    ->relationship('assignedTo', BugReport::userAttribute())
                    ->searchable()
                    ->preload(),
                Textarea::make('resolution_note')
                    ->rows(4)
                    ->maxLength(5000)
                    ->columnSpanFull(),
            ]);
    }
}
