<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAnalyticsEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_receives_the_default_thirty_day_contract(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->getJson('/dashboard/analytics')
            ->assertOk()
            ->assertJsonPath('window.days', 30)
            ->assertJsonPath('series.sales_by_day', [])
            ->assertJsonPath('series.sales_by_category', []);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_invalid_window_returns_unprocessable_entity(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)->getJson('/dashboard/analytics?window=0')
            ->assertStatus(422)
            ->assertJsonValidationErrors('window');
    }

    public function test_guest_is_not_given_chart_data(): void
    {
        $this->getJson('/dashboard/analytics')->assertUnauthorized();
    }
}
