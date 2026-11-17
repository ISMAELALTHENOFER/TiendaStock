<?php

namespace Tests\Unit;

use App\Models\ActivityEvent;
use App\Models\User;
use App\Services\ActivityRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityRecorderTest extends TestCase
{
    use RefreshDatabase;

    protected function refreshTestDatabase(): void
    {
        $this->artisan('migrate:fresh');
        $this->updateLocalCacheOfInMemoryDatabases();
        RefreshDatabaseState::$migrated = true;
    }

    public function test_records_event_after_an_outer_transaction_commits(): void
    {
        $user = User::factory()->admin()->create();

        DB::transaction(function () use ($user): void {
            ActivityRecorder::recordAfterCommit($user, 'product.created', 'Product created', 'A product was created');

            $this->assertDatabaseCount('activity_events', 0);
        });

        $this->assertDatabaseHas('activity_events', [
            'actor_id' => $user->id,
            'type' => 'product.created',
            'title' => 'Product created',
            'description' => 'A product was created',
        ]);
    }

    public function test_does_not_record_when_the_transaction_rolls_back(): void
    {
        $user = User::factory()->admin()->create();

        try {
            DB::transaction(function () use ($user): void {
                ActivityRecorder::recordAfterCommit($user, 'product.created', 'Product created', 'A product was created');

                throw new \RuntimeException('rollback');
            });
        } catch (\RuntimeException) {
        }

        $this->assertDatabaseCount('activity_events', 0);
    }

    public function test_returns_newest_events_and_prunes_events_older_than_ninety_days(): void
    {
        $user = User::factory()->admin()->create();
        ActivityEvent::factory()->create(['actor_id' => $user->id, 'occurred_at' => now()->subDays(91)]);
        ActivityEvent::factory()->count(21)->create(['actor_id' => $user->id]);

        $events = ActivityRecorder::recent();

        $this->assertCount(20, $events);
        $this->assertDatabaseCount('activity_events', 22);
    }
}
