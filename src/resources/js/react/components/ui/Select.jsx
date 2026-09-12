import { cn } from '../../lib/utils.js';

export function Select({ className, children, ...props }) {
    return <select className={cn('min-h-11 w-full rounded-control border border-border bg-surface px-3 text-ink transition-colors duration-200', className)} {...props}>{children}</select>;
}
