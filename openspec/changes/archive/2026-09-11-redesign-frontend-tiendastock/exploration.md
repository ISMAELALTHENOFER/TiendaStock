# Exploration: Frontend Redesign of TiendaStock

Source of truth: `SDD_Redisen_Frontend_TiendaStock.md` (read in full). This exploration is read-only; no application code was modified.

## Current State

**Stack (confirmed from source)**: Laravel 12 + PHP 8.2, Blade templates, Tailwind CSS v3 (`@tailwindcss/forms` plugin), Alpine.js v3, Vite 7 + laravel-vite-plugin, PostCSS, Figtree font (fonts.bunny.net), Flatpickr 4.6.13 (CDN). App root is `src/`. No React/Inertia usage anywhere — the `@inertiajs/*`, `react`, `react-dom`, `lucide-react`, `class-variance-authority`, `clsx`, `tailwind-merge` dependencies and `resources/js/lib/utils.js` (`cn()`) are unused dead weight. `@vitejs/plugin-react` and `@tailwindcss/vite` (v4 plugin with a v3 core config) are likewise unused/leftover.

**Layout**: `layouts/app.blade.php` is a flex shell: dark `slate-950` sidebar (`layouts/sidebar.blade.php`, desktop `hidden md:flex` w-64 + duplicated mobile drawer markup) + near-empty topbar (`layouts/topbar.blade.php` — only hamburger + user dropdown; the empty space the spec complains about is real) + `main` content. Sidebar links are role-gated (ADMIN/Ventas → Ventas; ADMIN/Control Stock → Productos/Categorías; ADMIN → Usuarios), have no section groups (spec wants PRINCIPAL/OPERACIONES/INVENTARIO/ADMINISTRACIÓN), and the active state uses a gold `border-l-2 border-brand-300` accent. `layouts/navigation.blade.php` is legacy Breeze nav — dead code, not included anywhere.

**Screens**:
- **Dashboard**: 4 stat cards (3 white + 1 brand→sky gradient), role-gated quick actions (Nueva Venta POS / Nuevo Producto / Nueva Categoría / Nuevo Usuario) with inconsistent accent colors (green/brand/sky border-l-4), null-role fallback panel, and a **"Actividad Reciente" section that is 100% hardcoded fake data** ("Producto Ejemplo fue creado hace 2 horas") — there is no backend activity feed. Metrics come from a route Closure (4 counts), matching the spec's metric list.
- **Ventas (index)**: GET filter form (desde/hasta/estado → Filtrar/Limpiar) with flatpickr (inline script; note `fpConfig` is assigned without `const`, an implicit global), desktop table + mobile cards (duplicated render), inline status badges (`bg-green-100`/`bg-red-100`), delivery pills (`bg-sky-100`/`bg-amber-100`), two action icons (Ver + Anular) — already close to the spec's "Ver + ⋮" suggestion. Money via `formato_pesos()`.
- **Ventas (POS)**: full Alpine cart app (`posApp`), live search against `/productos/search`, shared `moneyInput` ARS mask for Descuento/Pago con, delivery-type cards, change calculation, JSON POST to `/ventas` with CSRF. Cart column uses `order-1 lg:order-2` (cart on top on mobile). Sticky cart on desktop.
- **Ventas (show)**: printable receipt (`no-print` buttons), max-w-2xl.
- **Productos (index)**: client-side catalog app (`productSearch` Alpine.data) fetching `/productos/data` JSON; OR text search across nombre/categoria/talle/color + categoria/talle/color filters; 15-row client pagination; desktop table + mobile cards; stock badge red when `cantidad <= 5`; 3 action icons (Ver/Editar + Desactivar|Activar toggle forms with `confirmDialogShow`). CSRF read from meta.
- **Productos (create/edit)**: card with `from-brand-400 to-brand-500` gradient header, ad-hoc inputs (`border-gray-300 rounded-lg focus:ring-brand-300`) — **different style from the Breeze `x-text-input` component** (`form-control` + CSS-var border). Shared Alpine partials: `moneyInput`, `duplicateCheck` (advisory duplicate check), `inlineCategory` (modal POST to `/categorias/inline`), `productImage` preview.
- **Categorías**: card grid with `from-brand-300 via-sky-300 to-sky-100` gradient banners; Ver/Editar/Eliminar — **delete uses native `confirm()`**, inconsistent with `confirmDialogShow` used elsewhere.
- **Usuarios (admin)**: table with `from-sky-50 to-blush-50` gradient thead (different from the `bg-sky-50` theads elsewhere), role badges, single edit action; mobile cards.

