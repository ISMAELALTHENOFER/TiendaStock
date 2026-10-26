import { useState } from 'react';
import { Badge } from './components/ui/Badge.jsx';
import { Button } from './components/ui/Button.jsx';
import { Card } from './components/ui/Card.jsx';
import { EmptyState } from './components/ui/EmptyState.jsx';
import { ConfirmDialog } from './components/ui/ConfirmDialog.jsx';
import { Modal } from './components/ui/Modal.jsx';
import { Pagination } from './components/ui/Pagination.jsx';
import { csrf } from './lib/api.js';
import { saveResponse } from './lib/saveResponse.js';
import { useLiveSearchResults } from './lib/useLiveSearchResults.js';

function CategoryForm({ categoria, routes, onClose }) {
    const isEdit = Boolean(categoria);
    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);
    const action = isEdit ? `${routes.categorias}/${categoria.id}` : routes.categorias;

    async function submit(event) {
        event.preventDefault();
        setSubmitting(true);
        setErrors({});
        const body = new FormData(event.currentTarget);
        if (isEdit) body.append('_method', 'PUT');
        const headers = { Accept: 'application/json' };
        const token = csrf();
        if (token) headers['X-CSRF-TOKEN'] = token;

        try {
            const response = await fetch(action, { method: 'POST', headers, body });
            const result = await saveResponse(response);
            window.location.assign(result.redirect);
        } catch (error) {
            setErrors(error.errors || { _general: [error.message || 'No se pudo guardar la categoría. Intentá nuevamente.'] });
            setSubmitting(false);
        }
    }

    const error = (field) => errors[field]?.[0];

    return (
        <Modal open title={isEdit ? 'Editar Categoría' : 'Nueva Categoría'} onClose={onClose} size="product">
            <form onSubmit={submit} className="p-4 sm:p-7">
                <p className="mb-6 text-sm text-muted">{isEdit ? 'Actualizá los datos de esta categoría.' : 'Añadí una clasificación para organizar tu inventario.'}</p>
                {error('_general') && <p role="alert" className="mb-4 rounded-control bg-red-50 p-3 text-sm text-red-700">{error('_general')}</p>}
                <div className="space-y-5">
                    <div>
                        <label htmlFor="categoria-nombre" className="mb-1.5 block text-sm font-semibold text-ink">Nombre de Categoría <span className="text-red-600">*</span></label>
                        <input id="categoria-nombre" name="nombre" type="text" required maxLength={255} defaultValue={categoria?.nombre ?? ''} placeholder="Ej: Ropa, Electrónica, Alimentos..." aria-invalid={Boolean(error('nombre'))} aria-describedby={error('nombre') ? 'categoria-nombre-error' : undefined} className="min-h-11 w-full rounded-control border border-border bg-surface px-3.5 text-sm text-ink placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" />
                        {error('nombre') && <p id="categoria-nombre-error" role="alert" className="mt-1 text-sm text-red-600">{error('nombre')}</p>}
                    </div>
                    <div>
                        <label htmlFor="categoria-descripcion" className="mb-1.5 block text-sm font-semibold text-ink">Descripción <span className="font-normal text-muted">(Opcional)</span></label>
                        <textarea id="categoria-descripcion" name="descripcion" rows="3" maxLength={500} defaultValue={categoria?.descripcion ?? ''} placeholder="Describe los productos que pertenecen a este grupo..." aria-invalid={Boolean(error('descripcion'))} aria-describedby={error('descripcion') ? 'categoria-descripcion-error' : undefined} className="w-full resize-y rounded-control border border-border bg-surface px-3.5 py-3 text-sm text-ink placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" />
                        {error('descripcion') && <p id="categoria-descripcion-error" role="alert" className="mt-1 text-sm text-red-600">{error('descripcion')}</p>}
                    </div>
                </div>
                <div className="mt-7 flex flex-col-reverse gap-3 border-t border-border pt-5 sm:flex-row sm:justify-end">
                    <Button type="button" variant="secondary" onClick={onClose} disabled={submitting}>Cancelar</Button>
                    <Button type="submit" disabled={submitting}>{submitting ? 'Guardando…' : isEdit ? 'Guardar Cambios' : 'Guardar Categoría'}</Button>
                </div>
            </form>
        </Modal>
    );
}

