<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RootRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_guest_login_has_a_heading_and_named_form_controls(): void
    {
        $this->get('/login')->assertOk()->assertSee('id="react-root"', false);
        $auth = file_get_contents(resource_path('js/react/auth.jsx'));

        $this->assertSame(1, substr_count($auth, '<h1 id="login-title">'));
        $this->assertStringContainsString('htmlFor="username"', $auth);
        $this->assertStringContainsString('htmlFor="password"', $auth);
        $this->assertStringContainsString('action={props.routes.login}', $auth);
    }

    public function test_login_explains_its_purpose(): void
    {
        $auth = file_get_contents(resource_path('js/react/auth.jsx'));

        $this->assertStringContainsString('Ingresa tus datos para continuar a TiendaStock.', $auth);
    }

    public function test_logout_redirects_through_root_to_login(): void
    {
        $logoutUser = User::factory()->create();
        $logoutResponse = $this->actingAs($logoutUser)->post('/logout');

        $logoutResponse->assertRedirect('/');
        $this->get('/')->assertRedirect('/login');

    }

    public function test_authenticated_root_still_redirects_to_login_and_login_to_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $this->get('/login')
            ->assertOk()
            ->assertSee('Iniciar sesión');

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect('/login');

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/dashboard');
    }

    public function test_login_viewport_and_focus_contracts_are_present(): void
    {
        $content = $this->get('/login')->getContent();
        $stylesheet = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($stylesheet);
        $this->assertStringContainsString('name="viewport"', $content);
        $this->assertStringContainsString('class="login-react-host"', $content);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $stylesheet);
        $this->assertStringContainsString(':focus-visible', $stylesheet);
    }

    public function test_authenticated_login_redirects_by_role(): void
    {
        foreach (['ADMIN' => '/dashboard', 'Ventas' => '/ventas/pos', 'Control Stock' => '/productos', '' => '/profile', 'invalid' => '/profile'] as $role => $destination) {
            $user = User::factory()->create(['role' => $role ?: null]);
            $this->actingAs($user)->get('/login')->assertRedirect($destination);
            $this->get($destination)->assertOk();
        }
    }

    public function test_removed_email_recovery_and_verification_routes_are_not_found(): void
    {
        foreach (['/forgot-password', '/reset-password/test-token', '/verify-email', '/email/verification-notification'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }
}
