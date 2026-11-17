<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaReactViewContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_routes_render_the_react_host(): void
    {
        $user = User::factory()->ventas()->create();
        $venta = Venta::factory()->create(['user_id' => $user->id]);

        foreach ([
            ['/ventas', 'ventas.index'],
            [route('ventas.pos'), 'ventas.pos'],
            [route('ventas.show', $venta), 'ventas.show'],
        ] as [$url, $page]) {
            $response = $this->actingAs($user)->get($url)->assertOk()->assertSee('id="react-root"', false);
            preg_match('/data-props="([^"]*)"/', $response->getContent(), $matches);
            $props = json_decode(html_entity_decode($matches[1] ?? '', ENT_QUOTES | ENT_HTML5), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame($page, $props['page']);
        }

        $this->assertSame($venta->id, $props['sale']['id']);
    }

    public function test_react_sales_host_receives_search_and_page_size_query_values(): void
    {
        $source = file_get_contents(base_path('resources/views/layouts/app.blade.php'));

        $this->assertStringContainsString("'query' => request()->only(['desde', 'hasta', 'estado', 'buscar', 'per_page', 'inactivos'])", $source);
    }

    public function test_react_sales_app_contains_the_three_sales_surfaces_and_contracts(): void
    {
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        foreach (['SalesHistory', 'SalesPos', 'SaleShow', 'desde', 'hasta', 'estado', 'routes.productosSearch', 'routes.ventas', 'no-print'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_sales_react_surface_preserves_complete_history_information(): void
    {
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        foreach (['Artículos', 'items_count', 'Total', 'Entrega', 'Procesado por', 'Completada', 'Anulada', 'Cancelar', 'overflow-x-auto'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }

        $this->assertStringContainsString('ConfirmDialog', $source, 'Sale cancellation must use the shared styled dialog.');
        $this->assertStringNotContainsString('window.confirm', $source, 'The native sale cancellation prompt must be gone.');
        $this->assertStringContainsString('routes.ventas}/${sale.id}/cancel', $source);
        $this->assertStringContainsString('Cancelar venta #${sale.id}', $source);
        $this->assertStringContainsString('className="flex items-center justify-start gap-2"', $source);
    }

    public function test_sales_table_headers_match_the_alignment_of_each_column(): void
    {
        $source = file_get_contents(base_path('resources/js/react/sales.jsx'));

        foreach ([
            "['#', 'text-left']",
            "['Fecha', 'text-left']",
            "['Artículos', 'text-right']",
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
            'Gracias por tu compra',
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
            '`${routes.productosSearch}?q=${encodeURIComponent(query)}`',
            'Resultados mostrados:',
            'Limpiar búsqueda',
            'const available = product.cantidad - inCart',
            'stockTone(available, lowStockThreshold)',
            'sm:grid-cols-2',
            'space-y-3 xl:max-h-40 xl:overflow-y-auto xl:pr-2',
            'aria-label="Artículos del carrito" tabIndex={0}',
            'Cantidad de ${item.nombre}',
            'Descuento (monto fijo)',
            'descuento: parseMoney(discount)',
            "['efectivo', 'Efectivo']",
            "['tarjeta', 'Tarjeta']",
            "['transferencia', 'Transferencia']",
            'tipo_entrega: delivery',
            'pago_con: parseMoney(paid)',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }

        foreach (['Lector: Conectado', 'Cuenta Corriente Cliente', '0% o $ monto'] as $unsupported) {
            $this->assertStringNotContainsString($unsupported, $source);
        }

        $this->assertStringNotContainsString('xl:max-h-[calc(100dvh-3rem)] xl:overflow-y-auto', $source, 'Payment controls must not scroll with cart items.');
        $this->assertLessThan(strpos($source, 'aria-label="Resumen y cobro"'), strpos($source, 'aria-label="Artículos del carrito"'), 'Cart items must live outside the checkout card.');
        $this->assertStringContainsString('[@media(max-height:47.999rem)]:xl:static', $source, 'Short viewports must be able to scroll to every checkout control.');
        $this->assertStringContainsString("'Cobrar'", $source);
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
    }

    public function test_pos_search_debounces_and_aborts_stale_requests(): void
    {
        $react = file_get_contents(resource_path('js/react/sales.jsx'));

        $this->assertStringContainsString('signal: controller.signal', $react);
        $this->assertStringContainsString('window.setTimeout(async () =>', $react);
        $this->assertStringContainsString('}, 250);', $react);
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
