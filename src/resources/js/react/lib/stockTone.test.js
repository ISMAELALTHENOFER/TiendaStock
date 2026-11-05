import test from 'node:test';
import assert from 'node:assert/strict';
import { stockTone } from './stockTone.js';

test('stock tone distinguishes exhausted, low, and normal stock', () => {
    assert.equal(stockTone(0, 1), 'danger');
    assert.equal(stockTone(1, 1), 'warning');
    assert.equal(stockTone(2, 1), 'success');
});
