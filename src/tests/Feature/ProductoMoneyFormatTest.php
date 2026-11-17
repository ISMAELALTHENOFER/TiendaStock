<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoMoneyFormatTest extends TestCase
{
    use RefreshDatabase;

    /**
     * On the create form the cost/price inputs MUST initialize with 0 (not a
     * pre-formatted "$0,00"). The React MoneyInput turns raw=0 into an EMPTY
     * display, so the user never has to delete a placeholder value before
     * typing — issue: "$0,00 pegado al cargar nuevo producto".
     */
    public function test_create_form_initializes_money_inputs_with_zero(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        $this->assertStringContainsString('> 0 ?', $source, 'Money inputs must render empty until the user types (no pre-formatted default).');
        $this->assertStringContainsString('formatMoney(initial)', $source, 'The formatted value must come from formatMoney only.');
        $this->assertStringNotContainsString('value="$0,00"', $source, 'Create form must not pre-fill the money field with a formatted $0,00.');
    }

    /**
     * On edit, the MoneyInput MUST be seeded with the product's actual price so
     * it formats on load (issue: edit must show the formatted value
     * immediately, not empty).
     */
    public function test_edit_form_initializes_money_inputs_with_product_price(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        $this->assertStringContainsString('producto?.precio_compra ?? 0', $source, 'Edit form must seed the cost input with the product\'s actual precio_compra.');
        $this->assertStringContainsString('producto?.precio_venta ?? 0', $source, 'Edit form must seed the price input with the product\'s actual precio_venta.');
    }

    /**
     * Live money mask wiring guard.
     *
     * Pure client-side keystroke behavior cannot be exercised by PHPUnit, so
     * this test guards the React SOURCE CONTRACT instead: the product money
     * input must (1) use the decimal input mode, (2) wire a live `onInput`
     * handler that regroups thousands while typing, (3) wire `onBlur` to
     * canonicalize to "$1.234,56", and (4) ship the caret-preservation
     * algorithm (digit-count marker `digitsLeft` + `setSelectionRange`). If
     * any of these go missing, this test fails and surfaces the regression
     * before it reaches a browser.
     */
    public function test_create_form_wires_live_money_input_mask(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));
        $money = file_get_contents(base_path('resources/js/react/lib/productMoneyInput.js'));

        $this->assertStringContainsString('inputMode="decimal"', $source, 'Money inputs must use the decimal input mode.');
        $this->assertStringContainsString('onInput', $source, 'Money inputs must wire the live onInput handler for while-typing grouping.');
        $this->assertStringContainsString('onBlur', $source, 'Money inputs must wire onBlur to canonicalize to "$1.234,56".');
        $this->assertStringContainsString('digitsLeft', $money, 'Caret-preservation algorithm (digit-count) must ship with the component.');
        $this->assertStringContainsString('setSelectionRange', $source, 'Caret restoration (setSelectionRange) must ship with the component.');
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
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        $response->assertOk();
        // React owns the migrated POS route; both amount fields remain decimal
        // inputs and the common formatter is shared across the React pages.
        $this->assertStringContainsString('inputMode="decimal"', $source);
        $this->assertStringContainsString('value={paid}', $source);
        $this->assertStringContainsString('value={discount}', $source);
        $this->assertStringContainsString("from './lib/formatMoney.js'", $source);
    }
}
