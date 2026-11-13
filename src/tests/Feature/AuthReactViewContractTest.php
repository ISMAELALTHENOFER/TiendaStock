<?php

namespace Tests\Feature;

use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthReactViewContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_react_host_includes_refresh_preamble_before_its_entrypoint(): void
    {
        $host = file_get_contents(resource_path('views/react/app.blade.php'));

        $this->assertStringContainsString("@viteReactRefresh\n@vite('resources/js/react/main.jsx')", $host);
    }

    public function test_login_renders_the_react_host(): void
    {
        $response = $this->get('/login')->assertOk()->assertSee('id="react-root"', false)->assertSee('data-theme-system', false);
        $this->assertSame(1, substr_count($response->getContent(), 'data-theme-toggle aria-pressed'));
        $response->assertSee('data-login-theme-toggle', false)->assertSee('login-react__theme-moon')->assertSee('login-react__theme-sun');
        $response->assertDontSee('class="login-brand"', false);
    }

    public function test_profile_renders_the_react_host(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/profile')->assertOk()->assertSee('id="react-root"', false);
    }

    public function test_react_auth_forms_keep_existing_server_endpoints_and_validation(): void
    {
        $auth = file_get_contents(resource_path('js/react/auth.jsx'));
        $this->assertStringContainsString('Gestioná tu negocio de forma simple', $auth);
        foreach (['props.routes.login', 'props.routes.profile', 'props.routes.passwordUpdate', 'name="_method"', 'current_password', 'password_confirmation', 'csrf-token', 'aria-describedby', 'aria-pressed'] as $contract) {
            $this->assertStringContainsString($contract, $auth);
        }

        $this->post('/login', [])->assertSessionHasErrors(['username', 'password']);
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/profile', [])->assertSessionHasErrors(['name', 'username', 'email']);
        $this->actingAs($user)->put('/password', [])->assertSessionHasErrorsIn('updatePassword', ['current_password', 'password']);
    }

    public function test_react_shell_and_password_fields_keep_mobile_controls_unbroken(): void
    {
        $header = file_get_contents(resource_path('js/react/layout/Header.jsx'));
        $auth = file_get_contents(resource_path('js/react/auth.jsx'));

        $this->assertStringContainsString('whitespace-nowrap font-bold tracking-tight', $header);
        $this->assertStringContainsString('sr-only text-right sm:not-sr-only', $header);
        $this->assertStringContainsString('className="min-w-0"', $auth);
        $this->assertStringContainsString('className="shrink-0 whitespace-nowrap"', $auth);
        $this->assertStringContainsString("aria-label={visible ? 'Ocultar contraseña' : 'Mostrar contraseña'}", $auth);
    }

    public function test_confirm_password_renders_the_react_host(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/confirm-password');
        $response->assertOk()->assertSee('id="react-root"', false);
        $props = $this->reactProps($response->getContent());
        $this->assertSame(route('password.confirm'), $props['routes']['passwordConfirm']);

        $auth = file_get_contents(resource_path('js/react/auth.jsx'));
        $this->assertStringContainsString('props.routes.passwordConfirm', $auth);
        $this->assertStringContainsString('autoComplete="current-password"', $auth);
    }

    public function test_frontend_driver_switch_is_removed(): void
    {
        $this->assertFileDoesNotExist(config_path('frontend.php'));
        $this->assertNull(config('frontend.driver'));
    }

    public function test_auth_react_hosts_receive_laravel_generated_action_urls_and_root_still_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $login = $this->get('/login');
        $this->assertSame(route('login'), $this->reactProps($login->getContent())['routes']['login']);
        $profile = $this->actingAs(User::factory()->create())->get('/profile');
        $profileProps = $this->reactProps($profile->getContent());
        $this->assertSame(route('profile.update'), $profileProps['routes']['profile']);
        $this->assertSame(route('password.update'), $profileProps['routes']['passwordUpdate']);

        $hosts = file_get_contents(resource_path('views/layouts/app.blade.php')) . file_get_contents(resource_path('views/layouts/guest.blade.php'));
        foreach (["'login' => route('login')", "'profile' => route('profile.update')", "'passwordUpdate' => route('password.update')", "'passwordConfirm' => route('password.confirm')"] as $contract) {
            $this->assertStringContainsString($contract, $hosts);
        }
    }

    private function reactProps(string $html): array
    {
        $document = new DOMDocument;
        $previousErrorMode = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);
        $node = (new DOMXPath($document))->query('//*[@id="react-root"]')->item(0);
        $this->assertNotNull($node);

        return json_decode($node->getAttribute('data-props'), true, flags: JSON_THROW_ON_ERROR);
    }
}
