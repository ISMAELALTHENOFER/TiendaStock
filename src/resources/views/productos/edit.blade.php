<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Producto: {{ $producto->nombre }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('productos.update', $producto) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Categoría *</label>
                            <select name="categoria_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Seleccionar --</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categoria_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cantidad *</label>
                            <input type="number" name="cantidad" value="{{ old('cantidad', $producto->cantidad) }}" min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('cantidad') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Precio de Compra *</label>
                            <input type="number" name="precio_compra" value="{{ old('precio_compra', $producto->precio_compra) }}" step="0.01" min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('precio_compra') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Precio de Venta *</label>
                            <input type="number" name="precio_venta" value="{{ old('precio_venta', $producto->precio_venta) }}" step="0.01" min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('precio_venta') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Talle</label>
                            <select name="talle"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Sin talle --</option>
                                @foreach(['XS','S','M','L','XL','XXL','XXXL','Único'] as $t)
                                    <option value="{{ $t }}" {{ old('talle', $producto->talle) == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Color</label>
                            <input type="text" name="color" value="{{ old('color', $producto->color) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea name="descripcion" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('descripcion', $producto->descripcion) }}</textarea>
                        </div>

                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <a href="{{ route('productos.index') }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Actualizar Producto
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
