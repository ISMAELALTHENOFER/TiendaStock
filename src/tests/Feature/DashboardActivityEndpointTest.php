<?php

namespace Tests\Feature;

use App\Models\ActivityEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardActivityEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_receives_newest_events_with_a_twenty_item_limit(): void
    {
        $user = User::factory()->admin()->create();
        ActivityEvent::factory()->count(21)->create(['actor_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/dashboard/activity');

        $response->assertOk()->assertJsonStructure([
            'data' => [['id', 'type', 'title', 'description', 'actor', 'subject', 'occurred_at']],
            'meta' => ['limit'],
        ])->assertJsonPath('meta.limit', 20);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertCount(20, $response->json('data'));
    }

    public function test_guest_keeps_the_existing_dashboard_auth_redirect(): void
    {
        $this->getJson('/dashboard/activity')->assertUnauthorized();
    }

    public function test_unverified_user_is_rejected(): void
    {
        $user = User::factory()->admin()->unverified()->create();

        $this->actingAs($user)->getJson('/dashboard/activity')->assertForbidden();
    }

    public function test_verified_user_receives_an_explicit_empty_data_set(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->getJson('/dashboard/activity')
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.limit', 20);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
