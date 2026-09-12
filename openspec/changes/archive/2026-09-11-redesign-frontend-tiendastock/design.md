# Design: TiendaStock React Frontend Redesign

## Technical Approach

Migrate now to JSON-driven React, not Inertia. Laravel remains the authority for named routes, middleware, validation, redirects, CSRF, flash messages, printing, and mutations. A Blade host mounts one shared React shell per migrated route; legacy Blade/Alpine remains the owner of unmigrated routes. Add only authenticated activity and analytics endpoints.

## Architecture Decisions

| Decision | Choice | Rationale |
|---|---|---|
| Boundary | React fetch client + Blade host | Existing routes and AJAX contracts are Laravel/JSON; no Inertia server integration exists. |
| Ownership | Route-level, one DOM owner | Preserves URLs and makes rollback isolated. |
| Activity | Explicit `ActivityEvent` writes after committed mutations; 20 newest, 90-day retention | Avoids observer/test side effects; bounded persisted feed, not notifications. |
| Charts | `/dashboard/analytics?window=30`; completed sales, daily and category totals | Uses existing `ventas`/`venta_items`; never fabricates values. |
| Cache/auth | No browser/server cache for dashboard JSON; refetch after mutations; existing auth/verified/role middleware | Correctness is safer than speculative cache invalidation; permissions do not move to React. |

## Data Flow

```text
Laravel route + middleware → Blade host/CSRF → React shell → existing mutations/JSON
Successful mutation → ActivityRecorder → activity_events
ventas + venta_items → Analytics service → dashboard chart contract
```

## Route Compatibility Matrix

| Surface | Required preservation and RED coverage |
|---|---|
| Dashboard | `dashboard`, auth+verified; four current metrics, role-gated actions, persisted activity, real charts. |
| Ventas/POS/detail | `ventas.index/pos/show/store/cancel`, `ADMIN,Ventas`; filters, `/productos/search`, validation, redirects, cancellation/stock restore, Spanish flash, receipt print and `no-print`. |
| Productos | `productos.*`, `search/data/check-duplicate/activate`, `ADMIN,Control Stock`; JSON shapes, validation, soft-disable/reactivate and redirects. |
| Categorías | `categorias.*`, `categorias.inline`, `ADMIN,Control Stock`; CRUD, JSON 201/422, validation and deletion rule. |
| Usuarios | `admin.users.*`, `ADMIN`; CRUD, pagination, validation and redirects. |
| Auth/profile | `auth.php`, `profile.*`; guest/auth/verified middleware, login/logout/reset/verification/password/profile flows and redirects. |
| Cross-cutting | POST/PATCH/DELETE include Laravel CSRF; flash renders once; unauthorized access retains current 302/403 behavior. |

## File Changes

| File | Action | Description |
|---|---|---|
| `src/resources/js/{app.js,react/**}` | Modify/Create | React bootstrap, shell, primitives, adapters, fetch/CSRF client. |
| `src/resources/views/react/**`, `layouts/app.blade.php` | Create/Modify | Route hosts and coexistence bridge. |
| `src/config/frontend.php`, `src/routes/web.php` | Create/Modify | Route ownership switch; dashboard/activity/analytics routes. |
| `src/app/{Models/ActivityEvent.php,Services/{ActivityRecorder,DashboardAnalytics}.php,Controllers/**}`, `database/migrations/*activity*` | Create/Modify | Persistence, aggregation, post-success recording. |
| `src/resources/{css/app.css,../tailwind.config.js}`, `package.json`, `vite.config.js` | Modify | Approved tokens, responsive rules, React entry. |
| `src/tests/{Feature,Unit}/**` | Modify/Create | RED compatibility, data, authorization, build/host contracts. |

## Interfaces / Contracts

`GET /dashboard/activity` → `{data:[{id,type,title,description,actor,subject,occurred_at}],meta:{limit}}`. `GET /dashboard/analytics?window=30` → `{window:{days,from,to},series:{sales_by_day:[{date,total,count}],sales_by_category:[{category,total,count}]}}`; invalid window 422, no data empty arrays.

## Mobile-first and Design-System Task Boundaries

1. **Tokens/primitives**: Inter; green primary; neutral canvas/surface/ink/border; 4/8 spacing rhythm; 8px controls, 12px cards; subtle shadow; Button primary/secondary/danger/ghost/icon, Input/Select states, semantic Badge, Card/Table, loading/empty/error. Acceptance: no duplicated equivalent styles.
2. **Shell**: mobile stacked base; tablet collapsible sidebar; mobile drawer/backdrop; desktop fixed sidebar; compact header. Acceptance: Escape, focus trap/return, keyboard-visible focus, 44px touch targets, no scroll lock leak.
3. **Screens**: filter controls reflow; cards stack; tables remain semantic with intentional horizontal scroll or mobile cards, never clipped/hidden. Acceptance: all route matrix actions remain available.
4. **Motion/verification**: 150–250ms transitions, `prefers-reduced-motion` disables motion. Verify 320–412px mobile, 768px tablet, 1024–1440px desktop, 1600px+ large desktop, zoom, long labels, loading/empty/error, keyboard/touch and printing.

## Testing Strategy

Unit tests cover recorder, retention and aggregation. Feature tests cover every matrix row, permissions, CSRF, redirects, flash, cancellation, endpoint contracts and rollback switch. Build plus bounded browser/manual checks cover React mount and responsive/accessibility behavior; no E2E runner exists.

## Threat Matrix

All supplied rows are `N/A`: documentation-like paths, Git selection, commit state, push state and PR commands are unchanged; no shell, VCS or PR automation is introduced. Routing is covered by the compatibility matrix and RED tests above.

## Migration / Rollout

`src/config/frontend.php` owns a named-route map. Migrated routes default to `react`; unmigrated routes default to `blade`. `FRONTEND_DRIVER=blade` is the emergency global rollback; route-level overrides disable one slice. Rollback preserves routes, records and activity data. Legacy views are removed only after compatibility tests pass.

## Open Questions

None. The stated activity, chart, authorization and no-cache values are approved implementation defaults, not silently introduced business behavior.
