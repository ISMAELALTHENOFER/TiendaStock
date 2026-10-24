<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="min-w-0">
                <h1 class="page-title break-words">{{ $categoria->nombre }}</h1>
                <p class="mt-1 text-sm text-muted">Detalles de la categoría</p>
            </div>
            <div class="flex flex-col gap-2 sm:ml-auto sm:flex-row">
                <a href="{{ route('categorias.edit', $categoria) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-control bg-primary px-4 text-sm font-semibold text-white hover:bg-primary-700">Editar</a>
                <a href="{{ route('categorias.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-border px-4 text-sm font-semibold text-ink hover:bg-surface-muted">Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-card border border-border bg-surface shadow-subtle" aria-labelledby="category-details-title">
                <div class="border-b border-border px-5 py-4 sm:px-7">
                    <h2 id="category-details-title" class="text-lg font-bold text-ink">Información de la Categoría</h2>
                </div>
                <dl class="grid gap-5 p-5 sm:grid-cols-2 sm:p-7">
                    <div>
                        <dt class="text-sm font-semibold text-muted">Nombre</dt>
                        <dd class="mt-1 break-words text-xl font-bold text-ink">{{ $categoria->nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-muted">Total de Productos</dt>
                        <dd class="mt-1 text-xl font-bold text-ink">{{ $categoria->productos->count() }}</dd>
                    </div>
                    @if ($categoria->descripcion)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-semibold text-muted">Descripción</dt>
                            <dd class="mt-1 whitespace-pre-line break-words text-sm leading-6 text-ink">{{ $categoria->descripcion }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section class="overflow-hidden rounded-card border border-border bg-surface shadow-subtle" aria-labelledby="category-products-title">
                <div class="border-b border-border px-5 py-4 sm:px-7">
                    <h2 id="category-products-title" class="text-lg font-bold text-ink">Productos en esta Categoría</h2>
                    <p class="mt-1 text-sm text-muted">{{ $categoria->productos->count() }} {{ $categoria->productos->count() === 1 ? 'producto' : 'productos' }}</p>
                </div>

                @if ($categoria->productos->isNotEmpty())
                    <div class="hidden overflow-x-auto md:block">
                        <table class="w-full text-sm">
                            <thead class="border-b border-border bg-surface-muted text-left text-xs font-semibold text-muted">
                                <tr>
                                    <th scope="col" class="px-5 py-3">Nombre</th>
                                    <th scope="col" class="px-5 py-3">Stock</th>
                                    <th scope="col" class="px-5 py-3">Precio de Compra</th>
                                    <th scope="col" class="px-5 py-3">Precio de Venta</th>
                                    <th scope="col" class="px-5 py-3"><span class="sr-only">Opciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach ($categoria->productos as $producto)
                                    <tr class="hover:bg-surface-muted">
                                        <td class="px-5 py-4 font-semibold text-ink">{{ $producto->nombre }}</td>
                                        <td class="px-5 py-4 text-muted">{{ $producto->cantidad }}</td>
                                        <td class="px-5 py-4 text-muted">{{ formato_pesos($producto->precio_compra) }}</td>
                                        <td class="px-5 py-4 text-muted">{{ formato_pesos($producto->precio_venta) }}</td>
                                        <td class="px-5 py-4 text-right"><a href="{{ route('productos.show', $producto) }}" class="inline-flex min-h-11 items-center rounded-control px-3 font-semibold text-primary hover:bg-surface-muted">Ver</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="space-y-3 p-4 md:hidden">
                        @foreach ($categoria->productos as $producto)
                            <article class="rounded-card border border-border p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <h3 class="min-w-0 break-words font-semibold text-ink">{{ $producto->nombre }}</h3>
                                    <span class="shrink-0 rounded-full bg-surface-muted px-2.5 py-1 text-xs font-semibold text-ink">{{ $producto->cantidad }} u.</span>
                                </div>
                                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div><dt class="text-xs text-muted">P. Compra</dt><dd class="mt-1 font-medium text-ink">{{ formato_pesos($producto->precio_compra) }}</dd></div>
                                    <div><dt class="text-xs text-muted">P. Venta</dt><dd class="mt-1 font-medium text-ink">{{ formato_pesos($producto->precio_venta) }}</dd></div>
                                </dl>
                                <a href="{{ route('productos.show', $producto) }}" class="mt-4 inline-flex min-h-11 items-center rounded-control px-3 text-sm font-semibold text-primary hover:bg-surface-muted">Ver producto</a>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center sm:p-12">
                        <h3 class="text-lg font-bold text-ink">Esta categoría aún no tiene productos</h3>
                        <p class="mt-2 text-sm text-muted">Los productos asignados a esta categoría aparecerán acá.</p>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
