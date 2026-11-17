import test from 'node:test';
import assert from 'node:assert/strict';
import { isValidDateRange } from './dateRange.js';

test('date range accepts missing or ordered dates and rejects reversed dates', () => {
    assert.equal(isValidDateRange('', '2026-11-03'), true);
    assert.equal(isValidDateRange('2026-11-03', '2026-11-03'), true);
    assert.equal(isValidDateRange('2026-11-04', '2026-11-03'), false);
});
