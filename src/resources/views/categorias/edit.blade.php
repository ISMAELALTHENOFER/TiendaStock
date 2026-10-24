<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title">Editar Categoría</h1>
            <p class="mt-1 text-sm text-muted">{{ $categoria->nombre }}</p>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="w-full min-w-0">
            <div class="overflow-hidden rounded-card border border-border bg-surface shadow-subtle">
                <div class="border-b border-border px-5 py-4 sm:px-7">
                    <h2 class="text-lg font-bold text-ink">Modificar Categoría</h2>
                </div>
                <form action="{{ route('categorias.update', $categoria) }}" method="POST" class="space-y-5 p-5 sm:p-7">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="nombre" class="mb-1.5 block text-sm font-semibold text-ink">Nombre de Categoría <span class="text-red-600">*</span></label>
                        <input id="nombre" name="nombre" type="text" required maxlength="255" value="{{ old('nombre', $categoria->nombre) }}" class="min-h-11 w-full rounded-control border border-border bg-surface px-3.5 text-sm text-ink placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 @error('nombre') border-red-600 @enderror">
                        @error('nombre') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="descripcion" class="mb-1.5 block text-sm font-semibold text-ink">Descripción <span class="font-normal text-muted">(Opcional)</span></label>
                        <textarea id="descripcion" name="descripcion" rows="3" maxlength="500" class="w-full resize-y rounded-control border border-border bg-surface px-3.5 py-3 text-sm text-ink placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 @error('descripcion') border-red-600 @enderror">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                        @error('descripcion') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t border-border pt-5 sm:flex-row sm:justify-end">
                        <a href="{{ route('categorias.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-border px-5 text-sm font-semibold text-ink hover:bg-surface-muted">Cancelar</a>
                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-control bg-primary px-5 text-sm font-semibold text-white hover:bg-primary-700">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
