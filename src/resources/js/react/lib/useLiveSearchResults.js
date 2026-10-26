import { useEffect, useRef, useState } from 'react';
import { isInteractiveSearch } from './interactiveSearch.js';

export function useLiveSearchResults(initialResults, { query, route, pageSize, filters = {}, enabled = true }) {
    const [results, setResults] = useState(initialResults);
    const [searching, setSearching] = useState(false);
    const [searchError, setSearchError] = useState(false);
    const appliedQuery = useRef(isInteractiveSearch(query) ? query.trim() : '');
    const appliedFilterKey = useRef(JSON.stringify(filters));
    const requestId = useRef(0);

    useEffect(() => {
        setResults(initialResults);
        appliedQuery.current = isInteractiveSearch(query) ? query.trim() : '';
        appliedFilterKey.current = JSON.stringify(filters);
    }, [initialResults]);

    const filterKey = JSON.stringify(filters);
    useEffect(() => {
        const normalized = isInteractiveSearch(query) ? query.trim() : '';
        if (!enabled) return undefined;
        if (normalized === appliedQuery.current && filterKey === appliedFilterKey.current) {
            setSearchError(false);
            setSearching(false);
            return undefined;
        }

        const currentRequest = ++requestId.current;
        const controller = new AbortController();
        const timeout = window.setTimeout(async () => {
            setSearching(true);
            setSearchError(false);
            const params = new URLSearchParams(window.location.search);
            if (normalized) params.set('buscar', normalized);
            else params.delete('buscar');
            params.set('per_page', String(pageSize));
            params.set('page', '1');
            for (const [name, value] of Object.entries(filters)) {
                if (value) params.set(name, value);
                else params.delete(name);
            }
            const url = `${route}?${params}`;

            try {
                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error('Search request failed');
                const data = await response.json();
                if (currentRequest !== requestId.current) return;
                setResults(data);
                appliedQuery.current = normalized;
                appliedFilterKey.current = filterKey;
                window.history.replaceState({}, '', url);
            } catch (error) {
                if (error.name !== 'AbortError' && currentRequest === requestId.current) setSearchError(true);
            } finally {
                if (currentRequest === requestId.current) setSearching(false);
            }
        }, 250);

        return () => {
            window.clearTimeout(timeout);
            controller.abort();
        };
    }, [query, route, pageSize, filterKey, enabled]);

    return { results, searching, searchError };
}
