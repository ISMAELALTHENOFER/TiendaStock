export async function saveResponse(response) {
    const type = response.headers.get('content-type') || '';
    if (response.redirected || !type.includes('application/json')) {
        throw new Error('El servidor respondió de forma inesperada. No se pudo guardar.');
    }
    const body = await response.json();
    if (!response.ok || body?.saved !== true || !body?.redirect) {
        const error = new Error([401, 419].includes(response.status)
            ? 'Tu sesión venció. Volvé a iniciar sesión antes de guardar.'
            : 'No se pudo guardar. Revisá los datos e intentá nuevamente.');
        error.errors = response.status === 422 && body?.errors && Object.keys(body.errors).length
            ? body.errors : null;
        throw error;
    }
    return body;
}
