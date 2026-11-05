import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import postcss from 'postcss';

const source = readFileSync(new URL('../../../views/layouts/theme.blade.php', import.meta.url), 'utf8').replace(/<\/?script>/g, '');

function setup(saved, unavailable = false) {
    const events = {};
    const windowEvents = {};
    const classes = new Set();
    const button = { setAttribute: (name, value) => { button[name] = value; } };
    const document = {
        documentElement: { classList: { toggle: (name, enabled) => enabled ? classes.add(name) : classes.delete(name) } },
        querySelectorAll: () => [button],
        addEventListener: (name, handler) => { events[name] = handler; },
        body: {},
    };
    const localStorage = {
        getItem: () => { if (unavailable) throw Error('denied'); return saved; },
        setItem: (_, value) => { if (unavailable) throw Error('denied'); saved = value; },
    };
    vm.runInNewContext(source, { document, localStorage, window: { addEventListener: (name, handler) => { windowEvents[name] = handler; } }, MutationObserver: class { observe() {} } });
    return { classes, button, events, windowEvents, stored: () => saved, click: () => events.click({ target: { closest: () => button } }) };
}

test('restores and persists an explicit theme before rendering', () => {
    const theme = setup('dark');
    assert.equal(theme.classes.has('dark'), true);
    assert.equal(theme.button['aria-pressed'], 'true');
    theme.click();
    assert.equal(theme.classes.has('dark'), false);
    assert.equal(theme.button['aria-pressed'], 'false');
    assert.equal(theme.stored(), 'light');
    theme.windowEvents.storage({ key: 'tiendastock.theme', newValue: 'dark' });
    assert.equal(theme.classes.has('dark'), true);
});

test('works without storage and treats invalid values as light', () => {
    const theme = setup('invalid', true);
    assert.equal(theme.classes.has('dark'), false);
    theme.click();
    assert.equal(theme.classes.has('dark'), true);
    assert.equal(theme.button['aria-pressed'], 'true');
    assert.equal(setup('invalid').classes.has('dark'), false);
});

test('dark theme text and status palette meet normal-text contrast', () => {
    const luminance = (hex) => {
        const channels = hex.match(/\w\w/g).map((channel) => parseInt(channel, 16) / 255)
            .map((value) => value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4);
        return channels[0] * 0.2126 + channels[1] * 0.7152 + channels[2] * 0.0722;
    };
    for (const [foreground, background] of [['ebf2f8', '192430'], ['b8c6d3', '192430'], ['ffb4b4', '451a1a'], ['a3f3cb', '123d30'], ['ffdc8d', '49320b'], ['ffffff', '0b1c30']]) {
        const values = [luminance(foreground), luminance(background)].sort((a, b) => b - a);
        assert.ok((values[0] + 0.05) / (values[1] + 0.05) >= 4.5, `${foreground} on ${background}`);
    }
});

test('dark foreground overrides contrast with their actual CSS surfaces without changing primary buttons', () => {
    const css = postcss.parse(readFileSync(new URL('../../../css/app.css', import.meta.url), 'utf8'));
    const declaration = (selector, property) => {
        let value;
        css.walkRules((rule) => {
            if (!rule.selectors.includes(selector)) return;
            rule.walkDecls(property, (decl) => { value = decl.value; });
        });
        assert.ok(value, `${selector}: ${property}`);
        return value;
    };
    const darkVariable = (value) => value.startsWith('var(') ? declaration(':root.dark', value.slice(4, -1)) : value;
    const luminance = (hex) => {
        assert.match(hex, /^#[\da-f]{6}$/i);
        const channels = hex.slice(1).match(/../g).map((channel) => parseInt(channel, 16) / 255)
            .map((value) => value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4);
        return channels[0] * 0.2126 + channels[1] * 0.7152 + channels[2] * 0.0722;
    };
    const contrast = (foreground, background) => {
        const values = [luminance(darkVariable(foreground)), luminance(darkVariable(background))].sort((a, b) => b - a);
        assert.ok((values[0] + 0.05) / (values[1] + 0.05) >= 4.5, `${foreground} on ${background}`);
    };
    const primaryText = declaration('.dark .text-primary', 'color');
    for (const surface of ['--canvas', '--surface', '--surface-muted']) contrast(primaryText, declaration(':root.dark', surface));
    contrast(primaryText, declaration('.dark .bg-green-50', 'background-color'));
    contrast('#ffffff', declaration('.dark .data-table thead', 'background'));
    contrast(declaration('.dark .text-slate-600', 'color'), declaration('.dark .bg-sky-50', 'background-color'));
    contrast(declaration('.dark .text-gray-700', 'color'), declaration('.dark thead.bg-gradient-to-r', 'background-color'));
    contrast(declaration('.dark .text-primary', 'color'), declaration('.dark .hover\\:bg-green-50:hover', 'background-color'));
    const config = readFileSync(new URL('../../../../tailwind.config.js', import.meta.url), 'utf8');
    const primaryBackground = config.match(/primary:\s*\{\s*DEFAULT:\s*'([^']+)'/)?.[1];
    assert.equal(primaryBackground, '#006948');
    contrast('#ffffff', primaryBackground);
});
