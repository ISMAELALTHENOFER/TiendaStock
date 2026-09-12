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
import { ConfirmDialog } from './components/ui/ConfirmDialog.jsx';
import { api, csrf } from './lib/api.js';

const formatter = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });

export function formatMoney(value) {
    return formatter.format(value);
}

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
        const start = element.selectionStart;
        const digits = event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
        const rawValue = digits ? Number(digits) : 0;
        const formatted = rawValue > 0 ? formatMoney(rawValue) : '';
        const digitsLeft = digits.length - (formatted.length - start);
        setRaw(rawValue);
        setDisplay(formatted);
        requestAnimationFrame(() => {
            element.setSelectionRange(Math.max(0, formatted.length - digitsLeft), Math.max(0, formatted.length - digitsLeft));
        });
    };

    const onBlur = () => {
        if (raw > 0) setDisplay(formatMoney(raw));
    };

    return (
        <div>
            <label className="block text-sm font-medium text-ink">{label}</label>
            <Input type="text" inputMode="decimal" value={display} onInput={onInput} onBlur={onBlur} error={error} placeholder="0,00" className="mt-1" />
            <input type="hidden" name={name} value={raw} />
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}

/**
 * Image picker with client-side preview. On edit the current image is
 * preseeded; the legacy note about keeping the existing file is preserved.
 */
