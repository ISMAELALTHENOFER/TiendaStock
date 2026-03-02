<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-gray-900 leading-tight">
                    {{ $categoria->nombre }}
                </h2>
                <p class="text-gray-600 text-sm mt-1">Detalles de la categoría</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('categorias.edit', $categoria) }}"
                    class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('categorias.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition-colors duration-200">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Información de la Categoría -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-8">
                    <h3 class="text-white font-bold text-lg">Información de la Categoría</h3>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6 p-8">
                    <div class="border-b md:border-b-0 pb-4 md:pb-0">
                        <dt class="text-sm font-bold text-gray-600 uppercase tracking-wide mb-2">Nombre</dt>
                        <dd class="text-2xl font-bold text-gray-900">{{ $categoria->nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-gray-600 uppercase tracking-wide mb-2">Total de Productos</dt>
                        <dd class="text-2xl font-bold text-indigo-600">{{ $categoria->productos()->count() }}</dd>
                    </div>
                    @if($categoria->descripcion)
                    <div class="col-span-1 md:col-span-2">
                        <dt class="text-sm font-bold text-gray-600 uppercase tracking-wide mb-2">Descripción</dt>
                        <dd class="text-gray-900 leading-relaxed">{{ $categoria->descripcion }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Productos de la Categoría -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-8">
                    <h3 class="text-white font-bold text-lg">Productos en esta Categoría</h3>
                    <p class="text-purple-100 text-sm mt-1">{{ $categoria->productos()->count() }} producto(s)</p>
                </div>

                @if($categoria->productos()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Nombre</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Stock</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Precio Compra</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Precio Venta</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Ganancia</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Opciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($categoria->productos as $producto)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $producto->nombre }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                            {{ $producto->cantidad <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $producto->cantidad }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">${{ number_format($producto->precio_compra, 2) }}</td>
                                <td class="px-6 py-4 text-gray-600">${{ number_format($producto->precio_venta, 2) }}</td>
                                <td class="px-6 py-4 font-bold text-green-600">${{ number_format($producto->ganancia, 2) }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('productos.show', $producto) }}"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">Ver</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-gray-500 font-medium">Esta categoría aún no tiene productos</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>