export function isValidDateRange(from, to) {
    return !from || !to || from <= to;
}
