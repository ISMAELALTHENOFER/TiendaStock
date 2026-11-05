import { Pagination } from './components/ui/Pagination.jsx';
import { formatMoney as money } from './lib/formatMoney.js';

function PageHeader({ title, subtitle, edit, back }) {
    return <header className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div className="min-w-0"><p className="eyebrow">Inventario</p><h1 className="page-title mt-2 break-words">{title}</h1><p className="mt-1 text-sm text-muted">{subtitle}</p></div>
        <div className="flex flex-col gap-2 sm:flex-row"><a href={edit} className="inline-flex min-h-11 items-center justify-center rounded-control bg-primary px-4 text-sm font-semibold text-white hover:bg-primary-700">Editar</a><a href={back} className="inline-flex min-h-11 items-center justify-center rounded-control border border-border bg-surface px-4 text-sm font-semibold text-ink hover:bg-surface-muted">Volver</a></div>
    </header>;
}

function Field({ label, children }) {
    return <div className="min-w-0"><dt className="text-xs font-semibold uppercase tracking-wide text-muted">{label}</dt><dd className="mt-1 break-words text-base font-semibold text-ink">{children}</dd></div>;
}

export function ProductShow({ producto, routes, lowStockThreshold = 1 }) {
    if (!producto) return null;
    const stock = Number(producto.cantidad) || 0;
    const stockTone = stock === 0 ? 'bg-red-50 text-red-800' : stock <= lowStockThreshold ? 'bg-amber-50 text-amber-800' : 'bg-green-50 text-primary-700';
    return <div className="mx-auto max-w-5xl space-y-6">
        <PageHeader title={producto.nombre} subtitle="Detalles del producto" edit={routes.productoEdit} back={routes.productos} />
        <section className="overflow-hidden rounded-card border border-border bg-surface shadow-subtle" aria-labelledby="product-details-title">
            <div className="border-b border-border px-5 py-4 sm:px-7"><h2 id="product-details-title" className="text-lg font-bold text-ink">Información del producto</h2></div>
            <dl className="grid gap-5 p-5 sm:grid-cols-2 sm:p-7">
                <Field label="Categoría">{producto.categoria?.nombre}</Field>
                <Field label="Stock actual"><span className={`inline-flex rounded-full px-3 py-1 text-sm ${stockTone}`}>{stock === 0 ? 'Agotado' : stock <= lowStockThreshold ? `Bajo · ${stock}` : stock} unidades</span></Field>
                {producto.talle && <Field label="Talle">{producto.talle}</Field>}{producto.color && <Field label="Color">{producto.color}</Field>}
                {routes.canViewProductCost && <Field label="Costo">{money(producto.precio_compra)}</Field>}
                <Field label="Precio de venta">{money(producto.precio_venta)}</Field>
                {routes.canViewProductCost && <Field label="Ganancia por unidad">{money(producto.ganancia)}</Field>}
                {routes.productoImage && <div className="sm:col-span-2"><dt className="text-xs font-semibold uppercase tracking-wide text-muted">Imagen</dt><dd className="mt-2"><img src={routes.productoImage} alt={producto.nombre} className="h-48 w-48 max-w-full rounded-xl border border-border object-cover" /></dd></div>}
            </dl>
        </section>
        {producto.descripcion && <section className="rounded-card border border-border bg-surface p-5 shadow-subtle sm:p-7"><h2 className="text-lg font-bold text-ink">Descripción</h2><p className="mt-3 whitespace-pre-line break-words leading-7 text-ink">{producto.descripcion}</p></section>}
    </div>;
}

