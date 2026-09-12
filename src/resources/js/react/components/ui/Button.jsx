import { cn } from '../../lib/utils.js';

const variants = {
    primary: 'bg-primary text-white hover:bg-primary-700',
    secondary: 'border border-border bg-surface text-ink hover:bg-surface-muted',
    danger: 'bg-red-600 text-white hover:bg-red-700',
    ghost: 'text-ink hover:bg-surface-muted',
    icon: 'text-muted hover:bg-surface-muted',
};

export function Button({ className, variant = 'primary', ...props }) {
    return <button className={cn('inline-flex min-h-11 min-w-11 items-center justify-center gap-2 rounded-control px-4 text-sm font-semibold transition-colors duration-200 disabled:cursor-not-allowed disabled:opacity-50', variants[variant], className)} {...props} />;
}
