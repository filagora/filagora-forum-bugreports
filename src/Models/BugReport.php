<?php

declare(strict_types=1);

namespace Filagora\ForumBugReports\Models;

use Carbon\CarbonInterface;
use Filagora\Core\Support\Tables;
use Filagora\Core\Support\UserModel;
use Filagora\ForumBugReports\Enums\BugReportStatus;
use Filagora\Forums\Support\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $thread_id
 * @property int $reported_by_id
 * @property int|null $assigned_to_id
 * @property BugReportStatus $status
 * @property string|null $description
 * @property string|null $resolution_note
 * @property CarbonInterface|null $confirmed_at
 * @property CarbonInterface|null $completed_at
 * @property CarbonInterface|null $created_at
 */
class BugReport extends Model
{
    protected $fillable = [
        'thread_id',
        'reported_by_id',
        'assigned_to_id',
        'status',
        'description',
        'resolution_note',
        'confirmed_at',
        'completed_at',
    ];

    /**
     * The attribute that names a person on a bug report.
     */
    public static function userAttribute(): string
    {
        $attribute = config('filagora-forum-bugreports.user_attribute');

        return is_string($attribute) && $attribute !== '' ? $attribute : UserModel::titleAttribute();
    }

    public function getTable(): string
    {
        return Tables::name('bug_reports');
    }

    protected function casts(): array
    {
        return [
            'status' => BugReportStatus::class,
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Model, $this>
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Models::class('thread'), 'thread_id');
    }

    /**
     * @return BelongsTo<Model, $this>
     */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class(), 'reported_by_id');
    }

    /**
     * @return BelongsTo<Model, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(UserModel::class(), 'assigned_to_id');
    }
}
