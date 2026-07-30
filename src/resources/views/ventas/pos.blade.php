<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl bg-gradient-to-r from-brand-300 to-sky-300 bg-clip-text text-transparent">
                    Punto de Venta
                </h2>
                <p class="text-gray-600 text-sm mt-1">Registra nuevas ventas en el sistema</p>
            </div>
            <a href="{{ route('ventas.index') }}"
                class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg shadow-md transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg shadow-md" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ $errors->first() }}</p>
                    </div>
                </div>
            </div>
            @endif

            <div x-data="posApp" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                    {{-- COLUMNA IZQUIERDA (3/5): Búsqueda y resultados --}}
                    <div class="lg:col-span-3 space-y-4">
                        <div class="bg-white rounded-xl shadow-sm border border-sky-100 p-4">
                            <div class="relative">
                                <input type="text"
                                    x-model="searchQuery"
                                    @input.debounce.300ms="searchProducts"
                                    @keydown.enter.prevent="addFirstResult"
                                    placeholder="Buscar productos por nombre..."
                                    class="w-full px-5 py-3 pl-12 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all text-gray-700">
                                <svg class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Loading --}}
                        <div x-show="searching" class="text-center p-8 text-gray-400">
                            <svg class="animate-spin h-8 w-8 mx-auto text-brand-300 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Buscando...
                        </div>

                        {{-- Resultados --}}
                        <div x-show="!searching && searchResults.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="product in searchResults" :key="product.id">
                                <div class="bg-white rounded-xl shadow-sm border border-sky-100 p-4 flex items-center justify-between hover:shadow-md transition-shadow duration-200">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 truncate" x-text="product.nombre"></p>
                                        <p class="text-sm text-gray-500">
                                            $<span x-text="formatCurrency(product.precio_venta)"></span>
                                            | Stock: <span x-text="product.cantidad" :class="product.cantidad <= 5 ? 'text-red-600 font-bold' : ''"></span>
                                            <template x-if="product.cantidad <= 5">
                                                <span class="text-red-500">⚠</span>
                                            </template>
                                        </p>
                                    </div>
                                    <button @click="addToCart(product)"
                                        class="ml-3 flex-shrink-0 bg-brand-300 hover:bg-brand-400 text-white px-3 py-2 rounded-lg text-sm font-bold transition-colors duration-200">
                                        + Agregar
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Sin resultados --}}
                        <div x-show="!searching && searchQuery.length >= 2 && searchResults.length === 0"
                            class="bg-white rounded-xl shadow-sm border border-sky-100 p-8 text-center text-gray-500">
                            No se encontraron productos para "<span x-text="searchQuery" class="font-semibold"></span>"
                        </div>

                        {{-- Hint de búsqueda --}}
                        <div x-show="searchQuery.length < 2 && searchResults.length === 0"
                            class="bg-white rounded-xl shadow-sm border border-sky-100 p-8 text-center text-gray-400">
                            <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <p>Escriba al menos 2 caracteres para buscar productos.</p>
                        </div>
                    </div>

                    {{-- COLUMNA DERECHA (2/5): Carrito --}}
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white rounded-xl shadow-sm border border-sky-100 p-4 sticky top-4">
                            <h3 class="font-bold text-lg text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-6 h-6 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path>
                                </svg>
                                Carrito
                            </h3>

                            {{-- Carrito vacío --}}
                            <div x-show="cart.length === 0"
                                class="text-center py-8 text-gray-400">
                                <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path>
                                </svg>
                                <p>El carrito está vacío. Busque productos para agregar.</p>
                            </div>

                            {{-- Items del carrito --}}
                            <template x-for="(item, index) in cart" :key="item.producto_id">
                                <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate" x-text="item.nombre"></p>
                                        <p class="text-sm text-gray-500">$<span x-text="formatCurrency(item.precio_venta)"></span> c/u</p>
                                    </div>
                                    <div class="flex items-center gap-2 ml-3">
                                        <button @click="decreaseQty(index)"
                                            :disabled="item.cantidad <= 1"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg font-bold transition-colors"
                                            :class="item.cantidad <= 1 ? 'bg-gray-100 text-gray-300 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200 text-gray-600'">−</button>
                                        <input type="number" x-model="item.cantidad"
                                            @input.debounce="updateCart"
                                            min="1" :max="item.stock_disponible"
                                            class="w-14 text-center border-2 border-slate-200 rounded-lg py-1 text-sm font-semibold">
                                        <button @click="increaseQty(index)"
                                            :disabled="item.cantidad >= item.stock_disponible"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg font-bold transition-colors"
                                            :class="item.cantidad >= item.stock_disponible ? 'bg-gray-100 text-gray-300 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200 text-gray-600'">+</button>
                                    </div>
                                    <div class="text-right ml-3 min-w-[80px]">
                                        <p class="font-bold text-gray-900">$<span x-text="formatCurrency(item.cantidad * item.precio_venta)"></span></p>
                                    </div>
                                    <button @click="removeFromCart(index)"
                                        class="ml-2 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 transition-colors p-2 rounded-lg"
                                        title="Quitar producto">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            {{-- Totales --}}
                            <div x-show="cart.length > 0" class="mt-4 space-y-2 pt-4 border-t border-gray-200">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Subtotal:</span>
                                    <span class="font-semibold">$<span x-text="formatCurrency(subtotal)"></span></span>
                                </div>
                                <div class="flex justify-between items-center text-sm text-gray-600">
                                    <span>Descuento:</span>
                                    <div x-data="moneyInput(0)" x-init="$watch('raw', v => descuento = v)" class="w-24">
                                        <input type="text" :value="display" @input="onInput($event)" @blur="onBlur()"
                                            inputmode="decimal"
                                            placeholder="0,00"
                                            class="w-full text-right border-2 border-slate-200 rounded-lg px-2 py-1 text-sm font-semibold">
                                    </div>
                                </div>
                                <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t border-gray-200">
                                    <span>Total:</span>
                                    <span>$<span x-text="formatCurrency(total)"></span></span>
                                </div>
                            </div>

                            {{-- Datos de pago --}}
                            <div x-show="cart.length > 0" class="mt-4 space-y-3 pt-4 border-t border-gray-200">
                                {{-- Tipo de entrega: local o envio por Uber --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de entrega
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="flex items-center gap-2 border-2 rounded-lg px-3 py-2.5 cursor-pointer transition-all"
                                            :class="tipoEntrega === 'local'
                                                ? 'border-brand-300 bg-brand-50 text-gray-900'
                                                : 'border-slate-200 hover:border-brand-200 text-gray-700'">
                                            <input type="checkbox"
                                                :checked="tipoEntrega === 'local'"
                                                @change="tipoEntrega = ($el.checked ? 'local' : '')"
                                                class="w-4 h-4 rounded text-brand-300 focus:ring-brand-300">
                                            <span class="text-sm font-medium">En el local</span>
                                        </label>
                                        <label class="flex items-center gap-2 border-2 rounded-lg px-3 py-2.5 cursor-pointer transition-all"
                                            :class="tipoEntrega === 'uber'
                                                ? 'border-brand-300 bg-brand-50 text-gray-900'
                                                : 'border-slate-200 hover:border-brand-200 text-gray-700'">
                                            <input type="checkbox"
                                                :checked="tipoEntrega === 'uber'"
                                                @change="tipoEntrega = ($el.checked ? 'uber' : '')"
                                                class="w-4 h-4 rounded text-brand-300 focus:ring-brand-300">
                                            <span class="text-sm font-medium">Envío por Uber</span>
                                        </label>
                                    </div>
                                    <p x-show="!tipoEntrega" class="text-xs text-gray-400 mt-1">
                                        Seleccione una opción para cerrar la venta.
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Método de pago</label>
                                    <select x-model="metodoPago"
                                        class="w-full border-2 border-slate-200 rounded-lg px-3 py-2 text-gray-700 focus:ring-2 focus:ring-brand-300 focus:border-brand-300">
                                        <option value="efectivo">Efectivo</option>
                                        <option value="tarjeta">Tarjeta</option>
                                        <option value="transferencia">Transferencia</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pago con</label>
                                    <div x-data="moneyInput(0)" x-init="$watch('raw', v => pagoCon = v)">
                                        <input type="text" :value="display" @input="onInput($event)" @blur="onBlur()"
                                            @focus="$el.select()"
                                            inputmode="decimal"
                                            placeholder="0,00"
                                            class="w-full pl-3 pr-3 py-2 border-2 border-slate-200 rounded-lg text-gray-700 focus:ring-2 focus:ring-brand-300 focus:border-brand-300 text-right font-semibold">
                                    </div>
                                </div>
                                <div class="flex justify-between text-lg pt-2" x-show="pagoCon > 0">
                                    <span class="text-gray-600">Cambio:</span>
                                    <span class="font-bold text-green-600">$<span x-text="formatCurrency(cambio)"></span></span>
                                </div>
                            </div>

                            {{-- Botones de acción --}}
                            <div class="mt-6 space-y-2">
                                <button @click="submitSale"
                                    :disabled="!canSubmit"
                                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-3 rounded-lg font-bold text-lg transition-all duration-200"
                                    x-text="submitting ? 'Procesando...' : 'Cobrar'">
                                </button>
                                <button @click="clearCart"
                                    x-show="cart.length > 0"
                                    class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 rounded-lg font-medium transition-all duration-200">
                                    Vaciar carrito
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posApp', () => ({
        searchQuery: '',
        searchResults: [],
        searching: false,
        cart: [],
        metodoPago: 'efectivo',
        tipoEntrega: '',
        pagoCon: 0,
        descuento: 0,
        submitting: false,

        formatCurrency(value) {
            return parseFloat(value || 0).toLocaleString('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.cantidad * item.precio_venta), 0);
        },

        get total() {
            return Math.max(0, this.subtotal - parseFloat(this.descuento || 0));
        },

        get cambio() {
            return Math.max(0, parseFloat(this.pagoCon || 0) - this.total);
        },

        get canSubmit() {
            return this.cart.length > 0
                && this.total > 0
                && (this.tipoEntrega === 'local' || this.tipoEntrega === 'uber')
                && parseFloat(this.pagoCon || 0) >= this.total
                && !this.submitting;
        },

        async searchProducts() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            this.searching = true;
            try {
                const response = await fetch(`/productos/search?q=${encodeURIComponent(this.searchQuery)}`);
                this.searchResults = await response.json();
            } catch (e) {
                console.error('Error en búsqueda:', e);
                this.searchResults = [];
            } finally {
                this.searching = false;
            }
        },

        addToCart(product) {
            const existing = this.cart.find(item => item.producto_id === product.id);

            if (existing) {
                if (existing.cantidad < product.cantidad) {
                    existing.cantidad++;
                }
            } else {
                this.cart.push({
                    producto_id: product.id,
                    nombre: product.nombre,
                    precio_venta: parseFloat(product.precio_venta),
                    cantidad: 1,
                    stock_disponible: product.cantidad,
                });
            }
        },

        addFirstResult() {
            if (this.searchResults.length > 0) {
                this.addToCart(this.searchResults[0]);
            }
        },

        increaseQty(index) {
            const item = this.cart[index];
            if (item.cantidad < item.stock_disponible) {
                item.cantidad++;
            }
        },

        decreaseQty(index) {
            const item = this.cart[index];
            if (item.cantidad > 1) {
                item.cantidad--;
            }
        },

        async removeFromCart(index) {
            const item = this.cart[index];
            const confirmed = await confirmDialogShow(
                'Quitar producto',
                '¿Eliminar "' + item.nombre + '" del carrito?',
                'Sí, quitar',
                'bg-red-600 hover:bg-red-700'
            );
            if (!confirmed) return;
            this.cart.splice(index, 1);
            if (this.cart.length === 0) {
                this.pagoCon = 0;
                this.descuento = 0;
            }
        },

        clearCart() {
            if (this.cart.length === 0) return;
            this.cart = [];
            this.pagoCon = 0;
            this.descuento = 0;
        },

        updateCart() {
            // Trigger reactivity
        },

        async submitSale() {
            if (!this.canSubmit) return;

            if (this.tipoEntrega !== 'local' && this.tipoEntrega !== 'uber') {
                alert('Debe indicar si la venta fue en el local o envío por Uber.');
                return;
            }

            this.submitting = true;

            const payload = {
                items: this.cart.map(item => ({
                    producto_id: item.producto_id,
                    cantidad: item.cantidad,
                })),
                subtotal: this.subtotal,
                descuento: this.descuento,
                impuesto: 0,
                total: this.total,
                pago_con: parseFloat(this.pagoCon),
                metodo_pago: this.metodoPago,
                tipo_entrega: this.tipoEntrega,
            };

            try {
                const response = await fetch('/ventas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    const data = await response.json();
                    if (data.errors) {
                        alert(Object.values(data.errors).flat().join('\n'));
                    } else {
                        window.location.reload();
                    }
                }
            } catch (e) {
                alert('Error al procesar la venta. Intente nuevamente.');
            } finally {
                this.submitting = false;
            }
        },
    }));
});
</script>

{{--
    Shared moneyInput: register the single Argentine money mask component
    used by the "Pago con" and "Descuento" inputs above, so live formatting
    and caret preservation are identical to the product create/edit forms.
--}}
@once
    @include('partials._money-input')
@endonce
