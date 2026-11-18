import { cn } from '../../lib/utils.js';

const SIZES = {
    sm: { icon: 'h-4 w-4', text: 'text-sm' },
    md: { icon: 'h-8 w-8', text: 'text-sm' },
    lg: { icon: 'h-12 w-12', text: 'text-base' },
};

const VARIANTS = {
    card: 'flex-col rounded-card border border-border bg-surface p-8 text-center shadow-subtle',
    inline: 'flex-row',
    overlay: 'fixed inset-0 z-[90] flex-col justify-center bg-ink/40 p-4 text-center backdrop-blur-sm dark:bg-black/60',
};

export function Spinner({ className }) {
    return (
        <svg className={cn('motion-safe:animate-spin text-primary dark:text-primary-300', className)} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" className="opacity-20" />
            <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
        </svg>
    );
}

/**
 * Shared loader. variant: "card" (default, block placeholder), "inline" (next to text or inside a button)
 * or "overlay" (blocks the whole screen during a save). size: "sm" | "md" | "lg".
 */
export function LoadingState({ label = 'Cargando…', description, variant = 'card', size = variant === 'inline' ? 'sm' : 'md', className }) {
    const { icon, text } = SIZES[size] ?? SIZES.md;
    const content = (
        <>
            <Spinner className={cn('shrink-0', icon)} />
            {(label || description) && (
                <span className="min-w-0">
                    {label && <span className={cn('block font-semibold text-ink', text, variant === 'inline' && 'font-medium text-muted')}>{label}</span>}
                    {description && <span className="mt-1 block text-sm text-muted">{description}</span>}
                </span>
            )}
        </>
    );

    return (
        <div className={cn('flex items-center gap-3', VARIANTS[variant] ?? VARIANTS.card, className)} role="status" aria-live="polite" aria-busy="true">
            {variant === 'overlay'
                ? <div className="flex flex-col items-center gap-3 rounded-card bg-surface px-8 py-6 shadow-subtle">{content}</div>
                : content}
        </div>
    );
}
