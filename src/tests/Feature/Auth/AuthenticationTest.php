<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login')->assertStatus(200)->assertSee('id="react-root"', false);
        $props = $this->loginProps($response->getContent());

        $this->assertSame('login', $props['page']);
        $this->assertNull($props['authError']);
    }

    public function test_login_screen_has_no_dangling_error_descriptions(): void
    {
        $props = $this->loginProps($this->get('/login')->getContent());

        $this->assertSame([], $props['errors']);
        $this->assertNull($props['authError']);
    }

    public function test_remember_me_sets_a_persistent_login_cookie_only_when_checked(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['username' => $user->username, 'password' => 'password', 'remember' => '1'])
            ->assertCookie(auth()->guard()->getRecallerName());
        $this->assertNotNull($user->fresh()->remember_token);

        auth()->logout();
        $this->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertCookieMissing(auth()->guard()->getRecallerName());
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('profile.edit', absolute: false));
    }

    public function test_login_defaults_follow_the_user_role(): void
    {
        foreach (['ADMIN' => 'dashboard', 'Ventas' => 'ventas.pos', 'Control Stock' => 'productos.index', '' => 'profile.edit', 'invalid' => 'profile.edit'] as $role => $destination) {
            $user = User::factory()->create(['role' => $role ?: null]);
            $this->post('/login', ['username' => $user->username, 'password' => 'password'])
                ->assertRedirect(route($destination, absolute: false));
            $this->post('/logout');
        }
    }

    public function test_intended_dashboard_redirect_remains_subject_to_role_authorization(): void
    {
        $user = User::factory()->ventas()->create();
        $this->get('/dashboard')->assertRedirect('/login');
        $this->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertForbidden();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');

        $props = $this->loginProps($this->get('/login')->getContent());

        $this->assertSame(__('auth.failed'), $props['authError']);
        $this->assertSame(['username' => [__('auth.failed')]], $props['errors']);
    }

    public function test_inactive_account_is_reported_only_after_valid_password(): void
    {
        $user = User::factory()->create(['activo' => false]);

        $this->post('/login', ['username' => $user->username, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['username' => __('auth.failed')]);
        $this->post('/login', ['username' => 'missing-user', 'password' => 'password'])
            ->assertSessionHasErrors(['username' => __('auth.failed')]);
        $this->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertSessionHasErrors(['username' => 'Esta cuenta está desactivada.']);
        $this->assertGuest();
    }

    public function test_inactive_authenticated_user_is_logged_out_on_next_non_logout_request(): void
    {
        $user = User::factory()->ventas()->create();
        $this->actingAs($user);
        $user->update(['activo' => false]);

        $this->get('/profile')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_inactive_authenticated_user_can_still_explicitly_logout(): void
    {
        $user = User::factory()->create(['activo' => false]);
        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_missing_credentials_keep_field_errors_without_authentication_alert(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['username', 'password']);

        $props = $this->loginProps($this->get('/login')->getContent());

        $this->assertNull($props['authError']);
        $this->assertArrayHasKey('username', $props['errors']);
        $this->assertArrayHasKey('password', $props['errors']);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    private function loginProps(string $html): array
    {
        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $node = (new DOMXPath($document))->query('//*[@id="react-root"]')->item(0);
        $this->assertNotNull($node);

        return json_decode($node->getAttribute('data-props'), true, flags: JSON_THROW_ON_ERROR);
    }
}
