<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['frontend.driver' => 'blade']);
    }

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(200);
    }

    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_confirmation_redirects_by_role_and_preserves_intended_url(): void
    {
        foreach (['ADMIN' => '/dashboard', 'Ventas' => '/ventas/pos', 'Control Stock' => '/productos', '' => '/profile', 'invalid' => '/profile'] as $role => $destination) {
            $user = User::factory()->create(['role' => $role ?: null]);
            $this->actingAs($user)->post('/confirm-password', ['password' => 'password'])->assertRedirect($destination);
        }

        $this->actingAs(User::factory()->ventas()->create())
            ->withSession(['url.intended' => '/dashboard'])
            ->post('/confirm-password', ['password' => 'password'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertForbidden();
    }
}
