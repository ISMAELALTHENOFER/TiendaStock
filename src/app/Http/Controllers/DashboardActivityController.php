<?php

namespace App\Http\Controllers;

use App\Services\ActivityRecorder;
use Illuminate\Http\JsonResponse;

class DashboardActivityController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => ActivityRecorder::recent()->map(fn ($event): array => [
                'id' => $event->id,
                'type' => $event->type,
                'title' => $event->title,
                'description' => $event->description,
                'actor' => $event->actor,
                'subject' => $event->subject,
                'occurred_at' => $event->occurred_at?->toISOString(),
            ])->values(),
            'meta' => ['limit' => 20],
        ])->header('Cache-Control', 'no-store');
    }
}
