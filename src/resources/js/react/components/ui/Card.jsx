import { cn } from '../../lib/utils.js';

export function Card({ className, ...props }) {
    return <section className={cn('rounded-card border border-border bg-surface p-4 shadow-subtle sm:p-6', className)} {...props} />;
}
