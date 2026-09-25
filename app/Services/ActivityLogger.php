<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(
        ?User $actor,
        string $action,
        string $subjectType,
        ?int $subjectId,
        string $subjectLabel,
        array $properties = [],
    ): void {
        ActivityLog::query()->create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'subject_label' => $subjectLabel,
            'properties' => $properties === [] ? null : $properties,
        ]);
    }
}
