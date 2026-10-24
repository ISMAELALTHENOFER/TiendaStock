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
        $content = $this->get('/login')->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1\b[^>]*>.*?<\/h1>/is', $content));
        $this->assertStringContainsString('for="username"', $content);
        $this->assertStringContainsString('for="password"', $content);
        $this->assertStringContainsString('action="'.route('login').'"', $content);
    }

    public function test_login_links_to_password_recovery(): void
    {
        $content = $this->get('/login')->getContent();

        $this->assertStringContainsString('href="'.route('password.request').'"', $content);
        $this->assertStringContainsString('¿Olvidaste tu contraseña?', $content);
    }

    public function test_login_explains_its_purpose(): void
    {
        $content = $this->get('/login')->getContent();

        $this->assertStringContainsString('Ingresa a TiendaStock para gestionar tu negocio.', $content);
    }

    public function test_logout_and_profile_deletion_redirect_through_root_to_login(): void
    {
        $logoutUser = User::factory()->create();
        $logoutResponse = $this->actingAs($logoutUser)->post('/logout');

        $logoutResponse->assertRedirect('/');
        $this->get('/')->assertRedirect('/login');

        $deletionUser = User::factory()->create();
        $deletionResponse = $this->actingAs($deletionUser)->delete('/profile', [
            'password' => 'password',
        ]);

        $deletionResponse->assertRedirect('/');
        $this->get('/')->assertRedirect('/login');
    }

    public function test_authenticated_root_still_redirects_to_login_and_login_to_dashboard(): void
    {
        $user = User::factory()->create();

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
        $this->assertStringContainsString('class="login-form"', $content);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $stylesheet);
        $this->assertStringContainsString(':focus-visible', $stylesheet);
    }
}
