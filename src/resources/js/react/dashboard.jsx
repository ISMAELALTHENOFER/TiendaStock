import { useCallback, useEffect, useState } from 'react';
import { AlertTriangle, ArrowUpRight, Boxes, ChartNoAxesCombined, CircleDollarSign, RefreshCw } from 'lucide-react';
import { api } from './lib/api.js';
import { Button } from './components/ui/Button.jsx';
import { Card } from './components/ui/Card.jsx';
import { EmptyState } from './components/ui/EmptyState.jsx';
import { ErrorState } from './components/ui/ErrorState.jsx';

const currency = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });
const dateTime = new Intl.DateTimeFormat('es-AR', { dateStyle: 'medium', timeStyle: 'short' });

function formatDateTime(value) {
    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime()) ? 'Fecha no disponible' : dateTime.format(parsed);
}

function Skeleton({ className = '' }) {
    return <span className={`block animate-pulse rounded bg-surface-muted ${className}`} aria-hidden="true" />;
}

function KpiCard({ icon: Icon, label, value, detail, loading, tone = 'neutral' }) {
    const toneClass = tone === 'warning' ? 'bg-amber-50 text-amber-700' : 'bg-primary-50 text-primary-700';
    return <Card className="min-w-0 p-4 sm:p-5"><div className="flex items-start justify-between gap-3"><div className="min-w-0"><p className="text-sm font-medium text-muted">{label}</p>{loading ? <Skeleton className="mt-3 h-9 w-28" /> : <p className="mt-2 truncate text-3xl font-bold tracking-tight">{value}</p>}<p className="mt-2 text-sm text-muted">{loading ? <Skeleton className="h-4 w-20" /> : detail}</p></div><span className={`grid h-10 w-10 shrink-0 place-items-center rounded-control ${toneClass}`}><Icon size={20} aria-hidden="true" /></span></div></Card>;
}

function SalesChart({ data }) {
    if (!data.length) return <EmptyState title="Todavía no hay ventas para este período." />;
    const max = Math.max(...data.map((item) => Number(item.total)), 1);
    return <div className="mt-5 overflow-x-auto"><svg viewBox={`0 0 ${Math.max(data.length * 54, 320)} 180`} className="h-48 min-w-[320px] w-full" role="img" aria-label="Ventas por día"><line x1="16" x2="100%" y1="144" y2="144" stroke="currentColor" className="text-border" />{data.map((item, index) => { const height = Math.max((Number(item.total) / max) * 112, 3); const x = 26 + index * 54; return <g key={item.date}><title>{`${item.date}: ${currency.format(Number(item.total))}, ${item.count} ventas`}</title><rect x={x} y={144 - height} width="28" height={height} rx="4" className="fill-primary" /><text x={x + 14} y="165" textAnchor="middle" className="fill-muted text-[10px]">{new Date(`${item.date}T00:00:00`).toLocaleDateString('es-AR', { weekday: 'short' }).replace('.', '')}</text></g>; })}</svg></div>;
}

function AnalyticsContent({ analytics, onRetry, routes }) {
    if (analytics.state === 'loading') return <div className="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(18rem,1fr)]"><Card><Skeleton className="h-5 w-40" /><Skeleton className="mt-6 h-48 w-full" /></Card><Card><Skeleton className="h-5 w-40" /><Skeleton className="mt-6 h-32 w-full" /></Card></div>;
    if (analytics.state === 'error') return <ErrorState title="No pudimos cargar la analítica del dashboard." onRetry={onRetry} />;
    const { data } = analytics;
    return <><div className="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(18rem,1fr)]"><Card><SalesChart data={data.series.sales_by_day} /></Card><Card><h2 className="text-lg font-bold">Productos más vendidos</h2>{data.top_products.length ? <ol className="mt-4 divide-y divide-border">{data.top_products.map((product, index) => <li key={product.id} className="flex items-center gap-3 py-3"><span className="w-5 text-sm font-semibold text-muted">{index + 1}</span><span className="min-w-0 flex-1 truncate font-semibold">{product.name}</span><span className="text-sm text-muted">{product.count} uds.</span></li>)}</ol> : <div className="mt-4"><EmptyState title="Todavía no hay productos vendidos." /></div>}</Card></div><Card><div className="flex flex-wrap items-center justify-between gap-3"><h2 className="text-lg font-bold">Stock que requiere atención</h2>{routes.productos && <a href={routes.productos} className="text-sm font-semibold text-primary-700 hover:text-primary-800">Ver productos</a>}</div>{data.low_stock.length ? <div className="mt-4 divide-y divide-border">{data.low_stock.map((product) => <div key={product.id} className="flex items-center gap-3 py-3"><span className="min-w-0 flex-1 truncate font-semibold">{product.name}</span><span className="text-sm text-muted">{product.quantity} uds.</span><span className={`status-badge ${product.status === 'out_of_stock' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'}`}>{product.status === 'out_of_stock' ? 'Sin stock' : 'Bajo stock'}</span></div>)}</div> : <div className="mt-4"><EmptyState title="No hay alertas de stock." /></div>}</Card></>;
}

