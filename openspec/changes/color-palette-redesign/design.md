# Design: Color Palette Redesign

## Technical Approach

Replace custom purple/pink/indigo/amber Tailwind tokens with `brand`, `sky`, `blush` families across `tailwind.config.js` and all Blade views. No Blade logic changes — pure class-to-class substitution. Gradient color stops, box shadows, and focus rings are remapped to the new palette. Danger (red), success (green), warning (yellow), blue (purchase price), and cyan (description section) remain untouched.

## Architecture Decisions

### Decision: Color token structure

| Option | Tradeoff | Decision |
|--------|----------|----------|
| Flat keys (`brand`, `brand-light`) | Simple but no Tailwind variant prefixing | Flat keys as objects with `.DEFAULT`, `.light`, `.dark` for each family — enables `bg-brand/50` opacity modifiers |
| Nested object (`brand: { DEFAULT, light, dark }`) | Requires explicit key for default | ✅ Chosen — matches Tailwind v3 convention for custom colors |

### Decision: Indigo/amber → sky/brand mapping

| Color | Mapped to | Rationale |
|-------|-----------|-----------|
| `indigo-*` | `sky` / `sky-*` | Indigo was the "secondary" accent for form headers and focus rings; sky replaces that secondary role |
| `amber-*` | `brand` / `brand-*` | Amber was the "warm" accent for edit pages; brand (gold) is a closer warm-tone replacement |

### Decision: purple-900/purple-800 → brand-dark

