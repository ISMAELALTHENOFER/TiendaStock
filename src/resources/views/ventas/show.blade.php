<x-app-layout :venta="$venta">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
            <div>
                <h2 class="page-title">
                    Venta #{{ $venta->id }}
                </h2>
                <p class="text-gray-600 text-sm mt-1">{{ $venta->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 no-print">
                <button onclick="window.print()"
                    class="inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-all duration-200 w-full sm:w-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Imprimir
                </button>
                <a href="{{ route('ventas.index') }}"
                    class="inline-flex items-center justify-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg shadow-md transition-all duration-200 w-full sm:w-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            {{-- Recibo --}}
            <div class="bg-white rounded-xl shadow-sm border border-sky-100 p-4 sm:p-8" id="recibo">
                {{-- Cabecera del recibo --}}
                <div class="text-center border-b border-gray-200 pb-6 mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">TiendaStock</h1>
                    <p class="text-gray-500 mt-1">Recibo de Venta #{{ $venta->id }}</p>
                    <p class="text-gray-500">{{ $venta->created_at->format('d/m/Y H:i') }}</p>
                </div>

                {{-- Cliente --}}
                @if($venta->cliente_nombre)
                <div class="mb-4 text-gray-700">
                    <p><strong>Cliente:</strong> {{ $venta->cliente_nombre }}</p>
                </div>
                @endif

                {{-- Items --}}
                <table class="w-full mb-6">
                    <thead>
                        <tr class="border-b border-gray-200 text-left">
                            <th class="py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">Producto</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Precio</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Cant.</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($venta->items as $item)
                        <tr class="border-b border-gray-100">
                            <td class="py-3 text-gray-900 text-sm sm:text-base">{{ $item->producto->nombre }}</td>
                            <td class="py-3 text-right text-gray-600 text-xs sm:text-sm">{{ formato_pesos($item->precio_unitario) }}</td>
                            <td class="py-3 text-right text-gray-600 text-xs sm:text-sm">{{ $item->cantidad }}</td>
                            <td class="py-3 text-right font-semibold text-gray-900 text-sm sm:text-base">{{ formato_pesos($item->subtotal) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Totales --}}
                <div class="border-t border-gray-200 pt-4 space-y-1 text-right">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal:</span>
                        <span>{{ formato_pesos($venta->subtotal) }}</span>
                    </div>
                    @if($venta->descuento > 0)
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Descuento:</span>
                        <span class="text-red-600">-{{ formato_pesos($venta->descuento) }}</span>
                    </div>
                    @endif
                    @if($venta->impuesto > 0)
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Impuesto:</span>
                        <span>{{ formato_pesos($venta->impuesto) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-xl font-bold text-gray-900 pt-2 border-t border-gray-200">
                        <span>Total:</span>
                        <span>{{ formato_pesos($venta->total) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Pagó:</span>
                        <span>{{ formato_pesos($venta->pago_con) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Cambio:</span>
                        <span>{{ formato_pesos($venta->cambio) }}</span>
                    </div>
                </div>

                {{-- Método de pago y procesado por --}}
                <div class="mt-6 pt-4 border-t border-gray-200 space-y-1 text-sm text-gray-600">
                    <p><strong>Método de pago:</strong> {{ ucfirst($venta->metodo_pago) }}</p>
                    <p><strong>Tipo de entrega:</strong> {{ $venta->etiquetaTipoEntrega() }}</p>
                    <p><strong>Procesado por:</strong> {{ $venta->user->name }}</p>
                </div>

                {{-- Estado --}}
                <div class="mt-6 text-center">
                    @if($venta->isAnulada())
                    <span class="inline-block px-6 py-3 bg-red-100 text-red-700 rounded-full font-bold text-lg">
                        ANULADA
                    </span>
                    @else
                    <p class="text-gray-500 text-sm">Gracias por su compra</p>
                    @endif
                </div>
            </div>

            {{-- Botón de anulación (solo si completada) --}}
            @if($venta->isCompletada())
            <div class="mt-6 text-center no-print">
                <form action="{{ route('ventas.cancel', $venta) }}" method="POST" id="cancel-form-show">
                    @csrf
                    <button type="button"
                        onclick="confirmDialogShow('Anular venta #{{ $venta->id }}', '¿Anular esta venta por {{ formato_pesos($venta->total) }}? Se restaurará el stock automáticamente.', 'Sí, anular', 'bg-red-600 hover:bg-red-700').then(r => r && document.getElementById('cancel-form-show').submit())"
                        class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg shadow-md transition-all duration-200 w-full sm:w-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Anular Venta
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>

@push('styles')
<style>
@media print {
    body * { visibility: hidden; }
    #recibo, #recibo * { visibility: visible; }
    #recibo { position: absolute; left: 0; top: 0; width: 100%; padding: 0; border: none; box-shadow: none; }
    .no-print { display: none !important; }
    header, nav, footer, .sidebar { display: none !important; }
    .bg-white { background: white !important; }
}
</style>
@endpush
