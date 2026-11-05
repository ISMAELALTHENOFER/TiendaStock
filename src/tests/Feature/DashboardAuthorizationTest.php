<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_endpoints_require_an_authenticated_admin(): void
    {
        foreach (['/dashboard', '/dashboard/activity', '/dashboard/analytics'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }

        foreach (['Ventas', 'Control Stock', null, 'invalid'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            foreach (['/dashboard', '/dashboard/activity', '/dashboard/analytics'] as $url) {
                $this->get($url)->assertForbidden();
            }
        }

        $this->actingAs(User::factory()->admin()->unverified()->create());
        foreach (['/dashboard', '/dashboard/activity', '/dashboard/analytics'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
