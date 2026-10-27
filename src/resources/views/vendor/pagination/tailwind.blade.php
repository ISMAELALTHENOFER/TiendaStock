<div class="pagination-controls">
    <p class="pagination-summary" aria-live="polite">
        Mostrando {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }} resultados
    </p>

    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Paginación" class="pagination-actions">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Página {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
            @else
                <span aria-disabled="true">Siguiente</span>
            @endif
        </nav>
    @endif

    <form method="GET" action="{{ url()->current() }}" class="contents">
        @foreach (request()->except(['page', 'per_page']) as $key => $value)
            @if (is_scalar($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <label class="pagination-size">Por página
            <select name="per_page" aria-label="Elementos por página" onchange="this.form.submit()">
                @foreach ([15, 30, 50] as $pageSize)
                    <option value="{{ $pageSize }}" @selected($paginator->perPage() === $pageSize)>{{ $pageSize }}</option>
                @endforeach
            </select>
        </label>
    </form>
</div>
