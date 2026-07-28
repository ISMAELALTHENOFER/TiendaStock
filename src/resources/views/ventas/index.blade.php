<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl bg-gradient-to-r from-brand-300 to-sky-300 bg-clip-text text-transparent">
                    Historial de Ventas
                </h2>
                <p class="text-gray-600 text-sm mt-1">Consulta y gestiona todas las ventas realizadas</p>
            </div>
            <a href="{{ route('ventas.pos') }}"
                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Nueva Venta
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Filtros --}}
            <form method="GET" action="{{ route('ventas.index') }}" class="mb-6" x-data="{
                desde: '{{ request('desde') }}',
                hasta: '{{ request('hasta') }}',
                validar() {
                    if (this.desde && this.hasta && this.desde > this.hasta) {
                        confirmDialogShow('Fechas invalidas', 'La fecha Desde debe ser menor o igual a Hasta.', 'Entendido', 'bg-red-600 hover:bg-red-700');
                        return false;
                    }
                    return true;
                }
            }"
            @submit.prevent="validar() && $el.submit()">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                        <input type="text" id="filtro-desde" name="desde" value="{{ request('desde') }}"
                            x-model="desde"
                            class="w-full px-4 py-2 border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all text-gray-700 bg-white cursor-pointer"
                            placeholder="Seleccionar fecha">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                        <input type="text" id="filtro-hasta" name="hasta" value="{{ request('hasta') }}"
                            x-model="hasta"
                            class="w-full px-4 py-2 border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all text-gray-700 bg-white cursor-pointer"
                            placeholder="Seleccionar fecha">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="estado"
                            class="w-full px-4 py-2 border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all text-gray-700">
                            <option value="">Todos los estados</option>
                            <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completadas</option>
                            <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anuladas</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                            class="w-full bg-brand-300 hover:bg-brand-400 text-white font-bold py-2 px-4 rounded-lg transition-all duration-200">
                            Filtrar
                        </button>
                    </div>
                    <div>
                        <a href="{{ route('ventas.index') }}"
                            class="w-full inline-block text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition-all duration-200">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>

            {{-- Tabla de ventas --}}
            <div class="bg-white rounded-xl shadow-sm border border-sky-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-sky-50 border-b border-sky-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">#</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Procesado por</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($ventas as $venta)
                            <tr class="hover:bg-sky-50/50 transition-colors duration-200">
                                <td class="px-6 py-4 font-bold text-gray-900">{{ $venta->id }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 text-center text-gray-600">{{ $venta->items->count() }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ formato_pesos($venta->total) }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $venta->user->name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold
                                        {{ $venta->isCompletada() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $venta->isCompletada() ? 'Completada' : 'Anulada' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('ventas.show', $venta) }}"
                                            title="Ver detalle"
                                            class="text-brand-300 hover:text-brand-400 hover:bg-brand-50 p-2 rounded-lg transition-colors duration-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        @if($venta->isCompletada())
                                        <form action="{{ route('ventas.cancel', $venta) }}" method="POST" id="cancel-form-{{ $venta->id }}">
                                            @csrf
                                            <button type="button"
                                                onclick="confirmDialogShow('Anular venta', '¿Anular venta #{{ $venta->id }} por {{ formato_pesos($venta->total) }}? Se restaurará el stock.', 'Sí, anular').then(r => r && document.getElementById('cancel-form-{{ $venta->id }}').submit())"
                                                title="Anular venta"
                                                class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-lg transition-colors duration-200">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <p class="text-lg font-medium">No hay ventas registradas.</p>
                                    <p class="text-sm text-gray-400 mt-1">Las ventas realizadas aparecerán aquí.</p>
                                    <a href="{{ route('ventas.pos') }}"
                                        class="mt-6 inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all duration-200">
                                        Realizar primera venta
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($ventas->hasPages())
                <div class="p-6 bg-gray-50 border-t border-gray-200">
                    {{ $ventas->appends(request()->query())->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const desdeEl = document.getElementById('filtro-desde');
    const hastaEl = document.getElementById('filtro-hasta');

    if (!desdeEl || !hastaEl || typeof flatpickr === 'undefined') return;

    fpConfig = {
        locale: 'es',
        dateFormat: 'Y-m-d',
        allowInput: false,
        disableMobile: true,
    };

    const fpDesde = flatpickr(desdeEl, {
        ...fpConfig,
        onChange: function(selectedDates, dateStr) {
            fpHasta.set('minDate', dateStr);
        },
    });

    const fpHasta = flatpickr(hastaEl, {
        ...fpConfig,
        onChange: function(selectedDates, dateStr) {
            fpDesde.set('maxDate', dateStr);
        },
    });

    // Set initial min/max if values exist
    @if(request('desde'))
        fpHasta.set('minDate', '{{ request('desde') }}');
    @endif
    @if(request('hasta'))
        fpDesde.set('maxDate', '{{ request('hasta') }}');
    @endif
});
</script>
