import test from 'node:test';
import assert from 'node:assert/strict';
import { createDuplicateCheck } from './duplicateCheck.js';

const fakeLookup = (reply) => {
    const calls = [];
    const lookup = (nombre) => { calls.push(nombre); return reply(nombre); };
    return { calls, lookup };
};

test('skips short names, blank names and edit mode without a request', async () => {
    const { calls, lookup } = fakeLookup(async () => ({ exists: true }));
    const check = createDuplicateCheck(lookup);
    assert.equal(await check(' a '), null);
    assert.equal(await check(''), null);
    assert.equal(await check(undefined), null);
    assert.equal(await check('Remera', { edit: true }), null);
    assert.deepEqual(calls, []);
});

test('trims the name and reports an existing product', async () => {
    const found = [];
    const { calls, lookup } = fakeLookup(async () => ({ exists: true, id: 7 }));
    const check = createDuplicateCheck(lookup, (dato) => found.push(dato));
    assert.deepEqual(await check('  Remera  '), { exists: true, id: 7 });
    assert.deepEqual(calls, ['Remera']);
    assert.deepEqual(found, [{ exists: true, id: 7 }]);
});

test('concurrent and repeated checks of the same name share one request', async () => {
    const { calls, lookup } = fakeLookup(async () => ({ exists: false }));
    const check = createDuplicateCheck(lookup);
    const [blur, submit] = [check('Remera'), check('Remera ')];
    assert.equal(blur, submit);
    assert.equal(await submit, null);
    await check('Remera');
    assert.deepEqual(calls, ['Remera']);
    await check('Buzo');
    assert.deepEqual(calls, ['Remera', 'Buzo']);
});

test('a failed request resolves to null so the save proceeds', async () => {
    const found = [];
    const check = createDuplicateCheck(async () => { throw new Error('offline'); }, (dato) => found.push(dato));
    assert.equal(await check('Remera'), null);
    assert.deepEqual(found, []);
});
