import assert from 'node:assert/strict';
import test from 'node:test';
import { formatMoney } from './formatMoney.js';

test('formats ARS values and treats missing values as zero', () => {
    assert.equal(formatMoney(1234.56), '$ 1.234,56');
    assert.equal(formatMoney(undefined), '$ 0,00');
});