function ProductImage({ value }) {
    const [preview, setPreview] = useState(value || '');

    const handleChange = (event) => {
        const file = event.target.files?.[0];
        if (file) setPreview(URL.createObjectURL(file));
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
                className="mt-1 block w-full text-sm text-muted file:mr-3 file:rounded-control file:border-0 file:bg-surface-muted file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink"
            />
            {preview && (
                <div className="mt-3 flex items-center gap-3">
                    <img src={preview} alt="Vista previa" className="h-20 w-20 rounded-lg border border-border object-cover" />
                    {value && <p className="text-xs text-muted">Imagen actual. Si no elegís un archivo nuevo, se conserva.</p>}
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
export function ProductForm({ routes, categorias = [], producto = null }) {
    const isEdit = Boolean(producto);
    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const [inlineOpen, setInlineOpen] = useState(false);
    const [inlineNombre, setInlineNombre] = useState('');
    const [inlineError, setInlineError] = useState('');
    const [inlineSubmitting, setInlineSubmitting] = useState(false);
    const [duplicado, setDuplicado] = useState(null);
    const lastCheckedName = useRef(null);
    const formRef = useRef(null);

    const verificarDuplicado = async () => {
        if (isEdit) return;
        const nombre = formRef.current?.querySelector('[name="nombre"]')?.value.trim();
        if (!nombre || nombre.length < 2 || nombre === lastCheckedName.current) return;
        lastCheckedName.current = nombre;
        try {
            const dato = await api.get(`/productos/check-duplicate?nombre=${encodeURIComponent(nombre)}`);
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
            const categoria = await api.post('/categorias/inline', { nombre: inlineNombre.trim() });
            const select = formRef.current?.querySelector('[name="categoria_id"]');
            if (select) {
                const option = document.createElement('option');
                option.value = String(categoria.id);
                option.selected = true;
                option.textContent = categoria.nombre;
                select.appendChild(option);
            }
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
        const data = new FormData(formRef.current);
        if (isEdit) data.append('_method', 'PUT');
        const headers = { Accept: 'application/json' };
        const token = csrf();
        if (token) headers['X-CSRF-TOKEN'] = token;
        try {
            const response = await fetch(formRef.current.action, { method: 'POST', headers, body: data });
            if (response.redirected) {
                window.location.assign(response.url);
                return;
            }
            const contentType = response.headers.get('content-type') || '';
            const body = contentType.includes('application/json') ? await response.json() : null;
            if (!response.ok) {
                setSubmitting(false);
                setErrors(body?.errors || {});
                return;
            }
            setSubmitting(false);
            window.location.assign(body?.redirect || routes.productos);
        } catch {
            setSubmitting(false);
            setErrors({ _general: ['No se pudo guardar el producto. Reintentá.'] });
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

    return (
        <div className="py-4 sm:py-8">
            <div className="mx-auto max-w-3xl overflow-hidden rounded-card border border-border bg-surface shadow-subtle">
                <div className="bg-gradient-to-r from-primary to-primary-700 px-6 py-8">
                    <h3 className="text-lg font-bold text-white">Información del Producto</h3>
                    <p className="mt-1 text-sm text-white/80">Ingresa todos los detalles del producto</p>
                </div>

                <form ref={formRef} action={action} method="POST" enctype="multipart/form-data" onSubmit={handleSubmit} noValidate className="space-y-6 p-6 sm:p-8">
                    {errors._general && (
                        <div className="rounded-card bg-red-50 p-3 text-sm text-red-700">{errors._general[0]}</div>
                    )}

                    <div>
                        <label htmlFor="nombre-field" className="block text-sm font-medium text-ink">Nombre <span className="text-red-600">*</span></label>
                        <Input id="nombre-field" name="nombre" required defaultValue={producto?.nombre ?? ''} placeholder="Ej: Remera básica" error={errors?.nombre?.[0]} onBlur={verificarDuplicado} className="mt-1" />
                        {errors?.nombre && <p className="mt-1 text-sm text-red-600">{errors.nombre[0]}</p>}
                    </div>

                    <div>
                        <label htmlFor="categoria-field" className="block text-sm font-medium text-ink">Categoría <span className="text-red-600">*</span></label>
                        <div className="mt-1 flex gap-2">
                            <Select id="categoria-field" name="categoria_id" required defaultValue={producto?.categoria_id ?? (categorias[0]?.id ?? '')} className="flex-1">
                                <option value="">Seleccioná una categoría</option>
                                {categorias.map((cat) => <option key={cat.id} value={cat.id}>{cat.nombre}</option>)}
                            </Select>
                            <Button type="button" variant="secondary" title="Crear categoría nueva" aria-label="Crear categoría nueva" onClick={() => setInlineOpen(true)}>+ Nueva</Button>
                        </div>
                        {errors?.categoria_id && <p className="mt-1 text-sm text-red-600">{errors.categoria_id[0]}</p>}
                    </div>

                    <div className="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label htmlFor="cantidad-field" className="block text-sm font-medium text-ink">Cantidad</label>
                            <Input id="cantidad-field" type="number" name="cantidad" min="0" defaultValue={producto?.cantidad ?? 1} error={errors?.cantidad?.[0]} className="mt-1" />
                        </div>
                        <MoneyInput label="Costo" name="precio_compra" initial={producto?.precio_compra ?? 0} error={errors?.precio_compra?.[0]} />
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

                    <ProductImage value={producto?.imagen ? `/storage/${producto.imagen}` : ''} />

                    <div className="flex flex-col-reverse gap-3 border-t border-border pt-6 sm:flex-row sm:justify-end">
                        <a href={routes.productos} className="inline-flex min-h-11 items-center justify-center rounded-control border border-border px-4 text-sm font-semibold text-ink">Cancelar</a>
                        <Button type="submit" disabled={submitting}>{submitting ? 'Guardando...' : (isEdit ? 'Guardar Cambios' : 'Guardar Producto')}</Button>
                    </div>
                </form>
            </div>

            <Modal open={inlineOpen} title="Nueva Categoría" onClose={() => setInlineOpen(false)}>
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
                message={`Ya existe un producto llamado «${duplicado?.nombre}». ¿Querés editarlo?`}
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
}

function acciones(producto, routes, setToggle) {
    const nombre = producto.nombre;
    return (
        <div className="data-table-actions">
            <a href={`${routes.productos}/${producto.id}`} className="data-table-action text-ink hover:bg-surface-muted" aria-label={`Ver ${nombre}`}>Ver</a>
            <a href={`${routes.productos}/${producto.id}/edit`} className="data-table-action text-primary hover:bg-surface-muted" aria-label={`Editar ${nombre}`}>Editar</a>
            {producto.activo ? (
                <button type="button" onClick={() => setToggle({ tipo: 'desactivar', producto })} className="data-table-action text-red-600 hover:bg-surface-muted" aria-label={`Desactivar ${nombre}`}>Desactivar</button>
            ) : (
                <button type="button" onClick={() => setToggle({ tipo: 'activar', producto })} className="data-table-action text-green-600 hover:bg-surface-muted" aria-label={`Activar ${nombre}`}>Activar</button>
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
export function ProductList({ categorias, routes }) {
    const [verInactivos, setVerInactivos] = useState(false);
    const [busqueda, setBusqueda] = useState('');
    const [filtroCategoria, setFiltroCategoria] = useState('');
    const [filtroTalle, setFiltroTalle] = useState('');
    const [filtroColor, setFiltroColor] = useState('');
    const [productos, setProductos] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [error, setError] = useState(false);
    const [paginaActual, setPaginaActual] = useState(1);
    const [toggle, setToggle] = useState(null);
    const searchRef = useRef(null);
    const POR_PAGINA = 15;

    const cargar = useCallback(async () => {
        setCargando(true);
        setError(false);
        try {
            const response = await fetch(`/productos/data${verInactivos ? '?inactivos=1' : ''}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });
            if (!response.ok) throw new Error('carga');
            const data = await response.json();
            setProductos(data);
            setPaginaActual(1);
        } catch {
            setError(true);
        } finally {
            setCargando(false);
        }
    }, [verInactivos]);

    useEffect(() => { cargar(); }, [cargar]);
    useEffect(() => { setPaginaActual(1); }, [busqueda, filtroCategoria, filtroTalle, filtroColor]);

    const filtrados = useMemo(() => {
        if (!productos) return [];
        const termino = busqueda.trim().toLowerCase();
        const tokens = termino.split(' ').filter(Boolean);
        return productos.filter((p) => {
            const matchesTermino = tokens.length === 0 || tokens.every((token) =>
                [p.nombre, p.categoria?.nombre, p.talle, p.color].some((campo) => campo && campo.toLowerCase().includes(token))
            );
            const matchesCategoria = !filtroCategoria || String(p.categoria_id) === filtroCategoria;
            const matchesTalle = !filtroTalle || p.talle === filtroTalle;
            const matchesColor = !filtroColor || p.color === filtroColor;
            return matchesTermino && matchesCategoria && matchesTalle && matchesColor;
        });
    }, [productos, busqueda, filtroCategoria, filtroTalle, filtroColor]);

    const talies = useMemo(() => [...new Set((productos || []).map((p) => p.talle).filter(Boolean))].sort(), [productos]);
    const colores = useMemo(() => [...new Set((productos || []).map((p) => p.color).filter(Boolean))].sort(), [productos]);

    const totalPaginas = Math.max(1, Math.ceil(filtrados.length / POR_PAGINA));
    const paginaSegura = Math.min(paginaActual, totalPaginas);
    const visibles = filtrados.slice((paginaSegura - 1) * POR_PAGINA, paginaSegura * POR_PAGINA);
    const desde = filtrados.length === 0 ? 0 : (paginaSegura - 1) * POR_PAGINA + 1;
    const hasta = Math.min(paginaSegura * POR_PAGINA, filtrados.length);
    const hayFiltros = Boolean(busqueda || filtroCategoria || filtroTalle || filtroColor);

    const limpiarFiltros = () => {
        setBusqueda('');
        setFiltroCategoria('');
        setFiltroTalle('');
        setFiltroColor('');
        searchRef.current?.focus();
    };

    const confirmarToggle = () => {
        if (!toggle) return;
        const id = toggle.producto.id;
        const formId = toggle.tipo === 'activar' ? `activar-${id}` : `desactivar-${id}`;
        document.getElementById(formId)?.submit();
    };

    return (
        <div className="py-4 sm:py-8">
            <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 className="page-title">Inventario de Productos</h2>
                    <p className="mt-1 text-sm text-muted">Gestiona todos tus productos en un solo lugar</p>
                </div>
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label htmlFor="show-inactive-products" className="flex cursor-pointer items-center gap-2 text-sm font-medium text-ink">
                        <input id="show-inactive-products" type="checkbox" checked={verInactivos} onChange={(event) => setVerInactivos(event.target.checked)} className="h-4 w-4 rounded border-border accent-primary" />
                        Ver inactivos
                    </label>
                    <a href={routes.productosCreate} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-control bg-primary px-5 text-sm font-semibold text-white hover:bg-primary-700">Nuevo Producto</a>
                </div>
            </div>

            <div className="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center">
                <div className="flex-1">
                    <Input
                        ref={searchRef}
                        value={busqueda}
                        onChange={(event) => setBusqueda(event.target.value)}
                        placeholder="Buscar por nombre, categoría, talle o color..."
                        aria-label="Buscar por nombre, categoría, talle o color"
                    />
                </div>
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:w-80">
                    <label className="text-xs font-semibold text-muted">Categoría<Select aria-label="Filtrar por categoría" value={filtroCategoria} onChange={(event) => setFiltroCategoria(event.target.value)} className="mt-1 text-sm font-normal">
                        <option value="">Todas las categorías</option>
                        {categorias.map((cat) => <option key={cat.id} value={cat.id}>{cat.nombre}</option>)}
                    </Select></label>
                    <label className="text-xs font-semibold text-muted">Talle<Select aria-label="Filtrar por talle" value={filtroTalle} onChange={(event) => setFiltroTalle(event.target.value)} className="mt-1 text-sm font-normal">
                        <option value="">Todos los talles</option>
                        {talies.map((talle) => <option key={talle} value={talle}>{talle}</option>)}
                    </Select></label>
                    <label className="text-xs font-semibold text-muted">Color<Select aria-label="Filtrar por color" value={filtroColor} onChange={(event) => setFiltroColor(event.target.value)} className="mt-1 text-sm font-normal">
                        <option value="">Todos los colores</option>
                        {colores.map((color) => <option key={color} value={color}>{color}</option>)}
                    </Select></label>
                </div>
                {hayFiltros && <Button variant="secondary" onClick={limpiarFiltros}>Limpiar filtros</Button>}
            </div>

            {cargando ? (
                <LoadingState label="Cargando catálogo..." />
            ) : error ? (
                <ErrorState title="No se pudo cargar el catálogo de productos." description="Revisá tu conexión e intentá nuevamente." onRetry={() => cargar()} />
            ) : filtrados.length === 0 ? (
                <EmptyState
                    title="No se encontraron productos"
                    description="Prueba con otros términos de búsqueda"
                />
            ) : (
                <div className="space-y-6">
                    <Card className="hidden overflow-x-auto p-0 sm:p-0 md:block">
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
                                                ? <img src={`/storage/${producto.imagen}`} alt={producto.nombre} className="h-12 w-12 rounded-lg object-cover" />
                                                : <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-surface-muted text-xs text-muted">—</div>}
                                        </td>
                                        <td className="px-4 py-3 font-semibold text-ink">
                                            {producto.nombre}
                                            {!producto.activo && <Badge tone="neutral" className="ml-2">Inactivo</Badge>}
                                        </td>
                                        <td className="px-4 py-3"><Badge tone="neutral">{producto.categoria?.nombre ?? '—'}</Badge></td>
                                        <td className="px-4 py-3 text-muted">{producto.talle || '—'}</td>
                                        <td className="px-4 py-3 text-muted">{producto.color || '—'}</td>
                                        <td className="px-4 py-3 text-right">{formatMoney(producto.precio_compra)}</td>
                                        <td className="px-4 py-3 text-right">{formatMoney(producto.precio_venta)}</td>
                                         <td className={`px-4 py-3 text-right font-medium ${producto.precio_venta - producto.precio_compra < 0 ? 'text-red-700' : 'text-green-700'}`}>{formatMoney(producto.precio_venta - producto.precio_compra)}</td>
                                         <td className="px-4 py-3 text-right tabular-nums">
                                             <Badge tone={producto.cantidad === 0 ? 'danger' : producto.cantidad <= 5 ? 'warning' : 'success'}>{producto.cantidad === 0 ? 'Sin stock' : producto.cantidad <= 5 ? `${producto.cantidad} · Bajo` : producto.cantidad}</Badge>
                                         </td>
                                         <td className="px-4 py-3">{acciones(producto, routes, setToggle)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </Card>

                    <div className="space-y-4 md:hidden">
                        {visibles.map((producto) => (
                            <Card key={producto.id}>
                                <div className="flex items-start gap-3">
                                    {producto.imagen
                                        ? <img src={`/storage/${producto.imagen}`} alt={producto.nombre} className="h-14 w-14 rounded-lg object-cover" />
                                        : <div className="flex h-14 w-14 items-center justify-center rounded-lg bg-surface-muted text-xs text-muted">—</div>}
                                    <div className="min-w-0">
                                        <p className="font-bold text-ink">{producto.nombre}</p>
                                        <p className="mt-0.5 text-xs text-muted">{producto.categoria?.nombre}</p>
                                    </div>
                                    <div className="ml-auto shrink-0">
                                         <Badge tone={producto.cantidad === 0 ? 'danger' : producto.cantidad <= 5 ? 'warning' : 'success'}>{producto.cantidad === 0 ? 'Sin stock' : producto.cantidad <= 5 ? `${producto.cantidad} · Bajo` : producto.cantidad}</Badge>
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
                                     <div className="data-card-field">
                                        <dt className="text-xs uppercase tracking-wide text-muted">P. Compra</dt>
                                        <dd className="mt-0.5 font-medium text-ink">{formatMoney(producto.precio_compra)}</dd>
                                    </div>
                                     <div className="data-card-field">
                                        <dt className="text-xs uppercase tracking-wide text-muted">P. Venta</dt>
                                        <dd className="mt-0.5 font-medium text-ink">{formatMoney(producto.precio_venta)}</dd>
                                    </div>
                                     <div className="data-card-field">
                                        <dt className="text-xs uppercase tracking-wide text-muted">Ganancia</dt>
                                         <dd className={`mt-0.5 font-medium ${producto.precio_venta - producto.precio_compra < 0 ? 'text-red-700' : 'text-green-700'}`}>{formatMoney(producto.precio_venta - producto.precio_compra)}</dd>
                                    </div>
                                     <div className="data-card-field">
                                        <dt className="text-xs uppercase tracking-wide text-muted">Stock</dt>
                                        <dd className="mt-0.5 font-medium text-ink">
                                             {producto.cantidad === 0 ? 'Sin stock' : producto.cantidad <= 5 ? `${producto.cantidad} · Bajo` : producto.cantidad}
                                            {!producto.activo && <Badge tone="neutral" className="ml-2">Inactivo</Badge>}
                                        </dd>
                                    </div>
                                </dl>
                                <div className="mt-4 border-t border-border pt-3">{acciones(producto, routes, setToggle)}</div>
                            </Card>
                        ))}
                    </div>

                    <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <p className="text-sm text-muted">
                             Mostrando <strong className="text-ink">{desde}</strong>–<strong className="text-ink">{hasta}</strong> de <strong className="text-ink">{filtrados.length}</strong> {filtrados.length === 1 ? 'producto' : 'productos'}
                        </p>
                        {totalPaginas > 1 && (
                            <div className="flex items-center gap-3">
                                <button type="button" disabled={paginaSegura <= 1} onClick={() => setPaginaActual(paginaSegura - 1)} className="rounded-control border border-border px-3 py-2 text-sm font-semibold text-ink disabled:opacity-40">Anterior</button>
                                <span className="text-sm text-muted">Página <strong className="text-ink">{paginaSegura}</strong> de <strong className="text-ink">{totalPaginas}</strong></span>
                                <button type="button" disabled={paginaSegura >= totalPaginas} onClick={() => setPaginaActual(paginaSegura + 1)} className="rounded-control border border-border px-3 py-2 text-sm font-semibold text-ink disabled:opacity-40">Siguiente</button>
                            </div>
                        )}
                    </div>
                </div>
            )}

            {filtrados.length === 0 && hayFiltros && !cargando && !error && (
                <div className="mt-4 text-center">
                    <Button variant="secondary" onClick={limpiarFiltros}>Ver todos los productos</Button>
                </div>
            )}

            {visibles.map((producto) => (
                <div key={producto.id} className="hidden">
                    <form id={`desactivar-${producto.id}`} action={`/productos/${producto.id}`} method="POST">
                        <input type="hidden" name="_token" value={csrf()} />
                        <input type="hidden" name="_method" value="DELETE" />
                    </form>
                    <form id={`activar-${producto.id}`} action={`/productos/${producto.id}/activate`} method="POST">
                        <input type="hidden" name="_token" value={csrf()} />
                        <input type="hidden" name="_method" value="PATCH" />
                    </form>
                </div>
            ))}

            <ConfirmDialog
                open={Boolean(toggle)}
                title={toggle?.tipo === 'activar' ? 'Activar producto' : 'Desactivar producto'}
                 message={toggle?.tipo === 'activar' ? `¿Activar «${toggle?.producto?.nombre}» para que vuelva a estar disponible?` : `«${toggle?.producto?.nombre}» se desactivará, no se borrará. Podrás reactivarlo si vuelve a tener stock.`}
                confirmLabel={toggle?.tipo === 'activar' ? 'Sí, activar' : 'Sí, desactivar'}
                tone={toggle?.tipo === 'activar' ? 'primary' : 'danger'}
                onConfirm={confirmarToggle}
                onClose={() => setToggle(null)}
            />
        </div>
    );
}
