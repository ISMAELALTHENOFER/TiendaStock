import { test } from 'node:test';
import assert from 'node:assert/strict';
import { saveResponse } from './saveResponse.js';

const reply = (status, body, { redirected = false, type = 'application/json' } = {}) => ({
    ok: status >= 200 && status < 300,
    status,
    redirected,
    headers: { get: () => type },
    json: async () => body,
});

test('accepts only an explicit successful JSON save contract', async () => {
    assert.deepEqual(await saveResponse(reply(200, { saved: true, redirect: '/productos' })), { saved: true, redirect: '/productos' });
    await assert.rejects(saveResponse(reply(200, { redirect: '/productos' })), /guardar/i);
    await assert.rejects(saveResponse(reply(200, null, { redirected: true, type: 'text/html' })), /inesperada/i);
});

test('distinguishes expired sessions, validation, and unexpected failures', async () => {
    await assert.rejects(saveResponse(reply(401, { message: 'Unauthenticated.' })), /sesión/i);
    await assert.rejects(saveResponse(reply(419, { message: 'Page Expired' })), /sesión/i);
    await assert.rejects(saveResponse(reply(422, { errors: { nombre: ['Requerido'] } })), (error) => error.errors.nombre[0] === 'Requerido');
    await assert.rejects(saveResponse(reply(500, { message: 'Server error' })), /guardar/i);
});

test('empty validation errors leave the general save message visible', async () => {
    await assert.rejects(saveResponse(reply(422, { errors: {} })), (error) =>
        /guardar/i.test(error.message) && !error.errors);
});
