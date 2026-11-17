import { cn } from '../../lib/utils.js';

const tones = { neutral: 'bg-surface-muted text-muted', success: 'bg-green-100 text-green-800', warning: 'bg-amber-100 text-amber-800', danger: 'bg-red-100 text-red-800', info: 'bg-sky-100 text-sky-800' };

// Dark counterparts of the semantic tones (darkMode: 'class').
const darkTones = {
    success: 'dark:bg-primary-900 dark:text-primary-300',
    warning: 'dark:bg-amber-950 dark:text-amber-200',
    danger: 'dark:bg-red-950 dark:text-red-200',
    info: 'dark:bg-sky-800 dark:text-sky-100',
};

export function Badge({ tone = 'neutral', className, ...props }) {
    return <span className={cn('inline-flex min-h-7 items-center rounded-full px-3 text-xs font-semibold', tones[tone], darkTones[tone], className)} {...props} />;
}
