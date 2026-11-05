import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Badge } from './components/ui/Badge.jsx';
import { Button } from './components/ui/Button.jsx';
import { Card } from './components/ui/Card.jsx';
import { EmptyState } from './components/ui/EmptyState.jsx';
import { ErrorState } from './components/ui/ErrorState.jsx';
import { Input } from './components/ui/Input.jsx';
import { LoadingState } from './components/ui/LoadingState.jsx';
import { Modal } from './components/ui/Modal.jsx';
import { Select } from './components/ui/Select.jsx';
import { Pagination } from './components/ui/Pagination.jsx';
import { ConfirmDialog } from './components/ui/ConfirmDialog.jsx';
import { api, csrf } from './lib/api.js';
import { productMoneyInput } from './lib/productMoneyInput.js';
import { saveResponse } from './lib/saveResponse.js';
import { isInteractiveSearch } from './lib/interactiveSearch.js';
import { stockTone } from './lib/stockTone.js';
import { formatMoney } from './lib/formatMoney.js';

/**
 * Money input with the same live mask semantics as the legacy Alpine
 * moneyInput component: full-amount typing with thousands grouping and
 * caret preservation while typing (digitsLeft + setSelectionRange), an
 * empty display until the user types (raw=0 renders empty), and a blur
 * that canonicalizes to the "$1.234,56" format. The real value is carried
 * by a hidden sibling input with the same name so the backend receives a
 * plain numeric string.
 */
