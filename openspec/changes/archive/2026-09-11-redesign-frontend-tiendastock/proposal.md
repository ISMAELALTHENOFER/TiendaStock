# Proposal: TiendaStock React Frontend Redesign

## Intent

Replace the inconsistent Blade + Alpine presentation layer with a coherent, responsive React frontend while preserving Laravel business behavior, authorization, routes, forms, and existing API contracts. The redesign also replaces fake dashboard activity with persisted backend data and adds real backend-backed chart data rather than placeholders.

## Scope

### In Scope
- Establish React/Vite integration and a reusable SaaS design system for the application shell, dashboard, sales, products, categories, users, feedback, and responsive states.
- Migrate screens progressively from Blade + Alpine, with explicit Blade/React coexistence and unchanged public route names.
- Add authenticated recent-activity persistence/query boundaries and dashboard chart endpoints/data contracts; update tests and required backend controllers/services.
- Preserve `/ventas`, `/ventas/pos`, `/productos/*`, `/categorias/*`, `/admin/users/*`, auth, profile, CSRF, role middleware, filters, JSON search/data endpoints, flash behavior, and printable sales details unless a compatibility adapter is required.

### Out of Scope
- Rewriting domain rules, permissions, sales transactions, inventory semantics, or unrelated backend modules.
- New product features such as global search or notifications unless already supported by backend contracts.
- Removing Blade/Alpine in one atomic cutover; legacy surfaces remain until their React replacements are verified.

## Capabilities

### New Capabilities
- `react-frontend-shell`: React application shell, design system, responsive navigation, and migration boundary.
- `dashboard-activity`: Backend-backed authenticated recent activity feed with empty/loading/error states.
- `dashboard-analytics`: Backend-backed dashboard chart data with explicit aggregation and authorization contracts.

### Modified Capabilities
- `ventas`: React presentation must preserve existing POS, history, detail, filtering, cancellation, permission, and print behavior.

## Approach

Use token-first design-system extraction, then migrate route surfaces in vertical slices. React consumes Laravel-provided contracts through a documented integration boundary; existing route/API payloads remain compatible, with additive endpoints only for activity and charts. Coexistence uses route-level ownership (React replaces one surface at a time while Blade + Alpine continues elsewhere). Rollout is feature-flag or route-switch based where practical, with rollback to the prior Blade surface per route.

Unresolved architecture choices for Design: Inertia versus JSON/API-driven React; single React shell versus route-level islands; activity event schema/retention and chart aggregation windows; endpoint versioning and caching policy. These must be decided before implementation, not inferred here.

## Affected Areas

| Area | Impact | Description |
|---|---|---|
| `src/resources/js`, `src/vite.config.js`, `src/package.json` | Modified | React entrypoints, build integration, dependencies. |
| `src/resources/views`, `src/resources/css`, `src/tailwind.config.js` | Modified | Coexistence bridge and shared visual tokens. |
| `src/routes/web.php`, `src/app/Http/Controllers`, `src/app/Models`, `src/database/migrations` | New/Modified | Activity and chart contracts/storage. |
| `src/tests` | Modified | Contract, authorization, regression, and rendering tests. |

## Risks

| Risk | Likelihood | Mitigation |
|---|---|---|
| React migration regresses route/permission/form behavior | High | Vertical slices, contract tests, route-level rollback. |
| Activity/chart data becomes inconsistent or expensive | Medium | Define ownership, aggregation, indexes, and empty/error semantics in Design. |
| Blade/React duplication increases maintenance cost | Medium | Time-box coexistence and remove each legacy surface after verification. |

## Rollback Plan

Disable the migrated route/switch, restore the previous Blade + Alpine entrypoint, and revert additive frontend/backend migrations and endpoints only after data compatibility is confirmed. Preserve existing sales/inventory routes and records throughout rollback.

## Dependencies

- React integration decision (Inertia or JSON boundary), activity event source, chart aggregation contract, and browser-level responsive verification approach.

## Success Criteria

- [ ] All principal screens operate through React without changing authorized Laravel route behavior.
- [ ] No fake activity or chart values are rendered; authenticated users receive real data or explicit empty/error states.
- [ ] Existing PHPUnit/lint contracts pass, and responsive keyboard/touch behavior is verified across mobile, tablet, and desktop.
