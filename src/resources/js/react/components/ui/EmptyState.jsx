export function EmptyState({ title, description }) {
    return <div className="rounded-card border border-dashed border-border p-8 text-center"><h3 className="font-bold">{title}</h3>{description && <p className="mt-2 text-sm text-muted">{description}</p>}</div>;
}
