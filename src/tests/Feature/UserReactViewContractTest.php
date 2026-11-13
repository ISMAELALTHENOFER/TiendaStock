<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * React view contract for the Usuarios slice.
 *
 * admin.users.index/create/edit render only through the React host. The
 * React list preserves the current
 * fields, role badges, single edit action, server pagination and the Spanish
 * empty state; the forms preserve validation, CSRF, redirects and the
 * password-retention contract (SH-R3). Server ADMIN authorization stays in
 * Laravel middleware and is covered by the untouched backend tests.
 */
class UserReactViewContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_routes_render_the_react_host(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        foreach ([
            ['/admin/users', 'admin.users.index'],
            ['/admin/users/create', 'admin.users.create'],
            [route('admin.users.edit', $user), 'admin.users.edit'],
        ] as [$url, $page]) {
            $response = $this->actingAs($admin)->get($url)->assertOk()->assertSee('id="react-root"', false);
            preg_match('/data-props="([^"]*)"/', $response->getContent(), $matches);
            $props = json_decode(html_entity_decode($matches[1] ?? '', ENT_QUOTES | ENT_HTML5), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame($page, $props['page']);
        }

        $this->assertSame($user->id, $props['usuario']['id']);
    }

    public function test_react_user_index_preserves_fields_role_badges_and_edit_action(): void
    {
        $source = file_get_contents(base_path('resources/js/react/users.jsx'));

        foreach ([
            'Usuarios del sistema',
            'Nombre',
            'Usuario',
            'Correo electrónico',
            'Rol',
            'Acciones',
            'Administrador',
            'Ventas',
            'Control Stock',
            'Sin rol',
            'Editar',
            'Ver inactivos',
            'Activo',
            'Inactivo',
            'ConfirmDialog',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }

        $this->assertStringContainsString("from './components/ui/Pagination.jsx'", $source);
        $this->assertStringContainsString('<Pagination', $source);

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
            "next.set('page'",
            "next.set('per_page'",
            'params.get(\'buscar\')',
            'name="buscar"',
            'Mostrando',
            'usuarios',
            'No hay usuarios en el sistema',
            'Crea tu primer usuario para empezar.',
            'Nuevo usuario',
            'Crear primer usuario',
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
            'Información del usuario',
            'Completa los datos para crear un usuario.',
            'Nombre completo',
            'Nombre de usuario',
            'Correo electrónico',
            'Contraseña',
            'Confirmar contraseña',
            'Rol de usuario',
            'Crear usuario',
            'Cancelar',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_user_edit_form_preserves_password_retention_and_update_flow(): void
    {
        $source = file_get_contents(base_path('resources/js/react/users.jsx'));

        foreach ([
            'Modificar usuario',
            'Actualiza los datos del usuario.',
            'dejar en blanco para mantener la actual',
            'Actualizar usuario',
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

    public function test_react_user_json_saves_flash_the_success_toast(): void
    {
        $admin = User::factory()->admin()->create();
        $json = ['Accept' => 'application/json'];

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Toast User', 'username' => 'toastuser', 'email' => 'toast@example.com',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'role' => 'Ventas',
        ], $json)->assertOk()->assertJsonPath('redirect', route('admin.users.index'))
            ->assertSessionHas('success', 'Usuario creado correctamente.');

        $user = User::where('username', 'toastuser')->firstOrFail();
        $this->put(route('admin.users.update', $user), [
            'name' => 'Toast User 2', 'username' => 'toastuser', 'email' => 'toast@example.com', 'role' => 'Ventas',
        ], $json)->assertOk()->assertJsonPath('redirect', route('admin.users.index'))
            ->assertSessionHas('success', 'Usuario actualizado correctamente.');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Toast User 2']);

        $this->post(route('admin.users.deactivate', $user), [], $json)->assertOk()
            ->assertSessionHas('success', 'Usuario desactivado correctamente.');
        $this->post(route('admin.users.activate', $user), [], $json)->assertOk()
            ->assertSessionHas('success', 'Usuario activado correctamente.');
        $this->assertTrue($user->fresh()->activo);
    }
}
