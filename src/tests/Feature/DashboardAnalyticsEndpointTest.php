<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAnalyticsEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_dashboard_period_is_available_to_admin_only(): void
    {
        $admin = User::factory()->admin()->create();
        foreach ([1, 7, 30] as $days) {
            $this->actingAs($admin)->getJson("/dashboard/analytics?window={$days}")
                ->assertOk()->assertJsonPath('window.days', $days);
        }
        $this->actingAs(User::factory()->create(['role' => 'Ventas']))
            ->getJson('/dashboard/analytics?window=1')->assertForbidden();
    }

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
