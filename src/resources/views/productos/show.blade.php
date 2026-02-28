<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $producto->nombre }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('productos.edit', $producto) }}"
                   class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                    Editar
                </a>
                <a href="{{ route('productos.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                        <dd class="mt-1 text-gray-900">{{ $producto->categoria->nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Stock actual</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 rounded text-sm font-semibold
                                {{ $producto->cantidad <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                {{ $producto->cantidad }} unidades
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Precio de Compra</dt>
                        <dd class="mt-1 text-gray-900">${{ number_format($producto->precio_compra, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Precio de Venta</dt>
                        <dd class="mt-1 text-gray-900">${{ number_format($producto->precio_venta, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Ganancia por unidad</dt>
                        <dd class="mt-1 text-green-600 font-semibold">${{ number_format($producto->ganancia, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Talle</dt>
                        <dd class="mt-1 text-gray-900">{{ $producto->talle ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Color</dt>
                        <dd class="mt-1 text-gray-900">{{ $producto->color ?? '-' }}</dd>
                    </div>
                    @if($producto->descripcion)
                    <div class="col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                        <dd class="mt-1 text-gray-900">{{ $producto->descripcion }}</dd>
                    </div>
                    @endif
                </dl>

            </div>
        </div>
    </div>
</x-app-layout>
