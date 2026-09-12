import { useEffect, useRef, useState } from 'react';
import { Header } from './Header.jsx';
import { Sidebar } from './Sidebar.jsx';

export function AppShell({ user, routes, children }) {
    const [open, setOpen] = useState(false);
    const [isMobile, setIsMobile] = useState(() => window.matchMedia('(max-width: 47.999rem)').matches);
    const previousFocus = useRef(null);
    const drawerRef = useRef(null);
    useEffect(() => {
        const media = window.matchMedia('(max-width: 47.999rem)');
        const update = () => setIsMobile(media.matches);
        update();
        media.addEventListener('change', update);
        return () => media.removeEventListener('change', update);
    }, []);
    useEffect(() => {
        if (!open || !isMobile) return undefined;
        previousFocus.current = document.activeElement;
        document.body.classList.add('overflow-hidden');
        drawerRef.current?.querySelector('a,button')?.focus();
        const onKeyDown = (event) => {
            if (event.key === 'Escape') setOpen(false);
            if (event.key !== 'Tab') return;
            const focusables = [...drawerRef.current.querySelectorAll('a,button')].filter((element) => element.tabIndex !== -1);
            const index = focusables.indexOf(document.activeElement);
            const next = event.shiftKey ? (index <= 0 ? focusables.length - 1 : index - 1) : (index + 1) % focusables.length;
            event.preventDefault(); focusables[next]?.focus();
        };
        document.addEventListener('keydown', onKeyDown);
        return () => { document.body.classList.remove('overflow-hidden'); document.removeEventListener('keydown', onKeyDown); previousFocus.current?.focus?.(); };
    }, [open, isMobile]);
    const mobileClosed = isMobile && !open;
    return <div className="min-h-dvh bg-canvas lg:flex"><div ref={drawerRef} inert={mobileClosed}><Sidebar user={user} routes={routes} open={open} onClose={() => setOpen(false)} mobileClosed={mobileClosed} /></div>{open && isMobile && <button className="fixed inset-0 z-30 bg-ink/50" onClick={() => setOpen(false)} aria-label="Close navigation backdrop" />}
        <div className="min-w-0 flex-1"><Header user={user} sidebarOpen={open} onMenu={() => setOpen((current) => !current)} /><main className="mx-auto w-full max-w-[1440px] p-4 sm:p-6 lg:p-8">{children}</main></div>
    </div>;
}
