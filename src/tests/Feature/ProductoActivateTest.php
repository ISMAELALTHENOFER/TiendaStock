<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reactivation flow: a soft-disabled product (activo=false) can be brought
 * back to active inventory via PATCH /productos/{producto}/activate.
 *
 * Authorization mirrors the resource group: ADMIN + Control Stock may activate,
 * Ventas is forbidden. The row is never deleted — only `activo` flips to true.
 */
class ProductoActivateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->categoria = Categoria::factory()->create();
    }

    public function test_activate_as_admin_sets_activo_true_and_redirects_to_index(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'activo' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('productos.activate', $producto));

        $response->assertRedirect(route('productos.index'));
        $response->assertSessionHas('success', 'Producto activado correctamente.');

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'activo' => true,
        ]);
    }

    public function test_activate_as_control_stock_succeeds(): void
    {
        $controlStock = User::factory()->controlStock()->create();
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'activo' => false,
        ]);

        $response = $this->actingAs($controlStock)
            ->patch(route('productos.activate', $producto));

        $response->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'activo' => true,
        ]);
    }

    public function test_activate_as_ventas_is_forbidden(): void
    {
        $ventas = User::factory()->ventas()->create();
        $producto = Producto::factory()->inactivo()->create([
            'categoria_id' => $this->categoria->id,
        ]);

        $response = $this->actingAs($ventas)
            ->patch(route('productos.activate', $producto));

        $response->assertForbidden();

        // Product stays inactive; untouched.
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'activo' => false,
        ]);
    }

    public function test_activate_keeps_the_product_row_no_deletion(): void
    {
        $producto = Producto::factory()->inactivo()->create([
            'categoria_id' => $this->categoria->id,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('productos.activate', $producto));

        // Exactly one row; reactivation never removes the record.
        $this->assertDatabaseCount('productos', 1);
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'activo' => true,
        ]);
    }

    /**
     * The index template MUST render an 'Activar' affordance for inactive
     * products. The React slice ships it in the productos.jsx source; the
     * affirmative CTA must stay wired through the shared ConfirmDialog (the
     * legacy confirmDialogShow is gone on the React surface).
     */
    public function test_index_view_renders_activar_label(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        $this->assertStringContainsString('Activar', $source, 'The index must render an Activar affordance for inactive products.');
        $this->assertStringContainsString('Sí, activar', $source, 'The activation confirmation CTA must stay wired through the shared dialog.');
        $this->assertStringContainsString('ConfirmDialog', $source, 'The activation toggle must use the shared confirm dialog.');
    }
}
