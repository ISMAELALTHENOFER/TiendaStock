import { AlertCircle } from 'lucide-react';
import { Button } from './Button.jsx';

export function ErrorState({ title = 'No se pudo cargar la información.', description = 'Intentá nuevamente.', onRetry }) {
    return <div className="rounded-card border border-red-200 bg-red-50 p-8 text-center text-red-900">
        <AlertCircle className="mx-auto" size={24} aria-hidden="true" />
        <h3 className="mt-3 font-bold">{title}</h3>
        <p className="mt-2 text-sm text-red-800">{description}</p>
        {onRetry && <Button variant="secondary" className="mt-4" onClick={onRetry}>Reintentar</Button>}
    </div>;
}
