import { useEffect, useRef } from 'react';

export function Modal({ open, title, onClose, children }) {
    const closeRef = useRef(null);
    useEffect(() => {
        if (!open) return undefined;
        const previous = document.activeElement;
        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        closeRef.current?.focus();
        const onKeyDown = (event) => {
            if (event.key === 'Escape') onClose();
            if (event.key !== 'Tab') return;
            const focusables = [...document.querySelectorAll('[role="dialog"] button, [role="dialog"] input, [role="dialog"] select, [role="dialog"] textarea, [role="dialog"] a')];
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
    return <div className="fixed inset-0 z-50 grid place-items-center bg-ink/50 p-4" role="presentation" onMouseDown={onClose}>
        <section className="w-full max-w-lg rounded-card bg-surface p-6 shadow-subtle" role="dialog" aria-modal="true" aria-labelledby="modal-title" onMouseDown={(event) => event.stopPropagation()}>
            <div className="flex items-start justify-between gap-4"><h2 id="modal-title" className="text-lg font-bold">{title}</h2><button ref={closeRef} className="min-h-11 min-w-11" onClick={onClose} aria-label="Close">×</button></div>
            {children}
        </section>
    </div>;
}
