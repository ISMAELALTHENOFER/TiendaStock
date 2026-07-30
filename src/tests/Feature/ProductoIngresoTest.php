<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoIngresoTest extends TestCase
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

    public function test_create_form_defaults_cantidad_to_one(): void
    {
        $response = $this->actingAs($this->admin)->get(route('productos.create'));

        $response->assertOk();

        $content = $response->getContent();
        // The cantidad input must render value="1" on first load (old('cantidad', 1)).
        $matched = preg_match('/<input[^>]*name="cantidad"[^>]*value="1"/', $content);
        $this->assertSame(1, $matched, 'Create form should default cantidad to 1.');
    }

    public function test_create_form_shows_costo_label_and_not_precio_de_compra(): void
    {
        $response = $this->actingAs($this->admin)->get(route('productos.create'));

        $response->assertOk();
        $response->assertSee('Costo');
        $response->assertDontSee('Precio de Compra');
    }

    public function test_store_with_empty_talle_creates_null_talle(): void
    {
        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Body de algodón',
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 10.50,
            'precio_venta' => 25.00,
            'cantidad' => 5,
            'talle' => '',
            'color' => 'Rojo',
            'descripcion' => 'Detalle',
        ]);

        $response->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Body de algodón',
            'talle' => null,
            'color' => 'Rojo',
        ]);
    }

    public function test_store_with_empty_color_creates_null_color(): void
    {
        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Medias',
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 3.00,
            'precio_venta' => 8.00,
            'cantidad' => 20,
            'talle' => 'Único',
            'color' => '',
        ]);

        $response->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Medias',
            'talle' => 'Único',
            'color' => null,
        ]);
    }

    public function test_store_with_both_empty_talle_and_color_accepts_both_null(): void
    {
        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Gorro único',
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 5.00,
            'precio_venta' => 12.00,
            'cantidad' => 8,
            'talle' => '',
            'color' => '',
        ]);

        $response->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Gorro único',
            'talle' => null,
            'color' => null,
        ]);
    }

    public function test_store_accepts_numeric_precio_and_persists_float(): void
    {
        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Producto numérico',
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 1234.56,
            'precio_venta' => 2000.00,
            'cantidad' => 1,
            'talle' => 'M',
            'color' => 'Azul',
        ]);

        $response->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Producto numérico',
            'precio_compra' => 1234.56,
            'precio_venta' => 2000.00,
        ]);
    }

    public function test_store_rejects_tampered_formatted_string_as_precio_compra(): void
    {
        // Defense-in-depth: even if a malicious client bypasses the Alpine mask
        // and submits the formatted display string "$1.234,56", the backend
        // numeric rule MUST reject it with 422.
        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Producto hackeado',
            'categoria_id' => $this->categoria->id,
            'precio_compra' => '$1.234,56',
            'precio_venta' => 25.00,
            'cantidad' => 1,
            'talle' => 'M',
            'color' => 'Azul',
        ]);

        $response->assertSessionHasErrors('precio_compra');
        $this->assertDatabaseMissing('productos', ['nombre' => 'Producto hackeado']);
    }

    public function test_store_rejects_tampered_formatted_string_as_precio_venta(): void
    {
        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Producto hackeado 2',
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 10.00,
            'precio_venta' => '$1.000,00',
            'cantidad' => 1,
            'talle' => 'M',
            'color' => 'Azul',
        ]);

        $response->assertSessionHasErrors('precio_venta');
        $this->assertDatabaseMissing('productos', ['nombre' => 'Producto hackeado 2']);
    }

    public function test_update_accepts_empty_talle_and_preserves_other_fields(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'talle' => 'L',
            'color' => 'Negro',
        ]);

        $response = $this->actingAs($this->admin)->put(route('productos.update', $producto), [
            'nombre' => $producto->nombre,
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 5.00,
            'precio_venta' => 15.00,
            'cantidad' => 4,
            'talle' => '',
            'color' => '',
        ]);

        $response->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'talle' => null,
            'color' => null,
        ]);
    }

    public function test_edit_form_shows_costo_label(): void
    {
        $producto = Producto::factory()->create(['categoria_id' => $this->categoria->id]);

        $response = $this->actingAs($this->admin)->get(route('productos.edit', $producto));

        $response->assertOk();
        $response->assertSee('Costo');
        $response->assertDontSee('Precio de Compra');
    }

    public function test_talle_field_does_not_carry_stray_step_or_min_attributes(): void
    {
        $response = $this->actingAs($this->admin)->get(route('productos.create'));

        $content = $response->getContent();

        // Find the talle input and ensure no step="0.01" nor min="0" leaked from the old bug.
        $matched = preg_match('/<input[^>]*name="talle"[^>]*>/', $content, $matches);
        $this->assertSame(1, $matched, 'Talle input must exist on create form.');
        $talleInput = $matches[0];
        $this->assertStringNotContainsString('step="0.01"', $talleInput, 'Talle input must not carry step="0.01".');
        $this->assertStringNotContainsString('min="0"', $talleInput, 'Talle input must not carry min="0".');
    }
}
