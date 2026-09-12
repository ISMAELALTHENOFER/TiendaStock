@use('Illuminate\Support\Facades\Storage')
<x-app-layout :producto="$producto" :categorias="$categorias">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
            <div>
                <h2 class="page-title">
                    Editar Producto
                </h2>
                <p class="text-gray-600 text-sm mt-1">{{ $producto->nombre }}</p>
            </div>
            <a href="{{ route('productos.index') }}"
                class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-4 rounded-lg transition-colors duration-200 text-center sm:text-left">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                <div class="bg-gradient-to-r from-brand-400 to-brand-500 px-6 py-8">
                    <h3 class="text-white font-bold text-lg">Modificar Producto</h3>
                    <p class="text-white/80 text-sm mt-1">Actualiza los detalles del producto</p>
                </div>

                <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data" class="p-8"
                      x-data="{
                          confirmar() {
                              return confirmDialogShow(
                                  'Editar producto',
                                  '¿Confirmar los cambios en este producto?',
                                  'Sí, guardar',
                                   'bg-brand-600 hover:bg-brand-700'
                              );
                          }
                      }"
                      @submit.prevent="confirmar().then(r => r && $el.submit())">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Nombre del Producto
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('nombre') border-red-500 @enderror">
                            @error('nombre') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Categoría — span full width for placeholder fit + explicit chevron
                                     so the native arrow never overlaps the text. --}}
                        <div class="col-span-2" x-data="inlineCategory()">
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Categoría
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <select name="categoria_id"
                                        class="w-full appearance-none pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('categoria_id') border-red-500 @enderror">
                                        <option value="">-- Seleccionar Categoría --</option>
                                        @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                <button type="button" @click="open = true"
                                    title="Crear categoría nueva"
                                    class="flex-shrink-0 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold py-3 px-4 rounded-lg border border-brand-200 transition-colors duration-200">
                                    + Nueva
                                </button>
                            </div>
                            @error('categoria_id') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror

                            {{-- Modal alta inline --}}
                            <div x-show="open" x-cloak
                                 class="fixed inset-0 z-40 flex items-center justify-center p-4"
                                 style="display:none;">
                                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
                                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">Nueva Categoría</h3>
                                    <input type="text" x-model="nombre" @keydown.enter.prevent="submit()"
                                        placeholder="Nombre de la categoría"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200">
                                    <p x-show="error" x-text="error" class="text-red-500 text-sm font-medium mt-2"></p>
                                    <div class="flex flex-col sm:flex-row gap-3 mt-6">
                                        <button type="button" @click="open = false"
                                            class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 px-4 rounded-xl transition-colors duration-200">
                                            Cancelar
                                        </button>
                                        <button type="button" @click="submit()" :disabled="submitting"
                                            class="flex-1 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-md">
                                            <span x-text="submitting ? 'Guardando...' : 'Crear'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Cantidad
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="cantidad" value="{{ old('cantidad', $producto->cantidad) }}" min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('cantidad') border-red-500 @enderror">
                            @error('cantidad') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Costo (precio_compra) — masked ARS + hidden numeric --}}
                        <div x-data="moneyInput({{ old('precio_compra', $producto->precio_compra) }})">
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Costo
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" :value="display" @input="onInput($event)" @blur="onBlur()"
                                inputmode="decimal"
                                placeholder="0,00"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('precio_compra') border-red-500 @enderror">
                            <input type="hidden" name="precio_compra" :value="raw">
                            @error('precio_compra') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Precio de Venta — masked ARS + hidden numeric --}}
                        <div x-data="moneyInput({{ old('precio_venta', $producto->precio_venta) }})">
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Precio de Venta
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" :value="display" @input="onInput($event)" @blur="onBlur()"
                                inputmode="decimal"
                                placeholder="0,00"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('precio_venta') border-red-500 @enderror">
                            <input type="hidden" name="precio_venta" :value="raw">
                            @error('precio_venta') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Talle (Opcional) — fixed: no step/min, @error block present --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Talle
                                <span class="text-gray-400 font-normal text-xs">(Opcional)</span>
                            </label>
                            <input type="text" name="talle" value="{{ old('talle', $producto->talle) }}"
                                placeholder="Ej: Talle 1, Talle 4..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('talle') border-red-500 @enderror">
                            @error('talle') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Color (Opcional) --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Color
                                <span class="text-gray-400 font-normal text-xs">(Opcional)</span>
                            </label>
                            <input type="text" name="color" value="{{ old('color', $producto->color) }}"
                                placeholder="Ej: Rojo, Azul marino, Verde..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('color') border-red-500 @enderror">
                            @error('color') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Imagen (Opcional) — preserva la existente si no se reemplaza --}}
                        <div class="col-span-2" x-data="productImage('{{ $producto->imagen ? Storage::url($producto->imagen) : '' }}')">
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Imagen
                                <span class="text-gray-400 font-normal text-xs">(Opcional)</span>
                            </label>

                            @if($producto->imagen)
                            <div class="mb-3 flex items-center gap-3">
                                <img src="{{ Storage::url($producto->imagen) }}" alt="Imagen actual"
                                    class="h-24 w-24 object-cover rounded-lg border border-slate-200 shadow-sm">
                                <p class="text-xs text-slate-500">Imagen actual. Si no elegís un archivo nuevo, se conserva.</p>
                            </div>
                            @endif

                            <input type="file" accept="image/*" name="imagen" @change="showPreview($event)"
                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-100 file:text-brand-700 file:font-bold hover:file:bg-brand-200 transition-colors duration-200">
                            @error('imagen') <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p> @enderror
                            <div x-show="preview" x-cloak class="mt-3">
                                <img :src="preview" alt="Vista previa" class="h-32 w-32 object-cover rounded-lg border border-slate-200 shadow-sm">
                            </div>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Descripción
                                <span class="text-gray-400 font-normal text-xs">(Opcional)</span>
                            </label>
                            <textarea name="descripcion" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200">{{ old('descripcion', $producto->descripcion) }}</textarea>
                        </div>

                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('productos.index') }}"
                            class="flex-1 w-full sm:w-auto text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="flex-1 w-full sm:w-auto bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 shadow-md">
                            Actualizar Producto
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>

@once
    @include('productos.partials._alpine')
@endonce
