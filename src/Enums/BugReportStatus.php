<?php

declare(strict_types=1);

namespace Filagora\ForumBugReports\Enums;

enum BugReportStatus: string
{
    case REPORTED = 'reported';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case FEATURE_REQUEST = 'feature_request';
    case WONT_FIX = 'wont_fix';

    public function label(): string
    {
        return __('filagora-forum-bugreports::messages.status.'.$this->value);
    }

    /**
     * Terminal statuses close out a bug report: they are left out of the default "active" views and
     * do not clear `completed_at` on further updates.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::COMPLETED, self::FEATURE_REQUEST, self::WONT_FIX => true,
            self::REPORTED, self::CONFIRMED => false,
        };
    }

    /**
     * @return list<string>
     */
    public static function terminalValues(): array
    {
        return array_values(array_map(
            fn (self $case): string => $case->value,
            array_filter(self::cases(), fn (self $case): bool => $case->isTerminal()),
        ));
    }
}
