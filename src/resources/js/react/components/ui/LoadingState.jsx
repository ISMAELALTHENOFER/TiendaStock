export function LoadingState({ label = 'Loading…' }) {
    return <div className="rounded-card border border-border bg-surface p-8 text-center text-sm text-muted" role="status" aria-live="polite">{label}</div>;
}
