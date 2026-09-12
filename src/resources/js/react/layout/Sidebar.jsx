import { BarChart3, Boxes, FolderKanban, LayoutDashboard, ShoppingCart, Users, X } from 'lucide-react';
import { Button } from '../components/ui/Button.jsx';

const groups = [
    { label: 'Workspace', items: [{ key: 'dashboard', label: 'Dashboard', icon: LayoutDashboard, roles: ['ADMIN', 'Ventas', 'Control Stock', null] }] },
    { label: 'Operación', items: [{ key: 'ventas', label: 'Ventas', icon: ShoppingCart, roles: ['ADMIN', 'Ventas'] }, { key: 'productos', label: 'Productos', icon: Boxes, roles: ['ADMIN', 'Control Stock'] }, { key: 'categorias', label: 'Categorías', icon: FolderKanban, roles: ['ADMIN', 'Control Stock'] }] },
    { label: 'Administración', items: [{ key: 'users', label: 'Usuarios', icon: Users, roles: ['ADMIN'] }] },
];

export function Sidebar({ user, routes, open, onClose, mobileClosed }) {
    const visible = groups.map((group) => ({ ...group, items: group.items.filter((item) => item.roles.includes(user.role)) })).filter((group) => group.items.length);
    return <aside aria-label="Main navigation" aria-hidden={mobileClosed || undefined} className={`fixed inset-y-0 left-0 z-40 w-72 transform bg-ink text-white transition-[transform,width] duration-200 ${open ? 'translate-x-0' : '-translate-x-full'} md:static md:translate-x-0 ${open ? 'md:w-72' : 'md:w-20'} lg:w-72`}>
        <div className="flex h-full flex-col"><div className="flex min-h-16 items-center justify-between border-b border-white/10 px-5 md:px-4 lg:px-5"><strong className={`text-lg tracking-tight ${open ? 'md:not-sr-only' : 'md:sr-only'} lg:not-sr-only`}>TiendaStock</strong><Button variant="icon" className="text-white md:hidden" onClick={onClose} aria-label="Close navigation" tabIndex={mobileClosed ? -1 : undefined}><X size={22} /></Button></div>
            <nav className="flex-1 space-y-6 overflow-y-auto px-3 py-6">{visible.map((group) => <div key={group.label}><p className={`px-3 text-xs font-semibold uppercase tracking-widest text-white/50 ${open ? 'md:not-sr-only' : 'md:sr-only'} lg:not-sr-only`}>{group.label}</p><div className="mt-2 space-y-1">{group.items.map(({ key, label, icon: Icon }) => <a key={key} href={routes[key]} aria-label={label} tabIndex={mobileClosed ? -1 : undefined} className={`flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold text-white/75 transition-colors hover:bg-white/10 hover:text-white ${open ? 'md:justify-start' : 'md:justify-center'} lg:justify-start ${key === 'dashboard' ? 'bg-white/10 text-white' : ''}`}><Icon size={19} aria-hidden="true" /><span className={open ? 'md:not-sr-only lg:not-sr-only' : 'md:sr-only lg:not-sr-only'}>{label}</span></a>)}</div></div>)}</nav>
            <div className="border-t border-white/10 px-5 py-4 text-sm"><p className={`font-semibold ${open ? 'md:not-sr-only' : 'md:sr-only'} lg:not-sr-only`}>{user.name}</p><p className={`text-white/50 ${open ? 'md:not-sr-only' : 'md:sr-only'} lg:not-sr-only`}>@{user.username}</p></div>
        </div>
    </aside>;
}
