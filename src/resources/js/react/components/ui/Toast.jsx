export function Toast({ type = 'info', message, onClose }) {
    if (!message) return null;
    return <div className="fixed right-4 top-4 z-[100] flex max-w-sm items-center gap-3 rounded-card border border-border bg-surface p-4 shadow-subtle" role="status" aria-live="polite"><span className="flex-1 text-sm">{message}</span><button className="min-h-11 min-w-11" onClick={onClose} aria-label="Close notification">×</button></div>;
}
