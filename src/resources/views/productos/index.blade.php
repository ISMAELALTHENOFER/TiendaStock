<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl bg-gradient-to-r from-brand-300 to-sky-300 bg-clip-text text-transparent">
                    Inventario de Productos
                </h2>
                <p class="text-gray-600 text-sm mt-1">Gestiona todos tus productos en un solo lugar</p>
            </div>
            <a href="{{ route('productos.create') }}"
                class="inline-flex items-center gap-2 bg-brand-300 hover:bg-brand-400 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Producto
            </a>
        </div>
    </x-slot>

    {{-- Evita parpadeo de bloques x-show/x-cloak antes de que Alpine hidrate --}}
    <style>[x-cloak]{display:none!important}</style>

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

            {{-- Buscador client-side: el input vive fuera del x-for para no perder foco al re-renderizar filas --}}
            <div x-data="productSearch({{ Js::from($categorias) }})" x-init="cargarProductos()" class="space-y-6">

                {{-- Estado de carga: el input SIGUE habilitado (nunca se deshabilita) --}}
                <div x-show="cargando" x-cloak class="bg-white rounded-xl shadow-sm border border-sky-100 p-8 text-center text-gray-400">
                    <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-brand-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Cargando catálogo...
                </div>

                {{-- Error de carga: el buscador sigue disponible para reintentar --}}
                <div x-show="errorCarga" x-cloak class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg flex items-center justify-between">
                    <span>No se pudo cargar el catálogo de productos.</span>
                    <button type="button" @click="cargarProductos()" class="text-red-700 font-semibold underline">Reintentar</button>
                </div>

                {{-- Barra de búsqueda: input estático, sobrevive al re-render de la lista --}}
                <div x-ref="barraBusqueda" class="space-y-4">
                    <div class="relative">
                        <input
                            type="text"
                            x-model="busqueda"
                            aria-label="Buscar por nombre, categoría, talle o color"
                            placeholder="Buscar por nombre, categoría, talle o color..."
                            autocomplete="off"
                            autofocus
                            class="w-full px-5 py-3 pl-12 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all text-gray-700"
                        >
                        <svg class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    {{-- Filtros secundarios: categoría, talle y color --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="filtro-categoria" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Categoría</label>
                            <select
                                id="filtro-categoria"
                                x-model="filtroCategoria"
                                aria-label="Filtrar por categoría"
                                class="w-full border-2 border-slate-200 rounded-xl px-3 py-2 text-gray-700 focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all"
                            >
                                <option value="">Todas las categorías</option>
                                <template x-for="categoria in categorias" :key="categoria.id">
                                    <option :value="categoria.id" x-text="categoria.nombre"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label for="filtro-talle" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Talle</label>
                            <input
                                id="filtro-talle"
                                type="text"
                                x-model="filtroTalle"
                                aria-label="Filtrar por talle"
                                placeholder="Ej: M, 42, Único..."
                                autocomplete="off"
                                class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all text-gray-700"
                            >
                        </div>
                        <div>
                            <label for="filtro-color" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Color</label>
                            <input
                                id="filtro-color"
                                type="text"
                                x-model="filtroColor"
                                aria-label="Filtrar por color"
                                placeholder="Ej: Rojo, Negro..."
                                autocomplete="off"
                                class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all text-gray-700"
                            >
                        </div>
                    </div>

                    {{-- Indicador de resultados + limpiar: fuera del template x-for --}}
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="px-4 py-2 bg-brand-50 border border-brand-200 rounded-lg text-brand-700 text-sm">
                            Mostrando <strong x-text="rangoInicio"></strong>–<strong x-text="rangoFin"></strong>
                            de <strong x-text="totalFiltrados"></strong> producto(s)
                        </div>
                        <button
                            type="button"
                            x-show="hayFiltrosActivos"
                            x-cloak
                            @click="limpiar()"
                            class="inline-flex items-center gap-2 text-brand-600 hover:text-brand-800 text-sm font-medium"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Limpiar filtros
                        </button>
                    </div>
                </div>

                {{-- Tabla de resultados: solo las filas se re-renderizan vía x-for --}}
                <div x-show="totalFiltrados > 0" class="bg-white rounded-xl shadow-sm border border-sky-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-sky-50 border-b border-sky-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Categoría</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Talle</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Color</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">P. Compra</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">P. Venta</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Ganancia</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template x-for="producto in productosPagina" :key="producto.id">
                                    <tr class="hover:bg-sky-50/50 transition-colors duration-200">
                                        <td class="px-6 py-4 font-semibold text-gray-900" x-text="producto.nombre"></td>
                                        <td class="px-6 py-4">
                                            <span class="inline-block bg-brand-100 text-brand-700 rounded-full px-3 py-1 text-xs font-semibold" x-text="producto.categoria?.nombre ?? '—'"></span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600" x-text="producto.talle ?? '—'"></td>
                                        <td class="px-6 py-4 text-gray-600" x-text="producto.color ?? '—'"></td>
                                        <td class="px-6 py-4 text-right text-gray-600 font-medium" x-text="formatCurrency(producto.precio_compra)"></td>
                                        <td class="px-6 py-4 text-right text-gray-600 font-medium" x-text="formatCurrency(producto.precio_venta)"></td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-green-600 font-bold" x-text="formatCurrency(calcularGanancia(producto))"></span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-block px-3 py-1 rounded-full text-xs font-bold"
                                                :class="producto.cantidad <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                                                x-text="producto.cantidad"
                                            ></span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex justify-center gap-2">
                                                <a :href="`/productos/${producto.id}`"
                                                    title="Ver detalles"
                                                    class="text-brand-300 hover:text-brand-400 hover:bg-brand-50 p-2 rounded-lg transition-colors duration-200">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>
                                                <a :href="`/productos/${producto.id}/edit`"
                                                    title="Editar"
                                                    class="text-sky-500 hover:text-sky-600 hover:bg-sky-50 p-2 rounded-lg transition-colors duration-200">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </a>
                                                <form
                                                    :action="`/productos/${producto.id}`"
                                                    method="POST"
                                                    style="display:inline;"
                                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?')"
                                                >
                                                    <input type="hidden" name="_token" :value="csrfToken">
                                                    <input type="hidden" name="_method" value="DELETE">
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
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación client-side de a 50 filas sobre el resultado filtrado.
                         Los botones de nav se deshabilitan en los bordes; el buscador NUNCA se deshabilita. --}}
                    <div x-show="totalPaginas > 1" class="p-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                        <button type="button"
                            @click="paginaAnterior()"
                            :disabled="paginaActual === 1"
                            class="inline-flex items-center gap-1 bg-white border-2 border-slate-200 rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 transition-all"
                            :class="paginaActual === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-50 hover:border-brand-300'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Anterior
                        </button>

                        <div class="text-sm text-gray-600">
                            Página <strong x-text="paginaActual"></strong> de <strong x-text="totalPaginas"></strong>
                        </div>

                        <button type="button"
                            @click="paginaSiguiente()"
                            :disabled="paginaActual === totalPaginas"
                            class="inline-flex items-center gap-1 bg-white border-2 border-slate-200 rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 transition-all"
                            :class="paginaActual === totalPaginas ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-50 hover:border-brand-300'">
                            Siguiente
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Estado vacío: sin resultados para los filtros activos --}}
                <div x-show="totalFiltrados === 0" x-cloak class="bg-white rounded-xl shadow-sm border border-sky-100 overflow-hidden">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-500 text-lg font-medium">No se encontraron productos</p>
                        <p class="text-gray-400 text-sm mt-2">Prueba con otros términos de búsqueda</p>
                        <button type="button"
                            @click="limpiar()"
                            class="mt-6 inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Ver todos los productos
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productSearch', (categorias) => ({
        // Tamaño de página. El catálogo entero vive en memoria; solo se
        // renderizan 15 filas por vez para no saturar el DOM.
        POR_PAGINA: 15,

        // Catálogo vacío hasta que cargue vía fetch; el buscador ya es usable
        // (los filtros no dependen del catálogo, solo filtran 0 filas).
        productos: [],
        categorias,

        // Estado de carga: el input NUNCA se deshabilita por esto.
        cargando: false,
        errorCarga: false,

        // Estado de filtros: el input principal nunca se deshabilita ni se recrea.
        busqueda: '',
        filtroCategoria: '',
        filtroTalle: '',
        filtroColor: '',

        // Página actual del paginado cliente. Se resetea a 1 al cambiar filtros.
        paginaActual: 1,

        // Token CSRF leído una sola vez del meta (no se hardcodea).
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content ?? '',

        // Cuando un filtro cambia, ver la página 2 de resultados viejos no tiene
        // sentido. Los watchers resetean a la primera página.
        init() {
            this.$watch('busqueda', () => { this.paginaActual = 1; });
            this.$watch('filtroCategoria', () => { this.paginaActual = 1; });
            this.$watch('filtroTalle', () => { this.paginaActual = 1; });
            this.$watch('filtroColor', () => { this.paginaActual = 1; });
        },

        /**
         * Carga el catálogo desde el endpoint cacheable. En error deja el
         * buscador usable y ofrece reintentar, sin bloquear la UI.
         */
        async cargarProductos() {
            this.cargando = true;
            this.errorCarga = false;
            try {
                const response = await fetch('{{ route('productos.data') }}', {
                    headers: { 'Accept': 'application/json' },
                });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                this.productos = await response.json();
                this.paginaActual = 1;
            } catch (e) {
                console.error('Error al cargar el catálogo:', e);
                this.errorCarga = true;
                this.productos = [];
            } finally {
                this.cargando = false;
            }
        },

        /** Coincidencia OR en los 4 campos de texto, case-insensitive. Soporta null. */
        coincideTexto(valor, termino) {
            if (!termino) return true;
            const haystack = (valor ?? '').toString().toLowerCase();
            return haystack.includes(termino);
        },

        get hayFiltrosActivos() {
            return this.busqueda.trim() !== ''
                || this.filtroCategoria !== ''
                || this.filtroTalle.trim() !== ''
                || this.filtroColor.trim() !== '';
        },

        /**
         * Aplica AND entre filtros activos. La caja de texto busca OR entre
         * nombre / categoria.nombre / talle / color. Los demás filtros acotan
         * su campo correspondiente. Dispara desde 1 caracter (sin mínimo).
         */
        get filtrados() {
            const termino = this.busqueda.trim().toLowerCase();
            const talle = this.filtroTalle.trim().toLowerCase();
            const color = this.filtroColor.trim().toLowerCase();
            const categoriaId = this.filtroCategoria;

            return this.productos.filter((producto) => {
                // Filtro por categoría exacta (id).
                if (categoriaId !== '' && String(producto.categoria_id) !== String(categoriaId)) {
                    return false;
                }

                // Filtro por talle: solo dentro del campo talle.
                if (talle && !this.coincideTexto(producto.talle, talle)) {
                    return false;
                }

                // Filtro por color: solo dentro del campo color.
                if (color && !this.coincideTexto(producto.color, color)) {
                    return false;
                }

                // Caja de texto principal: OR entre los 4 campos.
                if (termino) {
                    const enNombre = this.coincideTexto(producto.nombre, termino);
                    const enCategoria = this.coincideTexto(producto.categoria?.nombre, termino);
                    const enTalle = this.coincideTexto(producto.talle, termino);
                    const enColor = this.coincideTexto(producto.color, termino);
                    if (!(enNombre || enCategoria || enTalle || enColor)) {
                        return false;
                    }
                }

                return true;
            });
        },

        get totalFiltrados() {
            return this.filtrados.length;
        },

        // --- Paginación client-side sobre el resultado filtrado ---

        get totalPaginas() {
            return Math.max(1, Math.ceil(this.totalFiltrados / this.POR_PAGINA));
        },

        /** Slice del resultado filtrado correspondiente a la página actual. */
        get productosPagina() {
            const inicio = (this.paginaActual - 1) * this.POR_PAGINA;
            return this.filtrados.slice(inicio, inicio + this.POR_PAGINA);
        },

        /** Índice (1-based) del primer producto visible en la página actual. */
        get rangoInicio() {
            if (this.totalFiltrados === 0) return 0;
            return (this.paginaActual - 1) * this.POR_PAGINA + 1;
        },

        /** Índice (1-based) del último producto visible en la página actual. */
        get rangoFin() {
            return Math.min(this.paginaActual * this.POR_PAGINA, this.totalFiltrados);
        },

        irAPagina(n) {
            const destino = Math.max(1, Math.min(n, this.totalPaginas));
            this.paginaActual = destino;
        },

        paginaAnterior() {
            if (this.paginaActual > 1) this.paginaActual--;
        },

        paginaSiguiente() {
            if (this.paginaActual < this.totalPaginas) this.paginaActual++;
        },

        limpiar() {
            this.busqueda = '';
            this.filtroCategoria = '';
            this.filtroTalle = '';
            this.filtroColor = '';
            this.paginaActual = 1;
            // Devuelve el foco al buscador para seguir escribiendo sin fricción.
            this.$refs.barraBusqueda?.querySelector('input[type="text"]')?.focus();
        },

        /** Ganancia = precio_venta - precio_compra (recrea el accessor PHP client-side, ya que no se serializa). */
        calcularGanancia(producto) {
            return parseFloat(producto.precio_venta || 0) - parseFloat(producto.precio_compra || 0);
        },

        formatCurrency(value) {
            return '$' + parseFloat(value || 0).toLocaleString('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },
    }));
});
</script>