function MoneyInput({ label, name, initial = 0, error }) {
    const [raw, setRaw] = useState(Number(initial) || 0);
    const [display, setDisplay] = useState(Number(initial) > 0 ? formatMoney(initial) : '');

    const onInput = (event) => {
        const element = event.currentTarget;
        const next = productMoneyInput(element.value, element.selectionStart ?? element.value.length);
        if (!next) { element.value = display; return; }
        setRaw(next.raw);
        setDisplay(next.display);
        requestAnimationFrame(() => {
            element.setSelectionRange(next.caret, next.caret);
        });
    };

    const onBlur = () => {
        setDisplay(raw > 0 ? formatMoney(raw) : '');
    };

    return (
        <div>
            <label htmlFor={`${name}-field`} className="block text-sm font-medium text-ink">{label}</label>
            <Input id={`${name}-field`} type="text" inputMode="decimal" value={display} onInput={onInput} onBlur={onBlur} error={error} placeholder="0,00" className="mt-1" />
            <input type="hidden" name={name} value={raw} />
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}

/**
 * Image picker with client-side preview. On edit the current image is
 * preseeded; the legacy note about keeping the existing file is preserved.
 */
function ProductImage({ value, error }) {
    const [preview, setPreview] = useState(value || '');
    const previewUrl = useRef(null);
    useEffect(() => () => { if (previewUrl.current) URL.revokeObjectURL(previewUrl.current); }, []);

    const handleChange = (event) => {
        const file = event.target.files?.[0];
        if (file) {
            if (previewUrl.current) URL.revokeObjectURL(previewUrl.current);
            previewUrl.current = URL.createObjectURL(file);
            setPreview(previewUrl.current);
        }
    };

    return (
        <div>
            <label htmlFor="imagen-field" className="block text-sm font-medium text-ink">Imagen</label>
            <input
                id="imagen-field"
                type="file"
                name="imagen"
                accept="image/*"
                onChange={handleChange}
                aria-invalid={Boolean(error)}
                aria-describedby={error ? 'imagen-error' : undefined}
                className="mt-1 block w-full cursor-pointer text-sm text-muted file:mr-3 file:cursor-pointer file:rounded-control file:border-0 file:bg-surface-muted file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink"
            />
            {error && <p id="imagen-error" className="mt-1 text-sm text-red-600">{error}</p>}
            {preview && (
                <div className="mt-3 flex items-center gap-3">
                    <img src={preview} alt="Vista previa" width="80" height="80" className="h-20 w-20 rounded-lg border border-border object-cover" />
                    {value && <p className="text-xs text-muted">Imagen actual. Si no eliges un archivo nuevo, se conserva.</p>}
                </div>
            )}
        </div>
    );
}

/**
 * Shared create/edit form. Create posts directly to productos.store; edit
 * asks for confirmation (matching the legacy confirmDialogShow flow) and
 * then posts to productos.update. Error responses (422 JSON) keep the
 * typed state and render inline field errors instead of redirecting back.
 */
export function ProductForm({ routes, categorias = [], producto = null, modal = false, onClose, onSaved, onCategoryCreated }) {
    const isEdit = Boolean(producto);
    const [errors, setErrors] = useState({});
    const [categoryOptions, setCategoryOptions] = useState(categorias);
    const [categoryId, setCategoryId] = useState(String(producto?.categoria_id ?? ''));
    const [submitting, setSubmitting] = useState(false);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const [inlineOpen, setInlineOpen] = useState(false);
    const [inlineNombre, setInlineNombre] = useState('');
    const [inlineError, setInlineError] = useState('');
    const [inlineSubmitting, setInlineSubmitting] = useState(false);
    const [duplicado, setDuplicado] = useState(null);
    const lastCheckedName = useRef(null);
    const formRef = useRef(null);
    const closeInline = useCallback(() => setInlineOpen(false), []);

    const verificarDuplicado = async () => {
        if (isEdit) return;
        const nombre = formRef.current?.querySelector('[name="nombre"]')?.value.trim();
        if (!nombre || nombre.length < 2 || nombre === lastCheckedName.current) return;
        lastCheckedName.current = nombre;
        try {
            const dato = await api.get(`${routes.productoDuplicateCheck}?nombre=${encodeURIComponent(nombre)}`);
            if (dato.exists) setDuplicado(dato);
        } catch {
            // Advisory only: the database unique rule is the final guard.
        }
    };

    const crearCategoria = async (event) => {
        event.preventDefault();
        setInlineSubmitting(true);
        setInlineError('');
        try {
            const categoria = await api.post(routes.categoriasInline, { nombre: inlineNombre.trim() });
            setCategoryOptions((current) => [...current, categoria]);
            setCategoryId(String(categoria.id));
            onCategoryCreated?.(categoria);
            setInlineOpen(false);
            setInlineNombre('');
        } catch (err) {
            const message = err.status === 422 ? err.errors?.nombre?.[0] : null;
            setInlineError(message || 'No se pudo crear la categoría.');
        } finally {
            setInlineSubmitting(false);
        }
    };

    const submit = async () => {
        setSubmitting(true);
        setErrors({});
        const data = new FormData(formRef.current);
        if (isEdit) data.append('_method', 'PUT');
        const headers = { Accept: 'application/json' };
        const token = csrf();
        if (token) headers['X-CSRF-TOKEN'] = token;
        try {
            const response = await fetch(formRef.current.action, { method: 'POST', headers, body: data });
            const body = await saveResponse(response);
            setSubmitting(false);
            if (onSaved) {
                onSaved();
                return;
            }
            window.location.assign(body.redirect);
        } catch (error) {
            setSubmitting(false);
            setErrors(error.errors || { _general: [error.message || 'No se pudo guardar el producto. Intenta nuevamente.'] });
        }
    };

    const handleSubmit = (event) => {
        event.preventDefault();
        if (isEdit) {
            setConfirmOpen(true);
            return;
        }
        submit();
    };

    const action = isEdit ? `${routes.productos}/${producto.id}` : routes.productos;

    const formContent = (
        <div className={modal ? '' : 'py-4 sm:py-8'}>
            <div className={modal ? 'min-w-0 bg-surface' : 'min-w-0 overflow-hidden rounded-card border border-border bg-surface shadow-subtle'}>
                {modal ? (
                    <div className="flex items-start justify-between gap-4 bg-primary px-5 py-4 text-white sm:px-6">
                        <div>
                            <div className="flex items-center gap-2.5">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="h-6 w-6"><path d="M4 5h16v15H4zM8 9h8M8 13h5" /><path d="M9 2h6" /></svg>
                                <h2 className="text-lg font-bold tracking-tight">Información del Producto</h2>
                            </div>
                            <p className="mt-1 text-sm text-white/85">Ingresa todos los detalles del producto</p>
                        </div>
                        <button type="button" onClick={onClose} aria-label="Cerrar" className="inline-flex min-h-11 min-w-11 items-center justify-center rounded-control text-white/90 hover:bg-white/15 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="h-5 w-5"><path d="m6 6 12 12M18 6 6 18" /></svg>
                        </button>
                    </div>
                ) : (
                    <div className="bg-gradient-to-r from-primary to-primary-700 px-6 py-8">
                        <h1 className="text-lg font-bold text-white">Información del Producto</h1>
                        <p className="mt-1 text-sm text-white/80">Ingresa todos los detalles del producto</p>
                    </div>
                )}

                <form ref={formRef} action={action} method="POST" enctype="multipart/form-data" onSubmit={handleSubmit} noValidate className={modal ? 'space-y-4 p-4 sm:p-6' : 'space-y-6 p-4 sm:p-8'}>
                    {isEdit && <input type="hidden" name="stock_original" value={producto.cantidad} />}
                    {errors.stock_original && <div role="alert" className="rounded-card bg-red-50 p-3 text-sm text-red-700">{errors.stock_original[0]}</div>}
                    {errors._general && (
                        <div role="alert" className="rounded-card bg-red-50 p-3 text-sm text-red-700">{errors._general[0]}</div>
                    )}

                    <div>
                        <label htmlFor="nombre-field" className="block text-sm font-medium text-ink">Nombre <span className="text-red-600">*</span></label>
                        <Input id="nombre-field" name="nombre" required defaultValue={producto?.nombre ?? ''} placeholder="Ej: Remera básica" error={errors?.nombre?.[0]} onBlur={verificarDuplicado} className="mt-1" />
                        {errors?.nombre && <p className="mt-1 text-sm text-red-600">{errors.nombre[0]}</p>}
                    </div>

                    <div>
                        <label htmlFor="categoria-field" className="block text-sm font-medium text-ink">Categoría <span className="text-red-600">*</span></label>
                        <div className="mt-1 flex flex-wrap gap-2">
                            <Select id="categoria-field" name="categoria_id" required value={categoryId} onChange={(event) => setCategoryId(event.target.value)} className="min-w-0 flex-1 basis-40">
                                <option value="">Selecciona una categoría</option>
                                {categoryOptions.map((cat) => <option key={cat.id} value={cat.id}>{cat.nombre}</option>)}
                            </Select>
                            <Button type="button" variant="secondary" title="Crear categoría nueva" aria-label="Crear categoría nueva" onClick={() => setInlineOpen(true)}>+ Nueva</Button>
                        </div>
                        {errors?.categoria_id && <p className="mt-1 text-sm text-red-600">{errors.categoria_id[0]}</p>}
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label htmlFor="cantidad-field" className="block text-sm font-medium text-ink">Cantidad</label>
                            <Input id="cantidad-field" type="number" name="cantidad" min="0" defaultValue={producto?.cantidad ?? 1} error={errors?.cantidad?.[0]} className="mt-1" />
                        </div>
                        {routes.canViewProductCost ? <MoneyInput label="Costo" name="precio_compra" initial={producto?.precio_compra ?? 0} error={errors?.precio_compra?.[0]} /> : <input type="hidden" name="precio_compra" value="0" />}
                        <MoneyInput label="Precio de Venta" name="precio_venta" initial={producto?.precio_venta ?? 0} error={errors?.precio_venta?.[0]} />
                        <div>
                            <label htmlFor="talle-field" className="block text-sm font-medium text-ink">Talle</label>
                            <Input id="talle-field" name="talle" defaultValue={producto?.talle ?? ''} placeholder="Ej: Talle 1, Talle 4..." error={errors?.talle?.[0]} className="mt-1" />
                        </div>
                        <div>
                            <label htmlFor="color-field" className="block text-sm font-medium text-ink">Color</label>
                            <Input id="color-field" name="color" defaultValue={producto?.color ?? ''} placeholder="Ej: Azul" error={errors?.color?.[0]} className="mt-1" />
                        </div>
                    </div>

                    <div>
                        <label htmlFor="descripcion-field" className="block text-sm font-medium text-ink">Descripción</label>
                        <textarea id="descripcion-field" name="descripcion" rows="3" defaultValue={producto?.descripcion ?? ''} placeholder="Detalles opcionales (material, cuidados, etc.)" className="mt-1 block w-full rounded-control border border-border bg-surface px-3 py-2 text-ink placeholder:text-muted focus:border-primary focus:outline-none" />
                    </div>

                    <div><ProductImage value={producto?.imagen || ''} error={errors?.imagen?.[0]} /></div>

                    <div className="flex flex-col-reverse gap-3 border-t border-border pt-6 sm:flex-row sm:justify-end">
                        {modal ? (
                            <Button type="button" variant="secondary" onClick={onClose}>Cancelar</Button>
                        ) : (
                            <a href={routes.productos} className="inline-flex min-h-11 items-center justify-center rounded-control border border-border px-4 text-sm font-semibold text-ink">Cancelar</a>
                        )}
                        <Button type="submit" disabled={submitting}>{submitting ? 'Guardando...' : (isEdit ? 'Guardar Cambios' : 'Guardar Producto')}</Button>
                    </div>
                </form>
            </div>

            <Modal open={inlineOpen} title="Nueva Categoría" onClose={closeInline}>
                <form onSubmit={crearCategoria} className="mt-4 space-y-4">
                    <div>
                        <label htmlFor="inline-nombre-field" className="block text-sm font-medium text-ink">Nombre</label>
                        <Input id="inline-nombre-field" value={inlineNombre} onChange={(event) => setInlineNombre(event.target.value)} placeholder="Ej: Accesorios" required className="mt-1" />
                    </div>
                    {inlineError && <p className="text-sm text-red-600">{inlineError}</p>}
                    <div className="flex justify-end gap-3">
                        <Button type="button" variant="secondary" onClick={() => setInlineOpen(false)}>Cancelar</Button>
                        <Button type="submit" disabled={inlineSubmitting}>{inlineSubmitting ? 'Creando...' : 'Crear Categoría'}</Button>
                    </div>
                </form>
            </Modal>

            <ConfirmDialog
                open={Boolean(duplicado)}
                title="Producto existente"
                message={`Ya existe un producto llamado «${duplicado?.nombre}». ¿Quieres editarlo?`}
                confirmLabel="Sí, editar"
                onConfirm={() => window.location.assign(`${routes.productos}/${duplicado.id}/edit`)}
                onClose={() => setDuplicado(null)}
            />

            <ConfirmDialog
                open={confirmOpen}
                title="Editar producto"
                message="¿Confirmar los cambios en este producto?"
                confirmLabel="Sí, guardar"
                tone="primary"
                onConfirm={submit}
                onClose={() => setConfirmOpen(false)}
            />
        </div>
    );
    return modal ? (
        <Modal open title="Información del Producto" onClose={onClose} size="product" hideHeader>
            {formContent}
        </Modal>
    ) : formContent;
}

function acciones(producto, routes, setToggle, setEditingProducto, setProductoViendo) {
    const nombre = producto.nombre;
    return (
        <div className="data-table-actions">
            <a href={`${routes.productos}/${producto.id}`} onClick={(event) => { if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return; event.preventDefault(); setProductoViendo(producto); }} className="data-table-action text-ink hover:bg-surface-muted" aria-label={`Ver ${nombre}`}>Ver</a>
            <a href={`${routes.productos}/${producto.id}/edit`} onClick={(event) => { if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return; event.preventDefault(); setEditingProducto(producto); }} className="data-table-action text-primary hover:bg-surface-muted" aria-label={`Editar ${nombre}`}>Editar</a>
            {producto.activo ? (
                <button type="button" onClick={() => setToggle({ tipo: 'desactivar', producto })} className="data-table-action text-red-600 hover:bg-surface-muted" aria-label={`Desactivar ${nombre}`}>Desactivar</button>
            ) : (
                <button type="button" onClick={() => setToggle({ tipo: 'activar', producto })} className="data-table-action text-green-700 hover:bg-surface-muted" aria-label={`Activar ${nombre}`}>Activar</button>
            )}
        </div>
    );
}

/**
 * Inventory catalog. Data comes from the no-store /productos/data endpoint
 * (bare JSON array, eager-loaded categoria); search, filters and the 15-row
 * client-side pagination happen in the browser. Inactive products are only
 * included when "Ver inactivos" is on. Activation/deactivation stays
 * server-driven via hidden native forms confirmed with the shared dialog.
 */
export function ProductList({ categorias, routes, lowStockThreshold = 1 }) {
    const params = new URLSearchParams(window.location.search);
    const [verInactivos, setVerInactivos] = useState(params.get('inactivos') === '1');
    const [busqueda, setBusqueda] = useState(params.get('buscar') || '');
    const [filtroCategoria, setFiltroCategoria] = useState(params.get('categoria_id') || '');
    const [filtroTalle, setFiltroTalle] = useState(params.get('talle') || '');
    const [filtroColor, setFiltroColor] = useState(params.get('color') || '');
    const [catalogo, setCatalogo] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [error, setError] = useState(false);
    const [paginaActual, setPaginaActual] = useState(Number(params.get('page')) || 1);
    const [pageSize, setPageSize] = useState([15, 30, 50].includes(Number(params.get('per_page'))) ? Number(params.get('per_page')) : 15);
    const [reload, setReload] = useState(0);
    const [toggle, setToggle] = useState(null);
    const [activationError, setActivationError] = useState('');
    const [activating, setActivating] = useState(false);
    const activationDialog = useRef(null);
    const [crearAbierto, setCrearAbierto] = useState(false);
    const [categoryOptions, setCategoryOptions] = useState(categorias);
    const [productoEditando, setProductoEditando] = useState(null);
    const [productoViendo, setProductoViendo] = useState(null);
    const searchRef = useRef(null);

    useEffect(() => {
        const controller = new AbortController();
        const params = new URLSearchParams({ per_page: String(pageSize), page: String(paginaActual) });
        if (verInactivos) params.set('inactivos', '1');
        if (isInteractiveSearch(busqueda)) params.set('buscar', busqueda.trim());
        if (filtroCategoria) params.set('categoria_id', filtroCategoria);
        if (filtroTalle) params.set('talle', filtroTalle);
        if (filtroColor) params.set('color', filtroColor);
        const timeout = window.setTimeout(async () => {
            setCargando(true);
            setError(false);
            try {
                const response = await fetch(`${routes.productosData}?${params}`, {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error('carga');
                setCatalogo(await response.json());
            } catch (requestError) {
                if (requestError.name !== 'AbortError') setError(true);
            } finally {
                if (!controller.signal.aborted) setCargando(false);
            }
        }, busqueda ? 250 : 0);
        return () => { window.clearTimeout(timeout); controller.abort(); };
    }, [verInactivos, busqueda, filtroCategoria, filtroTalle, filtroColor, paginaActual, pageSize, reload, routes.productosData]);
    useEffect(() => {
        if (toggle?.tipo === 'activar') activationDialog.current?.showModal();
    }, [toggle]);
    const visibles = catalogo?.data || [];
    const total = catalogo?.total || 0;
    const talies = catalogo?.filters?.talles || [];
    const colores = catalogo?.filters?.colores || [];
    const totalPaginas = catalogo?.last_page || 1;
    const paginaSegura = catalogo?.current_page || 1;
    const desde = catalogo?.from || 0;
    const hasta = catalogo?.to || 0;
    const hayFiltros = Boolean(busqueda || filtroCategoria || filtroTalle || filtroColor);
    const cargar = () => { setPaginaActual(1); setReload((value) => value + 1); };
    const cambiarFiltro = (setter) => (value) => { setter(value); setPaginaActual(1); };

    const limpiarFiltros = () => {
        setBusqueda('');
        setFiltroCategoria('');
        setFiltroTalle('');
        setFiltroColor('');
        setPaginaActual(1);
        searchRef.current?.focus();
    };

    const confirmarToggle = () => {
        if (!toggle) return;
        const id = toggle.producto.id;
        const formId = toggle.tipo === 'activar' ? `activar-${id}` : `desactivar-${id}`;
        document.getElementById(formId)?.submit();
    };

    const activar = async (event) => {
        event.preventDefault();
        setActivating(true);
        setActivationError('');
        try {
            const response = await fetch(`${routes.productos}/${toggle.producto.id}/activate`, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: new FormData(event.currentTarget),
            });
            const body = await response.json();
            if (!response.ok) throw new Error(body.errors?.cantidad?.[0] || body.message || 'No se pudo activar el producto.');
            activationDialog.current.close();
            setToggle(null);
            await cargar();
        } catch (error) {
            setActivationError(error.message);
        } finally {
            setActivating(false);
        }
    };

    return (
        <div className="py-4 sm:py-8">
            <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="page-title">Inventario de Productos</h1>
                    <p className="mt-1 text-sm text-muted">Gestiona todos tus productos en un solo lugar</p>
                </div>
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label htmlFor="show-inactive-products" className="flex min-h-11 cursor-pointer items-center gap-2 text-sm font-medium text-ink">
                        <input id="show-inactive-products" type="checkbox" checked={verInactivos} onChange={(event) => { setVerInactivos(event.target.checked); setPaginaActual(1); }} className="h-4 w-4 rounded border-border accent-primary" />
                        Ver inactivos
                    </label>
                    <a href={routes.productosCreate} onClick={(event) => { if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return; event.preventDefault(); setCrearAbierto(true); }} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-control bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-primary-700 focus-visible:outline-offset-4">
                        Nuevo Producto
                    </a>
                </div>
            </div>

            <div className="mb-6 flex flex-col gap-3 rounded-card border border-border bg-surface p-4 shadow-subtle lg:flex-row lg:items-center">
                <div className="min-w-0 flex-1">
                    <Input
                        ref={searchRef}
                        value={busqueda}
                        onChange={(event) => { setBusqueda(event.target.value); setPaginaActual(1); }}
                        placeholder="Buscar por nombre, categoría, talle o color..."
                        aria-label="Buscar por nombre, categoría, talle o color"
                    />
                </div>
                <div className="grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-3 lg:flex-[2]">
                    <label className="text-xs font-semibold text-muted">Categoría<Select aria-label="Filtrar por categoría" value={filtroCategoria} onChange={(event) => cambiarFiltro(setFiltroCategoria)(event.target.value)} className="mt-1 text-sm font-normal">
                        <option value="">Todas las categorías</option>
                        {categoryOptions.map((cat) => <option key={cat.id} value={cat.id}>{cat.nombre}</option>)}
                    </Select></label>
                    <label className="text-xs font-semibold text-muted">Talle<Select aria-label="Filtrar por talle" value={filtroTalle} onChange={(event) => cambiarFiltro(setFiltroTalle)(event.target.value)} className="mt-1 text-sm font-normal">
                        <option value="">Todos los talles</option>
                        {talies.map((talle) => <option key={talle} value={talle}>{talle}</option>)}
                    </Select></label>
                    <label className="text-xs font-semibold text-muted">Color<Select aria-label="Filtrar por color" value={filtroColor} onChange={(event) => cambiarFiltro(setFiltroColor)(event.target.value)} className="mt-1 text-sm font-normal">
                        <option value="">Todos los colores</option>
                        {colores.map((color) => <option key={color} value={color}>{color}</option>)}
                    </Select></label>
                </div>
                {hayFiltros && <Button variant="secondary" onClick={limpiarFiltros}>Limpiar filtros</Button>}
            </div>

            {cargando ? (
                <LoadingState label="Cargando catálogo..." />
            ) : error ? (
                <ErrorState title="No se pudo cargar el catálogo de productos." description="Revisa tu conexión e intenta nuevamente." onRetry={() => cargar()} />
            ) : total === 0 ? (
                <EmptyState
                    title="No se encontraron productos"
                    description="Prueba con otros términos de búsqueda"
                />
            ) : (
                <div className="space-y-6">
                    <Card className="hidden overflow-x-auto p-0 sm:p-0 xl:block">
                         <table className="data-table w-full min-w-[960px] text-left text-sm">
                            <thead>
                                 <tr className="border-b border-border text-xs uppercase tracking-wide text-white">
                                    <th className="px-4 py-3">Imagen</th>
                                    <th className="px-4 py-3">Nombre</th>
                                    <th className="px-4 py-3">Categoría</th>
                                    <th className="px-4 py-3">Talle</th>
                                    <th className="px-4 py-3">Color</th>
                                    <th className="px-4 py-3 text-right">P. Compra</th>
                                    <th className="px-4 py-3 text-right">P. Venta</th>
                                    <th className="px-4 py-3 text-right">Ganancia</th>
                                    <th className="px-4 py-3 text-right">Stock</th>
                                    <th className="px-4 py-3 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {visibles.map((producto) => (
                                    <tr key={producto.id} className="border-b border-border last:border-0 hover:bg-surface-muted/50">
                                        <td className="px-4 py-3">
                                            {producto.imagen
                                                ? <img src={producto.imagen} alt={producto.nombre} width="48" height="48" loading="lazy" className="h-12 w-12 rounded-lg object-cover" />
                                                : <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-surface-muted text-xs text-muted">—</div>}
                                        </td>
                                        <td className="px-4 py-3 font-semibold text-ink">
                                            {producto.nombre}
                                            {!producto.activo && <Badge tone="neutral" className="ml-2">Inactivo</Badge>}
                                        </td>
                                        <td className="px-4 py-3"><Badge tone="neutral">{producto.categoria?.nombre ?? '—'}</Badge></td>
                                        <td className="px-4 py-3 text-muted">{producto.talle || '—'}</td>
                                        <td className="px-4 py-3 text-muted">{producto.color || '—'}</td>
                                        {routes.canViewProductCost && <td className="px-4 py-3 text-right">{formatMoney(producto.precio_compra)}</td>}
                                        <td className="px-4 py-3 text-right">{formatMoney(producto.precio_venta)}</td>
                                         {routes.canViewProductCost && <td className={`px-4 py-3 text-right font-medium ${producto.precio_venta - producto.precio_compra < 0 ? 'text-red-700' : 'text-green-700'}`}>{formatMoney(producto.precio_venta - producto.precio_compra)}</td>}
                                         <td className="px-4 py-3 text-right tabular-nums">
                                             <Badge tone={stockTone(producto.cantidad, lowStockThreshold)}>{producto.cantidad === 0 ? 'Sin stock' : producto.cantidad <= lowStockThreshold ? `${producto.cantidad} · Bajo` : producto.cantidad}</Badge>
                                         </td>
                                          <td className="px-4 py-3">{acciones(producto, routes, setToggle, setProductoEditando, setProductoViendo)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </Card>

                    <div className="space-y-4 xl:hidden">
                        {visibles.map((producto) => (
                            <Card key={producto.id} className="min-w-0">
                                <div className="flex items-start gap-3">
                                    {producto.imagen
                                        ? <img src={producto.imagen} alt={producto.nombre} width="56" height="56" loading="lazy" className="h-14 w-14 rounded-lg object-cover" />
                                        : <div className="flex h-14 w-14 items-center justify-center rounded-lg bg-surface-muted text-xs text-muted">—</div>}
                                    <div className="min-w-0">
                                        <p className="font-bold text-ink">{producto.nombre}</p>
                                        <p className="mt-0.5 text-xs text-muted">{producto.categoria?.nombre}</p>
                                    </div>
                                    <div className="ml-auto shrink-0">
                                         <Badge tone={stockTone(producto.cantidad, lowStockThreshold)}>{producto.cantidad === 0 ? 'Sin stock' : producto.cantidad <= lowStockThreshold ? `${producto.cantidad} · Bajo` : producto.cantidad}</Badge>
                                    </div>
                                </div>
                                <dl className="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                     <div className="data-card-field">
                                        <dt className="text-xs uppercase tracking-wide text-muted">Talle</dt>
                                        <dd className="mt-0.5 font-medium text-ink">{producto.talle || '—'}</dd>
                                     </div>
                                      <div className="data-card-field">
                                        <dt className="text-xs uppercase tracking-wide text-muted">Color</dt>
                                        <dd className="mt-0.5 font-medium text-ink">{producto.color || '—'}</dd>
                                      </div>
                                      {routes.canViewProductCost && <div className="data-card-field">
                                        <dt className="text-xs uppercase tracking-wide text-muted">P. Compra</dt>
                                        <dd className="mt-0.5 font-medium text-ink">{formatMoney(producto.precio_compra)}</dd>
                                    </div>}
                                     <div className="data-card-field">
                                        <dt className="text-xs uppercase tracking-wide text-muted">P. Venta</dt>
                                        <dd className="mt-0.5 font-medium text-ink">{formatMoney(producto.precio_venta)}</dd>
                                    </div>
                                      {routes.canViewProductCost && <div className="data-card-field">
                                         <dt className="text-xs uppercase tracking-wide text-muted">Ganancia</dt>
                                          <dd className={`mt-0.5 font-medium ${producto.precio_venta - producto.precio_compra < 0 ? 'text-red-700' : 'text-green-700'}`}>{formatMoney(producto.precio_venta - producto.precio_compra)}</dd>
                                     </div>}
                                     <div className="data-card-field">
                                        <dt className="text-xs uppercase tracking-wide text-muted">Stock</dt>
                                        <dd className="mt-0.5 font-medium text-ink">
                                             {producto.cantidad === 0 ? 'Sin stock' : producto.cantidad <= lowStockThreshold ? `${producto.cantidad} · Bajo` : producto.cantidad}
                                            {!producto.activo && <Badge tone="neutral" className="ml-2">Inactivo</Badge>}
                                        </dd>
                                    </div>
                                </dl>
                                <div className="mt-4 border-t border-border pt-3">{acciones(producto, routes, setToggle, setProductoEditando, setProductoViendo)}</div>
                            </Card>
                        ))}
                    </div>

                    <Pagination currentPage={paginaSegura} lastPage={totalPaginas} pageSize={pageSize} ariaLabel="Paginación de productos" summary={`Mostrando ${desde}–${hasta} de ${total} productos`} onPageChange={setPaginaActual} onPageSizeChange={(size) => { setPageSize(size); setPaginaActual(1); }} />
                </div>
            )}

            {total === 0 && hayFiltros && !cargando && !error && (
                <div className="mt-4 text-center">
                    <Button variant="secondary" onClick={limpiarFiltros}>Ver todos los productos</Button>
                </div>
            )}

            {visibles.map((producto) => (
                <div key={producto.id} className="hidden">
                    <form id={`desactivar-${producto.id}`} action={`/productos/${producto.id}`} method="POST">
                        <input type="hidden" name="_token" value={csrf()} />
                        <input type="hidden" name="_method" value="DELETE" />
                        <input type="hidden" name="stock_original" value={producto.cantidad} />
                    </form>
                </div>
            ))}

            <ConfirmDialog
                open={toggle?.tipo === 'desactivar'}
                title={toggle?.tipo === 'activar' ? 'Activar producto' : 'Desactivar producto'}
                 message={`«${toggle?.producto?.nombre}» se desactivará y su stock pasará a cero. No se registrará una venta.`}
                confirmLabel={toggle?.tipo === 'activar' ? 'Sí, activar' : 'Sí, desactivar'}
                tone={toggle?.tipo === 'activar' ? 'primary' : 'danger'}
                onConfirm={confirmarToggle}
                onClose={() => setToggle(null)}
            />

            <dialog ref={activationDialog} aria-labelledby="activation-title" onCancel={(event) => { if (activating) event.preventDefault(); }} onClose={() => { setToggle(null); setActivationError(''); }} className="w-[calc(100%-2rem)] max-w-md rounded-card p-5 shadow-subtle backdrop:bg-black/50">
                <form onSubmit={activar} className="space-y-4">
                    <h2 id="activation-title" className="text-lg font-bold">Activar producto</h2>
                    <p>Ingresa el stock de «{toggle?.producto?.nombre}» y confirma la activación.</p>
                    <label htmlFor="activation-stock" className="block font-medium">Stock</label>
                    <input id="activation-stock" name="cantidad" type="number" min="1" max="2147483647" step="1" required autoFocus aria-invalid={Boolean(activationError)} aria-describedby={activationError ? 'activation-error' : undefined} className="w-full rounded-control border border-border px-3 py-2" />
                    <input type="hidden" name="_method" value="PATCH" />
                    {activationError && <p id="activation-error" role="alert" className="text-sm text-red-700">{activationError}</p>}
                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <Button type="button" variant="secondary" disabled={activating} onClick={() => activationDialog.current.close()}>Cancelar</Button>
                        <Button type="submit" disabled={activating}>Sí, activar</Button>
                    </div>
                </form>
            </dialog>

            {productoViendo && <Modal open title="Detalles del producto" onClose={() => setProductoViendo(null)} size="product">
                <div className="space-y-6 p-4 sm:p-7">
                    <div className="flex min-w-0 flex-col gap-4 border-b border-border pb-5 sm:flex-row sm:items-center">
                        {productoViendo.imagen && <img src={productoViendo.imagen} alt={productoViendo.nombre} width="80" height="80" loading="lazy" className="h-20 w-20 shrink-0 rounded-card border border-border object-cover" />}
                        <div className="min-w-0 flex-1">
                            <p className="text-sm font-semibold text-primary">{productoViendo.categoria?.nombre || 'Sin categoría'}</p>
                            <h3 className="mt-1 break-words text-2xl font-bold tracking-tight text-ink">{productoViendo.nombre}</h3>
                        </div>
                         <Badge tone={stockTone(productoViendo.cantidad, lowStockThreshold)}>
                             {productoViendo.cantidad === 0 ? 'Sin stock' : productoViendo.cantidad <= lowStockThreshold ? `${productoViendo.cantidad} · Bajo` : productoViendo.cantidad} {productoViendo.cantidad === 1 ? 'unidad' : 'unidades'}
                        </Badge>
                    </div>
                    <dl className="grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                        {[
                            ['Talle', productoViendo.talle || '—'],
                            ['Color', productoViendo.color || '—'],
                        ].map(([label, value]) => <div key={label} className="min-w-0"><dt className="text-xs font-semibold uppercase tracking-wide text-muted">{label}</dt><dd className="mt-1 break-words font-medium text-ink">{value}</dd></div>)}
                    </dl>
                    <dl className={`grid grid-cols-1 divide-y divide-border border-y border-border ${routes.canViewProductCost ? 'sm:grid-cols-3 sm:divide-x sm:divide-y-0' : 'sm:grid-cols-1'}`}>
                        {[
                            ...(routes.canViewProductCost ? [['Costo', productoViendo.precio_compra]] : []),
                            ['Precio de venta', productoViendo.precio_venta],
                            ...(routes.canViewProductCost ? [['Ganancia por unidad', productoViendo.precio_venta - productoViendo.precio_compra]] : []),
                        ].map(([label, value]) => <div key={label} className="min-w-0 py-3 sm:px-3 first:sm:pl-0 last:sm:pr-0"><dt className="text-xs font-semibold uppercase tracking-wide text-muted">{label}</dt><dd className="mt-1 break-words text-base font-bold tabular-nums text-ink">{formatMoney(value)}</dd></div>)}
                    </dl>
                    {productoViendo.descripcion && <section aria-label="Descripción del producto" className="space-y-1"><h4 className="text-xs font-semibold uppercase tracking-wide text-muted">Descripción</h4><p className="whitespace-pre-line break-words text-sm leading-6 text-ink">{productoViendo.descripcion}</p></section>}
                </div>
            </Modal>}

            {(crearAbierto || productoEditando) && (
                <ProductForm
                    key={productoEditando?.id ?? 'new-product'}
                    routes={routes}
                    categorias={categorias}
                    onCategoryCreated={(categoria) => setCategoryOptions((current) => [...current, categoria])}
                    producto={productoEditando}
                    modal
                    onClose={() => { setCrearAbierto(false); setProductoEditando(null); }}
                    onSaved={async () => {
                        setCrearAbierto(false);
                        setProductoEditando(null);
                        await cargar();
                    }}
                />
            )}
        </div>
    );
}
