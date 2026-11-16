import test from 'node:test';
import assert from 'node:assert/strict';
import { blurMoney, parseMoney, typeMoney } from './money.js';

test('parses POS amounts typed with comma decimals and dot thousands', () => {
    assert.equal(parseMoney('20000'), 20000);
    assert.equal(parseMoney('20000,5'), 20000.5);
    assert.equal(parseMoney('20.000,50'), 20000.5);
    assert.equal(parseMoney('1.234.567'), 1234567);
    assert.equal(parseMoney(''), 0);
    assert.equal(parseMoney('abc'), 0);
    assert.equal(parseMoney('-500'), -500);
});

test('typing groups thousands, keeps the decimal comma and rejects overflow', () => {
    assert.equal(typeMoney('20000', ''), '20.000');
    assert.equal(typeMoney('20000,5', '20.000'), '20.000,5');
    assert.equal(typeMoney('-500', ''), '500');
    assert.equal(typeMoney('abc', '10'), '');
    assert.equal(typeMoney('9'.repeat(17), '1.000'), '1.000');
});

test('blur formats to two decimals and leaves an empty field empty', () => {
    assert.equal(blurMoney('20000,5'), '20.000,50');
    assert.equal(blurMoney('20.000'), '20.000,00');
    assert.equal(blurMoney(''), '');
});