function ActivityCard() {
    const [activity, setActivity] = useState({ state: 'loading', data: [] });
    const loadActivity = useCallback(() => { setActivity({ state: 'loading', data: [] }); api.get('/dashboard/activity', { cache: 'no-store' }).then((data) => setActivity({ state: data.data.length ? 'ready' : 'empty', data: data.data.slice(0, 8) })).catch(() => setActivity({ state: 'error', data: [] })); }, []);
    useEffect(() => { loadActivity(); }, [loadActivity]);
    return <Card><div className="flex items-center justify-between gap-3"><h2 className="text-lg font-bold">Actividad reciente</h2><Button variant="icon" onClick={loadActivity} aria-label="Actualizar actividad"><RefreshCw size={18} /></Button></div><div className="mt-4">{activity.state === 'loading' && <div className="space-y-4"><Skeleton className="h-12 w-full" /><Skeleton className="h-12 w-full" /><Skeleton className="h-12 w-full" /></div>}{activity.state === 'error' && <ErrorState title="No pudimos cargar la actividad." onRetry={loadActivity} />}{activity.state === 'empty' && <EmptyState title="Todavía no hay actividad reciente." description="Las ventas y modificaciones realizadas aparecerán acá." />}{activity.state === 'ready' && <div className="divide-y divide-border">{activity.data.map((event) => <article key={event.id} className="py-3 first:pt-0 last:pb-0"><p className="font-semibold">{event.title}</p><p className="mt-1 truncate text-sm text-muted">{event.description}</p><time className="mt-1 block text-xs text-muted" dateTime={event.occurred_at}>{formatDateTime(event.occurred_at)}</time></article>)}</div>}</div></Card>;
}

export function Dashboard({ user, metrics, routes }) {
    const [windowDays, setWindowDays] = useState(7);
    const [analytics, setAnalytics] = useState({ state: 'loading', data: null });
    const loadAnalytics = useCallback(() => { setAnalytics({ state: 'loading', data: null }); api.get(`/dashboard/analytics?window=${windowDays}`, { cache: 'no-store' }).then((data) => setAnalytics({ state: 'ready', data })).catch(() => setAnalytics({ state: 'error', data: null })); }, [windowDays]);
    useEffect(() => { loadAnalytics(); }, [loadAnalytics]);
    const actions = user.role === 'ADMIN' ? [['Nueva Venta', routes.ventasPos], ['Nuevo Producto', routes.productosCreate], ['Nueva Categoría', routes.categoriasCreate], ['Nuevo Usuario', routes.usersCreate]] : user.role === 'Ventas' ? [['Nueva Venta', routes.ventasPos]] : user.role === 'Control Stock' ? [['Nuevo Producto', routes.productosCreate], ['Nueva Categoría', routes.categoriasCreate]] : [];
    const summary = analytics.data?.summary;
    return <div className="space-y-6"><div><p className="text-xs font-semibold uppercase tracking-widest text-muted">Overview</p><h1 className="page-title mt-2">Dashboard</h1><p className="mt-2 text-sm text-muted">Visualizá el estado general de tu negocio.</p></div>
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><KpiCard icon={CircleDollarSign} label="Ventas hoy" value={currency.format(summary?.sales_today.total ?? 0)} detail={`${summary?.sales_today.count ?? 0} ventas`} loading={!summary && analytics.state === 'loading'} /><KpiCard icon={ChartNoAxesCombined} label="Ventas del mes" value={currency.format(summary?.sales_month.total ?? 0)} detail={`${summary?.sales_month.count ?? 0} ventas`} loading={!summary && analytics.state === 'loading'} /><KpiCard icon={Boxes} label="Productos" value={metrics.productosCount} detail={`${metrics.productosActivos} activos`} /><KpiCard icon={AlertTriangle} label="Bajo stock" value={analytics.data?.low_stock.length ?? 0} detail="Requieren atención" loading={!analytics.data && analytics.state === 'loading'} tone="warning" /></div>
        {user.role ? <Card><h2 className="text-lg font-bold">Acciones rápidas</h2><div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">{actions.map(([label, href], index) => <a key={label} href={href} className={`flex min-h-12 items-center justify-between rounded-control border px-4 text-sm font-semibold transition-colors ${index === 0 ? 'border-primary bg-primary text-white hover:bg-primary-700' : 'border-border text-ink hover:bg-surface-muted'}`}>{label}<ArrowUpRight size={18} aria-hidden="true" /></a>)}</div></Card> : <Card><h2 className="text-lg font-bold">Bienvenido a TiendaStock</h2><p className="mt-3 text-sm text-muted">Tu cuenta está siendo configurada. Contactá al administrador del sistema para que te asigne un rol y puedas acceder a las funcionalidades disponibles.</p></Card>}
        <section aria-labelledby="sales-analytics-title"><div className="flex flex-wrap items-center justify-between gap-3"><h2 id="sales-analytics-title" className="text-lg font-bold">Ventas últimos {windowDays} días</h2><label className="flex items-center gap-2 text-sm font-semibold text-muted">Período<select className="form-control h-11 px-3 text-ink" value={windowDays} onChange={(event) => setWindowDays(Number(event.target.value))}><option value={7}>7 días</option><option value={30}>30 días</option></select></label></div><div className="mt-4"><AnalyticsContent analytics={analytics} onRetry={loadAnalytics} routes={routes} /></div></section><ActivityCard />
    </div>;
}
