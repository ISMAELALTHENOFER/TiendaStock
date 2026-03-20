<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-gray-900 leading-tight">
                    Editar Producto
                </h2>
                <p class="text-gray-600 text-sm mt-1">{{ $producto->nombre }}</p>
            </div>
            <a href="{{ route('productos.index') }}"
                class="text-gray-600 hover:text-gray-900 font-medium">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-8">
                    <h3 class="text-white font-bold text-lg">Modificar Producto</h3>
                    <p class="text-amber-100 text-sm mt-1">Actualiza los detalles del producto</p>
                </div>

                <form action="{{ route('productos.update', $producto) }}" method="POST" class="p-8">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Nombre del Producto
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 @error('nombre') border-red-500 @enderror">
                            @error('nombre') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Categoría
                                <span class="text-red-500">*</span>
                            </label>
                            <select name="categoria_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 @error('categoria_id') border-red-500 @enderror">
                                <option value="">-- Seleccionar --</option>
                                @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                                @endforeach
                            </select>
                            @error('categoria_id') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Cantidad
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="cantidad" value="{{ old('cantidad', $producto->cantidad) }}" min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 @error('cantidad') border-red-500 @enderror">
                            @error('cantidad') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Precio de Compra
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="precio_compra" value="{{ old('precio_compra', $producto->precio_compra) }}" step="0.01" min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 @error('precio_compra') border-red-500 @enderror">
                            @error('precio_compra') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Precio de Venta
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="precio_venta" value="{{ old('precio_venta', $producto->precio_venta) }}" step="0.01" min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 @error('precio_venta') border-red-500 @enderror">
                            @error('precio_venta') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Talle
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="talle" value="{{ old('talle', $producto->talle) }}"step="0.01" min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Color
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="color" value="{{ old('color', $producto->color) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 @error('color') border-red-500 @enderror">
                            @error('color') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Descripción (Opcional)
                            </label>
                            <textarea name="descripcion" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200">{{ old('descripcion', $producto->descripcion) }}</textarea>
                        </div>

                    </div>

                    <div class="flex gap-4 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('productos.index') }}"
                            class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="flex-1 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                            Actualizar Producto
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
