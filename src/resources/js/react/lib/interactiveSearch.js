export function isInteractiveSearch(value) {
    return value.trim().length >= 2;
}

export function shouldRunLiveSearch(value) {
    return value.trim().length === 0 || isInteractiveSearch(value);
}
