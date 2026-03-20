<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-3xl text-gray-900 leading-tight">
                Nuevo Producto
            </h2>
            <p class="text-gray-600 text-sm mt-1">Completa los detalles para agregar un nuevo producto a tu inventario</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-8">
                    <h3 class="text-white font-bold text-lg">Información del Producto</h3>
                    <p class="text-indigo-100 text-sm mt-1">Ingresa todos los detalles del producto</p>
                </div>

                <form action="{{ route('productos.store') }}" method="POST" class="p-8">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Nombre --}}
                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Nombre del Producto
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}"
                                placeholder="Ej: Body de algodón"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 @error('nombre') border-red-500 @enderror">
                            @error('nombre') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Categoría --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Categoría
                                <span class="text-red-500">*</span>
                            </label>
                            <select name="categoria_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 @error('categoria_id') border-red-500 @enderror">
                                <option value="">-- Seleccionar Categoría --</option>
                                @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                                @endforeach
                            </select>
                            @error('categoria_id') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Cantidad --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Cantidad
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="cantidad" value="{{ old('cantidad', 0) }}" min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 @error('cantidad') border-red-500 @enderror">
                            @error('cantidad') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Precio Compra --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Precio de Compra
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="precio_compra" value="{{ old('precio_compra') }}" step="0.01" min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 @error('precio_compra') border-red-500 @enderror">
                            @error('precio_compra') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Precio Venta --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Precio de Venta
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="precio_venta" value="{{ old('precio_venta') }}" step="0.01" min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 @error('precio_venta') border-red-500 @enderror">
                            @error('precio_venta') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Talle --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Talle
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="talle" value="{{ old('talle') }}"step="0.01" min="0"
                                placeholder="Ej: Talle 1, Talle 4..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 @error('talle') border-red-500 @enderror">
                            @error('talle') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Color --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Color
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="color" value="{{ old('color') }}"
                                placeholder="Ej: Rojo, Azul marino, Verde..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 @error('color') border-red-500 @enderror">
                            @error('color') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Descripción --}}
                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Descripción (Opcional)
                            </label>
                            <textarea name="descripcion" rows="3"
                                placeholder="Añade detalles adicionales sobre el producto..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">{{ old('descripcion') }}</textarea>
                        </div>

                    </div>

                    <div class="flex gap-4 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('productos.index') }}"
                            class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="flex-1 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-black font-bold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                            Guardar Producto
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
