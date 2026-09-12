const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content;

// Raw fetch consumers (e.g. multipart FormData submissions) need the CSRF
// token without the JSON wrapper apiFetch imposes.
export const csrf = csrfToken;

export async function apiFetch(url, options = {}) {
    const headers = new Headers(options.headers);
    headers.set('Accept', 'application/json');

    if (options.body && !headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
    }

    const token = csrfToken();
    if (token) headers.set('X-CSRF-TOKEN', token);

    const response = await fetch(url, { ...options, headers });
    const contentType = response.headers.get('content-type') || '';
    const data = contentType.includes('application/json')
        ? await response.json()
        : await response.text();

    if (!response.ok) {
        const error = new Error(data?.message || `Request failed with status ${response.status}`);
        error.status = response.status;
        error.data = data;
        error.errors = response.status === 422 ? data?.errors || {} : {};
        throw error;
    }

    return data;
}

export const api = {
    get: (url, options) => apiFetch(url, { ...options, method: 'GET' }),
    post: (url, body, options) => apiFetch(url, { ...options, method: 'POST', body: JSON.stringify(body) }),
    patch: (url, body, options) => apiFetch(url, { ...options, method: 'PATCH', body: JSON.stringify(body) }),
    delete: (url, options) => apiFetch(url, { ...options, method: 'DELETE' }),
};

export default apiFetch;
