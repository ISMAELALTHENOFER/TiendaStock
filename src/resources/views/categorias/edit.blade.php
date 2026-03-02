<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-gray-900 leading-tight">
                    Editar Categoría
                </h2>
                <p class="text-gray-600 text-sm mt-1">{{ $categoria->nombre }}</p>
            </div>
            <a href="{{ route('categorias.index') }}"
                class="text-gray-600 hover:text-gray-900 font-medium link">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-8">
                    <h3 class="text-white font-bold text-lg">Modificar Categoría</h3>
                    <p class="text-amber-100 text-sm mt-1">Actualiza los detalles de la categoría</p>
                </div>

                <form action="{{ route('categorias.update', $categoria) }}" method="POST" class="p-8">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-bold text-gray-900 mb-3">
                                Nombre de la Categoría
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $categoria->nombre) }}"
                                placeholder="Ej: Ropa, Electrónica, Alimentos..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 @error('nombre') border-red-500 @enderror">
                            @error('nombre')
                            <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcion" class="block text-sm font-bold text-gray-900 mb-3">
                                Descripción (Opcional)
                            </label>
                            <textarea id="descripcion" name="descripcion" rows="4"
                                placeholder="Describe los tipos de productos que incluirá esta categoría..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 @error('descripcion') border-red-500 @enderror">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                            @error('descripcion')
                            <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex gap-4 mt-8">
                        <a href="{{ route('categorias.index') }}"
                            class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="flex-1 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                            Actualizar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>