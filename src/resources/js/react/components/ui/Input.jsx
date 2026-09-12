import { cn } from '../../lib/utils.js';

export function Input({ className, error, ...props }) {
    return <input aria-invalid={Boolean(error)} className={cn('min-h-11 w-full rounded-control border bg-surface px-3 text-ink transition-colors duration-200 placeholder:text-muted', error ? 'border-red-600' : 'border-border', className)} {...props} />;
}
