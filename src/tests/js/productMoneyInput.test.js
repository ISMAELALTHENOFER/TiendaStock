import assert from 'node:assert/strict';
import test from 'node:test';
import { productMoneyInput } from '../../resources/js/react/lib/productMoneyInput.js';

test('both product price fields keep full amounts and decimal cents', () => {
    for (const field of ['precio_compra', 'precio_venta']) {
        for (const [typed, display, raw] of [
            ['2500', '2.500', 2500],
            ['1,5', '1,5', 1.5],
            ['1234,56', '1.234,56', 1234.56],
            ['1234.56', '1.234,56', 1234.56],
            ['$1.234,56', '1.234,56', 1234.56],
            ['', '', 0],
        ]) {
            const result = productMoneyInput(typed);
            assert.equal(result.display, display, `${field}: ${typed}`);
            assert.equal(result.raw, raw, `${field}: ${typed}`);
        }
    }
});

test('editing and backspacing around existing cents preserves the caret and value', () => {
    for (const caret of [0, 1, 2, 3, 4, 5, 6, 7, 8]) {
        assert.deepEqual(productMoneyInput('1.234,56', caret), { display: '1.234,56', raw: 1234.56, caret });
    }
    for (const caret of [1, 2, 3, 5]) {
        assert.deepEqual(productMoneyInput('1.234', caret), { display: '1.234', raw: 1234, caret });
    }
    assert.deepEqual(productMoneyInput('1.234,6', 7), { display: '1.234,6', raw: 1234.6, caret: 7 });
    assert.deepEqual(productMoneyInput('1.234,', 6), { display: '1.234,', raw: 1234, caret: 6 });
    assert.deepEqual(productMoneyInput('1.34,56', 2), { display: '134,56', raw: 134.56, caret: 1 });
    assert.deepEqual(productMoneyInput('12.345,6', 3), { display: '12.345,6', raw: 12345.6, caret: 3 });
    assert.deepEqual(productMoneyInput('1.234.56', 2), { display: '1.234,56', raw: 1234.56, caret: 1 });
    assert.deepEqual(productMoneyInput('1,5', 2), { display: '1,5', raw: 1.5, caret: 2 });
    assert.equal(productMoneyInput('9'.repeat(400)), null);
});
