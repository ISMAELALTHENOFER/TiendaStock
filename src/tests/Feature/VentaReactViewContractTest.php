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

    public function test_react_sales_host_receives_search_and_page_size_query_values(): void
    {
        $source = file_get_contents(base_path('resources/views/layouts/app.blade.php'));

        $this->assertStringContainsString("'query' => request()->only(['desde', 'hasta', 'estado', 'buscar', 'per_page'])", $source);
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

        $this->assertStringContainsString('ConfirmDialog', $source, 'Sale cancellation must use the shared styled dialog.');
        $this->assertStringNotContainsString('window.confirm', $source, 'The native sale cancellation prompt must be gone.');
        $this->assertStringContainsString('action={`/ventas/${sale.id}/cancel`}', $source);
        $this->assertStringContainsString('Cancelar venta #${sale.id}', $source);
        $this->assertStringContainsString('className="flex items-center justify-start gap-2"', $source);
    }

    public function test_sales_table_headers_match_the_alignment_of_each_column(): void
    {
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        foreach ([
            "['#', 'text-left']",
            "['Fecha', 'text-left']",
            "['Items', 'text-right']",
            "['Total', 'text-right']",
            "['Entrega', 'text-left']",
            "['Procesado por', 'text-left']",
            "['Estado', 'text-left']",
            "['Acciones', 'text-left']",
        ] as $column) {
            $this->assertStringContainsString($column, $source);
        }
    }

    public function test_react_sales_contract_preserves_payment_filters_and_receipt_metadata(): void
    {
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        foreach ([
            'paidCents < totalCents',
            'URLSearchParams',
            'new URLSearchParams(window.location.search)',
            "params.set('page', String(page))",
            "params.set('per_page'",
            'query.buscar',
            'name="buscar"',
            'page?.from',
            'Método de pago:',
            'Tipo de entrega:',
            'Procesado por:',
            'Estado:',
            'ANULADA',
            'Gracias por su compra',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }

        $this->assertStringContainsString("from './components/ui/Pagination.jsx'", $source);
        $this->assertStringContainsString('<Pagination', $source);
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
            'space-y-3 xl:max-h-40 xl:overflow-y-auto xl:pr-2',
            'aria-label="Artículos del carrito" tabIndex={0}',
            'Cantidad de ${item.nombre}',
            'Descuento (monto fijo)',
            'parseMoney(discount)',
            "['efectivo', 'Efectivo']",
            "['tarjeta', 'Tarjeta']",
            "['transferencia', 'Transferencia']",
            'tipo_entrega: delivery',
            'pago_con: parseMoney(paid)',
            'paidCents < totalCents',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }

        foreach (['Lector: Conectado', 'Cuenta Corriente Cliente', '0% o $ monto'] as $unsupported) {
            $this->assertStringNotContainsString($unsupported, $source);
        }

        $this->assertStringNotContainsString('xl:max-h-[calc(100dvh-3rem)] xl:overflow-y-auto', $source, 'Payment controls must not scroll with cart items.');
        $this->assertLessThan(strpos($source, 'aria-label="Resumen y cobro"'), strpos($source, 'aria-label="Artículos del carrito"'), 'Cart items must live outside the checkout card.');
        $this->assertStringContainsString('[@media(max-height:47.999rem)]:xl:static', $source, 'Short viewports must be able to scroll to every checkout control.');
        $this->assertSame(1, substr_count($source, "'Cobrar'"), 'There must be only one checkout action.');
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

    public function test_pos_delivery_and_payment_use_accessible_choice_tiles(): void
    {
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        $this->assertSame(2, substr_count($source, '<fieldset>'));
        $this->assertStringContainsString('<legend className="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Tipo de entrega</legend>', $source);
        $this->assertStringContainsString('<legend className="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Método de pago</legend>', $source);
        $this->assertStringContainsString("[['local', 'En el local', Store], ['uber', 'Envío por Uber', Truck]]", $source);
        $this->assertStringContainsString("[['efectivo', 'Efectivo'], ['tarjeta', 'Tarjeta'], ['transferencia', 'Transferencia']]", $source);
        $this->assertStringContainsString('name="tipo_entrega" value={value} checked={delivery === value} onChange={() => setDelivery(value)} required', $source);
        $this->assertStringContainsString('name="metodo_pago" value={value} checked={paymentMethod === value} onChange={() => setPaymentMethod(value)}', $source);
        $this->assertStringContainsString("[delivery, setDelivery] = useState('')", $source);
        $this->assertStringContainsString("[paymentMethod, setPaymentMethod] = useState('efectivo')", $source);
        $this->assertStringContainsString('const choiceTileClass = (selected)', $source);
        $this->assertStringContainsString('min-h-11 min-w-0', $source);
        $this->assertStringContainsString('focus-within:outline', $source);
        $this->assertStringContainsString('last:col-span-2 sm:last:col-span-1', $source);
        $this->assertStringNotContainsString('<Select value={delivery}', $source);
        $this->assertStringNotContainsString('<Select value={paymentMethod}', $source);
        $this->assertStringContainsString('Pago con<Input type="text" inputMode="decimal"', $source);
        $this->assertStringContainsString('className="mt-2 w-full text-right text-lg font-semibold tabular-nums', $source);
    }
}
