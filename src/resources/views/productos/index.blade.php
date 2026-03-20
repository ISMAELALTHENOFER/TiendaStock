<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Inventario de Productos
                </h2>
                <p class="text-gray-600 text-sm mt-1">Gestiona todos tus productos en un solo lugar</p>
            </div>
            <a href="{{ route('productos.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold py-3 px-6 rounded-lg shadow-lg transition-all duration-200 transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Producto
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg shadow-md" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($productos->count() > 0)
            <div class="bg-white rounded-xl shadow-lg border border-purple-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-purple-50 to-pink-50 border-b border-purple-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Categoría</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Talle</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Color</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-purple-900 uppercase tracking-wider">P. Compra</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-purple-900 uppercase tracking-wider">P. Venta</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-purple-900 uppercase tracking-wider">Ganancia</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-purple-900 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-purple-900 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($productos as $producto)
                            <tr class="hover:bg-purple-50 transition-colors duration-200">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $producto->nombre }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-block bg-gradient-to-r from-purple-100 to-pink-100 text-purple-800 rounded-full px-3 py-1 text-xs font-semibold">
                                        {{ $producto->categoria->nombre }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $producto->talle ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $producto->color ?? '—' }}</td>
                                <td class="px-6 py-4 text-right text-gray-600 font-medium">${{ number_format($producto->precio_compra, 2) }}</td>
                                <td class="px-6 py-4 text-right text-gray-600 font-medium">${{ number_format($producto->precio_venta, 2) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-green-600 font-bold">${{ number_format($producto->ganancia, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold
                                            {{ $producto->cantidad <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $producto->cantidad }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('productos.show', $producto) }}"
                                            title="Ver detalles"
                                            class="text-purple-600 hover:text-purple-800 hover:bg-purple-50 p-2 rounded-lg transition-colors duration-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('productos.edit', $producto) }}"
                                            title="Editar"
                                            class="text-pink-600 hover:text-pink-800 hover:bg-pink-50 p-2 rounded-lg transition-colors duration-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('productos.destroy', $producto) }}" method="POST" style="display:inline;"
                                            onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                title="Eliminar"
                                                class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-lg transition-colors duration-200">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="text-gray-500 text-lg font-medium">No hay productos cargados aún</p>
                                        <p class="text-gray-400 text-sm mt-2">Comienza creando tu primer producto</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6 bg-gray-50 border-t border-gray-200">
                    {{ $productos->links() }}
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl shadow-lg border border-purple-100 overflow-hidden">
                <div class="p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">No hay productos en el sistema</p>
                    <p class="text-gray-400 text-sm mt-2">Crea tu primer producto para comenzar</p>
                    <a href="{{ route('productos.create') }}"
                        class="mt-6 inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Crear Primer Producto
                    </a>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