export function CategoryShow({ categoria, productos, routes, canViewProductCost, query = {} }) {
    if (!categoria || !productos) return null;
    const hrefForPage = (page) => { const url = new URL(routes.categoriaDetail, window.location.origin); const params = new URLSearchParams(window.location.search); params.set('page', String(page)); url.search = params.toString(); return url.toString(); };
    const imageLess = productos.data || [];
    return <div className="mx-auto max-w-6xl space-y-6">
        <PageHeader title={categoria.nombre} subtitle="Detalles de la categoría" edit={routes.categoriaEdit} back={routes.categorias} />
        <section className="rounded-card border border-border bg-surface shadow-subtle" aria-labelledby="category-details-title">
            <div className="border-b border-border px-5 py-4 sm:px-7"><h2 id="category-details-title" className="text-lg font-bold text-ink">Información de la categoría</h2></div>
            <dl className="grid gap-5 p-5 sm:grid-cols-2 sm:p-7"><Field label="Nombre">{categoria.nombre}</Field><Field label="Total de productos">{categoria.productos_count}</Field>{categoria.descripcion && <div className="sm:col-span-2"><Field label="Descripción">{categoria.descripcion}</Field></div>}</dl>
        </section>
        <section className="overflow-hidden rounded-card border border-border bg-surface shadow-subtle" aria-labelledby="category-products-title">
            <div className="flex flex-col gap-4 border-b border-border px-5 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-7"><div><h2 id="category-products-title" className="text-lg font-bold text-ink">Productos en esta categoría</h2><p className="mt-1 text-sm text-muted">{categoria.productos_count} {categoria.productos_count === 1 ? 'producto' : 'productos'} en total</p></div>
                <form method="get" action={routes.categoriaDetail} className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-end"><div className="min-w-0 sm:w-72"><label htmlFor="category-product-search" className="mb-1 block text-sm font-semibold text-ink">Buscar productos</label><input id="category-product-search" type="search" name="buscar" defaultValue={query.buscar || ''} placeholder="Nombre, color, talle o descripción" className="min-h-11 w-full rounded-control border border-border bg-canvas px-3 text-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary" /></div><input type="hidden" name="per_page" value={productos.per_page} /><button type="submit" className="inline-flex min-h-11 items-center justify-center rounded-control bg-primary px-5 text-sm font-semibold text-white hover:bg-primary-700">Buscar</button></form>
            </div>
            {imageLess.length ? <><div className="hidden overflow-x-auto md:block"><table className="w-full text-left text-sm"><thead className="bg-surface-muted text-xs text-muted"><tr><th scope="col" className="px-5 py-3">Nombre</th><th scope="col" className="px-5 py-3">Stock</th>{canViewProductCost && <th scope="col" className="px-5 py-3">Costo</th>}<th scope="col" className="px-5 py-3">Precio de venta</th><th scope="col" className="px-5 py-3"><span className="sr-only">Acción</span></th></tr></thead><tbody className="divide-y divide-border">{imageLess.map((producto) => <tr key={producto.id}><td className="px-5 py-4 font-semibold text-ink">{producto.nombre}</td><td className="px-5 py-4">{producto.cantidad}</td>{canViewProductCost && <td className="px-5 py-4">{money(producto.precio_compra)}</td>}<td className="px-5 py-4">{money(producto.precio_venta)}</td><td className="px-5 py-4 text-right"><a href={`${routes.productos}/${producto.id}`} className="inline-flex min-h-11 items-center rounded-control px-3 font-semibold text-primary">Ver</a></td></tr>)}</tbody></table></div>
                <div className="grid gap-3 p-4 md:hidden">{imageLess.map((producto) => <article key={producto.id} className="rounded-card border border-border p-4"><div className="flex flex-wrap items-start justify-between gap-3"><h3 className="min-w-0 break-words font-semibold text-ink">{producto.nombre}</h3><span className="shrink-0 rounded-full bg-surface-muted px-3 py-1 text-xs font-semibold">{producto.cantidad} u.</span></div><dl className="mt-4 grid grid-cols-2 gap-3 text-sm">{canViewProductCost && <Field label="Costo">{money(producto.precio_compra)}</Field>}<Field label="Precio de venta">{money(producto.precio_venta)}</Field></dl><a href={`${routes.productos}/${producto.id}`} className="mt-3 inline-flex min-h-11 items-center rounded-control px-3 text-sm font-semibold text-primary">Ver producto</a></article>)}</div></> : <div className="p-8 text-center"><h3 className="font-bold text-ink">{query.buscar ? 'No se encontraron productos' : 'Esta categoría aún no tiene productos'}</h3><p className="mt-2 text-sm text-muted">{query.buscar ? 'Prueba con otro nombre, color, talle o descripción.' : 'Los productos asignados a esta categoría aparecerán aquí.'}</p></div>}
            <Pagination currentPage={productos.current_page} lastPage={productos.last_page} pageSize={productos.per_page} summary={`Mostrando ${productos.from ?? 0}–${productos.to ?? 0} de ${productos.total} productos`} ariaLabel="Paginación de productos" hrefForPage={hrefForPage} />
        </section>
    </div>;
}
