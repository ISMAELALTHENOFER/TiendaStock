import { useEffect, useRef, useState } from 'react';
import { ArrowUpRight, Boxes, ChartNoAxesCombined, CircleDollarSign } from 'lucide-react';
import { api } from './lib/api.js';
import { Card } from './components/ui/Card.jsx';
import { EmptyState } from './components/ui/EmptyState.jsx';
import { ErrorState } from './components/ui/ErrorState.jsx';
import { ProductForm } from './productos.jsx';

const currency = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });
const SHOW_LOW_STOCK_SECTION = false;

function Skeleton({ className = '' }) {
    return <span className={`block animate-pulse rounded bg-surface-muted ${className}`} aria-hidden="true" />;
}

function KpiCard({ icon: Icon, label, value, detail, loading, tone = 'neutral' }) {
    const toneClass = tone === 'warning' ? 'bg-amber-50 text-amber-700' : 'bg-primary-50 text-primary-700';
    return <Card className="min-w-0 p-4 sm:p-5"><div className="flex items-start justify-between gap-3"><div className="min-w-0"><p className="text-sm font-medium text-muted">{label}</p>{loading ? <Skeleton className="mt-3 h-9 w-28" /> : <p className="mt-2 break-words text-3xl font-bold tracking-tight">{value}</p>}<p className="mt-2 text-sm text-muted">{loading ? <Skeleton className="h-4 w-20" /> : detail}</p></div><span className={`grid h-10 w-10 shrink-0 place-items-center rounded-control ${toneClass}`}><Icon size={20} aria-hidden="true" /></span></div></Card>;
}

function SalesChart({ data }) {
    if (!data.length) return <EmptyState title="Todavía no hay ventas para este período." />;
    const max = Math.max(...data.map((item) => Number(item.total)), 1);
    return <><div className="mt-5 overflow-x-auto"><svg viewBox={`0 0 ${Math.max(data.length * 54, 320)} 180`} style={{ minWidth: Math.max(data.length * 54, 320) }} className="h-48 w-full" aria-hidden="true"><line x1="16" x2="100%" y1="144" y2="144" stroke="currentColor" className="text-border" />{data.map((item, index) => { const height = Math.max((Number(item.total) / max) * 112, 3); const x = 26 + index * 54; return <g key={item.date}><rect x={x} y={144 - height} width="28" height={height} rx="4" className="fill-primary" /><text x={x + 14} y="165" textAnchor="middle" className="fill-muted text-[12px]">{item.date.slice(5)}</text></g>; })}</svg></div><div className="mt-4 overflow-x-auto"><table className="w-full text-left text-sm"><caption className="text-left font-semibold">Ventas por día</caption><thead><tr><th scope="col">Fecha</th><th scope="col">Total</th><th scope="col">Ventas</th></tr></thead><tbody>{data.map((item) => <tr key={item.date}><td>{item.date}</td><td>{currency.format(Number(item.total))}</td><td>{item.count}</td></tr>)}</tbody></table></div></>;
}

function CategoryChart({ data }) {
    if (!data.length) return <div className="mt-4"><EmptyState title="Todavía no hay unidades vendidas." /></div>;
    const max = data.reduce((highest, category) => Math.max(highest, category.count), 1);
    return <ul className="mt-4 space-y-4">{data.map((category, index) => <li key={index} className="min-w-0"><div className="flex flex-wrap items-baseline justify-between gap-x-3"><span className="min-w-0 font-semibold [overflow-wrap:anywhere]">{category.category}</span><span>{category.count} unidades</span></div><div className="mt-1 h-3 w-full overflow-hidden rounded bg-surface-muted" aria-hidden="true"><div className="h-full rounded bg-primary" style={{ width: `${Math.max(0, category.count) / max * 100}%` }} /></div></li>)}</ul>;
}

function AnalyticsContent({ analytics, onRetry, routes }) {
    if (analytics.state === 'loading') return <div className="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(18rem,1fr)]"><Card><Skeleton className="h-5 w-40" /><Skeleton className="mt-6 h-48 w-full" /></Card><Card><Skeleton className="h-5 w-40" /><Skeleton className="mt-6 h-32 w-full" /></Card></div>;
    if (analytics.state === 'error') return <ErrorState title="No pudimos cargar la analítica del dashboard." onRetry={onRetry} />;
    const { data } = analytics;
    return <>
        <div className="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(18rem,1fr)]">
            <Card><SalesChart data={data.series.sales_by_day} /></Card>
            <Card>
                <h2 className="text-lg font-bold">Unidades vendidas por categoría</h2>
                <p className="mt-2 text-sm text-muted">Según la categoría actual de cada producto.</p>
                <CategoryChart data={data.series.sales_by_category} />
            </Card>
        </div>
        {SHOW_LOW_STOCK_SECTION && <Card>
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h2 className="text-lg font-bold">Stock que requiere atención</h2>
                {routes.productos && <a href={routes.productos} className="text-sm font-semibold text-primary-700 hover:text-primary-800">Ver productos</a>}
            </div>
            {data.low_stock.length ? <div className="mt-4 divide-y divide-border">{data.low_stock.map((product) => <div key={product.id} className="flex items-center gap-3 py-3"><span className="min-w-0 flex-1 truncate font-semibold">{product.name}</span><span className="text-sm text-muted">{product.quantity} uds.</span><span className={`status-badge ${product.status === 'out_of_stock' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'}`}>{product.status === 'out_of_stock' ? 'Sin stock' : 'Bajo stock'}</span></div>)}</div> : <div className="mt-4"><EmptyState title="No hay alertas de stock." /></div>}
        </Card>}
    </>;
}

