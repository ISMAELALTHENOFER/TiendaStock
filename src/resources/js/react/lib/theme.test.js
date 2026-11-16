import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import postcss from 'postcss';

const source = readFileSync(new URL('../../../views/layouts/theme.blade.php', import.meta.url), 'utf8').replace(/<\/?script>/g, '');

function setup(saved, unavailable = false, systemDark = false, login = false) {
    const events = {};
    const windowEvents = {};
    const classes = new Set();
    const button = { hasAttribute: (name) => login && name === 'data-login-theme-toggle', setAttribute: (name, value) => { button[name] = value; } };
    const document = {
        documentElement: { hasAttribute: () => login, classList: { toggle: (name, enabled) => enabled ? classes.add(name) : classes.delete(name) } },
        querySelectorAll: () => [button],
        addEventListener: (name, handler) => { events[name] = handler; },
        body: {},
    };
    const localStorage = {
        getItem: () => { if (unavailable) throw Error('denied'); return saved; },
        setItem: (_, value) => { if (unavailable) throw Error('denied'); saved = value; },
    };
    const system = { matches: systemDark, addEventListener: (_, handler) => { system.change = handler; } };
    vm.runInNewContext(source, { document, localStorage, window: { matchMedia: () => system, addEventListener: (name, handler) => { windowEvents[name] = handler; } }, MutationObserver: class { observe() {} } });
    return { classes, button, events, windowEvents, system, stored: () => saved, click: () => events.click({ target: { closest: () => button } }) };
}

test('login follows system until a theme is explicitly selected', () => {
    const theme = setup(null, false, true, true);
    assert.equal(theme.classes.has('dark'), true);
    assert.equal(theme.button['aria-label'], 'Activar modo claro');
    theme.system.change({ matches: false });
    assert.equal(theme.classes.has('dark'), false);
    assert.equal(theme.button['aria-label'], 'Activar modo oscuro');
    theme.click();
    assert.equal(theme.stored(), 'dark');
    assert.equal(theme.button['aria-label'], 'Activar modo claro');
    theme.system.change({ matches: false });
    assert.equal(theme.classes.has('dark'), true);
    assert.equal(setup('light', false, true, true).classes.has('dark'), false);
    assert.equal(setup(null, false, true).classes.has('dark'), false);
});

test('other pages retain their explicit theme and do not follow system changes', () => {
    const page = setup(null, false, true);
    assert.equal(page.classes.has('dark'), false);
    assert.equal(page.button['aria-label'], undefined);
    assert.equal(page.system.change, undefined);
    page.click();
    assert.equal(page.stored(), 'dark');
    page.windowEvents.storage({ key: 'tiendastock.theme', newValue: null });
    assert.equal(page.classes.has('dark'), false);
    assert.equal(setup('dark', false, false).classes.has('dark'), true);
});

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

test('dark mode follows the .dark class and its utility pairs meet normal-text contrast', async () => {
    // The config imports extensionless Tailwind paths that plain Node ESM cannot load, so read it as text.
    const config = readFileSync(new URL('../../../../tailwind.config.js', import.meta.url), 'utf8');
    const { default: palette } = await import('tailwindcss/colors.js');
    assert.match(config, /darkMode:\s*'class'/);

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
    // Raw Tailwind utilities get their dark colour from `dark:` variants, never from `.dark .<utility>` overrides.
    css.walkRules((rule) => {
        for (const selector of rule.selectors) assert.doesNotMatch(selector, /\.dark\s+(\.(bg|text|hover|focus|outline|fixed)\b|thead\.bg-)/, selector);
    });

    const color = (name) => {
        if (name.startsWith('--')) return declaration(':root.dark', name);
        if (name.startsWith('#')) return name;
        const [family, shade = 'DEFAULT'] = name.split('-');
        // Project palettes (primary, sky) override Tailwind's defaults in tailwind.config.js.
        const custom = config.match(new RegExp(`\\b${family}:\\s*\\{([^}]*)\\}`))?.[1]?.match(new RegExp(`\\b${shade}:\\s*'(#[\\da-fA-F]{6})'`))?.[1];
        return custom ?? palette[family][shade];
    };
    const luminance = (hex) => {
        assert.match(hex, /^#[\da-f]{6}$/i);
        const channels = hex.slice(1).match(/../g).map((channel) => parseInt(channel, 16) / 255)
            .map((value) => value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4);
        return channels[0] * 0.2126 + channels[1] * 0.7152 + channels[2] * 0.0722;
    };
    const contrast = (foreground, background) => {
        const values = [luminance(color(foreground)), luminance(color(background))].sort((a, b) => b - a);
        assert.ok((values[0] + 0.05) / (values[1] + 0.05) >= 4.5, `${foreground} on ${background}`);
    };

    for (const surface of ['--canvas', '--surface', '--surface-muted']) {
        for (const text of ['--ink', '--muted', 'primary-300', 'red-200', 'amber-200', 'sky-100']) contrast(text, surface);
    }
    // Status surfaces paired with their dark text in JSX (alerts, badges, toasts, stock tiles).
    contrast('red-200', 'red-950');
    contrast('primary-300', 'primary-900');
    contrast('amber-200', 'amber-950');
    contrast('sky-100', 'sky-800');
    contrast('#ffffff', declaration('.dark .data-table thead', 'background'));
    // Primary buttons keep white text on the brand green in both themes.
    assert.equal(color('primary'), '#006948');
    contrast('#ffffff', 'primary');
});
