<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaInlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category_inline_and_receives_json_id_nombre(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson(route('categorias.inline'), [
            'nombre' => 'Pantalones',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['id', 'nombre']);
        $this->assertDatabaseHas('categorias', ['nombre' => 'Pantalones']);
    }

    public function test_control_stock_can_create_category_inline(): void
    {
        $controlStock = User::factory()->controlStock()->create();

        $response = $this->actingAs($controlStock)->postJson(route('categorias.inline'), [
            'nombre' => 'Remeras',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('nombre', 'Remeras');
    }

    public function test_duplicate_nombre_rejected_with_422_and_no_row_inserted(): void
    {
        $admin = User::factory()->admin()->create();
        Categoria::factory()->create(['nombre' => 'Existente']);

        $response = $this->actingAs($admin)->postJson(route('categorias.inline'), [
            'nombre' => 'Existente',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nombre']);
        $this->assertDatabaseCount('categorias', 1);
    }

    public function test_ventas_role_gets_403_on_inline_category_creation(): void
    {
        $ventas = User::factory()->ventas()->create();

        $response = $this->actingAs($ventas)->postJson(route('categorias.inline'), [
            'nombre' => 'SoloVentas',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('categorias', ['nombre' => 'SoloVentas']);
    }

    public function test_guest_redirected_to_login_on_inline_category_creation(): void
    {
        $response = $this->postJson(route('categorias.inline'), [
            'nombre' => 'Invitado',
        ]);

        // Unauthenticated → middleware redirects to login (302) for non-JSON,
        // but postJson may get 401. The route sits behind `auth` middleware.
        $this->assertTrue(in_array($response->status(), [302, 401], true));
        $this->assertDatabaseMissing('categorias', ['nombre' => 'Invitado']);
    }

    public function test_validation_requires_nombre(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson(route('categorias.inline'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nombre']);
    }
}
