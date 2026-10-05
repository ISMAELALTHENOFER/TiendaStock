import { useEffect, useRef } from 'react';

export function Modal({ open, title, onClose, children, size = 'default', hideHeader = false }) {
    const closeRef = useRef(null);
    const dialogRef = useRef(null);
    useEffect(() => {
        if (!open) return undefined;
        const previous = document.activeElement;
        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        (closeRef.current || dialogRef.current?.querySelector('button, input, select, textarea, a'))?.focus();
        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                if (!event.defaultPrevented) {
                    event.preventDefault();
                    onClose();
                }
                return;
            }
            if (event.key !== 'Tab') return;
            const focusables = [...dialogRef.current.querySelectorAll('button, input, select, textarea, a')];
            const index = focusables.indexOf(document.activeElement);
            const next = event.shiftKey ? (index <= 0 ? focusables.length - 1 : index - 1) : (index + 1) % focusables.length;
            event.preventDefault();
            focusables[next]?.focus();
        };
        document.addEventListener('keydown', onKeyDown);
        return () => {
            document.removeEventListener('keydown', onKeyDown);
            document.body.style.overflow = previousOverflow;
            previous?.focus?.();
        };
    }, [open, onClose]);
    if (!open) return null;
    return <div className={`fixed inset-0 z-50 grid place-items-center overflow-y-auto p-4 ${size === 'product' ? 'bg-ink/60 backdrop-blur-sm' : 'bg-ink/50'}`} role="presentation" onMouseDown={(event) => { if (event.target === event.currentTarget) onClose(); }}>
        <section ref={dialogRef} tabIndex={-1} className={`my-auto w-full ${size === 'product' ? 'max-h-[calc(100dvh-2rem)] max-w-[620px] overflow-hidden p-0' : 'max-w-lg p-6'} rounded-card bg-surface shadow-subtle`} role="dialog" aria-modal="true" aria-label={hideHeader ? title : undefined} aria-labelledby={hideHeader ? undefined : 'modal-title'} onMouseDown={(event) => event.stopPropagation()}>
            {!hideHeader && <div className="flex items-start justify-between gap-4"><h2 id="modal-title" className="text-lg font-bold">{title}</h2><button ref={closeRef} className="min-h-11 min-w-11" onClick={onClose} aria-label="Close">×</button></div>}
            {children}
        </section>
    </div>;
}
