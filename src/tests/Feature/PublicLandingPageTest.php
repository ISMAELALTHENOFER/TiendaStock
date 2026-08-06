<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_root_renders_the_landing_view(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertViewIs('landing')
            ->assertSee('Gestiona tu tienda con claridad');
    }

    public function test_landing_page_has_required_semantic_landmarks_and_one_heading(): void
    {
        $content = $this->get('/')->getContent();

        $this->assertSame(1, preg_match_all('/<h1\b[^>]*>.*?<\/h1>/is', $content));
        $this->assertStringContainsString('<header', $content);
        $this->assertStringContainsString('<nav', $content);
        $this->assertStringContainsString('<main', $content);
        $this->assertStringContainsString('<footer', $content);
    }

    public function test_navigation_hero_and_final_ctas_link_to_login(): void
    {
        $content = $this->get('/')->getContent();
        $loginUrl = route('login');

        $this->assertSame(3, preg_match_all('/href="'.preg_quote($loginUrl, '/').'"/', $content));
        $this->assertStringContainsString('Iniciar sesión', $content);
    }

    public function test_landing_page_explains_features_and_how_the_product_works(): void
    {
        $content = $this->get('/')->getContent();

        $this->assertSame(3, preg_match_all('/<article\b[^>]*class="landing-feature-card"/i', $content));
        $this->assertStringContainsString('Inventario preciso', $content);
        $this->assertStringContainsString('Categorías claras', $content);
        $this->assertStringContainsString('Ventas conectadas', $content);
        $this->assertSame(1, preg_match_all('/<ol\b[^>]*class="landing-step-list"/i', $content));
        $this->assertStringContainsString('Ordená tu operación. Crecé con confianza.', $content);
    }

    public function test_logout_and_profile_deletion_redirect_to_the_public_landing_page(): void
    {
        $logoutUser = User::factory()->create();
        $logoutResponse = $this->actingAs($logoutUser)->post('/logout');

        $logoutResponse->assertRedirect('/');
        $this->get('/')->assertSee('Gestiona tu tienda con claridad');

        $deletionUser = User::factory()->create();
        $deletionResponse = $this->actingAs($deletionUser)->delete('/profile', [
            'password' => 'password',
        ]);

        $deletionResponse->assertRedirect('/');
        $this->get('/')->assertSee('Gestiona tu tienda con claridad');
    }

    public function test_authenticated_root_and_login_contract_remain_unchanged(): void
    {
        $user = User::factory()->create();

        $this->get('/login')
            ->assertOk()
            ->assertSee('Iniciar sesión');

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertViewIs('landing')
            ->assertSee('Gestiona tu tienda con claridad');

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/dashboard');
    }

    public function test_landing_accessibility_and_responsive_css_contracts_are_present(): void
    {
        $content = $this->get('/')->getContent();
        $stylesheet = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($stylesheet);
        $this->assertStringContainsString('aria-label="Navegación principal"', $content);
        $this->assertStringContainsString('min-height: 2.75rem', $stylesheet);
        $this->assertStringContainsString('@media (min-width: 412px)', $stylesheet);
        $this->assertStringContainsString('@media (min-width: 1024px)', $stylesheet);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $stylesheet);
        $this->assertStringContainsString(':focus-visible', $stylesheet);
    }
}
