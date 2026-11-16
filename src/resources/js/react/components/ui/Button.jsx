import { cn } from '../../lib/utils.js';

const variants = {
    primary: 'bg-primary text-white hover:bg-primary-700',
    secondary: 'border border-border bg-surface text-ink hover:bg-surface-muted',
    danger: 'bg-red-600 text-white hover:bg-red-700',
    // Text-only destructive row action (delete / deactivate / cancel) readable in both themes.
    'danger-ghost': 'text-red-600 hover:bg-red-50',
    ghost: 'text-ink hover:bg-surface-muted',
    icon: 'text-muted hover:bg-surface-muted',
};

const base = 'inline-flex min-h-11 min-w-11 items-center justify-center gap-2 rounded-control px-4 text-sm font-semibold transition-colors duration-200 disabled:cursor-not-allowed disabled:opacity-50';

/** Classes for elements that must look like a Button but are not one (e.g. a form's submit inside custom markup). */
export function buttonClasses(variant = 'primary', className) {
    return cn(base, variants[variant], className);
}

/** Renders an <a> when `href` is given, so link-buttons share the same variants. */
export function Button({ className, variant = 'primary', href, ...props }) {
    return href
        ? <a href={href} className={buttonClasses(variant, className)} {...props} />
        : <button className={buttonClasses(variant, className)} {...props} />;
}
