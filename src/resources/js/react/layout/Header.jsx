import { LogOut, Menu, UserCircle } from 'lucide-react';
import { Button } from '../components/ui/Button.jsx';
import { Dropdown } from '../components/ui/Dropdown.jsx';
import { csrf } from '../lib/api.js';

export function Header({ user, onMenu, sidebarOpen }) {
    const role = user.role === 'ADMIN' ? 'Administrador' : user.role;
    return <header className="sticky top-0 z-20 flex min-h-16 items-center justify-between gap-3 border-b border-border bg-surface px-4 sm:px-6">
        <div className="flex items-center gap-2 lg:hidden"><Button variant="icon" onClick={onMenu} aria-label={sidebarOpen ? 'Cerrar navegación' : 'Abrir navegación'} aria-expanded={sidebarOpen} aria-controls="main-navigation"><Menu size={22} /></Button><span className="font-bold tracking-tight">TiendaStock</span></div>
        <div className="ml-auto"><Dropdown label={<span className="flex items-center gap-2"><UserCircle size={22} aria-hidden="true" /><span className="max-w-[12rem] truncate text-sm font-semibold text-ink">{user.name}</span></span>}>
            <div className="border-b border-border px-3 pb-3" role="presentation"><p className="truncate text-sm font-semibold text-ink">{user.name}</p><p className="mt-1 text-xs text-muted">@{user.username}</p><p className="mt-1 text-xs text-muted">{role}</p></div>
            <a href="/profile" role="menuitem" className="mt-2 flex min-h-11 items-center rounded-control px-3 text-sm font-semibold text-ink hover:bg-surface-muted">Mi perfil</a>
            <form action="/logout" method="POST" className="mt-2"><input type="hidden" name="_token" value={csrf() || ''} /><button type="submit" role="menuitem" aria-label="Cerrar sesión" className="flex min-h-11 w-full items-center gap-2 rounded-control px-3 text-left text-sm font-semibold text-red-700 hover:bg-red-50"><LogOut size={17} aria-hidden="true" />Cerrar sesión</button></form>
        </Dropdown></div>
    </header>;
}
