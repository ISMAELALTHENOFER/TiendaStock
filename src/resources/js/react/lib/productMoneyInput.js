// Keep the decimal separator during editing; dots with three trailing digits are grouping.
export function productMoneyInput(value, selectionStart = value.length) {
    const cleaned = value.replace(/[^\d,.]/g, '');
    const comma = cleaned.indexOf(',');
    const lastDot = cleaned.lastIndexOf('.');
    const decimalAt = comma >= 0 ? comma : lastDot >= 0 && /^\d{0,2}$/.test(cleaned.slice(lastDot + 1)) ? lastDot : -1;
    const integer = (decimalAt < 0 ? cleaned : cleaned.slice(0, decimalAt)).replace(/\D/g, '').replace(/^0+(?=\d)/, '');
    const cents = decimalAt < 0 ? '' : cleaned.slice(decimalAt + 1).replace(/\D/g, '').slice(0, 2);
    const display = (integer || (decimalAt >= 0 ? '0' : '')).replace(/\B(?=(\d{3})+(?!\d))/g, '.') + (decimalAt >= 0 ? `,${cents}` : '');
    const raw = Number(`${integer || '0'}.${cents || '0'}`);
    if (!Number.isFinite(raw) || !Number.isSafeInteger(Number(integer || '0'))) return null;
    if (display === value) return { raw, display, caret: selectionStart };

    const before = value.slice(0, selectionStart);
    const digitsLeft = (before.match(/\d/g) || []).length;
    let caret = 0;
    let seen = 0;
    while (caret < display.length && seen < digitsLeft) {
        if (/\d/.test(display[caret])) seen++;
        caret++;
    }
    if (decimalAt >= 0 && value.lastIndexOf(cleaned[decimalAt]) < selectionStart) caret = Math.max(caret, display.indexOf(',') + 1);
    return { raw, display, caret };
}
