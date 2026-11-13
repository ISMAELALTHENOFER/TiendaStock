<script>
    (() => {
        const key = 'tiendastock.theme';
        const root = document.documentElement;
        const system = root.hasAttribute('data-theme-system') && window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
        let saved = null;
        try { saved = localStorage.getItem(key); } catch (_) { /* Storage is optional. */ }
        let dark = saved === 'dark' || (saved === null && !!system?.matches);
        const apply = () => {
            root.classList.toggle('dark', dark);
            document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
                button.setAttribute('aria-pressed', String(dark));
                if (button.hasAttribute('data-login-theme-toggle')) button.setAttribute('aria-label', dark ? 'Activar modo claro' : 'Activar modo oscuro');
            });
        };
        apply();
        document.addEventListener('click', (event) => {
            if (!event.target.closest('[data-theme-toggle]')) return;
            dark = !dark;
            saved = dark ? 'dark' : 'light';
            try { localStorage.setItem(key, dark ? 'dark' : 'light'); } catch (_) { /* Keep the current page usable. */ }
            apply();
        });
        document.addEventListener('DOMContentLoaded', () => {
            apply();
            new MutationObserver(apply).observe(document.body, { childList: true, subtree: true });
        });
        window.addEventListener('storage', (event) => {
            if (event.key !== key) return;
            saved = event.newValue;
            dark = saved === 'dark' || (saved === null && !!system?.matches);
            apply();
        });
        system?.addEventListener('change', (event) => {
            if (saved !== null) return;
            dark = event.matches;
            apply();
        });
    })();
</script>
