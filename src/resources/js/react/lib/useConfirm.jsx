import { useState } from 'react';
import { ConfirmDialog } from '../components/ui/ConfirmDialog.jsx';

const SAVE_PROMPT = { title: '¿Guardar cambios?', message: 'Se guardarán los datos ingresados.', confirmLabel: 'Guardar', tone: 'primary' };

/**
 * Gates an action behind the shared ConfirmDialog. `confirm(action, copy)`
 * opens the dialog (save copy by default) and runs `action` only on accept;
 * render the returned `dialog` element once in the component.
 */
export function useConfirm() {
    const [pending, setPending] = useState(null);
    const confirm = (action, copy = {}) => setPending({ ...SAVE_PROMPT, ...copy, action });
    const dialog = (
        <ConfirmDialog
            open={Boolean(pending)}
            title={pending?.title}
            message={pending?.message}
            confirmLabel={pending?.confirmLabel}
            tone={pending?.tone}
            onConfirm={() => pending?.action()}
            onClose={() => setPending(null)}
        />
    );
    return [confirm, dialog];
}
