<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Bug Reports',
    'model_label' => 'Bug Report',
    'plural_model_label' => 'Bug Reports',
    'badge_tooltip' => '{1} :count open bug report needs attention.|[2,*] :count open bug reports need attention.',
    'unknown' => 'Unknown',
    'view_thread' => 'View Thread',
    'unassigned' => 'Unassigned',
    'hide_closed' => 'Hide closed',
    'table' => [
        'thread' => 'Thread',
        'reported_by' => 'Reported By',
        'assigned_to' => 'Assigned To',
    ],
    'form' => [
        'thread_id' => 'Thread ID',
        'reported_by' => 'Reported By',
    ],
    'status' => [
        'reported' => 'Reported',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'feature_request' => 'Feature Request',
        'wont_fix' => "Won't Fix",
    ],
    'changes' => [
        'reported' => 'This thread was reported as a bug (:status).',
        'status' => 'Status changed from :from to :to.',
        'assigned' => 'Assigned to :name.',
        'unassigned' => 'Unassigned.',
        'note' => 'Note: :note',
    ],
];
