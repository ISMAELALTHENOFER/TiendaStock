<?php

namespace App\Services;

use App\Models\ActivityEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ActivityRecorder
{
    public static function recordAfterCommit(
        User $actor,
        string $type,
        string $title,
        ?string $description = null,
        ?Model $subject = null,
    ): void {
        DB::afterCommit(function () use ($actor, $type, $title, $description, $subject): void {
            ActivityEvent::create([
                'actor_id' => $actor->id,
                'type' => $type,
                'title' => $title,
                'description' => $description,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'occurred_at' => now(),
            ]);
        });
    }

    public static function recent()
    {
        return ActivityEvent::with(['actor', 'subject'])
            ->where('occurred_at', '>=', now()->subDays(90))
            ->latest('occurred_at')
            ->limit(20)
            ->get();
    }
}
