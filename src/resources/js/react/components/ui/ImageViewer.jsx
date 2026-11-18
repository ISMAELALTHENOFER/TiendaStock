import { useState } from 'react';
import { Modal } from './Modal.jsx';

const LEVELS = [1, 2, 3];

/** Clickable thumbnail that opens the full image in a modal with zoom (click or +/−) and scroll panning. */
export function ImageViewer({ src, alt, className, width, height }) {
    const [open, setOpen] = useState(false);
    const [level, setLevel] = useState(0);
    const close = () => { setOpen(false); setLevel(0); };
    const zoom = LEVELS[level];
    const zoomButton = 'min-h-11 min-w-11 rounded-control border border-border px-3 text-lg font-semibold text-ink disabled:opacity-40';

    return (
        <>
            <button type="button" onClick={() => setOpen(true)} className="shrink-0 cursor-zoom-in rounded-lg" aria-label={`Ampliar imagen de ${alt}`}>
                <img src={src} alt={alt} width={width} height={height} loading="lazy" className={className} />
            </button>
            <Modal open={open} title={alt} onClose={close} size="image">
                <div className="mt-2 flex items-center justify-end gap-2">
                    <button type="button" className={zoomButton} onClick={() => setLevel((l) => l - 1)} disabled={level === 0} aria-label="Alejar">−</button>
                    <span className="w-12 text-center text-sm tabular-nums text-muted">{zoom * 100}%</span>
                    <button type="button" className={zoomButton} onClick={() => setLevel((l) => l + 1)} disabled={level === LEVELS.length - 1} aria-label="Acercar">+</button>
                </div>
                <div className="mt-3 max-h-[75dvh] overflow-auto rounded-lg bg-surface-muted">
                    <img src={src} alt={alt} onClick={() => setLevel((l) => (l + 1) % LEVELS.length)} style={{ width: `${zoom * 100}%`, maxWidth: 'none' }} className={`mx-auto block h-auto ${zoom === 3 ? 'cursor-zoom-out' : 'cursor-zoom-in'}`} />
                </div>
            </Modal>
        </>
    );
}
