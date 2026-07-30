<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoMoneyFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_renders_precio_compra_with_argentine_format(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create([
            'categoria_id' => $categoria->id,
            'precio_compra' => 1234.56,
            'precio_venta' => 2000.00,
        ]);

        $response = $this->actingAs($admin)->get(route('productos.show', $producto));

        $response->assertOk();
        // formato_pesos(1234.56) === "$1.234,56"; the broken number_format used "$1,234.56".
        $response->assertSee('$1.234,56');
        $response->assertDontSee('$1,234.56');
        $response->assertDontSee('$1234.56');
    }

    public function test_show_renders_precio_venta_with_argentine_format(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create([
            'categoria_id' => $categoria->id,
            'precio_compra' => 1000.00,
            'precio_venta' => 5678.90,
        ]);

        $response = $this->actingAs($admin)->get(route('productos.show', $producto));

        $response->assertOk();
        $response->assertSee('$5.678,90');
    }

    public function test_show_renders_ganancia_with_argentine_format(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create([
            'categoria_id' => $categoria->id,
            'precio_compra' => 1000.00,
            'precio_venta' => 1500.50,
        ]);

        $response = $this->actingAs($admin)->get(route('productos.show', $producto));

        $response->assertOk();
        // ganancia = 1500.50 - 1000.00 = 500.50 → "$500,50"
        $response->assertSee('$500,50');
    }

    public function test_show_uses_costo_label_for_precio_compra(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $response = $this->actingAs($admin)->get(route('productos.show', $producto));

        $response->assertOk();
        $response->assertSee('Costo');
        $response->assertDontSee('Precio de Compra');
    }

    /**
     * On the create form the cost/price inputs MUST initialize moneyInput with
     * 0 (not a pre-formatted "$0,00"). The Alpine init() turns raw=0 into an
     * EMPTY display, so the user never has to delete a placeholder value
     * before typing — issue: "$0,00 pegado al cargar nuevo producto".
     */
    public function test_create_form_initializes_money_inputs_with_zero(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('productos.create'));
        $content = $response->getContent();

        $response->assertOk();
        // moneyInput(0) ⇒ raw=0 ⇒ display='' on the client. Must NOT receive a
        // pre-formatted default like moneyInput("$0,00") or a static value="$0,00".
        $this->assertStringContainsString('moneyInput(0)', $content, 'Create form must seed money inputs with 0 so they render empty until the user types.');
        $this->assertStringNotContainsString('value="$0,00"', $content, 'Create form must not pre-fill the money field with a formatted $0,00.');
        $this->assertStringNotContainsString('placeholder="$0,00"', $content, 'The misleading $0,00 placeholder must be gone; use a neutral format hint.');
    }

    /**
     * On edit, moneyInput MUST be seeded with the product's actual price so the
     * Alpine init() formats it on load (issue: edit must show the formatted
     * value immediately, not empty).
     */
    public function test_edit_form_initializes_money_inputs_with_product_price(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create([
            'categoria_id' => $categoria->id,
            'precio_compra' => 1234.56,
            'precio_venta' => 2000.00,
        ]);

        $response = $this->actingAs($admin)->get(route('productos.edit', $producto));
        $content = $response->getContent();

        $response->assertOk();
        // The costo input is seeded with the raw numeric price (1234.56); the
        // Alpine init() formats it to "$1.234,56" on load.
        $this->assertStringContainsString('moneyInput(1234.56)', $content, 'Edit form must seed the cost input with the product\'s actual precio_compra.');
    }

    /**
     * Live money mask wiring guard.
     *
     * Pure client-side keystroke behavior cannot be exercised by PHPUnit, so
     * this test guards the DOM + Alpine CONTRACT instead: the create form must
     * (1) bind each money input to the shared `moneyInput` Alpine component,
     * (2) wire an `@input` live handler (reformats on every keystroke), and
     * (3) include the shared partial that registers the component, which
     * contains the caret-preservation algorithm (digit-count marker
     * `digitsLeft`). If any of these go missing (e.g. someone removes the
     * include or rewires the handler), this test fails and surfaces the
     * regression before it reaches a browser.
     */
    public function test_create_form_wires_live_money_input_mask(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('productos.create'));
        $content = $response->getContent();

        $response->assertOk();
        // (1) each money input is bound to the moneyInput Alpine component.
        $this->assertStringContainsString('x-data="moneyInput(0)"', $content, 'Cost/price inputs must bind to the moneyInput Alpine component.');
        // (2) the visible field wires the live @input handler (per-keystroke mask).
        $this->assertStringContainsString('@input="onInput($event)"', $content, 'Money inputs must wire the live onInput handler for while-typing grouping.');
        $this->assertStringContainsString('@blur="onBlur()"', $content, 'Money inputs must wire onBlur to canonicalize to "$1.234,56".');
        // (3) the shared partial is included and registers the component, with
        //     the digit-count caret-preservation marker present in the payload.
        $this->assertStringContainsString("Alpine.data('moneyInput'", $content, 'The shared moneyInput component must be registered on the page.');
        $this->assertStringContainsString('digitsLeft', $content, 'Caret-preservation algorithm (digit-count) must ship with the component.');
        $this->assertStringContainsString('setSelectionRange', $content, 'Caret restoration (setSelectionRange) must ship with the component.');
    }

    /**
     * The POS "Pago con" and "Descuento" inputs must consume the SAME shared
     * moneyInput component (no duplicated inline formatter). Guards uniformity
     * of the live mask across every amount input in the app.
     */
    public function test_pos_uses_shared_money_input_component(): void
    {
        $ventas = User::factory()->ventas()->create();

        $response = $this->actingAs($ventas)->get(route('ventas.pos'));
        $content = $response->getContent();

        $response->assertOk();
        // Both money inputs bind moneyInput; the bespoke pagoConDisplay/
        // updatePagoCon inline formatter must be gone.
        $this->assertStringContainsString("x-data=\"moneyInput(0)\"", $content, 'Pago con / Descuento inputs must bind to the shared moneyInput component.');
        $this->assertStringContainsString('$watch(\'raw\', v => pagoCon = v)', $content, 'Pago con must mirror raw into posApp.pagoCon.');
        $this->assertStringContainsString('$watch(\'raw\', v => descuento = v)', $content, 'Descuento must mirror raw into posApp.descuento.');
        $this->assertStringNotContainsString('pagoConDisplay', $content, 'The old inline pagoConDisplay formatter must be removed from POS.');
        $this->assertStringNotContainsString('updatePagoCon', $content, 'The old inline updatePagoCon formatter must be removed from POS.');
        // The shared partial is included on the POS page too.
        $this->assertStringContainsString("Alpine.data('moneyInput'", $content, 'POS must include the shared moneyInput partial.');
    }
}
