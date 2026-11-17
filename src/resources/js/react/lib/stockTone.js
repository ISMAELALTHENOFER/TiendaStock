export function stockTone(quantity, threshold) {
    if (Number(quantity) === 0) return 'danger';
    return Number(quantity) <= threshold ? 'warning' : 'success';
}
