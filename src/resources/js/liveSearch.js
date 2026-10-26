import { shouldRunLiveSearch } from './react/lib/interactiveSearch.js';

function installLiveSearch() {
    const timers = new WeakMap();
    const requests = new WeakMap();

    const update = async (form) => {
        const selector = form.dataset.liveResults;
        const target = document.querySelector(selector);
        if (!target) return;

        requests.get(form)?.abort();
        const controller = new AbortController();
        requests.set(form, controller);
        const params = new URLSearchParams(new FormData(form));
        const search = params.get('buscar')?.trim() || '';
        if (search.length < 2) params.delete('buscar');
        params.set('page', '1');
        const url = `${form.action}?${params}`;
        target.setAttribute('aria-busy', 'true');
        const status = form.querySelector('[data-live-status]');
        if (status) {
            status.textContent = 'Buscando…';
            status.setAttribute('role', 'status');
        }

        try {
            const response = await fetch(url, { signal: controller.signal });
            if (!response.ok) throw new Error('Search request failed');
            const html = new DOMParser().parseFromString(await response.text(), 'text/html');
            const replacement = html.querySelector(selector);
            if (!replacement) throw new Error('Search results were not found');
            if (status) {
                status.textContent = '';
                status.setAttribute('role', 'status');
            }
            target.innerHTML = replacement.innerHTML;
            window.history.replaceState({}, '', url);
        } catch (error) {
            if (error.name !== 'AbortError' && requests.get(form) === controller) {
                const status = form.querySelector('[data-live-status]');
                if (status) {
                    status.textContent = 'No se pudo buscar. Modifica la búsqueda para reintentar.';
                    status.setAttribute('role', 'alert');
                }
            }
        } finally {
            if (requests.get(form) === controller) target.removeAttribute('aria-busy');
        }
    };

    document.addEventListener('input', (event) => {
        const input = event.target;
        const form = input.form;
        if (!form?.matches('form[data-live-search="true"]') || input.name !== 'buscar') return;

        window.clearTimeout(timers.get(form));
        requests.get(form)?.abort();
        const target = document.querySelector(form.dataset.liveResults);
        target?.removeAttribute('aria-busy');
        const status = form.querySelector('[data-live-status]');
        if (status) status.textContent = '';
        const value = input.value.trim();
        const previous = new URLSearchParams(window.location.search).get('buscar') || '';
        if (!shouldRunLiveSearch(value) && !previous) return;
        timers.set(form, window.setTimeout(() => update(form), 250));
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form.matches('form[data-live-search="true"]')) return;
        event.preventDefault();
        window.clearTimeout(timers.get(form));
        update(form);
    });
}

installLiveSearch();
