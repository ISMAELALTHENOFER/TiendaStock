# Exploration: Dashboard Redesign

## Current State

The archived `redesign-frontend-tiendastock` change already delivered the React shell, responsive drawer, role-filtered navigation, shared UI primitives, real activity endpoint, and real analytics endpoint. Its archived verification records 14/14 requirements, 22/22 scenarios, 264 PHPUnit tests, Pint, and Vite build passing at that time.

The supplied specification is therefore **partially satisfied but divergent**. The existing shell provides a mobile drawer with backdrop, Escape handling, focus return/trap, scroll locking, grouped navigation, role visibility, user footer, a compact header, responsive KPI/action grids, and explicit loading/empty/error states. However, the desktop sidebar is a flex item rather than a fixed `100dvh` independent region; it can grow with page content instead of remaining viewport-height with independently scrolling navigation.

The dashboard currently shows the four legacy inventory metrics, role-gated quick actions, up to 20 persisted activity events, and textual daily/category analytics for a hard-coded 30-day window. It does not provide the requested sales KPI set, selectable period, graphical sales visualization, top-products widget, low-stock widget, shared login isotipo, skeleton loaders, or route-aware active navigation. The active sidebar item is currently always Dashboard.

## Affected Areas

- `src/resources/js/react/layout/AppShell.jsx` — desktop shell ownership, viewport-height behavior, main-content offset, drawer state, and responsive layout.
- `src/resources/js/react/layout/Sidebar.jsx` — fixed `100dvh` desktop sidebar, independently scrolling navigation, footer anchoring, route-aware active state, brand isotipo, and optional desktop collapse behavior.
- `src/resources/js/react/layout/Header.jsx` — compact sticky header and existing user menu; profile entry only if its existing route is passed through the contract.
- `src/resources/js/react/dashboard.jsx` — KPI hierarchy, responsive widget grid, period selector, sales visualization, top-products, low-stock, activity limit, and widget-specific states.
- `src/resources/js/react/components/ui/{Card,LoadingState,EmptyState,ErrorState,Button}.jsx` — reuse existing primitives; evolve loading presentation to skeletons only where the dashboard needs stable widget dimensions.
- `src/resources/css/app.css` and `src/tailwind.config.js` — existing shared Inter/green/neutral tokens, responsive rules, sidebar height/positioning, visual consistency with login, and reduced-motion-safe transitions.
- `src/routes/web.php` and the dashboard host/controller that supplies `metrics`, `routes`, and `user` — additive dashboard payload/routes only; preserve Laravel authorization and named-route rollback.
- `src/app/Services/DashboardAnalytics.php` and `src/app/Http/Controllers/DashboardAnalyticsController.php` — validate only the approved period values and extend real aggregation contracts for sales KPIs/top products if required.
- New focused dashboard-query service/controller or an extension of the existing dashboard analytics boundary — supply real today/month totals, sale counts, top products, and low-stock data without querying from React views.
- `src/tests/Feature/DashboardRoleVisibilityTest.php`, dashboard activity/analytics endpoint tests, and `src/tests/Unit/DashboardAnalyticsTest.php` — preserve role/rollback contracts and add RED coverage for any new backend contract and visible dashboard behavior.

## Approaches

1. **Scoped dashboard successor (recommended)** — retain the React shell, primitives, and existing activity/analytics contracts; make targeted shell fixes and add one real dashboard-summary contract for only the missing approved data.
   - Pros: addresses the supplied specification without redoing the archived migration; preserves Laravel authority, rollback, and current dependencies.
   - Cons: requires additive query/tests for sales KPIs, top products, and low stock; a lightweight chart still needs an implementation decision.
   - Effort: Medium.

2. **Presentation-only refinement** — fix sidebar height, layout, copy, active state, branding, and textual analytics without adding new metrics/widgets.
   - Pros: smallest diff and no backend change.
   - Cons: does not meet the supplied requirements for sales KPIs, top products, or stock attention; would leave the redesign incomplete.
   - Effort: Low.

3. **Rebuild the archived frontend redesign** — replace shell/tokens/components before adding widgets.
   - Pros: none supported by current evidence.
   - Cons: duplicates an already verified implementation, increases regression risk, and violates the requirement to reuse existing work.
   - Effort: High.

## Recommendation

Create `dashboard-redesign` as a scoped successor. First correct the desktop sidebar to a fixed `100dvh` flex column with scrollable navigation and footer anchored at the viewport bottom; retain the already working mobile drawer. Then add only real, server-prepared dashboard summary data for the requested sales/stock/product widgets, constrain the period selector to supported values, and render the chart without a new dependency unless the existing stack cannot produce an accessible lightweight SVG/CSS visualization. Reuse `lucide-react`, shared primitives, green/neutral tokens, and Laravel JSON/CSRF/authorization boundaries.

## Risks

- The mandatory desktop-sidebar behavior requires a source-level layout change that can affect every migrated React route using `AppShell`; preserve the mobile drawer and tablet collapse contracts.
- The specification requests sales KPIs, top products, and low-stock alerts that the current dashboard contracts do not supply; do not hardcode or fabricate them.
- A chart library is not currently installed. Adding one would increase bundle/review cost; native SVG or an accessible summary should be preferred until evidence requires a library.
- The project has no browser/E2E runner, so responsive drawer, focus, viewport, and chart behavior need a bounded manual/browser check in addition to PHPUnit and Vite build.
- The preflight requires a single PR with a 400-line review policy; this scope is likely to exceed that budget, so tasks must forecast it and request an explicit exception or reduce scope before apply.

## Ready for Proposal

Yes — as a scoped successor, not a duplicate redesign. The proposal should preserve the archived React/Blade rollback boundary, specify the exact dashboard-summary JSON contract and stock threshold source, and state whether a lightweight native chart is sufficient before any dependency is proposed.
