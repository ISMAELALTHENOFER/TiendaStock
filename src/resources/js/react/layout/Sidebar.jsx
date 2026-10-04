import { Boxes, FolderKanban, LayoutDashboard, ShoppingCart, Users, X } from 'lucide-react';
import { Button } from '../components/ui/Button.jsx';

const groups = [
    { label: 'Workspace', items: [{ key: 'dashboard', activePages: ['dashboard'], label: 'Dashboard', icon: LayoutDashboard, roles: ['ADMIN', 'Ventas', 'Control Stock', null] }] },
    { label: 'Operación', items: [{ key: 'ventas', activePages: ['ventas.index', 'ventas.pos', 'ventas.show'], label: 'Ventas', icon: ShoppingCart, roles: ['ADMIN', 'Ventas'] }, { key: 'productos', activePages: ['productos.index', 'productos.create', 'productos.edit'], label: 'Productos', icon: Boxes, roles: ['ADMIN', 'Control Stock'] }, { key: 'categorias', activePages: ['categorias.index', 'categorias.create'], label: 'Categorías', icon: FolderKanban, roles: ['ADMIN', 'Control Stock'] }] },
    { label: 'Administración', items: [{ key: 'users', activePages: ['admin.users.index', 'admin.users.create', 'admin.users.edit'], label: 'Usuarios', icon: Users, roles: ['ADMIN'] }] },
];

export function Sidebar({ user, routes, activePage, open, onClose, mobileClosed }) {
    const visible = groups.map((group) => ({ ...group, items: group.items.filter((item) => item.roles.includes(user.role)) })).filter((group) => group.items.length);
    return <aside id="main-navigation" aria-label="Navegación principal" aria-hidden={mobileClosed || undefined} className={`fixed inset-y-0 left-0 z-40 flex h-dvh min-h-screen w-72 transform flex-col bg-ink text-white transition-transform duration-200 ${open ? 'translate-x-0' : '-translate-x-full'} lg:translate-x-0`}>
        <div className="flex min-h-16 items-center justify-between border-b border-white/10 px-5"><a href={routes.dashboard} className="flex min-h-11 items-center gap-3 font-bold tracking-tight" aria-label="TiendaStock, Dashboard" tabIndex={mobileClosed ? -1 : undefined}><img src="/images/logo.png" alt="" className="h-7 w-7 object-contain" /><span className="text-lg">TiendaStock</span></a><Button variant="icon" className="text-white lg:hidden" onClick={onClose} aria-label="Cerrar navegación" tabIndex={mobileClosed ? -1 : undefined}><X size={22} /></Button></div>
            <nav className="flex-1 space-y-6 overflow-y-auto px-3 py-6">{visible.map((group) => <div key={group.label}><p className="px-3 text-xs font-semibold uppercase tracking-widest text-white/50">{group.label}</p><div className="mt-2 space-y-1">{group.items.map(({ key, activePages, label, icon: Icon }) => { const isActive = activePages.includes(activePage); return <a key={key} href={routes[key]} aria-current={isActive ? 'page' : undefined} tabIndex={mobileClosed ? -1 : undefined} onClick={onClose} className={`flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold text-white/75 transition-colors hover:bg-white/10 hover:text-white ${isActive ? 'bg-white/10 text-white' : ''}`}><Icon size={19} aria-hidden="true" /><span>{label}</span></a>; })}</div></div>)}</nav>
            <div className="mt-auto border-t border-white/10 px-5 py-4 text-sm"><p className="font-semibold">{user.name}</p><p className="text-white/50">@{user.username}</p></div>
    </aside>;
}
