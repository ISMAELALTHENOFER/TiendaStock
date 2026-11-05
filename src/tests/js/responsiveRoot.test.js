import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import assert from 'node:assert/strict';

test('root elements do not force a minimum width beyond the viewport', () => {
    const css = readFileSync(new URL('../../resources/css/app.css', import.meta.url), 'utf8');
    for (const selector of ['html', 'body']) {
        const rule = css.match(new RegExp(`\\b${selector}\\s*\\{([^}]*)\\}`));
        assert.ok(rule, `missing ${selector} rule`);
        assert.doesNotMatch(rule[1], /min-width\s*:/);
    }
});
