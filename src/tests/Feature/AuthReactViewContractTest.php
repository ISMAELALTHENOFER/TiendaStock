<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthReactViewContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_uses_react_by_default_and_keeps_blade_fallback(): void
    {
        $this->get('/login')->assertOk()->assertSee('id="react-root"', false);

        config(['frontend.routes.login' => 'blade']);
        $this->get('/login')->assertOk()->assertDontSee('id="react-root"')->assertSee('class="login-brand"', false);

        config(['frontend.routes.login' => 'react', 'frontend.driver' => 'blade']);
        $this->get('/login')->assertOk()->assertDontSee('id="react-root"');
    }

    public function test_profile_uses_react_and_keeps_route_and_global_blade_fallbacks(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/profile')->assertOk()->assertSee('id="react-root"', false);

        config(['frontend.routes.profile.edit' => 'blade']);
        $this->assertSame('blade', config('frontend.routes.profile.edit'));
        $this->actingAs($user)->get('/profile')->assertOk()->assertDontSee('id="react-root"')->assertSee('Información del perfil');

        config(['frontend.routes.profile.edit' => 'react', 'frontend.driver' => 'blade']);
        $this->actingAs($user)->get('/profile')->assertOk()->assertDontSee('id="react-root"');
    }

    public function test_react_auth_forms_keep_existing_server_endpoints_and_validation(): void
    {
        $auth = file_get_contents(resource_path('js/react/auth.jsx'));
        foreach (['props.routes.login', 'props.routes.profile', 'props.routes.passwordUpdate', 'name="_method"', 'current_password', 'password_confirmation', 'csrf-token', 'aria-describedby', 'aria-pressed'] as $contract) {
            $this->assertStringContainsString($contract, $auth);
        }

        $this->post('/login', [])->assertSessionHasErrors(['username', 'password']);
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/profile', [])->assertSessionHasErrors(['name', 'username', 'email']);
        $this->actingAs($user)->put('/password', [])->assertSessionHasErrors(['current_password', 'password']);
    }

    public function test_confirm_password_uses_react_and_keeps_route_specific_blade_fallback(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/confirm-password')
            ->assertOk()->assertSee('id="react-root"', false)->assertSee(route('password.confirm'));

        config(['frontend.routes.password.confirm' => 'blade']);
        $this->assertSame('blade', config('frontend.routes.password.confirm'));
        $this->actingAs($user)->get('/confirm-password')
            ->assertOk()->assertDontSee('id="react-root"')->assertSee('confirm-password');

        $auth = file_get_contents(resource_path('js/react/auth.jsx'));
        $this->assertStringContainsString('props.routes.passwordConfirm', $auth);
        $this->assertStringContainsString('autoComplete="current-password"', $auth);
    }

    public function test_auth_route_driver_environment_keys_are_retained_with_nested_config(): void
    {
        $config = file_get_contents(config_path('frontend.php'));

        $this->assertStringContainsString("'profile' => [", $config);
        $this->assertStringContainsString("'password' => [", $config);
        $this->assertStringContainsString("env('FRONTEND_PROFILE_EDIT_DRIVER', 'react')", $config);
        $this->assertStringContainsString("env('FRONTEND_PASSWORD_CONFIRM_DRIVER', 'react')", $config);
    }

    public function test_auth_react_hosts_receive_laravel_generated_action_urls_and_root_still_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertSee(route('login'));
        $this->actingAs(User::factory()->create())->get('/profile')
            ->assertSee(route('profile.update'))->assertSee(route('password.update'));

        $hosts = file_get_contents(resource_path('views/layouts/app.blade.php')) . file_get_contents(resource_path('views/auth/login.blade.php')) . file_get_contents(resource_path('views/auth/confirm-password.blade.php'));
        foreach (["'login' => route('login')", "'profile' => route('profile.update')", "'passwordUpdate' => route('password.update')", "'passwordConfirm' => route('password.confirm')"] as $contract) {
            $this->assertStringContainsString($contract, $hosts);
        }
    }
}