export function CategoriaGrid({ categorias, routes, initialModal = false }) {
    const [eliminar, setEliminar] = useState(null);
    const [modalCategoria, setModalCategoria] = useState(initialModal);
    const [buscar, setBuscar] = useState(new URLSearchParams(window.location.search).get('buscar') || '');
    const liveSearch = useLiveSearchResults(categorias, { query: buscar, route: routes.categorias, pageSize: Number(categorias?.per_page) || 15 });
    const page = liveSearch.results || categorias;
    const data = page?.data || [];
    const summary = categorias?.summary || { total: categorias?.total ?? 0, productos: 0, vacias: 0 };
    const currentPage = page?.current_page ?? 1;
    const lastPage = page?.last_page ?? 1;
    const pageSize = Number(page?.per_page) || 15;
    const paginationHref = (page, size = pageSize) => {
        const params = new URLSearchParams(window.location.search);
        params.set('page', String(page));
        params.set('per_page', String(size));
        return `${routes.categorias}?${params}`;
    };

    const openEdit = (event, categoria) => {
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        setModalCategoria(categoria);
    };

    const confirmarEliminar = () => {
        if (eliminar) document.getElementById(`eliminar-categoria-${eliminar.id}`)?.submit();
    };

    const closeModal = () => {
        if (initialModal) {
            window.location.assign(routes.categorias);
            return;
        }
        setModalCategoria(false);
    };

    return (
        <div className="py-4 sm:py-8">
            <div className="mb-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 className="page-title">Categorías de Productos</h2>
                    <p className="mt-1 text-sm text-muted">Gestiona todas tus categorías de productos</p>
                </div>
                <Button type="button" onClick={() => setModalCategoria(true)}>＋ <span>Nueva Categoría</span></Button>
            </div>

            <section aria-label="Resumen de categorías" className="mb-7 grid grid-cols-1 gap-4 sm:grid-cols-3">
                {[
                    ['Total Categorías', summary.total, '▦', 'text-primary'],
                    ['Productos Asignados', summary.productos, '▣', 'text-primary'],
                    ['Categorías Vacías', summary.vacias, '▱', 'text-muted'],
                ].map(([label, value, icon, tone]) => (
                    <Card key={label} className="flex min-h-24 items-center justify-between border border-border p-4 shadow-sm sm:p-5">
                        <div><p className="text-xs font-semibold uppercase tracking-wider text-muted">{label}</p><p className="mt-1 text-2xl font-bold tabular-nums text-ink">{value}</p></div>
                        <span aria-hidden="true" className={`flex h-11 w-11 items-center justify-center rounded-xl bg-surface-muted text-2xl ${tone}`}>{icon}</span>
                    </Card>
                ))}
            </section>

            <form method="get" action={routes.categorias} onSubmit={(event) => event.preventDefault()} className="mb-5 flex w-full flex-col gap-3 sm:flex-row">
                <label htmlFor="category-search" className="sr-only">Buscar categorías</label>
                <input id="category-search" name="buscar" value={buscar} onChange={(event) => setBuscar(event.target.value)} placeholder="Buscar categorías…" className="min-h-11 min-w-0 flex-1 rounded-control border border-border bg-surface px-4 text-sm text-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary" />
                <input type="hidden" name="per_page" value={pageSize} />
                <Button type="submit">Buscar</Button>
            </form>
            {liveSearch.searching && <p role="status" className="mb-3 text-sm text-muted">Buscando categorías…</p>}
            {liveSearch.searchError && <p role="alert" className="mb-3 text-sm text-red-700">No se pudo buscar. Modifica la búsqueda para reintentar.</p>}

            {data.length === 0 ? (
                <Card className="p-8 text-center sm:p-12">
                    <EmptyState title="No hay categorías creadas aún" description="Las categorías agrupan tus productos para encontrarlos más rápido." />
                    <Button type="button" className="mt-5" onClick={() => setModalCategoria(true)}>Crear Primera Categoría</Button>
                </Card>
            ) : (
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {data.map((categoria) => (
                        <Card key={categoria.id} className="flex min-w-0 flex-col justify-between border border-border p-5 shadow-sm transition-shadow hover:shadow-md sm:p-6">
                            <div>
                                <div className="flex min-w-0 items-start justify-between gap-3">
                                    <h3 className="min-w-0 break-words font-bold text-ink">{categoria.nombre}</h3>
                                    <Badge tone="neutral" className="shrink-0">{categoria.productos_count} {categoria.productos_count === 1 ? 'producto' : 'productos'}</Badge>
                                </div>
                                <p className="mt-2 line-clamp-2 min-h-10 break-words text-sm text-muted">{categoria.descripcion || 'Sin descripción'}</p>
                            </div>
                            <div className="mt-5 flex flex-wrap items-center gap-1 border-t border-border pt-3">
                                <a href={`${routes.categorias}/${categoria.id}`} className="inline-flex min-h-11 items-center rounded-control px-3 text-sm font-semibold text-ink hover:bg-surface-muted">Ver</a>
                                <a href={`${routes.categorias}/${categoria.id}/edit`} onClick={(event) => openEdit(event, categoria)} className="inline-flex min-h-11 items-center rounded-control px-3 text-sm font-semibold text-primary hover:bg-surface-muted">Editar</a>
                                {categoria.productos_count > 0 ? (
                                    <button type="button" disabled title="No se puede eliminar mientras tenga productos" className="ml-auto inline-flex min-h-11 cursor-not-allowed items-center rounded-control px-3 text-sm font-semibold text-muted opacity-70">No se puede eliminar</button>
                                ) : (
                                    <button type="button" onClick={() => setEliminar(categoria)} className="ml-auto inline-flex min-h-11 items-center rounded-control px-3 text-sm font-semibold text-red-600 hover:bg-red-50">Eliminar</button>
                                )}
                            </div>
                            <form id={`eliminar-categoria-${categoria.id}`} action={`${routes.categorias}/${categoria.id}`} method="POST" className="hidden">
                                <input type="hidden" name="_token" value={csrf()} />
                                <input type="hidden" name="_method" value="DELETE" />
                            </form>
                        </Card>
                    ))}
                </div>
            )}

                <Pagination currentPage={currentPage} lastPage={lastPage} pageSize={pageSize} ariaLabel="Paginación de categorías" summary={`Mostrando ${page?.from ?? 0}–${page?.to ?? 0} de ${page?.total ?? 0} categorías`} hrefForPage={(page) => paginationHref(page)} onPageSizeChange={(size) => window.location.assign(paginationHref(1, size))} />

            {modalCategoria !== false && <CategoryForm key={modalCategoria?.id ?? 'new'} categoria={modalCategoria === true ? null : modalCategoria} routes={routes} onClose={closeModal} />}
            <ConfirmDialog open={Boolean(eliminar)} title="Eliminar categoría" message={eliminar && eliminar.productos_count > 0 ? 'Esta categoría tiene productos. No se puede eliminar una categoría que tiene productos.' : `¿Eliminar la categoría «${eliminar?.nombre}»? Esta acción no se puede deshacer.`} confirmLabel="Sí, eliminar" tone="danger" onConfirm={confirmarEliminar} onClose={() => setEliminar(null)} />
        </div>
    );
}
