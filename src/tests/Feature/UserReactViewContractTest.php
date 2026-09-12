<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Coexistence contract for the migrated Usuarios slice (react driver).
 *
 * admin.users.index/create/edit are React-owned by default but MUST roll back
 * to the legacy Blade surface via the per-route override or the global
 * FRONTEND_DRIVER=blade switch (SH-R1). The React list preserves the current
 * fields, role badges, single edit action, server pagination and the Spanish
 * empty state; the forms preserve validation, CSRF, redirects and the
 * password-retention contract (SH-R3). Server ADMIN authorization stays in
 * Laravel middleware and is covered by the untouched backend tests.
 */
class UserReactViewContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_routes_render_the_react_host_when_enabled(): void
    {
        $source = file_get_contents(base_path('resources/views/layouts/app.blade.php'));

        $this->assertStringContainsString("'admin.users.index'", $source);
        $this->assertStringContainsString("'admin.users.create'", $source);
        $this->assertStringContainsString("'admin.users.edit'", $source);
        $this->assertStringContainsString("@include('react.app'", $source);
    }

    public function test_user_index_can_roll_back_to_the_legacy_blade_surface(): void
    {
        $admin = User::factory()->admin()->create();
        config(['frontend.routes.admin.users.index' => 'blade']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Usuarios del Sistema');
        $response->assertSee('Gestiona todos los usuarios de la plataforma');
    }

    public function test_user_create_can_roll_back_to_the_legacy_blade_form(): void
    {
        $admin = User::factory()->admin()->create();
        config(['frontend.routes.admin.users.create' => 'blade']);

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Nuevo Usuario');
        $response->assertSee('Información del Usuario');
    }

    public function test_user_edit_can_roll_back_to_the_legacy_blade_form(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        config(['frontend.routes.admin.users.edit' => 'blade']);

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $user));

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Editar Usuario');
    }

    public function test_global_blade_driver_restores_the_users_surface(): void
    {
        $admin = User::factory()->admin()->create();
        config(['frontend.driver' => 'blade']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Usuarios del Sistema');
    }

    public function test_react_user_index_preserves_fields_role_badges_and_edit_action(): void
    {
        $source = file_get_contents(base_path('resources/js/react/users.jsx'));

        foreach ([
            'Usuarios del Sistema',
            'Nombre',
            'Usuario',
            'Email',
            'Rol',
            'Acciones',
            'Administrador',
            'Ventas',
            'Control Stock',
            'Sin rol',
            'Editar',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }

        // The Ventas badge keeps the legacy blue semantic tone; the shared
        // Badge primitive must carry the info tone that completes the four
        // semantic states from the source spec (success/warning/danger/info).
        $badge = file_get_contents(base_path('resources/js/react/components/ui/Badge.jsx'));
        $this->assertStringContainsString("info: 'bg-sky-100 text-sky-800'", $badge);
    }

    public function test_react_user_index_preserves_server_pagination_and_empty_state(): void
    {
        $source = file_get_contents(base_path('resources/js/react/users.jsx'));

        foreach ([
            'last_page',
            'current_page',
            '?page=',
            'Mostrando',
            'usuarios',
            'No hay usuarios en el sistema',
            'Crea tu primer usuario para comenzar',
            'Nuevo Usuario',
            'Crear Primer Usuario',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_user_mobile_cards_keep_complete_information(): void
    {
        $source = file_get_contents(base_path('resources/js/react/users.jsx'));

        // Mobile cards must carry the same field set as the desktop table —
        // nothing hidden or clipped at narrow widths.
        foreach (['md:hidden', '@ {user.username}', 'Editar usuario'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_user_create_form_preserves_fields_and_validation_contract(): void
    {
        $source = file_get_contents(base_path('resources/js/react/users.jsx'));

        foreach ([
            'Información del Usuario',
            'Completa los detalles para crear un nuevo usuario',
            'Nombre completo',
            'Nombre de usuario',
            'Correo electrónico',
            'Contraseña',
            'Confirmar contraseña',
            'Rol de usuario',
            'Crear Usuario',
            'Cancelar',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_user_edit_form_preserves_password_retention_and_update_flow(): void
    {
        $source = file_get_contents(base_path('resources/js/react/users.jsx'));

        foreach ([
            'Modificar Usuario',
            'Actualiza los detalles del usuario',
            'dejar en blanco para mantener la actual',
            'Actualizar Usuario',
            '_method',
            'PUT',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_user_form_posts_to_the_existing_laravel_store_contract(): void
    {
        $source = file_get_contents(base_path('resources/js/react/users.jsx'));

        // The create form must POST to the resource store endpoint
        // (admin.users.index URL == /admin/users) and the edit form to the
        // PUT-spoofed update URL — never to the GET create path.
        $this->assertStringContainsString('const action = isEdit ? `${routes.users}/${usuario.id}` : routes.users;', $source);

        foreach (["Accept: 'application/json'", 'X-CSRF-TOKEN', 'response.redirected'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }
}
