import { cn } from '../../lib/utils.js';

const tones = { neutral: 'bg-surface-muted text-muted', success: 'bg-green-100 text-green-800', warning: 'bg-amber-100 text-amber-800', danger: 'bg-red-100 text-red-800', info: 'bg-sky-100 text-sky-800' };

export function Badge({ tone = 'neutral', className, ...props }) {
    return <span className={cn('inline-flex min-h-7 items-center rounded-full px-3 text-xs font-semibold', tones[tone], className)} {...props} />;
}
