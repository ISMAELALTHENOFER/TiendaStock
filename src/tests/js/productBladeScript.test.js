import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { Script } from 'node:vm';

test('product Blade inline script has valid JavaScript syntax', () => {
    const blade = readFileSync(new URL('../../resources/views/productos/index.blade.php', import.meta.url), 'utf8');
    const script = blade.match(/<script>([\s\S]*?)<\/script>/)?.[1];
    assert.ok(script);
    const rendered = script.replace(/\{\{[\s\S]*?\}\}/g, 'null').replace(/@if\([^\n]*\)|@endif/g, '');
    assert.doesNotThrow(() => new Script(rendered));
});