export function Dashboard({ user, metrics, categorias = [], routes }) {
    const [windowDays, setWindowDays] = useState(7);
    const [createProduct, setCreateProduct] = useState(false);
    const [analytics, setAnalytics] = useState({ state: 'loading', data: null });
    const [attempt, setAttempt] = useState(0);
    const request = useRef(0);
    useEffect(() => {
        const id = ++request.current;
        const controller = new AbortController();
        setAnalytics({ state: 'loading', data: null });
        api.get(`${routes.dashboardAnalytics}?window=${windowDays}`, { cache: 'no-store', signal: controller.signal })
            .then((data) => { if (request.current === id) setAnalytics({ state: 'ready', data }); })
            .catch(() => { if (request.current === id && !controller.signal.aborted) setAnalytics({ state: 'error', data: null }); });
        return () => { request.current++; controller.abort(); };
    }, [windowDays, attempt]);
    const loadAnalytics = () => { request.current++; setAnalytics({ state: 'loading', data: null }); setAttempt((value) => value + 1); };
    const actions = user.role === 'ADMIN' ? [['Nueva Venta', routes.ventasPos], ['Nuevo Producto', routes.productosCreate], ['Nueva Categoría', routes.categoriasCreate], ['Nuevo Usuario', routes.usersCreate]] : user.role === 'Ventas' ? [['Nueva Venta', routes.ventasPos]] : user.role === 'Control Stock' ? [['Nuevo Producto', routes.productosCreate], ['Nueva Categoría', routes.categoriasCreate]] : [];
    const summary = analytics.data?.summary;
    return <div className="space-y-6"><div><p className="text-xs font-semibold uppercase tracking-widest text-muted">Resumen</p><h1 className="page-title mt-2">Panel de control</h1><p className="mt-2 text-sm text-muted">Visualiza el estado general de tu negocio.</p></div>
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><KpiCard icon={CircleDollarSign} label="Ventas hoy" value={summary ? currency.format(summary.sales_today.total) : 'No disponible'} detail={summary ? `${summary.sales_today.count} ventas` : 'No se pudieron cargar los datos'} loading={analytics.state === 'loading'} /><KpiCard icon={ChartNoAxesCombined} label="Ventas del mes" value={summary ? currency.format(summary.sales_month.total) : 'No disponible'} detail={summary ? `${summary.sales_month.count} ventas` : 'No se pudieron cargar los datos'} loading={analytics.state === 'loading'} /><KpiCard icon={Boxes} label="Productos" value={metrics.productosCount} detail={`${metrics.productosActivos} activos · ${metrics.productosAgotados} agotados · ${metrics.productosBajoStock} con bajo stock`} /><KpiCard icon={CircleDollarSign} label="Valor del inventario activo" value={currency.format(Number(metrics.valorTotal))} detail="Precio de venta por unidades disponibles" /></div>
        {user.role ? <Card><h2 className="text-lg font-bold">Acciones rápidas</h2><div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">{actions.map(([label, href], index) => <a key={label} href={href} onClick={(event) => { if (label !== 'Nuevo Producto' || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return; event.preventDefault(); setCreateProduct(true); }} className={`flex min-h-12 items-center justify-between rounded-control border px-4 text-sm font-semibold transition-colors ${index === 0 ? 'border-primary bg-primary text-white hover:bg-primary-700' : 'border-border text-ink hover:bg-surface-muted'}`}>{label}<ArrowUpRight size={18} aria-hidden="true" /></a>)}</div></Card> : <Card><h2 className="text-lg font-bold">Bienvenido a TiendaStock</h2><p className="mt-3 text-sm text-muted">Tu cuenta está siendo configurada. Contacta al administrador del sistema para que te asigne un rol y puedas acceder a las funcionalidades disponibles.</p></Card>}
        <section aria-labelledby="sales-analytics-title"><div className="flex flex-wrap items-center justify-between gap-3"><h2 id="sales-analytics-title" className="text-lg font-bold">Análisis de ventas</h2><label className="flex items-center gap-2 text-sm font-semibold text-muted">Período<select className="form-control h-11 px-3 text-ink" value={windowDays} onChange={(event) => { request.current++; setAnalytics({ state: 'loading', data: null }); setWindowDays(Number(event.target.value)); }}><option value={1}>Hoy</option><option value={7}>Últimos 7 días</option><option value={30}>Últimos 30 días</option></select></label></div><div className="mt-4" aria-live="polite"><AnalyticsContent analytics={analytics} onRetry={loadAnalytics} routes={routes} /></div></section>
        {createProduct && <ProductForm routes={routes} categorias={categorias} modal onClose={() => setCreateProduct(false)} onSaved={() => window.location.reload()} />}
    </div>;
}
