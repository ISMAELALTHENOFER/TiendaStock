<?php

namespace App\Http\Controllers;

use App\Services\DashboardAnalytics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardAnalyticsController extends Controller
{
    public function __invoke(Request $request, DashboardAnalytics $analytics): JsonResponse
    {
        $validated = $request->validate([
            'window' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        return response()->json($analytics->forWindow((int) ($validated['window'] ?? 30)))
            ->header('Cache-Control', 'no-store');
    }
}
