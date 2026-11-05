import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

export function Dropdown({ label, children, portal = false, ariaLabel }) {
    const [open, setOpen] = useState(false);
    const ref = useRef(null);
    const triggerRef = useRef(null);
    const panelRef = useRef(null);
    const [position, setPosition] = useState(null);
    useLayoutEffect(() => {
        if (!open || !portal) return undefined;
        const update = () => {
            const trigger = triggerRef.current.getBoundingClientRect();
            const panel = panelRef.current?.getBoundingClientRect();
            const top = trigger.bottom + 8 + (panel && trigger.bottom + 8 + panel.height > window.innerHeight ? -panel.height - trigger.height - 16 : 0);
            setPosition({ top: Math.max(8, top), right: Math.max(8, window.innerWidth - trigger.right) });
        };
        update();
        window.addEventListener('resize', update);
        window.addEventListener('scroll', update, true);
        return () => { window.removeEventListener('resize', update); window.removeEventListener('scroll', update, true); };
    }, [open, portal]);
    useEffect(() => {
        if (!open) return undefined;
        const onKeyDown = (event) => { if (event.key === 'Escape') { setOpen(false); triggerRef.current?.focus(); } };
        const onClickOutside = (event) => { if (!ref.current?.contains(event.target) && !panelRef.current?.contains(event.target)) setOpen(false); };
        document.addEventListener('keydown', onKeyDown);
        document.addEventListener('mousedown', onClickOutside);
        return () => { document.removeEventListener('keydown', onKeyDown); document.removeEventListener('mousedown', onClickOutside); };
    }, [open]);
    const closeOnBlur = (event) => { if (!ref.current?.contains(event.relatedTarget) && !panelRef.current?.contains(event.relatedTarget)) setOpen(false); };
    const panel = open && <div ref={panelRef} onBlur={closeOnBlur} style={portal ? { position: 'fixed', top: position?.top ?? 0, right: position?.right ?? 8 } : undefined} className={`${portal ? 'z-[60]' : 'absolute right-0 z-20 mt-2'} min-w-56 rounded-card border border-border bg-surface p-2 shadow-subtle`}>{children}</div>;
    return <div ref={ref} className="relative" onBlur={closeOnBlur}><button ref={triggerRef} type="button" className="min-h-11 rounded-control px-3 text-left" aria-label={ariaLabel} aria-expanded={open} onClick={() => setOpen(!open)}>{label}</button>{panel && (portal ? createPortal(panel, document.body) : panel)}</div>;
}
