const PAGE_SIZES = [15, 30, 50];

// First, last and the current page's neighbours, with gaps collapsed into an ellipsis.
export function pageItems(page, last) {
    if (last <= 7) return Array.from({ length: last }, (_, index) => index + 1);
    const pages = [...new Set([1, page - 1, page, page + 1, last])].filter((value) => value >= 1 && value <= last).sort((a, b) => a - b);
    return pages.flatMap((value, index) => (index > 0 && value - pages[index - 1] > 1 ? [`gap-${value}`, value] : [value]));
}

const Chevron = ({ direction }) => (
    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" className="h-4 w-4">
        <path fillRule="evenodd" d={direction === 'left' ? 'M12.79 5.23a.75.75 0 0 1-.02 1.06L8.83 10l3.94 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z' : 'M7.21 14.77a.75.75 0 0 1 .02-1.06L11.17 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z'} clipRule="evenodd" />
    </svg>
);

export function Pagination({ currentPage = 1, lastPage = 1, pageSize = 15, summary, hrefForPage, onPageChange, onPageSizeChange, ariaLabel = 'Paginación', numbered = true }) {
    const last = Math.max(1, Number(lastPage) || 1);
    const page = Math.min(last, Math.max(1, Number(currentPage) || 1));
    const navigate = (next) => onPageChange?.(Math.max(1, Math.min(last, next)));
    const link = (target, content, { current = false, label, className } = {}) => {
        const props = { key: label ?? target, 'aria-current': current ? 'page' : undefined, 'aria-label': label, className };
        return hrefForPage
            ? <a {...props} href={hrefForPage(target)}>{content}</a>
            : <button {...props} type="button" onClick={() => navigate(target)}>{content}</button>;
    };
    const step = (target, enabled, direction, text) => {
        const content = direction === 'left'
            ? <><Chevron direction="left" /><span className="pagination-step-text">{text}</span></>
            : <><span className="pagination-step-text">{text}</span><Chevron direction="right" /></>;
        return enabled
            ? link(target, content, { label: `${text} página`, className: 'pagination-step' })
            : <button key={text} type="button" className="pagination-step" disabled aria-label={`${text} página`}>{content}</button>;
    };

    return <nav className="pagination-controls" aria-label={ariaLabel}>
        {summary && <p className="pagination-summary" aria-live="polite">{summary}</p>}
        {last > 1 && <div className="pagination-actions">
            {step(page - 1, page > 1, 'left', 'Anterior')}
            {numbered
                ? pageItems(page, last).map((item) => (typeof item === 'number'
                    ? link(item, item, { current: item === page, label: `Página ${item}` })
                    : <span key={item} className="pagination-gap" aria-hidden="true">…</span>))
                : <span className="pagination-position">Página {page} de {last}</span>}
            {step(page + 1, page < last, 'right', 'Siguiente')}
        </div>}
        {onPageSizeChange && <label className="pagination-size">
            <span>Mostrar</span>
            <select value={pageSize} onChange={(event) => onPageSizeChange(Number(event.target.value))} aria-label="Elementos por página">
                {PAGE_SIZES.map((size) => <option key={size} value={size}>{size}</option>)}
            </select>
            <span className="hidden sm:inline">por página</span>
        </label>}
    </nav>;
}
