<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Productos
            </h2>
            <a href="{{ route('productos.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                + Nuevo Producto
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Talle</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">P. Compra</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">P. Venta</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ganancia</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($productos as $producto)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $producto->nombre }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $producto->categoria->nombre }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $producto->talle ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $producto->color ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">${{ number_format($producto->precio_compra, 2) }}</td>
                                <td class="px-4 py-3 text-gray-600">${{ number_format($producto->precio_venta, 2) }}</td>
                                <td class="px-4 py-3 text-green-600 font-medium">${{ number_format($producto->ganancia, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        {{ $producto->cantidad <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $producto->cantidad }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 flex gap-2">
                                    <a href="{{ route('productos.show', $producto) }}"
                                       class="text-blue-600 hover:underline text-sm">Ver</a>
                                    <a href="{{ route('productos.edit', $producto) }}"
                                       class="text-yellow-600 hover:underline text-sm">Editar</a>
                                    <form action="{{ route('productos.destroy', $producto) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar este producto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                    No hay productos cargados aún.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $productos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
