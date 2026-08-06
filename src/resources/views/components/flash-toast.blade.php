<div id="flash-toast-container" class="fixed z-[100] flex flex-col gap-3 pointer-events-none" aria-live="polite" aria-atomic="true" style="display:none; top: max(0.75rem, env(safe-area-inset-top)); right: max(0.75rem, env(safe-area-inset-right)); bottom: env(safe-area-inset-bottom); left: max(0.75rem, env(safe-area-inset-left));"></div>

<script>
function showFlash(type, message) {
    const container = document.getElementById('flash-toast-container');
    container.style.display = 'flex';

    const colors = {
        success: { bg: 'bg-green-50', border: 'border-green-500', text: 'text-green-800', iconBg: 'bg-green-100', iconColor: 'text-green-600', icon: 'check' },
        error: { bg: 'bg-red-50', border: 'border-red-500', text: 'text-red-800', iconBg: 'bg-red-100', iconColor: 'text-red-600', icon: 'error' },
        warning: { bg: 'bg-amber-50', border: 'border-amber-500', text: 'text-amber-800', iconBg: 'bg-amber-100', iconColor: 'text-amber-600', icon: 'warning' },
        info: { bg: 'bg-sky-50', border: 'border-sky-500', text: 'text-sky-800', iconBg: 'bg-sky-100', iconColor: 'text-sky-600', icon: 'info' },
    };

    const c = colors[type] || colors.info;

    const toast = document.createElement('div');
     toast.className = `pointer-events-auto ${c.bg} border-l-4 ${c.border} ${c.text} p-4 rounded-xl shadow-lg flex items-start gap-3 max-w-[calc(100vw-24px)] sm:max-w-md transition-all duration-200 opacity-0 translate-x-8`;
    toast.style.transform = 'translateX(32px)';
    toast.style.opacity = '0';

    let iconSvg = '';
    if (c.icon === 'check') {
        iconSvg = `<svg class="w-5 h-5 ${c.iconColor}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>`;
    } else if (c.icon === 'error') {
        iconSvg = `<svg class="w-5 h-5 ${c.iconColor}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>`;
    } else if (c.icon === 'warning') {
        iconSvg = `<svg class="w-5 h-5 ${c.iconColor}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>`;
    } else {
        iconSvg = `<svg class="w-5 h-5 ${c.iconColor}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>`;
    }

    toast.innerHTML = `
        <div class="flex-shrink-0 w-8 h-8 ${c.iconBg} rounded-full flex items-center justify-center">${iconSvg}</div>
        <div class="flex-1 pt-0.5">
            <p class="text-sm font-medium">${message}</p>
        </div>
        <button type="button" aria-label="Cerrar notificación" onclick="this.parentElement.remove()" class="flex-shrink-0 min-w-11 min-h-11 -m-2 flex items-center justify-center ${c.text} hover:opacity-70 transition-opacity">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    `;

    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        });
    });

    // Auto dismiss
    setTimeout(() => {
        toast.style.transform = 'translateX(32px)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 4500);
}

// Convert Laravel flash messages to toasts
document.addEventListener('DOMContentLoaded', () => {
    @if(session('success'))
        showFlash('success', '{{ session('success') }}');
    @endif
    @if(session('error'))
        showFlash('error', '{{ session('error') }}');
    @endif
    @if(session('warning'))
        showFlash('warning', '{{ session('warning') }}');
    @endif
    @if(session('info'))
        showFlash('info', '{{ session('info') }}');
    @endif
});
</script>
