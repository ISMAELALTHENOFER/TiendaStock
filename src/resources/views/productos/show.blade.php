@use('Illuminate\Support\Facades\Storage')
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div>
                <h2 class="page-title">
                    {{ $producto->nombre }}
                </h2>
                <p class="mt-1 text-sm text-muted">Detalles del producto</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:ml-auto">
                <a href="{{ route('productos.edit', $producto) }}"
                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-control bg-primary px-4 text-sm font-semibold text-white transition-colors hover:bg-primary-700 focus-visible:outline-offset-4 sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('productos.index') }}"
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-control bg-surface-muted px-4 text-sm font-semibold text-ink transition-colors hover:bg-border focus-visible:outline-offset-4 sm:w-auto">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="w-full min-w-0">

            <!-- Información Principal -->
            <div class="surface-panel mb-6 overflow-hidden">
                <div class="bg-primary px-4 py-6 sm:px-6">
                    <h3 class="text-white font-bold text-lg">Información del Producto</h3>
                </div>

                <dl class="grid grid-cols-1 gap-6 p-4 sm:grid-cols-2 sm:p-6">
                    <div class="border-b md:border-b-0 pb-4 md:pb-0">
                        <dt class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Categoría</dt>
                        <dd class="text-lg font-bold text-gray-900">{{ $producto->categoria->nombre }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Stock Actual</dt>
                        <dd>
                            <span class="inline-block px-4 py-2 rounded-full text-sm font-bold
                                {{ $producto->cantidad <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                {{ $producto->cantidad }} unidades
                            </span>
                        </dd>
                    </div>

                    @if($producto->talle)
                    <div class="border-b md:border-b-0 pb-4 md:pb-0">
                        <dt class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Talle</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $producto->talle }}</dd>
                    </div>
                    @endif

                    @if($producto->color)
                    <div>
                        <dt class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Color</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $producto->color }}</dd>
                    </div>
                    @endif

                    @if($producto->imagen)
                    <div class="md:col-span-2">
                        <dt class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Imagen</dt>
                        <dd>
                            <img src="{{ Storage::url($producto->imagen) }}" alt="{{ $producto->nombre }}"
                                class="h-40 w-40 object-cover rounded-xl border border-slate-200 shadow-sm">
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Información de Precios -->
            <div class="mb-6 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <!-- Costo (precio_compra) -->
                <div class="surface-panel overflow-hidden">
                    <div class="bg-blue-50 px-6 py-6">
                        <p class="text-sm font-bold text-blue-600 uppercase tracking-wider">Costo</p>
                        <p class="text-3xl font-bold text-blue-700 mt-2">{{ formato_pesos($producto->precio_compra) }}</p>
                    </div>
                </div>

                <!-- Precio de Venta -->
                <div class="surface-panel overflow-hidden">
                    <div class="bg-primary/10 px-6 py-6">
                        <p class="text-sm font-bold text-primary uppercase tracking-wider">Precio de Venta</p>
                        <p class="mt-2 text-3xl font-bold text-primary">{{ formato_pesos($producto->precio_venta) }}</p>
                    </div>
                </div>

                <!-- Ganancia por Unidad -->
                <div class="surface-panel overflow-hidden">
                    <div class="bg-green-50 px-6 py-6">
                        <p class="text-sm font-bold text-green-600 uppercase tracking-wider">Ganancia por Unidad</p>
                        <p class="text-3xl font-bold text-green-700 mt-2">{{ formato_pesos($producto->ganancia) }}</p>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            @if($producto->descripcion)
            <div class="surface-panel overflow-hidden">
                <div class="bg-primary px-4 py-6 sm:px-6">
                    <h3 class="text-white font-bold text-lg">Descripción</h3>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-gray-700 leading-relaxed text-lg">{{ $producto->descripcion }}</p>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