**Design tokens / CSS**: three sources of truth that will all need to change together:
1. `tailwind.config.js` — custom `brand` (gold `#D8A62A` family), `sky` (teal), `blush` (pink) palettes + `brand-glow`/`sky-glow` shadows. **This conflicts with the spec's green primary + neutral SaaS direction** (the prior `color-palette-redesign` change swapped purple/pink → gold/teal/pink).
2. `resources/css/app.css` `:root` vars (`--primary: #d8a62a`, `--focus: #286779`, etc.) — duplicated palette values.
3. Hardcoded hex in views (`bg-[#fffdf9]` topbar/modal/confirm-dialog) and the flatpickr inline `<style>` block (`#d8a62a` selected days).

Component CSS classes exist and are partially used: `.surface-panel`, `.page-title`, `.action-button`, `.form-control`, `.eyebrow`; `.status-badge` and `.empty-state` are **defined but unused** (badges are repeated inline utilities everywhere). The `landing-page` suite (~100 lines) lives in app.css and will inherit token changes.

**JS**: `app.js` only boots Alpine; `bootstrap.js` sets axios. All interactivity is Blade-embedded: Alpine.data in partials (`moneyInput`, `duplicateCheck`, `inlineCategory`, `productImage`) and inline page components (`productSearch`, `posApp`), plus global functions `confirmDialogShow/Hide` and `showFlash` (confirm-dialog + flash-toast components). No component encapsulation for these globals yet.

**Backend coupling (must not break)**: routes `dashboard`, `ventas.*`, `productos.*`, `categorias.*`, `admin.users.*`, `profile.*`, auth, landing; JSON endpoints `/productos/search`, `/productos/data`, `/categorias/inline`, `/productos/check-duplicate`; role middleware gates (`ADMIN`, `Ventas`, `Control Stock`); GET filter params `desde/hasta/estado`; CSRF + AJAX flows; session flash toasts; print receipt. No chart data endpoints exist → spec §10.4 charts would require backend work or deferral.

**Responsive**: already substantial — sidebar drawer with focus-trap/return-focus, mobile card views for ventas/productos/users tables, `overflow-x-auto` table regions, POS column reorder, safe-area insets, `prefers-reduced-motion` support, clamp()-based typography in app.css. Gaps are polish-level (8-column sales table still needs horizontal scroll on md, filter grids stack, header/topbar on narrow widths, categorias grid is fine but cards carry gradients).

**Testing (strict TDD, phpunit + SQLite :memory:)**: 24+ feature test files exist. **43 `assertSee`/`assertSeeInOrder` assertions couple tests to view content**, including `DashboardRoleVisibilityTest` (nav text), `VentaPosTest` (delivery labels, receipt text), `PublicLandingPageTest` (landing copy), `ProductoMoneyFormatTest` (`$1.234,56`, "Costo"), and — critically — `ProductoActivateTest` line 122 asserts the literal class string `'bg-green-600 hover:bg-green-700'` inside the JS `confirmDialogShow` call. A redesign that changes button/badge colors or copy WILL break these tests unless tests are updated in the same change.

## Affected Areas

- `src/tailwind.config.js` — palette (green primary per spec), font family (Figtree → Inter/Plus Jakarta Sans), tokens (radius, shadow, spacing).
- `src/resources/css/app.css` — `:root` vars, component classes (`.action-button`, `.form-control`, `.status-badge`), landing-page tokens; consolidate the three color sources into one token set.
- `src/resources/views/layouts/{app,sidebar,topbar}.blade.php` — shell restyle, sidebar sections/collapse, topbar fill; remove `layouts/navigation.blade.php` (dead).
- `src/resources/views/components/*` — extract/unify Badge, Button variants, Card, Input/Select, Table shell, Dropdown; reuse existing Breeze components; re-theme `confirm-dialog`, `flash-toast`, `modal`, `alert`.
- `src/resources/views/{dashboard,ventas/*,productos/*,categorias/*,admin/users/*}.blade.php` — per-screen restyle; **the inline `<script>` Alpine components and `confirmDialogShow(...)` calls with class strings must move to tokens with tests updated**.
- `src/resources/views/partials/_money-input.blade.php`, `productos/partials/_alpine.blade.php` — shared Alpine partials keep behavior, restyle only.
- `src/resources/views/auth/*`, `layouts/guest.blade.php`, `landing.blade.php`, `layouts/landing.blade.php` — share tokens/font; keep copy (tested by `PublicLandingPageTest`).
- `src/resources/js/app.js`, `bootstrap.js` — no change expected; `lib/utils.js` removable (dead).
- `src/package.json` — decision: drop unused React/Inertia/lucide/cva/clsx/tailwind-merge, `@vitejs/plugin-react`, `@tailwindcss/vite`; move `alpinejs` to `dependencies` (hygiene, optional).
- `src/tests/Feature/*` — ~43 `assertSee` assertions must be updated in the same change where copy/class strings change (TDD contract).
- `src/app/Helpers/helpers.php` — unchanged (money formatting contract).

## Approaches