`purple-900` used for table heading text and `purple-800` for sidebar backgrounds. `brand-dark` (#C8963A) is too low-contrast against white for body text. **Resolution**: table heading text (`text-purple-900`, `text-purple-800`) → `text-gray-700` for readability. Sidebar backgrounds (`bg-purple-800`, `bg-purple-900`) → custom brand-dark shades defined in config for accessible contrast with white text.

## Tailwind Config Design

```js
// Add to theme.extend in tailwind.config.js
colors: {
  brand: {
    DEFAULT: '#FFD16D',
    light: '#FFE8B6',
    dark: '#C8963A',
  },
  sky: {
    DEFAULT: '#AAD6E2',
    light: '#D5EBF0',
    dark: '#6DB5C8',
  },
  blush: {
    DEFAULT: '#FCD2DF',
    light: '#FEE9EF',
    dark: '#E6A3B8',
  },
  // Remove: entire purple: { ... } block
  // Remove: entire pink: { ... } block
},
gradientColorStops: {
  // Remove: purple-start, pink-end
  // Replace with inline class names (from-brand to-sky)
  // No custom gradientColorStops needed — Tailwind generates from brand/sky auto
},
boxShadow: {
  // Keep existing sm, md, lg, xl
  'brand-glow': '0 0 20px rgba(255, 209, 109, 0.3)',
  'sky-glow': '0 0 20px rgba(170, 214, 226, 0.3)',
  // Remove: purple-glow, pink-glow
},
```

## Color Mapping Dictionary

| Current | New | Context |
|---------|-----|---------|
| `purple-600` | `brand` | Primary buttons, icons, active text |
| `purple-700` | `brand-dark` | Hover states, headings, sidebar active |
| `purple-500` | `brand` | Focus rings, borders |
| `purple-900` | `gray-700` | Table header text |
| `purple-800` | `brand-dark` | Sidebar backgrounds |
| `purple-300` | `brand-light` | Hover borders |
| `purple-200` | `brand-light` | Borders, search input |
| `purple-100` | `sky-light` | Card backgrounds, table header bg |
| `purple-50` | `sky-light` | Subtle backgrounds |
| `pink-600` | `sky` | Secondary buttons, edit actions |
| `pink-500` | `sky` | Gradient partner |
| `pink-400` | `sky-light` | Gradient highlights |
| `pink-100` | `blush` | Badges, tag backgrounds |
| `pink-50` | `blush` | Subtle backgrounds |
| `indigo-600` | `sky` | Form headers, create pages |
| `indigo-700` | `sky-dark` | Form headers hover |
| `indigo-500` | `sky` | Focus rings |
| `indigo-400` | `sky` | Nav-link active border |
| `indigo-100` | `sky-light` | Subtle text |
| `indigo-700` | `sky-dark` | Nav-link active border hover |
| `amber-600` | `brand` | Edit page headers |
| `amber-700` | `brand-dark` | Edit page headers hover |
| `amber-500` | `brand` | Focus rings (edit forms) |
| `amber-800` | `brand-dark` | Extra hover state |
| `amber-100` | `brand-light` | Subtle text |
| `purple-glow` | `brand-glow` | Box shadow |
| `pink-glow` | `sky-glow` | Box shadow |

## Tailwind Class Mapping by File

### Layouts

| File | Lines | Change |
|------|-------|--------|
| `layouts/app.blade.php:20` | `from-gray-50 to-purple-50` | `from-gray-50 to-sky-light` |
| `layouts/guest.blade.php:20` | `from-purple-50 via-white to-pink-50` | `from-sky-light via-white to-blush` |
| `layouts/guest.blade.php:24` | `text-purple-600` (logo) | `text-brand` |
| `layouts/guest.blade.php:28` | `border-purple-100` | `border-sky-light` |
| `layouts/sidebar.blade.php:5,48` | `from-purple-900 to-purple-800` | `from-brand-dark to-brand-dark` (or custom bg) |
| `layouts/sidebar.blade.php:12,19,25,63,70,76` | `from-purple-700 to-pink-600` | `from-brand-dark to-sky` |
| `layouts/sidebar.blade.php:12,19,25,63,70,76` | `text-purple-200 hover:bg-purple-700` | `text-white/70 hover:bg-brand-dark` |
| `layouts/sidebar.blade.php:33,84` | `from-purple-800 to-purple-700 border-purple-700` | `from-brand-dark to-brand-dark border-brand-dark` |
| `layouts/sidebar.blade.php:37,88` | `text-purple-300` | `text-white/50` |
| `layouts/topbar.blade.php:1` | `border-purple-100` | `border-sky-light` |
| `layouts/topbar.blade.php:7` | `hover:text-purple-600 hover:bg-purple-50 focus:ring-purple-500` | `hover:text-brand hover:bg-brand-light focus:ring-brand` |
| `layouts/topbar.blade.php:20-21` | `focus:ring-purple-500 from-purple-600 to-pink-500` | `focus:ring-brand from-brand to-sky` |

### Components

| File | Lines | Change |
|------|-------|--------|
| `primary-button.blade.php:1` | `from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 focus:ring-purple-500` | `from-brand to-sky hover:from-brand-dark hover:to-sky-dark focus:ring-brand` |
| `primary-button.blade.php:1` | `hover:shadow-pink-glow` | `hover:shadow-sky-glow` |
| `secondary-button.blade.php:1` | `border-purple-200 text-purple-700 hover:bg-purple-50 hover:border-purple-300 focus:ring-purple-500` | `border-brand-light text-brand hover:bg-brand-light hover:border-brand focus:ring-brand` |
| `text-input.blade.php:1` | `focus:ring-purple-500 focus:border-purple-500` | `focus:ring-brand focus:border-brand` |
| `nav-link.blade.php:5` | `border-indigo-400 focus:border-indigo-700` | `border-sky focus:border-sky-dark` |
| `responsive-nav-link.blade.php:5` | `border-indigo-400 text-indigo-700 bg-indigo-50 focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700` | `border-sky text-sky bg-sky-light focus:text-sky-dark focus:bg-sky focus:border-sky-dark` |
| `alert.blade.php:6` | `bg-purple-50 border-purple-200 text-purple-800` | `bg-sky-light border-sky-light text-sky-dark` |
| `alert.blade.php:19` | `text-purple-400` | `text-brand` |
| `alert.blade.php:36` | `text-purple-500 hover:bg-purple-100 focus:ring-purple-600` | `text-brand hover:bg-brand-light focus:ring-brand-dark` |

### Dashboard

| Line(s) | Current | New |
|---------|---------|-----|
| 5 | `from-purple-600 to-pink-600` | `from-brand to-sky` |
| 15 | `border-purple-100 hover:border-purple-300` | `border-sky-light hover:border-brand-light` |
| 21 | `from-purple-100 to-purple-50` | `from-sky-light to-sky-light` |
| 22 | `text-purple-600` | `text-brand` |
| 30 | `border-pink-100 hover:border-pink-300` | `border-blush hover:border-blush-dark` |
| 36 | `from-pink-100 to-pink-50` | `from-blush to-blush` |
| 37 | `text-pink-600` | `text-sky` |
| 60 | `from-purple-600 to-pink-500` | `from-brand to-sky` |
| 63 | `text-purple-100` | `text-white/80` |
| 79 | `from-purple-600 to-pink-500` | `from-brand to-sky` |
| 84 | `from-purple-50 to-transparent border-purple-500` | `from-brand-light to-transparent border-brand` |
| 86 | `from-purple-600 to-purple-400` | `from-brand to-brand-light` |

### Products CRUD

| File | Lines | Current | New |
|------|-------|---------|-----|
| `productos/index.blade.php:5` | Head gradient | `from-purple-600 to-pink-600` | `from-brand to-sky` |
| `productos/index.blade.php:11` | Create btn gradient | `from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600` | `from-brand to-sky hover:from-brand-dark hover:to-sky-dark` |
| `productos/index.blade.php:47` | Search input | `border-purple-200 focus:ring-purple-500 focus:border-purple-500` | `border-brand-light focus:ring-brand focus:border-brand` |
| `productos/index.blade.php:91-95` | Search results banner | `bg-purple-50 border-purple-200 text-purple-700 text-purple-600 hover:text-purple-800` | `bg-brand-light border-brand-light text-brand-dark text-brand hover:text-brand-dark` |
| `productos/index.blade.php:102,186` | Card border | `border-purple-100` | `border-sky-light` |
| `productos/index.blade.php:105` | Table header | `from-purple-50 to-pink-50 border-purple-200` | `from-sky-light to-blush border-sky-light` |
| `productos/index.blade.php:107-115` | Header text | `text-purple-900` | `text-gray-700` |
| `productos/index.blade.php:120` | Row hover | `hover:bg-purple-50` | `hover:bg-sky-light` |
| `productos/index.blade.php:123` | Badge | `from-purple-100 to-pink-100 text-purple-800` | `from-brand-light to-blush text-brand-dark` |
| `productos/index.blade.php:144` | View icon | `text-purple-600 hover:text-purple-800 hover:bg-purple-50` | `text-brand hover:text-brand-dark hover:bg-brand-light` |
| `productos/index.blade.php:152` | Edit icon | `text-pink-600 hover:text-pink-800 hover:bg-pink-50` | `text-sky hover:text-sky-dark hover:bg-sky-light` |
| `productos/index.blade.php:202` | Empty CTA | `from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600` | `from-brand to-sky hover:from-brand-dark hover:to-sky-dark` |
| `productos/create.blade.php:14` | Header | `from-indigo-600 to-indigo-700` | `from-sky to-sky-dark` |
| `productos/create.blade.php:16` | Header text | `text-indigo-100` | `text-white/80` |
| `productos/create.blade.php:32,43,61,72,83,95,107,118` | Focus rings | `focus:ring-indigo-500 focus:border-indigo-500` | `focus:ring-brand focus:border-brand` |
| `productos/create.blade.php:129` | Submit btn | `from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800` | `from-brand to-brand-dark hover:from-brand-dark hover:to-brand` |
| `productos/edit.blade.php:20` | Header | `from-amber-600 to-amber-700` | `from-brand to-brand-dark` |
| `productos/edit.blade.php:22` | Header text | `text-amber-100` | `text-white/80` |
| `productos/edit.blade.php:37,47,64,74,84,94,103,112` | Focus rings | `focus:ring-amber-500 focus:border-amber-500` | `focus:ring-brand focus:border-brand` |
| `productos/edit.blade.php:123` | Submit btn | `from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800` | `from-brand to-brand-dark hover:from-brand-dark hover:to-brand` |
| `productos/show.blade.php:31` | Header | `from-indigo-600 to-indigo-700` | `from-sky to-sky-dark` |
| `productos/show.blade.php:79-81` | Sale price card | `bg-purple-50 text-purple-600 text-purple-700` | `bg-brand-light text-brand text-brand-dark` |

### Categories CRUD

| File | Lines | Current | New |
|------|-------|---------|-----|
| `categorias/index.blade.php:5` | Head gradient | `from-purple-600 to-pink-600` | `from-brand to-sky` |
| `categorias/index.blade.php:9` | Create btn | `from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600` | `from-brand to-sky hover:from-brand-dark hover:to-sky-dark` |
| `categorias/index.blade.php:44` | Card | `border-purple-100 hover:border-purple-300` | `border-sky-light hover:border-brand-light` |
| `categorias/index.blade.php:45` | Card top | `from-purple-600 via-pink-500 to-pink-400` | `from-brand via-sky to-sky-light` |
| `categorias/index.blade.php:54` | Badge | `from-purple-100 to-pink-100 text-purple-800` | `from-brand-light to-blush text-brand-dark` |
| `categorias/index.blade.php:60` | View btn | `bg-purple-50 text-purple-600 hover:bg-purple-100` | `bg-sky-light text-brand hover:bg-brand-light` |
| `categorias/index.blade.php:68` | Edit btn | `bg-pink-50 text-pink-600 hover:bg-pink-100` | `bg-blush text-sky hover:bg-sky-light` |
| `categorias/index.blade.php:95,103` | Empty CTA | `border-purple-100 from-purple-600 to-pink-500` | `border-sky-light from-brand to-sky` |
| `categorias/create.blade.php:11` | Header | `from-indigo-600 to-indigo-700` | `from-sky to-sky-dark` |
| `categorias/create.blade.php:13` | Header text | `text-indigo-100` | `text-white/80` |
| `categorias/create.blade.php:28,41,54` | Focus/submit | `focus:ring-indigo-500` / `from-indigo-600 to-indigo-700` | `focus:ring-brand` / `from-brand to-brand-dark` |
| `categorias/edit.blade.php:20` | Header | `from-amber-600 to-amber-700` | `from-brand to-brand-dark` |
| `categorias/edit.blade.php:22` | Header text | `text-amber-100` | `text-white/80` |
| `categorias/edit.blade.php:38,51,64` | Focus/submit | `focus:ring-amber-500` / `from-amber-600 to-amber-700` | `focus:ring-brand` / `from-brand to-brand-dark` |
| `categorias/show.blade.php:30` | Header | `from-indigo-600 to-indigo-700` | `from-sky to-sky-dark` |
| `categorias/show.blade.php:41` | Count | `text-indigo-600` | `text-brand` |
| `categorias/show.blade.php:54-56` | Prod section header | `from-purple-600 to-purple-700 text-purple-100` | `from-brand to-brand-dark text-white/80` |

### Auth Views

| File | Lines | Current | New |
|------|-------|---------|-----|
| `auth/login.blade.php:3` | Title gradient | `from-purple-600 to-pink-600` | `from-brand to-sky` |
| `auth/login.blade.php:67` | Checkbox | `border-purple-300 text-purple-600 focus:ring-purple-500` | `border-brand-light text-brand focus:ring-brand` |
| `auth/login.blade.php:73,93` | Links | `text-purple-600 hover:text-purple-700` | `text-brand hover:text-brand-dark` |
| `auth/register.blade.php:3` | Title gradient | `from-purple-600 to-pink-600` | `from-brand to-sky` |
| `auth/register.blade.php:99` | Link | `text-purple-600 hover:text-purple-700` | `text-brand hover:text-brand-dark` |
| `auth/verify-email.blade.php:26` | Logout btn ring | `focus:ring-indigo-500` | `focus:ring-brand` |
| `profile/partials/update-profile-information-form.blade.php:36` | Resend btn ring | `focus:ring-indigo-500` | `focus:ring-brand` |

## Custom CSS Considerations

- **`app.css`**: No changes needed. All Tailwind directives remain the same. New color tokens are resolved by the config.
- **Contrast**: Gold (`#FFD16D`) on white fails WCAG AA for text. Mitigation: gold is used for accents (icons, decorative badges), not body text. Buttons use gradient backgrounds with white text. Sidebar uses `brand-dark` (#C8963A) for backgrounds with white text — contrast ratio ~4.3:1 at 14px, meets AA for large text but check AA for smaller. Table header text uses `gray-700`, not gold.
- **Application logo SVG**: Uses `fill-current` and inherits `text-*` color from parent. Logo in sidebar is `text-white` (inherits from parent `text-white` on the wrapper). Logo in guest layout changes from `text-purple-600` to `text-brand`. No SVG path edits needed.
- **Focus rings**: All `focus:ring-purple-500` → `focus:ring-brand`. All `focus:ring-indigo-500` → `focus:ring-brand`. The gold ring is visible on focus — good for accessibility.

## Unchanged Colors (Out of Scope)

| Token | Reason |
|-------|--------|
| `red-*` | Danger buttons, error states, low-stock badges |
| `green-*` | Success states, active counts, profit display |
| `yellow-*` | Warning alerts, edit buttons on show pages |
| `blue-*` | Purchase price card in `productos/show.blade.php` |
| `cyan-*` | Description section header in `productos/show.blade.php` |
| `gray-*` | Neutral text, borders, backgrounds (stays consistent) |

## Implementation Order

1. **`tailwind.config.js`** — Add brand/sky/blush color objects. Add brand-glow/sky-glow shadows. Remove purple/pink colors, purple-start/pink-end gradient stops, purple-glow/pink-glow shadows.
2. **Layout files** — `app.blade.php`, `guest.blade.php`, `sidebar.blade.php`, `topbar.blade.php`
3. **Components** — `primary-button`, `secondary-button`, `text-input`, `nav-link`, `responsive-nav-link`, `alert`
4. **Dashboard** — `dashboard.blade.php` (all stat cards, activity section)
5. **Products CRUD** — `index`, `create`, `edit`, `show`
6. **Categories CRUD** — `index`, `create`, `edit`, `show`
7. **Auth views** — `login`, `register`, `verify-email`
8. **Profile** — `update-profile-information-form`
9. **Final pass** — `rg "purple-|pink-|indigo-|amber-" resources/views/` to confirm zero remaining matches

## Edge Cases

- **Logo SVG**: Already uses `fill-current` — no edits. Logo inherits `text-brand` in guest layout, stays `text-white` in sidebar.
- **Danger buttons** (`danger-button.blade.php`): All `red-*` classes — UNCHANGED.
- **Green states**: Stock badges (`bg-green-100 text-green-700`), profit values, success alerts — UNCHANGED.
- **Focus rings**: Every `focus:ring-*` that referenced purple/indigo/amber → `focus:ring-brand`. Red/green/yellow rings stay as-is.
- **text-black in productos/create submit**: Currently `text-black` on `from-indigo-600 to-indigo-700` background — this is likely a bug (should be `text-white`). Fix to `text-white` as part of this change.
- **Sidebar text contrast**: `text-purple-200` on `from-purple-900 to-purple-800` bg works for contrast. When replacing with brand, use `text-white/70` for inactive nav items on `brand-dark` background to maintain WCAG AA.

## Verification

- Run `rg "purple-|pink-|indigo-|amber-" resources/views/` after all changes — expect zero matches
- Run `php artisan pint` for code style
- Visual review: check all pages render, gradients flow, focus rings visible
