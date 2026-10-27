const PAGE_SIZES = [15, 30, 50];

export function Pagination({ currentPage = 1, lastPage = 1, pageSize = 15, summary, hrefForPage, onPageChange, onPageSizeChange, ariaLabel = 'Paginación', numbered = true }) {
    const page = Math.max(1, Number(currentPage) || 1);
    const last = Math.max(1, Number(lastPage) || 1);
    const pages = numbered ? Array.from({ length: Math.min(last, 5) }, (_, index) => {
        const start = Math.max(1, Math.min(page - 2, last - 4));
        return start + index;
    }).filter((value) => value <= last) : [];
    const navigate = (next) => onPageChange?.(Math.max(1, Math.min(last, next)));
    const link = (target, label, current = false) => hrefForPage ? (
        <a key={label} href={hrefForPage(target)} aria-current={current ? 'page' : undefined}>{label}</a>
    ) : (
        <button key={label} type="button" onClick={() => navigate(target)} aria-current={current ? 'page' : undefined}>{label}</button>
    );

    return <nav className="pagination-controls" aria-label={ariaLabel}>
        {summary && <p className="pagination-summary" aria-live="polite">{summary}</p>}
        <div className="pagination-actions">
            {page > 1 ? link(page - 1, 'Anterior') : <button type="button" disabled aria-disabled="true">Anterior</button>}
            {numbered && pages.map((number) => link(number, String(number), page === number))}
            {page < last ? link(page + 1, 'Siguiente') : <button type="button" disabled aria-disabled="true">Siguiente</button>}
        </div>
        {onPageSizeChange && <label className="pagination-size">Por página
            <select value={pageSize} onChange={(event) => onPageSizeChange(Number(event.target.value))} aria-label="Elementos por página">
                {PAGE_SIZES.map((size) => <option key={size} value={size}>{size}</option>)}
            </select>
        </label>}
    </nav>;
}
