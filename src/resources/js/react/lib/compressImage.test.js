import test from 'node:test';
import assert from 'node:assert/strict';
import { fitWithin, MAX_SOURCE_BYTES } from './compressImage.js';

test('scales the longest side down to the limit keeping the aspect ratio', () => {
    assert.deepEqual(fitWithin(4000, 3000, 1600), { width: 1600, height: 1200 });
    assert.deepEqual(fitWithin(3000, 4000, 1600), { width: 1200, height: 1600 });
});

test('never upscales small images', () => {
    assert.deepEqual(fitWithin(800, 600, 1600), { width: 800, height: 600 });
});

test('accepts sources up to 20 MB', () => {
    assert.equal(MAX_SOURCE_BYTES, 20 * 1024 * 1024);
});
