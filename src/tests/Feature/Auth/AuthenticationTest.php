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
        $response->assertRedirect(route('dashboard', absolute: false));
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

    private function loginXpath(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);

        return new DOMXPath($document);
    }
}
