// Resolves to the existing product (or null). The same name reuses the in-flight/finished
// check, so the blur fired by clicking "Guardar" and the submit share one request.
export function createDuplicateCheck(lookup, onFound = () => {}) {
    let lastName = null;
    let pending = Promise.resolve(null);
    return (rawName, { edit = false } = {}) => {
        if (edit) return Promise.resolve(null);
        const nombre = rawName?.trim();
        if (!nombre || nombre.length < 2) return Promise.resolve(null);
        if (nombre === lastName) return pending;
        lastName = nombre;
        pending = lookup(nombre)
            .then((dato) => (dato.exists ? dato : null))
            // Advisory only: the database unique rule is the final guard.
            .catch(() => null);
        pending.then((dato) => { if (dato) onFound(dato); });
        return pending;
    };
}
