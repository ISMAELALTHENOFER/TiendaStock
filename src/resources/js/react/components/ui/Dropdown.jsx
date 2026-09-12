import { useEffect, useRef, useState } from 'react';

export function Dropdown({ label, children }) {
    const [open, setOpen] = useState(false);
    const ref = useRef(null);
    const triggerRef = useRef(null);
    useEffect(() => {
        if (!open) return undefined;
        const onKeyDown = (event) => { if (event.key === 'Escape') { setOpen(false); triggerRef.current?.focus(); } };
        const onClickOutside = (event) => { if (ref.current && !ref.current.contains(event.target)) setOpen(false); };
        document.addEventListener('keydown', onKeyDown);
        document.addEventListener('mousedown', onClickOutside);
        return () => { document.removeEventListener('keydown', onKeyDown); document.removeEventListener('mousedown', onClickOutside); };
    }, [open]);
    return <div ref={ref} className="relative"><button ref={triggerRef} type="button" className="min-h-11 rounded-control px-3 text-left" aria-expanded={open} aria-haspopup="menu" onClick={() => setOpen(!open)}>{label}</button>{open && <div className="absolute right-0 z-20 mt-2 min-w-56 rounded-card border border-border bg-surface p-2 shadow-subtle" role="menu">{children}</div>}</div>;
}
