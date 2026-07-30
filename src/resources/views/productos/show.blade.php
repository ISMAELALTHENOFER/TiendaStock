@use('Illuminate\Support\Facades\Storage')
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl bg-gradient-to-r from-brand-300 to-sky-300 bg-clip-text text-transparent">
                    {{ $producto->nombre }}
                </h2>
                <p class="text-gray-600 text-sm mt-1">Detalles del producto</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('productos.edit', $producto) }}"
                    class="inline-flex items-center gap-2 bg-brand-300 hover:bg-brand-400 text-white font-bold py-2 px-4 rounded-lg transition-all duration-200 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('productos.index') }}"
                    class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <!-- Información Principal -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-brand-400 to-brand-500 px-6 py-8">
                    <h3 class="text-white font-bold text-lg">Información del Producto</h3>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6 p-8">
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
                    <div class="col-span-2">
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Costo (precio_compra) -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-blue-50 px-6 py-6">
                        <p class="text-sm font-bold text-blue-600 uppercase tracking-wider">Costo</p>
                        <p class="text-3xl font-bold text-blue-700 mt-2">{{ formato_pesos($producto->precio_compra) }}</p>
                    </div>
                </div>

                <!-- Precio de Venta -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-brand-50 px-6 py-6">
                        <p class="text-sm font-bold text-brand-300 uppercase tracking-wider">Precio de Venta</p>
                        <p class="text-3xl font-bold text-brand-300 mt-2">{{ formato_pesos($producto->precio_venta) }}</p>
                    </div>
                </div>

                <!-- Ganancia por Unidad -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-green-50 px-6 py-6">
                        <p class="text-sm font-bold text-green-600 uppercase tracking-wider">Ganancia por Unidad</p>
                        <p class="text-3xl font-bold text-green-700 mt-2">{{ formato_pesos($producto->ganancia) }}</p>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            @if($producto->descripcion)
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-brand-400 to-brand-500 px-6 py-6">
                    <h3 class="text-white font-bold text-lg">Descripción</h3>
                </div>
                <div class="p-8">
                    <p class="text-gray-700 leading-relaxed text-lg">{{ $producto->descripcion }}</p>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>