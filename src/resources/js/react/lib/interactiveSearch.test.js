import test from 'node:test';
import assert from 'node:assert/strict';
import { isInteractiveSearch, shouldRunLiveSearch } from './interactiveSearch.js';

test('interactive search starts at two non-whitespace characters', () => {
    assert.equal(isInteractiveSearch(''), false);
    assert.equal(isInteractiveSearch(' a '), false);
    assert.equal(isInteractiveSearch('ab'), true);
    assert.equal(isInteractiveSearch(' ab '), true);
});

test('live search can clear a previous query immediately', () => {
    assert.equal(shouldRunLiveSearch(''), true);
    assert.equal(shouldRunLiveSearch('  '), true);
    assert.equal(shouldRunLiveSearch('a'), false);
    assert.equal(shouldRunLiveSearch('ab'), true);
});
