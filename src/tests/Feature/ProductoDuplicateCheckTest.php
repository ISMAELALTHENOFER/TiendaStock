<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Duplicate-product detection on the create flow.
 *
 * When an admin/Control Stock user types a product name that already exists
 * (active OR soft-disabled), the create form prompts them to edit the
 * existing record instead of creating a duplicate. The backend exposes
 * GET /productos/check-duplicate?nombre=X (exact case-insensitive match,
 * includes inactive rows), and the create form fires a blur-triggered
 * advisory popup that redirects to the edit page on confirm.
 */
class ProductoDuplicateCheckTest extends TestCase
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

    public function test_check_duplicate_finds_existing_product_by_exact_name(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Remera Azul',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('productos.check-duplicate', ['nombre' => 'Remera Azul']));

        $response->assertOk();
        $response->assertJson([
            'exists' => true,
            'id' => $producto->id,
            'nombre' => 'Remera Azul',
        ]);
    }

    public function test_check_duplicate_is_case_insensitive(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Remera Azul',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('productos.check-duplicate', ['nombre' => 'remera azul']));

        $response->assertOk();
        $response->assertJson([
            'exists' => true,
            'id' => $producto->id,
            'nombre' => 'Remera Azul',
        ]);
    }

    public function test_check_duplicate_returns_false_for_nonexistent(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('productos.check-duplicate', ['nombre' => 'Producto Inexistente']));

        $response->assertOk();
        $response->assertJson(['exists' => false]);
    }

    public function test_check_duplicate_finds_inactive_product(): void
    {
        // A soft-disabled product is still a duplicate the user should be
        // warned about — redirecting them to edit lets them reactivate /
        // restock instead of re-creating the row.
        $producto = Producto::factory()->inactivo()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Pantalon Viejo',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('productos.check-duplicate', ['nombre' => 'Pantalon Viejo']));

        $response->assertOk();
        $response->assertJson([
            'exists' => true,
            'id' => $producto->id,
            'nombre' => 'Pantalon Viejo',
        ]);
    }

    public function test_check_duplicate_requires_min_2_chars(): void
    {
        // The frontend fetch() sends Accept: application/json; the backend
        // responds 422 JSON for validation failures (matches fetch contract).
        $response = $this->actingAs($this->admin)
            ->withHeaders(['Accept' => 'application/json'])
            ->get(route('productos.check-duplicate', ['nombre' => 'A']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nombre']);
    }

    public function test_check_duplicate_requires_nombre_param(): void
    {
        $response = $this->actingAs($this->admin)
            ->withHeaders(['Accept' => 'application/json'])
            ->get(route('productos.check-duplicate'));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nombre']);
    }

    public function test_check_duplicate_requires_admin_or_control_stock_role(): void
    {
        $ventas = User::factory()->ventas()->create();

        $response = $this->actingAs($ventas)
            ->get(route('productos.check-duplicate', ['nombre' => 'Remera Azul']));

        $response->assertForbidden();
    }

    public function test_check_duplicate_as_admin_succeeds(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('productos.check-duplicate', ['nombre' => 'Cualquiera']));

        $response->assertOk();
        $response->assertJson(['exists' => false]);
    }

    /**
     * The React create form must wire a blur-triggered duplicate check on the
     * nombre input. Pure client-side fetch behavior can't be exercised by
     * PHPUnit, so this guards the SOURCE CONTRACT on productos.jsx (same
     * pattern as VentaReactViewContractTest): the nombre field wires
     * verificarDuplicado, the endpoint reference stays embedded, and the
     * lastCheckedName guard remains (no double-check on the same name). If
     * anyone removes the wiring or the dedupe guard, this test surfaces the
     * regression before it reaches a browser.
     */
    public function test_create_form_wires_duplicate_check_on_nombre_blur(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        $this->assertStringContainsString('verificarDuplicado', $source, 'Create form must wire verificarDuplicado on the nombre input.');
        $this->assertStringContainsString('/productos/check-duplicate', $source, 'The duplicate-check endpoint URL must be embedded in the form.');
        $this->assertStringContainsString('lastCheckedName', $source, 'The duplicate-check flow must track lastCheckedName to avoid re-prompting on the same name.');
        $this->assertStringContainsString('Sí, editar', $source, 'The duplicate prompt must offer to edit the existing product.');
    }
}
