import { Modal } from './Modal.jsx';
import { Button } from './Button.jsx';

/**
 * Shared confirmation dialog replacing the legacy native confirm() /
 * confirmDialogShow Alpine helper. The caller decides what runs on confirm
 * (usually a native form submit or a window.location redirect); the dialog
 * itself only renders the prompt and closes itself after onConfirm.
 */
export function ConfirmDialog({ open, title, message, confirmLabel, tone = 'primary', onConfirm, onClose }) {
    return (
        <Modal open={open} title={title} onClose={onClose}>
            <p className="mt-2 text-sm text-muted">{message}</p>
            <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <Button variant="secondary" onClick={onClose}>Cancelar</Button>
                <Button variant={tone} onClick={() => { onConfirm(); onClose(); }}>{confirmLabel}</Button>
            </div>
        </Modal>
    );
}

export default ConfirmDialog;