import { useEffect, useId, useRef } from 'react';

const modalStack = [];
const focusableSelector = 'button:not([disabled]):not([hidden]), input:not([type="hidden"]):not([disabled]):not([hidden]), select:not([disabled]):not([hidden]), textarea:not([disabled]):not([hidden]), a[href]:not([hidden]), [tabindex]:not([tabindex="-1"]):not([hidden]):not(input[type="hidden"]):not([disabled])';

export function Modal({ open, title, onClose, children, size = 'default', hideHeader = false }) {
    const closeRef = useRef(null);
    const dialogRef = useRef(null);
    const onCloseRef = useRef(onClose);
    const titleId = useId();
    onCloseRef.current = onClose;
    useEffect(() => {
        if (!open) return undefined;
        const modal = { close: () => onCloseRef.current() };
        modalStack.push(modal);
        const previous = document.activeElement;
        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        (closeRef.current || dialogRef.current?.querySelector(focusableSelector))?.focus();
        const onKeyDown = (event) => {
            if (modalStack[modalStack.length - 1] !== modal) return;
            if (event.key === 'Escape') {
                if (!event.defaultPrevented) {
                    event.preventDefault();
                    modal.close();
                }
                return;
            }
            if (event.key !== 'Tab') return;
            const focusables = [...dialogRef.current.querySelectorAll(focusableSelector)].filter((element) => element.getClientRects().length);
            if (!focusables.length) return;
            const index = focusables.indexOf(document.activeElement);
            const next = event.shiftKey ? (index <= 0 ? focusables.length - 1 : index - 1) : (index + 1) % focusables.length;
            event.preventDefault();
            focusables[next]?.focus();
        };
        document.addEventListener('keydown', onKeyDown);
        return () => {
            document.removeEventListener('keydown', onKeyDown);
            modalStack.splice(modalStack.indexOf(modal), 1);
            document.body.style.overflow = previousOverflow;
            previous?.focus?.();
        };
    }, [open]);
    if (!open) return null;
    return <div className={`fixed inset-0 z-50 grid place-items-center overflow-y-auto p-2 sm:p-4 ${size === 'product' ? 'bg-ink/60 backdrop-blur-sm' : 'bg-ink/50'}`} role="presentation" onMouseDown={(event) => { if (event.target === event.currentTarget) onClose(); }}>
        <section ref={dialogRef} tabIndex={-1} className={`my-auto w-full min-w-0 max-h-[calc(100dvh-1rem)] overflow-y-auto ${size === 'product' ? 'max-w-[620px] p-0 sm:max-h-[calc(100dvh-2rem)]' : 'max-w-lg p-4 sm:p-6'} rounded-card bg-surface shadow-subtle`} role="dialog" aria-modal="true" aria-label={hideHeader ? title : undefined} aria-labelledby={hideHeader ? undefined : titleId} onMouseDown={(event) => event.stopPropagation()}>
            {!hideHeader && <div className={`flex items-start justify-between gap-4 ${size === 'product' ? 'px-5 pt-4 sm:px-7 sm:pt-5' : ''}`}><h2 id={titleId} className="text-lg font-bold">{title}</h2><button ref={closeRef} className="min-h-11 min-w-11" onClick={onClose} aria-label="Cerrar">×</button></div>}
            {children}
        </section>
    </div>;
}
