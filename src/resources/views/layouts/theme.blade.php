<script>
    (() => {
        const key = 'tiendastock.theme';
        const root = document.documentElement;
        let dark = false;
        try { dark = localStorage.getItem(key) === 'dark'; } catch (_) { /* Storage is optional. */ }
        const apply = () => {
            root.classList.toggle('dark', dark);
            document.querySelectorAll('[data-theme-toggle]').forEach((button) => button.setAttribute('aria-pressed', String(dark)));
        };
        apply();
        document.addEventListener('click', (event) => {
            if (!event.target.closest('[data-theme-toggle]')) return;
            dark = !dark;
            try { localStorage.setItem(key, dark ? 'dark' : 'light'); } catch (_) { /* Keep the current page usable. */ }
            apply();
        });
        document.addEventListener('DOMContentLoaded', () => {
            apply();
            new MutationObserver(apply).observe(document.body, { childList: true, subtree: true });
        });
        window.addEventListener('storage', (event) => {
            if (event.key !== key) return;
            dark = event.newValue === 'dark';
            apply();
        });
    })();
</script>
