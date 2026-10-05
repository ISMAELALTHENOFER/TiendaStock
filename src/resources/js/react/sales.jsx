import { useMemo, useRef, useState } from 'react';
import { ArrowLeft, CheckCircle2, CircleX, Minus, Package, Plus, Search, ShoppingBasket, ShoppingCart, Trash2, Truck, Store } from 'lucide-react';
import { api } from './lib/api.js';
import { Badge } from './components/ui/Badge.jsx';
import { Button } from './components/ui/Button.jsx';
import { Card } from './components/ui/Card.jsx';
import { Input } from './components/ui/Input.jsx';
import { Select } from './components/ui/Select.jsx';

const money = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });
const formatMoney = (value) => money.format(Number(value || 0));
const maskMoney = (value) => {
    const digits = String(value).replace(/\D/g, '');
    return digits ? (Number(digits) / 100).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
};
const parseMoney = (value) => Number(String(value).replace(/\./g, '').replace(',', '.').replace(/[^\d.-]/g, '')) || 0;
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const formatDate = (value) => value ? new Intl.DateTimeFormat('es-AR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value)) : '—';
const statusTone = (status) => status === 'completada' ? 'success' : 'danger';
const statusLabel = (status) => status === 'completada' ? 'Completada' : 'Anulada';
const deliveryLabel = (sale) => sale.tipo_entrega === 'local' ? 'En el local' : sale.tipo_entrega === 'uber' ? 'Uber' : '—';
const detailDeliveryLabel = (sale) => sale.tipo_entrega === 'local' ? 'En el local' : sale.tipo_entrega === 'uber' ? 'Envío por Uber' : '—';
const paymentLabel = (method) => method ? method.charAt(0).toUpperCase() + method.slice(1) : '—';

function CancelForm({ sale, compact = false }) {
    if (sale.estado !== 'completada') return null;
    return <form action={`/ventas/${sale.id}/cancel`} method="post" onSubmit={(event) => {
        if (!window.confirm(`¿Anular venta #${sale.id} por ${formatMoney(sale.total)}? Se restaurará el stock.`)) event.preventDefault();
    }}>
        <input type="hidden" name="_token" value={csrf()} />
        <button type="submit" aria-label={`Cancelar venta #${sale.id}`} className={compact ? 'block w-full rounded-control px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50' : 'min-h-11 rounded-control px-4 text-sm font-semibold text-red-700 hover:bg-red-50'}>Cancelar venta #{sale.id}</button>
    </form>;
}

function SaleActions({ sale }) {
    return <div className="flex items-center justify-end gap-2">
        <a href={`/ventas/${sale.id}`} className="inline-flex min-h-11 items-center rounded-control px-3 text-sm font-semibold text-primary hover:bg-green-50" aria-label={`Ver venta #${sale.id}`}>Ver</a>
        {sale.estado === 'completada' && <details className="relative">
            <summary className="flex min-h-11 min-w-11 cursor-pointer list-none items-center justify-center rounded-control text-lg text-muted hover:bg-surface-muted" aria-label={`Acciones de venta #${sale.id}`} title={`Acciones de venta #${sale.id}`}>⋮</summary>
            <div className="absolute right-0 z-10 mt-1 min-w-44 rounded-card border border-border bg-surface p-2 shadow-subtle"><CancelForm sale={sale} compact /></div>
        </details>}
    </div>;
}

export function SalesHistory({ sales, query = {}, routes }) {
    const data = sales?.data || [];
    const [filters, setFilters] = useState({ desde: query.desde || '', hasta: query.hasta || '', estado: query.estado || '' });
    const paginationUrl = (page) => {
        const params = new URLSearchParams({ page: String(page) });
        if (query.desde) params.set('desde', query.desde);
        if (query.hasta) params.set('hasta', query.hasta);
        if (query.estado) params.set('estado', query.estado);
        return `${routes.ventas}?${params}`;
    };
    const submit = (event) => {
        if (filters.desde && filters.hasta && filters.desde > filters.hasta) {
            event.preventDefault();
            window.alert('La fecha Desde debe ser menor o igual a Hasta.');
        }
    };
    return <div className="space-y-6">
        <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div><p className="eyebrow">Operaciones</p><h1 className="page-title mt-2">Historial de Ventas</h1><p className="mt-2 text-sm text-muted">Consulta y gestiona todas las ventas realizadas</p></div>
            <a href={routes.ventasPos} className="inline-flex min-h-11 items-center justify-center rounded-control bg-primary px-5 text-sm font-semibold text-white hover:bg-primary-700">Nueva Venta</a>
        </header>
        <Card>
            <form method="get" action={routes.ventas} onSubmit={submit} className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
                <label className="text-sm font-medium text-ink" htmlFor="sales-from">Desde<Input id="sales-from" type="date" name="desde" value={filters.desde} onChange={(event) => setFilters({ ...filters, desde: event.target.value })} className="mt-1" /></label>
                <label className="text-sm font-medium text-ink" htmlFor="sales-to">Hasta<Input id="sales-to" type="date" name="hasta" value={filters.hasta} onChange={(event) => setFilters({ ...filters, hasta: event.target.value })} className="mt-1" /></label>
                <label className="text-sm font-medium text-ink" htmlFor="sales-status">Estado<Select id="sales-status" name="estado" value={filters.estado} onChange={(event) => setFilters({ ...filters, estado: event.target.value })} className="mt-1"><option value="">Todos los estados</option><option value="completada">Completadas</option><option value="anulada">Anuladas</option></Select></label>
                <Button type="submit">Filtrar</Button><a href={routes.ventas} className="inline-flex min-h-11 items-center justify-center rounded-control border border-border bg-surface px-4 text-sm font-semibold text-ink hover:bg-surface-muted">Limpiar</a>
            </form>
        </Card>
        {data.length === 0 ? <Card className="py-12 text-center"><p className="text-lg font-semibold text-ink">No hay ventas registradas.</p><p className="mt-1 text-sm text-muted">Las ventas realizadas aparecerán aquí.</p><a href={routes.ventasPos} className="mt-6 inline-flex min-h-11 items-center rounded-control bg-primary px-5 text-sm font-semibold text-white">Realizar primera venta</a></Card> : <>
            <div className="hidden overflow-x-auto rounded-card border border-border bg-surface shadow-subtle md:block">
                 <table className="data-table w-full min-w-[920px] text-sm"><caption className="sr-only">Historial de ventas</caption><thead><tr>{['#', 'Fecha', 'Items', 'Total', 'Entrega', 'Procesado por', 'Estado', 'Acciones'].map((heading) => <th key={heading} scope="col" className="whitespace-nowrap px-4 py-3 text-left">{heading}</th>)}</tr></thead>
                     <tbody className="divide-y divide-border">{data.map((sale) => <tr key={sale.id} className="hover:bg-surface-muted"><td className="px-4 py-4 font-semibold tabular-nums">{sale.id}</td><td className="whitespace-nowrap px-4 py-4">{formatDate(sale.created_at)}</td><td className="px-4 py-4 text-right tabular-nums">{sale.items?.length || 0}</td><td className="whitespace-nowrap px-4 py-4 text-right font-semibold tabular-nums">{formatMoney(sale.total)}</td><td className="px-4 py-4">{deliveryLabel(sale)}</td><td className="px-4 py-4">{sale.user?.name || '—'}</td><td className="px-4 py-4"><Badge tone={statusTone(sale.estado)}>{statusLabel(sale.estado)}</Badge></td><td className="px-4 py-4"><SaleActions sale={sale} /></td></tr>)}</tbody>
                </table>
            </div>
             <div className="grid gap-3 md:hidden">{data.map((sale) => <Card key={sale.id} className="space-y-3"><div className="flex items-start justify-between gap-3"><div><p className="text-xs text-muted">Venta #{sale.id}</p><p className="font-semibold">{formatDate(sale.created_at)}</p></div><Badge tone={statusTone(sale.estado)}>{statusLabel(sale.estado)}</Badge></div><dl className="grid grid-cols-2 gap-3 text-sm"><div className="data-card-field"><dt>Items</dt><dd>{sale.items?.length || 0}</dd></div><div className="data-card-field"><dt>Entrega</dt><dd>{deliveryLabel(sale)}</dd></div><div className="data-card-field"><dt>Procesado por</dt><dd>{sale.user?.name || '—'}</dd></div><div className="data-card-field text-right"><dt>Total</dt><dd className="text-lg">{formatMoney(sale.total)}</dd></div></dl><SaleActions sale={sale} /></Card>)}</div>
        </>}
        {sales?.last_page > 1 && <nav className="flex flex-wrap gap-2" aria-label="Paginación">{Array.from({ length: sales.last_page }, (_, index) => <a key={index} href={paginationUrl(index + 1)} className={`inline-flex min-h-11 min-w-11 items-center justify-center rounded-control border px-3 text-sm ${sales.current_page === index + 1 ? 'border-primary bg-primary text-white' : 'border-border bg-surface'}`}>{index + 1}</a>)}</nav>}
    </div>;
}

async function postSale(payload, onError) {
    const response = await fetch('/ventas', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() }, body: JSON.stringify(payload) });
    if (response.redirected) { window.location.assign(response.url); return true; }
    const data = await response.json().catch(() => ({}));
    if (!response.ok) { onError(data.errors || { form: [data.message || 'No se pudo registrar la venta.'] }); return false; }
    window.location.reload();
    return true;
}

export function SalesPos({ routes, initialErrors = {} }) {
    const [search, setSearch] = useState('');
    const [results, setResults] = useState([]);
    const searchRequest = useRef(0);
    const searchInput = useRef(null);
    const [searchError, setSearchError] = useState(false);
    const [cart, setCart] = useState([]);
    const [delivery, setDelivery] = useState('');
    const [paymentMethod, setPaymentMethod] = useState('efectivo');
    const [paid, setPaid] = useState('');
    const [discount, setDiscount] = useState('');
    const [errors, setErrors] = useState(initialErrors);
    const [searching, setSearching] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const subtotal = useMemo(() => Math.round(cart.reduce((sum, item) => sum + item.cantidad * Number(item.precio_venta), 0) * 100) / 100, [cart]);
    const totalCents = Math.max(0, Math.round(subtotal * 100) - Math.round(parseMoney(discount) * 100));
    const total = totalCents / 100;
    const paidCents = Math.round(parseMoney(paid) * 100);
    const change = Math.max(0, parseMoney(paid) - total);
    const searchProducts = async (value) => {
        const request = ++searchRequest.current;
        setSearch(value);
        setSearchError(false);
        if (value.trim().length < 2) { setResults([]); setSearching(false); return; }
        setSearching(true);
        try {
            const products = await api.get(`/productos/search?q=${encodeURIComponent(value)}`);
            if (request === searchRequest.current) setResults(products);
        } catch {
            if (request === searchRequest.current) { setResults([]); setSearchError(true); }
        } finally {
            if (request === searchRequest.current) setSearching(false);
        }
    };
    const add = (product) => setCart((items) => items.some((item) => item.producto_id === product.id) ? items.map((item) => item.producto_id === product.id ? { ...item, cantidad: Math.min(item.cantidad + 1, product.cantidad) } : item) : [...items, { producto_id: product.id, nombre: product.nombre, precio_venta: product.precio_venta, cantidad: 1, stock: product.cantidad }]);
    const submit = async () => {
        if (!cart.length || !delivery || total <= 0 || paidCents < totalCents || submitting) return;
        setSubmitting(true); setErrors({});
        try {
            await postSale({ items: cart.map(({ producto_id, cantidad }) => ({ producto_id, cantidad })), subtotal, descuento: parseMoney(discount), impuesto: 0, total, pago_con: parseMoney(paid), metodo_pago: paymentMethod, tipo_entrega: delivery }, setErrors);
        } catch {
            setErrors({ form: ['No se pudo registrar la venta. Reintente.'] });
        } finally {
            setSubmitting(false);
        }
    };
    return <div className="space-y-6">
        <header className="flex flex-col gap-4 border-b border-border pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div><p className="eyebrow">Operaciones</p><h1 className="page-title mt-2">Punto de Venta</h1><p className="mt-2 text-sm text-muted">Registra nuevas ventas en el sistema</p></div>
            <a href={routes.ventas} className="inline-flex min-h-11 items-center justify-center gap-2 self-start rounded-control border border-border bg-surface px-4 text-sm font-semibold text-ink hover:bg-surface-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:self-auto"><ArrowLeft size={18} aria-hidden="true" />Volver</a>
        </header>
        {Object.values(errors).flat().map((error, index) => <p key={index} role="alert" className="rounded-control border border-red-200 bg-red-50 p-3 text-sm text-red-800">{error}</p>)}
        <div className="grid items-start gap-6 xl:grid-cols-12">
            <section aria-label="Catálogo de productos" className="min-w-0 space-y-4 xl:col-span-7">
                <Card className="space-y-3">
                    <div className="flex flex-wrap items-center justify-between gap-2 text-sm">
                        <label htmlFor="pos-search" className="flex items-center gap-2 font-semibold text-ink"><Search size={18} className="text-primary" aria-hidden="true" />Buscar productos</label>
                        {search.trim().length >= 2 && !searching && !searchError && <span className="text-xs text-muted" aria-live="polite">Resultados mostrados: <strong className="text-ink">{results.length}</strong>{results.length === 10 ? ' (máximo 10)' : ''}</span>}
                    </div>
                    <div className="relative">
                        <Search size={18} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted" aria-hidden="true" />
                        <Input id="pos-search" ref={searchInput} value={search} onChange={(event) => searchProducts(event.target.value)} placeholder="Busca por nombre, color, talle o categoría" className="pl-10 pr-12 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" />
                        {search && <button type="button" onClick={() => { searchProducts(''); searchInput.current?.focus(); }} aria-label="Limpiar búsqueda" className="absolute right-1 top-1/2 flex min-h-10 min-w-10 -translate-y-1/2 items-center justify-center rounded-control text-muted hover:text-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary"><CircleX size={18} aria-hidden="true" /></button>}
                    </div>
                    <p className="text-xs text-muted">Escribe al menos 2 caracteres para buscar productos disponibles.</p>
                </Card>
                {searching && <p role="status" className="p-6 text-center text-sm text-muted">Buscando...</p>}
                {!searching && searchError && <p role="alert" className="rounded-control border border-red-200 bg-red-50 p-4 text-sm text-red-800">No se pudo buscar. Modifica la búsqueda para reintentar.</p>}
                {!searching && !searchError && results.length > 0 && <div className="grid gap-3 sm:grid-cols-2">{results.map((product) => {
                    const inCart = cart.find((item) => item.producto_id === product.id)?.cantidad || 0;
                    const available = product.cantidad - inCart;
                    return <Card key={product.id} className="flex min-w-0 flex-col justify-between gap-4 p-4 sm:p-4">
                        <div className="flex flex-wrap items-start justify-between gap-2">
                            <div className="min-w-0 flex-1"><h3 className="break-words font-semibold text-ink">{product.nombre}</h3><p className="mt-1 text-xs text-muted">{product.categoria?.nombre}{product.color ? ` · ${product.color}` : ''}{product.talle ? ` · ${product.talle}` : ''}</p></div>
                            <span className={`inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold ${available === 0 ? 'bg-red-50 text-red-800' : available <= 6 ? 'bg-amber-50 text-amber-800' : 'bg-green-50 text-primary-700'}`}><Package size={14} aria-hidden="true" />{available} un.</span>
                        </div>
                        <div className="flex flex-wrap items-end justify-between gap-3 border-t border-border pt-3">
                            <div><p className="text-xs font-semibold uppercase text-muted">Precio unitario</p><p className="text-lg font-bold tabular-nums text-ink">{formatMoney(product.precio_venta)}</p></div>
                            <Button type="button" disabled={available <= 0} onClick={() => add(product)} className="gap-2 px-3 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"><ShoppingCart size={17} aria-hidden="true" />Agregar</Button>
                        </div>
                    </Card>;
                })}</div>}
                {!searching && !searchError && search.trim().length >= 2 && !results.length && <Card className="p-8 text-center text-sm text-muted">No se encontraron productos para “{search}”.</Card>}
            </section>
            <Card className="min-w-0 space-y-5 p-4 sm:p-5 xl:sticky xl:top-6 xl:col-span-5 xl:max-h-[calc(100dvh-3rem)] xl:overflow-y-auto">
                <div className="flex items-center justify-between gap-3 border-b border-border pb-4"><h2 className="flex items-center gap-2 text-lg font-bold text-ink"><span className="flex h-9 w-9 items-center justify-center rounded-control bg-green-50 text-primary"><ShoppingBasket size={20} aria-hidden="true" /></span>Carrito</h2><span className="rounded-full bg-surface-muted px-3 py-1 text-xs font-semibold text-muted">{cart.length} {cart.length === 1 ? 'artículo' : 'artículos'}</span></div>
                {!cart.length ? <p className="py-8 text-center text-sm text-muted">El carrito está vacío. Busca productos para agregar.</p> : <div className="space-y-3">{cart.map((item, index) => <div key={item.producto_id} className="space-y-3 rounded-card bg-surface-muted p-3">
                    <div className="flex items-start justify-between gap-2"><div className="min-w-0"><p className="break-words text-sm font-semibold text-ink">{item.nombre}</p><p className="text-xs text-muted">{formatMoney(item.precio_venta)} c/u</p></div><button type="button" onClick={() => setCart(cart.filter((_, itemIndex) => itemIndex !== index))} className="flex min-h-11 min-w-11 items-center justify-center rounded-control text-red-700 hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-red-700" aria-label={`Quitar ${item.nombre}`}><Trash2 size={17} aria-hidden="true" /></button></div>
                    <div className="flex flex-wrap items-center justify-between gap-2"><div className="inline-flex items-center rounded-control bg-surface">
                        <button type="button" disabled={item.cantidad <= 1} onClick={() => setCart(cart.map((current, itemIndex) => itemIndex === index ? { ...current, cantidad: current.cantidad - 1 } : current))} aria-label={`Reducir cantidad de ${item.nombre}`} className="flex min-h-11 min-w-11 items-center justify-center rounded-control hover:bg-border focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-40"><Minus size={16} aria-hidden="true" /></button>
                        <Input type="number" min="1" max={item.stock} step="1" value={item.cantidad} onChange={(event) => setCart(cart.map((current, itemIndex) => itemIndex === index ? { ...current, cantidad: Math.min(item.stock, Math.max(1, Math.trunc(Number(event.target.value) || 1))) } : current))} aria-label={`Cantidad de ${item.nombre}`} className="w-16 border-0 px-1 text-center text-sm font-semibold tabular-nums focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary" />
                        <button type="button" disabled={item.cantidad >= item.stock} onClick={() => setCart(cart.map((current, itemIndex) => itemIndex === index ? { ...current, cantidad: current.cantidad + 1 } : current))} aria-label={`Incrementar cantidad de ${item.nombre}`} className="flex min-h-11 min-w-11 items-center justify-center rounded-control hover:bg-border focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-40"><Plus size={16} aria-hidden="true" /></button>
                    </div><strong className="ml-auto tabular-nums text-ink">{formatMoney(item.cantidad * item.precio_venta)}</strong></div>
                </div>)}</div>}
                <div className="space-y-3 border-t border-border pt-4 text-sm"><div className="flex items-center justify-between gap-2"><span className="text-muted">Subtotal</span><strong className="tabular-nums text-ink">{formatMoney(subtotal)}</strong></div><label className="flex flex-wrap items-center justify-between gap-2 text-muted">Descuento (monto fijo)<Input type="text" inputMode="decimal" value={discount} onChange={(event) => setDiscount(maskMoney(event.target.value))} aria-label="Descuento en pesos" placeholder="0,00" className="w-32 bg-surface-muted text-right text-ink" /></label><div className="flex items-baseline justify-between gap-2 border-t border-border pt-3 font-bold text-ink"><span className="text-base">Total</span><span className="text-2xl tabular-nums">{formatMoney(total)}</span></div></div>
                <fieldset className="space-y-2"><legend className="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Tipo de entrega</legend><div className="grid grid-cols-2 gap-2">{[['local', 'En el local', Store], ['uber', 'Envío por Uber', Truck]].map(([value, label, Icon]) => <label key={value} className={`flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-control border p-2 text-center text-xs font-semibold focus-within:outline focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-primary ${delivery === value ? 'border-primary bg-green-50 text-primary-700' : 'border-border bg-surface-muted text-ink hover:bg-border'}`}><input type="radio" name="tipo_entrega" value={value} checked={delivery === value} onChange={() => setDelivery(value)} className="sr-only" /><Icon size={16} className="shrink-0" aria-hidden="true" />{label}</label>)}</div></fieldset>
                <label className="block text-xs font-semibold uppercase tracking-wide text-muted">Método de pago<Select value={paymentMethod} onChange={(event) => setPaymentMethod(event.target.value)} className="mt-2 bg-surface-muted text-sm font-medium normal-case tracking-normal focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"><option value="efectivo">Efectivo</option><option value="tarjeta">Tarjeta</option><option value="transferencia">Transferencia</option></Select></label>
                <div className="space-y-3 rounded-card bg-surface-muted p-3 text-sm"><label className="flex flex-wrap items-center justify-between gap-2 text-muted">Pago con<Input type="text" inputMode="decimal" value={paid} onChange={(event) => setPaid(maskMoney(event.target.value))} aria-label="Monto recibido en pesos" placeholder="0,00" className="w-36 text-right tabular-nums" /></label>{parseMoney(paid) > 0 && <div className="flex items-center justify-between border-t border-border pt-3"><span className="font-semibold text-muted">Cambio</span><strong className="tabular-nums text-primary">{formatMoney(change)}</strong></div>}</div>
                <div className="space-y-2"><Button type="button" disabled={!cart.length || !delivery || total <= 0 || paidCents < totalCents || submitting} onClick={submit} className="w-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"><CheckCircle2 size={18} aria-hidden="true" />{submitting ? 'Procesando...' : 'Cobrar'}</Button>{cart.length > 0 && <Button type="button" variant="secondary" onClick={() => { setCart([]); setPaid(''); setDiscount(''); }} className="w-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"><Trash2 size={17} aria-hidden="true" />Vaciar carrito</Button>}</div>
            </Card>
        </div>
    </div>;
}

export function SaleShow({ sale, routes }) {
    if (!sale) return null;
    return <div className="mx-auto max-w-2xl space-y-6"><header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between no-print"><div><p className="eyebrow">Recibo</p><h1 className="page-title mt-2">Venta #{sale.id}</h1><p className="mt-2 text-sm text-muted">{formatDate(sale.created_at)}</p></div><div className="flex flex-wrap gap-2"><Button type="button" onClick={() => window.print()}>Imprimir</Button><a href={routes.ventas} className="inline-flex min-h-11 items-center rounded-control border border-border px-4 text-sm font-semibold">Volver</a></div></header><Card id="recibo" className="space-y-6"><div className="border-b border-border pb-4 text-center"><h2 className="text-2xl font-bold">TiendaStock</h2><p className="text-sm text-muted">Recibo de Venta #{sale.id}</p></div>{sale.cliente_nombre && <p className="text-sm"><strong>Cliente:</strong> {sale.cliente_nombre}</p>}<div className="overflow-x-auto"><table className="w-full min-w-[480px] text-sm"><thead><tr className="border-b border-border text-left"><th className="py-2">Producto</th><th className="py-2 text-right">Precio</th><th className="py-2 text-right">Cant.</th><th className="py-2 text-right">Subtotal</th></tr></thead><tbody>{(sale.items || []).map((item) => <tr key={item.id} className="border-b border-border"><td className="py-3">{item.producto?.nombre}</td><td className="py-3 text-right">{formatMoney(item.precio_unitario)}</td><td className="py-3 text-right">{item.cantidad}</td><td className="py-3 text-right font-semibold">{formatMoney(item.subtotal)}</td></tr>)}</tbody></table></div><div className="space-y-2 border-t border-border pt-4 text-right text-sm"><p>Subtotal: {formatMoney(sale.subtotal)}</p>{Number(sale.descuento) > 0 && <p>Descuento: -{formatMoney(sale.descuento)}</p>}<p>Impuesto: {formatMoney(sale.impuesto)}</p><p className="border-t border-border pt-2 text-xl font-bold">Total: {formatMoney(sale.total)}</p><p>Pagó: {formatMoney(sale.pago_con)}</p><p>Cambio: {formatMoney(sale.cambio)}</p></div><div className="border-t border-border pt-4 text-sm text-muted"><p><strong>Método de pago:</strong> {paymentLabel(sale.metodo_pago)}</p><p><strong>Tipo de entrega:</strong> {detailDeliveryLabel(sale)}</p><p><strong>Procesado por:</strong> {sale.user?.name || '—'}</p><p><strong>Estado:</strong> {statusLabel(sale.estado)}</p></div><div className="text-center">{sale.estado === 'anulada' ? <Badge tone="danger">ANULADA</Badge> : <p className="text-sm text-muted">Gracias por su compra</p>}</div></Card>{sale.estado === 'completada' && <div className="no-print text-center"><CancelForm sale={sale} /></div>}<style>{'@media print { .no-print { display: none !important; } #recibo { box-shadow: none; border: 0; } }'}</style></div>;
}
