import test from 'node:test';
import assert from 'node:assert/strict';
import { categoryMatches } from './categoryMatches.js';

const categorias = [{ id: 1, nombre: 'Bodys' }, { id: 2, nombre: 'Buzos' }, { id: 3, nombre: 'Remeras body' }];

test('matches categories that start with the typed text, ignoring case and spaces', () => {
    assert.deepEqual(categoryMatches(categorias, ' b').map((c) => c.id), [1, 2]);
    assert.deepEqual(categoryMatches(categorias, 'BOD').map((c) => c.id), [1]);
});

test('empty text lists every category', () => {
    assert.equal(categoryMatches(categorias, '').length, 3);
});
