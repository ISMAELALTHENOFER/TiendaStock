<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk()
            ->assertSee('images/tiendastock-mark.svg')
            ->assertSee('aria-current="page"', false)
            ->assertSee('bg-primary-container', false)
            ->assertSee('Mi perfil')
            ->assertSee('Administre sus credenciales corporativas, preferencias de seguridad y parámetros de identidad dentro del sistema de inventario TiendaStock.')
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee('Configuración personal')
            ->assertSee('w-full space-y-6', false)
            ->assertSee('p-4 sm:p-6 lg:p-8', false)
            ->assertSee('text-xl font-bold text-ink', false)
            ->assertSee('bg-surface-muted', false)
            ->assertSee('login-input-icon', false)
            ->assertSee('login-password-toggle', false)
            ->assertSee('Mostrar contraseña', false)
            ->assertSee('Ocultar contraseña', false)
            ->assertSee('aria-pressed', false)
            ->assertDontSee('Zona de peligro')
            ->assertSee('Información del perfil')
            ->assertSee('Nombre de usuario')
            ->assertSee('Cambiar contraseña')
            ->assertDontSee('Eliminar cuenta')
            ->assertSee('Cerrar navegación');

        $this->assertGreaterThanOrEqual(3, substr_count($response->getContent(), 'class="login-input-icon"'));
        $this->assertGreaterThanOrEqual(1, substr_count($response->getContent(), 'class="login-password-toggle"'));
        $this->assertGreaterThanOrEqual(5, substr_count($response->getContent(), 'required="required"'));
        foreach (['current_password', 'password', 'password_confirmation'] as $field) {
            $this->assertMatchesRegularExpression(
                '/<input\b(?=[^>]*\bid="update_password_' . $field . '")(?=[^>]*\s+type="password")[^>]*>/',
                $response->getContent()
            );
        }
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('testuser', $user->username);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_forms_fill_the_available_content_width_without_changing_actions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()
            ->assertSee('class="w-full space-y-6"', false)
            ->assertDontSee('class="w-full lg:w-2/3 space-y-6"', false)
            ->assertDontSee('class="max-w-xl"', false)
            ->assertSee('action="'.route('profile.update').'"', false)
            ->assertSee('action="'.route('password.update').'"', false);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'username' => $user->username,
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_cannot_delete_their_account_or_lose_their_sales(): void
    {
        foreach ([User::ROLE_ADMIN, User::ROLE_VENTAS, User::ROLE_CONTROL_STOCK] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $sale = Venta::factory()->create(['user_id' => $user->id]);
            $userBefore = $user->fresh()->getAttributes();
            $saleBefore = $sale->fresh()->getAttributes();

            foreach ([[], ['password' => 'wrong-password'], ['password' => 'password']] as $payload) {
                $this->actingAs($user)->delete('/profile', $payload)->assertStatus(405);
                $this->assertSame($userBefore, $user->fresh()->getAttributes());
                $this->assertSame($saleBefore, $sale->fresh()->getAttributes());
            }
        }
    }

}