1. **Token-first extraction + screen restyle (recommended)** — Define design tokens (colors incl. green primary, typography scale, radius, shadow, spacing) as the single source of truth in `tailwind.config.js` + `app.css` vars; extract shared components (Badge, Button variants, Card, Input/Select, Table, EmptyState, LoadingState); then restyle screens in the spec's order (tokens → components → layout → dashboard → ventas → productos → categorías → usuarios → responsive/feedback polish), updating the coupled tests alongside.
   - Pros: respects spec's componentization and "no CSS duplication" rules; reuses existing Breeze components and Alpine partials; smallest behavioral risk (logic untouched); token swap gives instant consistency.
   - Cons: bigger up-front token/component work before visual payoff; test updates interleaved.
   - Effort: High (spans all views) but lowest risk.

2. **Screen-by-screen restyle without token consolidation** — Restyle each Blade view directly, keeping current class strings.
   - Pros: fast first visual wins; no test churn beyond changed screens.
   - Cons: duplicates the current inconsistency problem; violates spec's "unify colors/typography/components" and "no duplicated CSS" goals; three color sources of truth remain; more total work re-doing each screen twice.
   - Effort: Medium per screen, High overall, Medium quality ceiling.

3. **Component-library-first rebuild** — Extract a full Blade component library (all spec §7 components) before touching any screen, then rewire every view to use them.
   - Pros: maximal reuse; cleanest end state.
   - Cons: large opaque diff; every view rewired at once (highest regression surface against the 43-test coupling and Alpine logic); violates small-task chaining; not lazy — many components already exist (Breeze set).
   - Effort: Very High.

## Recommendation

Approach 1 (token-first extraction + progressive screen restyle), executed as the spec's Fase 3 task order. Reuse what exists: Breeze components (`x-text-input`, `x-primary/secondary/danger-button`, `x-modal`, `x-dropdown`), custom `x-confirm-dialog`, `x-flash-toast`, `x-alert`, CSS classes `.surface-panel`/`.page-title`/`.action-button`/`.form-control`, and the shared Alpine partials (`moneyInput`, etc.). Extract only the missing primitives the screens actually duplicate: **Badge** (`.status-badge` exists unused), **Button** variants, **Card**, **Input/Select** (unify the three current input styles), **Table** (thead/row shell), **EmptyState**, **LoadingState**. Resolve the palette conflict by remapping the `brand` family to the spec's green primary (mirroring the successful prior `color-palette-redesign` class-to-class swap) and flatten gold/teal/pink leftovers; delete hardcoded hex + flatpickr overrides in the same step. Keep every route, endpoint, form param, role gate, and Alpine behavior identical; change copy only where tests are updated in the same change.

## Risks

- **TDD breakage**: 43 `assertSee` couplings, incl. a literal class-string assertion (`bg-green-600 hover:bg-green-700` in `ProductoActivateTest`). Any color/copy change requires updating tests within the same change — plan it explicitly in tasks; do not let the redesign silently invalidate the suite.
- **Palette conflict with three sources of truth**: spec wants green primary; current truth lives in `tailwind.config.js` (gold `brand`), `app.css` `:root`, and hardcoded hex + flatpickr inline CSS. Missing one breaks consistency (e.g., date picker stays gold).
- **Dashboard "Actividad Reciente" is fake data**: spec says keep the logic that generates activities, but there is none — it's static HTML. Decision needed: remove the section, or add a real backend activity feed (a backend change the spec otherwise forbids).
- **Charts (§10.4)**: no sales-history chart data exists in the backend; must be deferred and documented, not faked.
- **Global JS functions** (`confirmDialogShow`, `showFlash`) carry class strings into JS strings — token changes must update them; extraction into an Alpine component is recommended but touches every caller.
- **Dead dependencies** (React/Inertia/lucide/cva/clsx/tailwind-merge, `@vitejs/plugin-react`, `@tailwindcss/vite` v4 with v3 core): removing them is hygiene but out of the visual scope — needs explicit approval to avoid scope creep.
- **Duplicated desktop/mobile markup** (sidebar, tables): extraction must preserve `x-for`/`x-template`/form IDs used by the Alpine flows (`disable-form-*`, `card-cancel-*`, etc.).
- **Responsive regressions**: existing mobile layouts are good; restyling can regress them. No E2E tooling exists (only PHPUnit feature tests) — verify manually per the responsive-design checklist or accept the risk.
- **Landing/auth screens share tokens and copy**: `PublicLandingPageTest` and auth tests assert copy; scope them explicitly (default: token-only, keep copy).

## Ready for Proposal

Yes. Tell the user the proposal must resolve these decisions first: (1) green-primary palette mapping over the current gold/teal/pink tokens (incl. flatpickr + CSS vars); (2) what to do with the hardcoded "Actividad Reciente" section (remove vs. real backend feed); (3) deferral of §10.4 charts (no backend data); (4) explicit approval to update the ~43 view-content test assertions and remove the unused React/Inertia dependencies as part of this change.