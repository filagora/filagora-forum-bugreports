<?php

declare(strict_types=1);

namespace Filagora\ForumBugReports\Filament\Resources\BugReports\Pages;

use Filagora\ForumBugReports\Enums\BugReportStatus;
use Filagora\ForumBugReports\Filament\Resources\BugReports\BugReportResource;
use Filagora\ForumBugReports\Models\BugReport;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditBugReport extends EditRecord
{
    protected static string $resource = BugReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewThread')
                ->label(__('filagora-forum-bugreports::messages.view_thread'))
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->visible(fn (): bool => $this->threadUrl() !== null)
                ->url(fn (): ?string => $this->threadUrl())
                ->openUrlInNewTab(),
            ViewAction::make(),
        ];
    }

    private function threadUrl(): ?string
    {
        $record = $this->getRecord();

        return $record instanceof BugReport ? BugReportResource::threadUrl($record->thread_id) : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $status = BugReportStatus::from((string) ($data['status'] ?? ''));

        if ($status === BugReportStatus::CONFIRMED) {
            $data['confirmed_at'] = $this->record->confirmed_at ?? now();
            $data['completed_at'] = null;

            return $data;
        }

        if ($status->isTerminal()) {
            $data['completed_at'] = $this->record->completed_at ?? now();

            return $data;
        }

        $data['confirmed_at'] = null;
        $data['completed_at'] = null;

        return $data;
    }
}
