<div id="confirm-dialog-overlay" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4" role="dialog" aria-modal="true" aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-message" style="display:none;">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="confirmDialogHide()"></div>
    <div id="confirm-dialog-box" class="relative bg-surface rounded-2xl shadow-xl w-full max-w-md max-h-[calc(100dvh-1.5rem)] overflow-y-auto p-5 sm:p-6 z-10 opacity-0 scale-95 translate-y-4 transition-all duration-200">
        <div id="confirm-dialog-icon" class="mx-auto flex items-center justify-center h-12 w-12 sm:h-16 sm:w-16 rounded-full bg-red-50 mb-3 sm:mb-4">
            <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
        </div>
        <h3 id="confirm-dialog-title" class="text-lg font-bold text-center text-gray-900 mb-2">Confirmar acción</h3>
        <p id="confirm-dialog-message" class="text-sm text-center text-gray-600 mb-6">¿Está seguro?</p>
        <div class="flex flex-col-reverse sm:flex-row gap-3">
            <button type="button" onclick="confirmDialogHide()" class="action-button flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-200">Cancelar</button>
            <button type="button" id="confirm-dialog-confirm" class="action-button flex-1 px-4 py-3 text-white font-semibold rounded-xl transition-all duration-200 shadow-md bg-red-600 hover:bg-red-700">Confirmar</button>
        </div>
    </div>
</div>

<script>
let confirmDialogResolve = null;

function confirmDialogShow(title, message, confirmText, btnClass) {
    const overlay = document.getElementById('confirm-dialog-overlay');
    const box = document.getElementById('confirm-dialog-box');
    document.getElementById('confirm-dialog-title').textContent = title || 'Confirmar acción';
    document.getElementById('confirm-dialog-message').textContent = message || '¿Está seguro?';
    document.getElementById('confirm-dialog-confirm').textContent = confirmText || 'Confirmar';

    const btn = document.getElementById('confirm-dialog-confirm');
    btn.className = 'flex-1 px-4 py-3 text-white font-semibold rounded-xl transition-all duration-200 shadow-md ' + (btnClass || 'bg-red-600 hover:bg-red-700');

    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Animate in
    requestAnimationFrame(() => {
        overlay.classList.remove('hidden');
        requestAnimationFrame(() => {
            box.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
            box.classList.add('opacity-100', 'scale-100', 'translate-y-0');
        });
    });

    return new Promise(resolve => {
        confirmDialogResolve = resolve;
    });
}

function confirmDialogHide() {
    const overlay = document.getElementById('confirm-dialog-overlay');
    const box = document.getElementById('confirm-dialog-box');

    box.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
    box.classList.add('opacity-0', 'scale-95', 'translate-y-4');

    setTimeout(() => {
        overlay.style.display = 'none';
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);

    if (confirmDialogResolve) {
        confirmDialogResolve(false);
        confirmDialogResolve = null;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('confirm-dialog-confirm').addEventListener('click', () => {
        if (confirmDialogResolve) {
            confirmDialogResolve(true);
            confirmDialogResolve = null;
        }
        confirmDialogHide();
    });
});
</script>
