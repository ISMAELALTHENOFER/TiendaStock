{{--
    Shared Alpine.component `moneyInput`.

    Purpose:
        A single masked Argentine money input used by every amount field in
        the app (product create/edit costo & precio_venta, POS "Pago con" and
        "Descuento"). Keeping the mask in ONE place guarantees the formatting
        rules (dot thousands, comma decimal, 2-decimal canonicalization on
        blur) never drift between surfaces.

    Binding model:
        - `display` (text shown in the visible input), `raw` (plain float).
          Forms bind a hidden `<input type="hidden" name="..." :value="raw">`
          so the backend always receives a pure float (defense in depth: the
          FormRequest `numeric` rule still rejects any tampered string).
        - For surfaces that need the value outside a form (e.g. the POS cart
          totals), the consumer wraps the input with
          `x-init="$watch('raw', v => <outerProp> = v)"` to mirror `raw` into
          the owning component's state.

    Live mask (while typing) + caret preservation:
        On every keystroke `onInput()` regroups the integer part with dot
        thousands separators and preserves the typed comma + decimals. The `$`
        prefix is intentionally absent during live typing (matches the live
        spec: "1" → "1", "1234" → "1.234", "12345,67" → "12.345,67"); the
        canonical "$1.234,56" with exactly 2 decimals is restored on blur.

        Reformatting moves the caret to the end unless we restore it. We count
        DIGITS to the left of the caret before reformatting, then after
        reformatting we place the caret so the same number of digits remains to
        its left. This handles end-typing, mid-string edits, deletion, and
        pasting.

    Usage:
        @once
            @include('partials._money-input')
        @endonce
--}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('moneyInput', (initial) => ({
        raw: parseFloat(initial) || 0,
        display: '',

        init() {
            // Don't mask zero: on create (or a genuinely 0 price) the field
            // starts empty so the user can just type. On edit with a real
            // price, the formatted value renders immediately.
            this.display = this.raw > 0 ? this.format(this.raw) : '';
        },

        // "$1.234,56" — dot thousands, comma decimal (matches formato_pesos()).
        // Used on init and on blur; the live (while-typing) mask in onInput()
        // intentionally omits the "$" prefix and 2-decimal padding so the
        // cursor-preservation algorithm operates on a clean digits/dots/comma
        // string and the user sees exactly what they typed, grouped.
        format(value) {
            const n = parseFloat(value) || 0;
            return '$' + n.toLocaleString('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        // Argentine live money mask while typing:
        //   "1"        → "1"
        //   "1234"     → "1.234"
        //   "12345,"   → "12.345,"
        //   "12345,67" → "12.345,67"
        //
        // Cursor preservation: when we rewrite `display` the caret would jump
        // to the end. We count the DIGITS to the left of the caret before
        // reformatting, then after reformatting we position the caret so the
        // same number of digits remains to its left. This handles typing at
        // the end, mid-string edits, deletion in the middle, and pasting.
        onInput(event) {
            const el = event.target;
            const value = el.value ?? '';
            const cursor = el.selectionStart ?? value.length;

            // Digits to the left of the caret, ignoring dots and the comma
            // (they are non-significant for the caret's logical position).
            const digitsLeft = (value.slice(0, cursor).match(/\d/g) || []).length;

            // Keep only digits and the FIRST comma (the AR decimal separator).
            // Extra commas are dropped. Existing thousands dots are stripped.
            const cleaned = value.replace(/[^\d,]/g, '').replace(/\./g, '');
            const firstComma = cleaned.indexOf(',');
            let intPart;
            let decPart = '';
            let commaTyped = false;
            if (firstComma >= 0) {
                commaTyped = true;
                intPart = cleaned.slice(0, firstComma);
                decPart = cleaned.slice(firstComma + 1).replace(/,/g, '');
            } else {
                intPart = cleaned;
            }
            // Cap to 2 decimal digits to match AR money precision.
            decPart = decPart.slice(0, 2);

            // Group the integer part with dot thousands separators from the
            // right (e.g. "12345" → "12.345"). \B keeps leading 3-digit groups
            // from getting a stray dot at the start.
            const maskedInt = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            let masked = maskedInt;
            if (commaTyped) {
                masked += ',' + decPart;
            }

            this.display = masked;
            this.raw = parseFloat((intPart || '0') + (decPart ? '.' + decPart : '')) || 0;

            // Restore the caret after Alpine re-binds `:value`. Place it so
            // exactly `digitsLeft` digits sit to its left.
            this.$nextTick(() => {
                let seen = 0;
                let pos = 0;
                for (pos = 0; pos < masked.length; pos++) {
                    if (/\d/.test(masked[pos])) {
                        if (seen === digitsLeft) {
                            break;
                        }
                        seen++;
                    }
                }
                try {
                    el.setSelectionRange(pos, pos);
                } catch (e) {
                    // Some inputs reject setSelectionRange (e.g. type=number);
                    // safe to ignore — the visible value is still correct.
                }
            });
        },

        onBlur() {
            // Blur with no value ⇒ keep empty (backend `required|numeric` will
            // reject an empty mandatory price, which is the correct behavior).
            // Otherwise finalize to the canonical "$1.234,56" with 2 decimals.
            this.display = this.raw > 0 ? this.format(this.raw) : '';
        },
    }));
});
</script>