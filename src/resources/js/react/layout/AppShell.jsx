import { useEffect, useRef, useState } from 'react';
import { Header } from './Header.jsx';
import { Sidebar } from './Sidebar.jsx';

export function AppShell({ user, routes, activePage, children }) {
    const [open, setOpen] = useState(false);
    const [collapsed, setCollapsed] = useState(false);
    const [isMobile, setIsMobile] = useState(() => window.matchMedia('(max-width: 63.999rem)').matches);
    const previousFocus = useRef(null);
    const drawerRef = useRef(null);
    useEffect(() => {
        const media = window.matchMedia('(max-width: 63.999rem)');
        const update = () => setIsMobile(media.matches);
        update();
        media.addEventListener('change', update);
        return () => media.removeEventListener('change', update);
    }, []);
    useEffect(() => {
        if (!open || !isMobile) return undefined;
        previousFocus.current = document.activeElement;
        document.body.classList.add('overflow-hidden');
        [...drawerRef.current.querySelectorAll('a,button')].find((element) => element.getClientRects().length)?.focus();
        const onKeyDown = (event) => {
            if (event.key === 'Escape') setOpen(false);
            if (event.key !== 'Tab') return;
            const focusables = [...drawerRef.current.querySelectorAll('a,button')].filter((element) => element.tabIndex !== -1 && element.getClientRects().length);
            const index = focusables.indexOf(document.activeElement);
            const next = event.shiftKey ? (index <= 0 ? focusables.length - 1 : index - 1) : (index + 1) % focusables.length;
            event.preventDefault(); focusables[next]?.focus();
        };
        document.addEventListener('keydown', onKeyDown);
        return () => { document.body.classList.remove('overflow-hidden'); document.removeEventListener('keydown', onKeyDown); previousFocus.current?.focus?.(); };
    }, [open, isMobile]);
    const mobileClosed = isMobile && !open;
    return <div className={`min-h-dvh bg-canvas transition-[padding-left] duration-200 ${collapsed ? 'lg:pl-[4.5rem]' : 'lg:pl-72'}`}><div ref={drawerRef} inert={mobileClosed}><Sidebar user={user} routes={routes} activePage={activePage} open={open} onClose={() => setOpen(false)} mobileClosed={mobileClosed} collapsed={collapsed} onToggleCollapse={() => setCollapsed((current) => !current)} /></div>{open && isMobile && <button className="fixed inset-0 z-30 bg-ink/50" onClick={() => setOpen(false)} aria-label="Cerrar navegación" />}
        <div className="min-w-0 min-h-dvh"><Header user={user} sidebarOpen={open} onMenu={() => setOpen((current) => !current)} /><main className="mx-auto w-full max-w-[1600px] p-4 sm:p-6 lg:p-8">{children}</main></div>
    </div>;
}
