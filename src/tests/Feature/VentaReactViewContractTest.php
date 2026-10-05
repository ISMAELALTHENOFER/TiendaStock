<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaReactViewContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_routes_render_the_react_host_when_enabled(): void
    {
        $source = file_get_contents(base_path('resources/views/layouts/app.blade.php'));

        $this->assertStringContainsString("'ventas.index'", $source);
        $this->assertStringContainsString("'ventas.pos'", $source);
        $this->assertStringContainsString("'ventas.show'", $source);
        $this->assertStringContainsString("@include('react.app'", $source);
    }

    public function test_sales_index_can_roll_back_to_the_legacy_blade_surface(): void
    {
        $user = User::factory()->ventas()->create();
        config(['frontend.routes.ventas.index' => 'blade']);

        $response = $this->actingAs($user)->get('/ventas');

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Historial de Ventas');
    }

    public function test_global_blade_driver_restores_the_sales_surface(): void
    {
        $user = User::factory()->ventas()->create();
        config(['frontend.driver' => 'blade']);

        $response = $this->actingAs($user)->get('/ventas');

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Historial de Ventas');
    }

    public function test_react_sales_app_contains_the_three_sales_surfaces_and_contracts(): void
    {
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        foreach (['SalesHistory', 'SalesPos', 'SaleShow', 'desde', 'hasta', 'estado', 'productos/search', '/ventas', 'no-print'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_sales_react_surface_preserves_complete_history_information(): void
    {
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        foreach (['Items', 'Total', 'Entrega', 'Procesado por', 'Completada', 'Anulada', 'Cancelar', 'overflow-x-auto'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_sales_contract_preserves_payment_filters_and_receipt_metadata(): void
    {
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        foreach ([
            'paidCents < totalCents',
            'URLSearchParams',
            "params.set('desde', query.desde)",
            "params.set('hasta', query.hasta)",
            "params.set('estado', query.estado)",
            'Método de pago:',
            'Tipo de entrega:',
            'Procesado por:',
            'Estado:',
            'ANULADA',
            'Gracias por su compra',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_pos_catalog_and_checkout_affordances_remain_connected_to_live_data(): void
    {
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        foreach ([
            '/productos/search?q=',
            'Resultados mostrados:',
            'Limpiar búsqueda',
            'const available = product.cantidad - inCart',
            'sm:grid-cols-2',
            'xl:sticky',
            'Cantidad de ${item.nombre}',
            'Descuento (monto fijo)',
            'parseMoney(discount)',
            'value="efectivo"',
            'value="tarjeta"',
            'value="transferencia"',
            'tipo_entrega: delivery',
            'pago_con: parseMoney(paid)',
            'paidCents < totalCents',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }

        foreach (['Lector: Conectado', 'Cuenta Corriente Cliente', '0% o $ monto'] as $unsupported) {
            $this->assertStringNotContainsString($unsupported, $source);
        }
    }

    public function test_pos_uses_cents_for_checkout_and_whole_stock_bounded_quantities(): void
    {
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        foreach ([
            'Math.round(cart.reduce(',
            'Math.round(subtotal * 100) - Math.round(parseMoney(discount) * 100)',
            'const total = totalCents / 100',
            'const paidCents = Math.round(parseMoney(paid) * 100)',
            'paidCents < totalCents',
            'step="1"',
            'Math.min(item.stock, Math.max(1, Math.trunc(Number(event.target.value) || 1)))',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }

        $this->assertSame(2, substr_count($source, 'paidCents < totalCents'));
    }
}
