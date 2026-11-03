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
        $response = $this->get('/login');

        $response
            ->assertStatus(200)
            ->assertSee('class="login-brand" aria-label="TiendaStock"', false)
            ->assertSee('class="login-brand__icon"', false)
            ->assertDontSee('class="w-14 h-14 text-brand-600"', false)
            ->assertDontSee('images/logo-tiendastock.png', false)
            ->assertDontSee('id="login-auth-error"', false);
    }

    public function test_login_screen_has_no_dangling_error_descriptions(): void
    {
        $response = $this->get('/login');
        $xpath = $this->loginXpath($response->getContent());

        foreach (['username', 'password'] as $field) {
            $this->assertSame('false', $xpath->query("//input[@id='$field']")->item(0)->getAttribute('aria-invalid'));
            $this->assertFalse($xpath->query("//input[@id='$field']")->item(0)->hasAttribute('aria-describedby'));
        }

        $this->assertSame(0, $xpath->query('//*[@id="login-auth-error"]')->length);
        $this->assertSame(1, $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " login-footer ")]')->length);
        $this->assertStringContainsString('Todos los derechos reservados • Términos Privacidad', $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " login-footer ")]')->item(0)->textContent);
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

        $response = $this->get('/login');
        $xpath = $this->loginXpath($response->getContent());

        $this->assertSame(1, $xpath->query('//*[@id="login-auth-error" and @role="alert"]')->length);
        $this->assertSame(__('auth.failed'), trim($xpath->query('//*[@id="login-auth-error"]')->item(0)->textContent));
        foreach (['username', 'password'] as $field) {
            $input = $xpath->query("//input[@id='$field']")->item(0);
            $this->assertSame('true', $input->getAttribute('aria-invalid'));
            $this->assertSame('login-auth-error', $input->getAttribute('aria-describedby'));
        }
        $this->assertSame(0, $xpath->query('//*[@id="username-error" or @id="password-error"]')->length);

        $this->assertSame(1, substr_count($response->getContent(), __('auth.failed')));
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

        $xpath = $this->loginXpath($this->get('/login')->getContent());

        $this->assertSame(0, $xpath->query('//*[@id="login-auth-error"]')->length);
        foreach (['username', 'password'] as $field) {
            $this->assertSame(1, $xpath->query("//*[@id='$field-error']")->length);
            $input = $xpath->query("//input[@id='$field']")->item(0);
            $this->assertSame('true', $input->getAttribute('aria-invalid'));
            $this->assertSame("$field-error", $input->getAttribute('aria-describedby'));
        }
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_blade_logout_controls_submit_post_without_logout_links(): void
    {
        config(['frontend.driver' => 'blade']);
        $this->actingAs(User::factory()->admin()->create());

        foreach ([
            [$this->get('/dashboard')->assertOk()->getContent(), 1],
            [view('layouts.navigation')->render(), 2],
        ] as [$html, $expectedForms]) {
            $xpath = $this->loginXpath($html);
            $logout = route('logout');

            $this->assertSame($expectedForms, $xpath->query("//form[@method='POST' and @action='$logout'][input[@name='_token'] and button[@type='submit']]")->length);
            $this->assertSame(0, $xpath->query("//*[@href='$logout']")->length);
        }
    }

    private function loginXpath(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);

        return new DOMXPath($document);
    }
}
