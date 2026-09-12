import { useCallback, useEffect, useState } from 'react';
import { ArrowUpRight, RefreshCw } from 'lucide-react';
import { api } from './lib/api.js';
import { Button } from './components/ui/Button.jsx';
import { Card } from './components/ui/Card.jsx';
import { EmptyState } from './components/ui/EmptyState.jsx';
import { ErrorState } from './components/ui/ErrorState.jsx';
import { LoadingState } from './components/ui/LoadingState.jsx';

function ResourceState({ state, emptyTitle, children, onRetry }) {
    if (state === 'loading') return <LoadingState />;
    if (state === 'error') return <ErrorState onRetry={onRetry} />;
    if (state === 'empty') return <EmptyState title={emptyTitle} />;
    return children;
}

const currency = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });
const date = new Intl.DateTimeFormat('es-AR', { dateStyle: 'medium' });
const dateTime = new Intl.DateTimeFormat('es-AR', { dateStyle: 'medium', timeStyle: 'short' });

function formatDate(value) {
    const parsed = new Date(`${value}T00:00:00`);
    return Number.isNaN(parsed.getTime()) ? 'Fecha no disponible' : date.format(parsed);
}

function formatDateTime(value) {
    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime()) ? 'Fecha no disponible' : dateTime.format(parsed);
}

export function Dashboard({ user, metrics, routes }) {
    const [activity, setActivity] = useState({ state: 'loading', data: [] });
    const [analytics, setAnalytics] = useState({ state: 'loading', data: null });
    const loadActivity = useCallback(() => { setActivity({ state: 'loading', data: [] }); api.get('/dashboard/activity', { cache: 'no-store' }).then((data) => setActivity({ state: data.data.length ? 'ready' : 'empty', data: data.data })).catch(() => setActivity({ state: 'error', data: [] })); }, []);
    const loadAnalytics = useCallback(() => { setAnalytics({ state: 'loading', data: null }); api.get('/dashboard/analytics?window=30', { cache: 'no-store' }).then((data) => { const hasData = data.series.sales_by_day.length || data.series.sales_by_category.length; setAnalytics({ state: hasData ? 'ready' : 'empty', data }); }).catch(() => setAnalytics({ state: 'error', data: null })); }, []);
    useEffect(() => { loadActivity(); loadAnalytics(); }, [loadActivity, loadAnalytics]);
    const actions = user.role === 'ADMIN' ? [['Nueva Venta', routes.ventasPos], ['Nuevo Producto', routes.productosCreate], ['Nueva Categoría', routes.categoriasCreate], ['Nuevo Usuario', routes.usersCreate]] : user.role === 'Ventas' ? [['Nueva Venta', routes.ventasPos]] : user.role === 'Control Stock' ? [['Nuevo Producto', routes.productosCreate], ['Nueva Categoría', routes.categoriasCreate]] : [];
    const cards = [['Total Productos', metrics.productosCount], ['Categorías', metrics.categoriasCount], ['Productos Activos', metrics.productosActivos], ['Valor Total', `$${Number(metrics.valorTotal).toLocaleString('es-AR', { minimumFractionDigits: 2 })}`]];
    return <div className="space-y-6"><div><p className="eyebrow">Overview</p><h1 className="page-title mt-2">Dashboard</h1><p className="mt-2 text-sm text-muted">Visualiza el estado general de tu inventario.</p></div>
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">{cards.map(([label, value]) => <Card key={label}><p className="text-sm font-medium text-muted">{label}</p><p className="mt-3 text-3xl font-bold tracking-tight">{value}</p></Card>)}</div>
        {user.role ? <Card><h2 className="text-lg font-bold">Acciones rápidas</h2><div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">{actions.map(([label, href], index) => <a key={label} href={href} className={`flex min-h-16 items-center justify-between rounded-control border p-4 text-sm font-semibold transition-colors hover:bg-surface-muted ${index === 0 ? 'border-primary bg-primary text-white hover:bg-primary-700' : 'border-border text-ink'}`}>{label}<ArrowUpRight size={18} /></a>)}</div></Card> : <Card><h2 className="text-lg font-bold">Bienvenido a TiendaStock</h2><p className="mt-3 text-sm text-muted">Tu cuenta está siendo configurada. Contactá al administrador del sistema para que te asigne un rol y puedas acceder a las funcionalidades disponibles.</p></Card>}
         <div className="grid gap-6 xl:grid-cols-2"><Card><div className="flex items-center justify-between"><h2 className="text-lg font-bold">Actividad reciente</h2><Button variant="icon" onClick={loadActivity} aria-label="Refresh activity"><RefreshCw size={18} /></Button></div><div className="mt-4"><ResourceState state={activity.state} emptyTitle="No hay actividad reciente." onRetry={loadActivity}>{activity.data.map((event) => <div key={event.id} className="border-b border-border py-3 last:border-0"><p className="font-semibold">{event.title}</p><p className="text-sm text-muted">{event.description}</p><time className="text-xs text-muted" dateTime={event.occurred_at}>{formatDateTime(event.occurred_at)}</time></div>)}</ResourceState></div></Card>
             <Card><h2 className="text-lg font-bold">Analítica de ventas</h2><div className="mt-4"><ResourceState state={analytics.state} emptyTitle="No hay ventas completadas en este período." onRetry={loadAnalytics}>{analytics.data && <div className="space-y-6 text-sm"><section><h3 className="font-semibold">Ventas por fecha</h3><ul className="mt-2 divide-y divide-border">{analytics.data.series.sales_by_day.map((item) => <li key={item.date} className="flex items-center justify-between gap-4 py-2"><span>{formatDate(item.date)}</span><span className="text-right font-semibold">{currency.format(Number(item.total))}<span className="ml-2 font-normal text-muted">({item.count} ventas)</span></span></li>)}</ul></section><section><h3 className="font-semibold">Ventas por categoría</h3><ul className="mt-2 divide-y divide-border">{analytics.data.series.sales_by_category.map((item) => <li key={item.category} className="flex items-center justify-between gap-4 py-2"><span>{item.category}</span><span className="text-right font-semibold">{currency.format(Number(item.total))}<span className="ml-2 font-normal text-muted">({item.count} ventas)</span></span></li>)}</ul></section></div>}</ResourceState></div></Card></div>
    </div>;
}
