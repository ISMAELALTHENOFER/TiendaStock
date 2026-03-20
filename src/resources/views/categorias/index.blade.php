<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Categorías de Productos</h1>
                <p class="mt-1 text-sm text-gray-600">Gestiona todas tus categorías de productos</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('categorias.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nueva Categoría
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Alertas -->
            @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center gap-3">
                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex items-center gap-3">
                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                {{ session('error') }}
            </div>
            @endif

            @if($categorias->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($categorias as $categoria)
                <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group border border-purple-100 hover:border-purple-300">
                    <div class="h-24 bg-gradient-to-r from-purple-600 via-pink-500 to-pink-400 opacity-90 group-hover:opacity-100 transition-opacity"></div>

                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $categoria->nombre }}</h3>

                        @if($categoria->descripcion)
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $categoria->descripcion }}</p>
                        @endif

                        <div class="inline-block bg-gradient-to-r from-purple-100 to-pink-100 text-purple-800 rounded-full px-4 py-1 text-sm font-semibold mb-4">
                            {{ $categoria->productos_count }} {{ $categoria->productos_count == 1 ? 'producto' : 'productos' }}
                        </div>

                        <div class="flex gap-2 mt-6">
                            <a href="{{ route('categorias.show', $categoria) }}"
                                class="flex-1 inline-flex justify-center items-center gap-2 bg-purple-50 text-purple-600 hover:bg-purple-100 font-semibold py-2 px-3 rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Ver
                            </a>
                            <a href="{{ route('categorias.edit', $categoria) }}"
                                class="flex-1 inline-flex justify-center items-center gap-2 bg-pink-50 text-pink-600 hover:bg-pink-100 font-semibold py-2 px-3 rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar
                            </a>
                            <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="flex-1"
                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta categoría?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full inline-flex justify-center items-center gap-2 bg-red-50 text-red-600 hover:bg-red-100 font-semibold py-2 px-3 rounded-lg transition-colors duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $categorias->links() }}
            </div>
            @else
            <div class="bg-white rounded-xl shadow-md border border-purple-100 overflow-hidden">
                <div class="p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.5a2 2 0 00-1 .267"></path>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">No hay categorías creadas aún</p>
                    <p class="text-gray-400 text-sm mt-2">Comienza creando tu primera categoría</p>
                    <a href="{{ route('categorias.create') }}"
                        class="mt-6 inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Crear Primera Categoría
                    </a>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
