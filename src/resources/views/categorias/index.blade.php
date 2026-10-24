<x-app-layout :categorias="$categorias">
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="page-title">Categorías de Productos</h1>
                <p class="mt-1 text-sm text-muted">Gestiona todas tus categorías de productos</p>
            </div>
            <a href="{{ route('categorias.create') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-control bg-primary px-5 text-sm font-semibold text-white hover:bg-primary-700">
                Nueva Categoría
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success') || session('error'))
                <div role="alert" class="mb-6 rounded-card border border-border p-4 text-sm {{ session('error') ? 'bg-red-50 text-red-700' : 'bg-surface text-ink' }}">
                    {{ session('error') ?? session('success') }}
                </div>
            @endif

            @if ($categorias->count() > 0)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($categorias as $categoria)
                        <article class="flex min-w-0 flex-col justify-between rounded-card border border-border bg-surface p-5 shadow-sm sm:p-6">
                            <div>
                                <div class="flex min-w-0 flex-wrap items-start justify-between gap-3">
                                    <h2 class="min-w-0 break-words font-bold text-ink">{{ $categoria->nombre }}</h2>
                                    <span class="shrink-0 rounded-full bg-surface-muted px-3 py-1 text-xs font-semibold text-ink">{{ $categoria->productos_count }} {{ $categoria->productos_count === 1 ? 'producto' : 'productos' }}</span>
                                </div>
                                <p class="mt-2 min-h-10 break-words text-sm text-muted">{{ $categoria->descripcion ?: 'Sin descripción' }}</p>
                            </div>
                            <div class="mt-5 flex flex-wrap items-center gap-1 border-t border-border pt-3">
                                <a href="{{ route('categorias.show', $categoria) }}" class="inline-flex min-h-11 items-center rounded-control px-3 text-sm font-semibold text-ink hover:bg-surface-muted">Ver</a>
                                <a href="{{ route('categorias.edit', $categoria) }}" class="inline-flex min-h-11 items-center rounded-control px-3 text-sm font-semibold text-primary hover:bg-surface-muted">Editar</a>
                                @if ($categoria->productos_count > 0)
                                    <button type="button" disabled title="No se puede eliminar mientras tenga productos" class="ml-auto inline-flex min-h-11 cursor-not-allowed items-center rounded-control px-3 text-sm font-semibold text-muted opacity-70">No se puede eliminar</button>
                                @else
                                    <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="ml-auto" id="delete-category-{{ $categoria->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDialogShow('Eliminar categoría', '¿Eliminar la categoría «{{ $categoria->nombre }}»? Esta acción no se puede deshacer.', 'Sí, eliminar', 'bg-red-600 hover:bg-red-700').then(r => r && document.getElementById('delete-category-{{ $categoria->id }}').submit())" class="inline-flex min-h-11 items-center rounded-control px-3 text-sm font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $categorias->links() }}
                </div>
            @else
                <div class="rounded-card border border-border bg-surface p-8 text-center shadow-sm sm:p-12">
                    <h2 class="text-lg font-bold text-ink">No hay categorías creadas aún</h2>
                    <p class="mt-2 text-sm text-muted">Las categorías agrupan tus productos para encontrarlos más rápido.</p>
                    <a href="{{ route('categorias.create') }}" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-control bg-primary px-5 text-sm font-semibold text-white hover:bg-primary-700">Crear Primera Categoría</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
