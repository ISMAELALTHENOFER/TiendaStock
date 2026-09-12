import { useState } from 'react';
import { Badge } from './components/ui/Badge.jsx';
import { Button } from './components/ui/Button.jsx';
import { Card } from './components/ui/Card.jsx';
import { EmptyState } from './components/ui/EmptyState.jsx';
import { ConfirmDialog } from './components/ui/ConfirmDialog.jsx';
import { csrf } from './lib/api.js';

/**
 * Categorías grid. Server-rendered pagination (the controller paginates 12
 * per page with productos_count), Ver/Editar links to the Blade detail/forms,
 * and Eliminar confirmed through the shared ConfirmDialog and submitted via
 * a native DELETE form — the server rule keeps refusing categories that
 * still have products.
 */
export function CategoriaGrid({ categorias, routes }) {
    const [eliminar, setEliminar] = useState(null);
    const data = categorias?.data || [];
    const { current_page: currentPage = 1, last_page: lastPage = 1 } = categorias || {};

    const confirmarEliminar = () => {
        if (!eliminar) return;
        document.getElementById(`eliminar-categoria-${eliminar.id}`)?.submit();
    };

    return (
        <div className="py-4 sm:py-8">
            <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 className="page-title">Categorías de Productos</h2>
                    <p className="mt-1 text-sm text-muted">Gestiona todas tus categorías de productos</p>
                </div>
                <a href={routes.categoriasCreate} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-control bg-primary px-5 text-sm font-semibold text-white hover:bg-primary-700">Nueva Categoría</a>
            </div>

            {data.length === 0 ? (
                <>
                    <EmptyState
                        title="No hay categorías creadas aún"
                        description="Las categorías agrupan tus productos para encontrarlos más rápido."
                    />
                    <div className="mt-4 text-center">
                        <a href={routes.categoriasCreate} className="inline-flex min-h-11 items-center justify-center rounded-control bg-primary px-5 text-sm font-semibold text-white hover:bg-primary-700">Crear Primera Categoría</a>
                    </div>
                </>
            ) : (
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {data.map((categoria) => (
                        <Card key={categoria.id} className="flex flex-col">
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <h3 className="font-bold text-ink">{categoria.nombre}</h3>
                                    <p className="mt-1 line-clamp-2 text-sm text-muted">{categoria.descripcion || 'Sin descripción'}</p>
                                </div>
                                 <Badge tone="neutral" className="shrink-0">{categoria.productos_count} {categoria.productos_count === 1 ? 'producto' : 'productos'}</Badge>
                            </div>
                            <div className="mt-4 flex items-center gap-2 border-t border-border pt-3">
                                <a href={`${routes.categorias}/${categoria.id}`} className="rounded-control px-3 py-2 text-sm font-semibold text-ink hover:bg-surface-muted">Ver</a>
                                <a href={`${routes.categorias}/${categoria.id}/edit`} className="rounded-control px-3 py-2 text-sm font-semibold text-primary hover:bg-surface-muted">Editar</a>
                                <button type="button" onClick={() => setEliminar(categoria)} className="ml-auto rounded-control px-3 py-2 text-sm font-semibold text-red-600 hover:bg-surface-muted">Eliminar</button>
                            </div>
                            <form id={`eliminar-categoria-${categoria.id}`} action={`${routes.categorias}/${categoria.id}`} method="POST" className="hidden">
                                <input type="hidden" name="_token" value={csrf()} />
                                <input type="hidden" name="_method" value="DELETE" />
                            </form>
                        </Card>
                    ))}
                </div>
            )}

            {data.length > 0 && (
                <div className="mt-6 flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <p className="text-sm text-muted">
                        Mostrando <strong className="text-ink">{categorias?.from ?? 0}</strong>–<strong className="text-ink">{categorias?.to ?? 0}</strong> de <strong className="text-ink">{categorias?.total ?? 0}</strong> {(categorias?.total ?? 0) === 1 ? 'categoría' : 'categorías'}
                    </p>
                    {lastPage > 1 && (
                        <div className="flex items-center gap-3">
                            {currentPage > 1 && (
                                <a href={`${routes.categorias}?page=${currentPage - 1}`} className="rounded-control border border-border px-3 py-2 text-sm font-semibold text-ink">Anterior</a>
                            )}
                            <span className="text-sm text-muted">Página <strong className="text-ink">{currentPage}</strong> de <strong className="text-ink">{lastPage}</strong></span>
                            {currentPage < lastPage && (
                                <a href={`${routes.categorias}?page=${currentPage + 1}`} className="rounded-control border border-border px-3 py-2 text-sm font-semibold text-ink">Siguiente</a>
                            )}
                        </div>
                    )}
                </div>
            )}

            <ConfirmDialog
                open={Boolean(eliminar)}
                title="Eliminar categoría"
                message={eliminar && eliminar.productos_count > 0
                    ? 'Esta categoría tiene productos. No se puede eliminar una categoría que tiene productos.'
                    : `¿Eliminar la categoría «${eliminar?.nombre}»? Esta acción no se puede deshacer.`}
                confirmLabel="Sí, eliminar"
                tone="danger"
                onConfirm={confirmarEliminar}
                onClose={() => setEliminar(null)}
            />
        </div>
    );
}
