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
     * products (client-side, gated by `producto.activo`). The button lives in
     * the static Alpine <template x-for> markup, so 'Activar' is visible in
     * the server-rendered HTML even before hydration.
     */
    public function test_index_view_renders_activar_label(): void
    {
        $response = $this->actingAs($this->admin)->get(route('productos.index'));

        $response->assertOk();
        $response->assertSee('Activar');
        // The green confirmation CTA must be wired through confirmDialogShow.
        $response->assertSee('Sí, activar');
        $response->assertSee('bg-green-600 hover:bg-green-700');
    }
}