import test from 'node:test';
import assert from 'node:assert/strict';
import { pageItems } from './pagination.js';

test('seven pages or fewer are all listed', () => {
    assert.deepEqual(pageItems(1, 1), [1]);
    assert.deepEqual(pageItems(4, 7), [1, 2, 3, 4, 5, 6, 7]);
});

test('long ranges keep first, last and neighbours with gap keys', () => {
    assert.deepEqual(pageItems(1, 20), [1, 2, 'gap-20', 20]);
    assert.deepEqual(pageItems(20, 20), [1, 'gap-19', 19, 20]);
    assert.deepEqual(pageItems(10, 20), [1, 'gap-9', 9, 10, 11, 'gap-20', 20]);
});

test('a gap hiding exactly one page shows that page', () => {
    assert.deepEqual(pageItems(4, 20), [1, 2, 3, 4, 5, 'gap-20', 20]);
    assert.deepEqual(pageItems(17, 20), [1, 'gap-16', 16, 17, 18, 19, 20]);
});

test('keys are unique for every page of a long range', () => {
    for (let page = 1; page <= 30; page++) {
        const items = pageItems(page, 30);
        assert.equal(new Set(items).size, items.length, `page ${page}`);
        assert.ok(items.includes(page), `page ${page}`);
    }
});
