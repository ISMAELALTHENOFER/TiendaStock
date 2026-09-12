<?php

if (! function_exists('formato_pesos')) {
    /**
     * Formatea un monto en formato pesos argentinos SIN depender de ext-intl.
     * Ejemplo: 1234567.89 → $1.234.567,89
     */
    function formato_pesos(float|int|string|null $value): string
    {
        $value = (float) ($value ?? 0);

        $parts = explode('.', number_format($value, 2, '.', ''));
        $integer = $parts[0];
        $decimals = $parts[1] ?? '00';

        // Add dots as thousands separators (Argentine format)
        $formattedInt = preg_replace('/(\d)(?=(\d{3})+(?!\d))/', '$1.', $integer);

        return '$'.$formattedInt.','.$decimals;
    }
}
