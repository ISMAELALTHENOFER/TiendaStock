# Tasks: TiendaStock React Frontend Redesign

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Session review budget | 10000 lines |
| Estimated changed lines | 6500–10000 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Split | PR 1 → PR 7, feature branch chain |
| Delivery strategy | auto-chain |

Decision needed before apply: No
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | PR (base) | Focused test command | Runtime harness | Rollback boundary |
|------|------|-----------|----------------------|-----------------|-------------------|
| 1 | Backend activity+analytics | PR 1 (tracker) | `cd src; vendor/bin/phpunit --filter "Activity\|Analytics"` | `migrate:fresh --seed && serve`; sale → `/dashboard/activity` | Revert migration+routes; additive |
| 2 | Tokens+React scaffold | PR 2 (PR 1) | `cd src; vendor/bin/phpunit --filter "ProductoActivateTest\|PublicLandingPageTest" && npm run build` | `npm run dev`; dashboard 320/1440px | Revert config/css/package/vite |
| 3 | Design system+shell+dashboard | PR 3 (PR 2) | `cd src; vendor/bin/phpunit --filter "Dashboard" && npm run build` | `FRONTEND_DRIVER=react`; `blade` restores old | Drop dashboard route-map entry |
| 4 | Ventas index/POS/show | PR 4 (PR 3) | `cd src; vendor/bin/phpunit --filter "Venta"` | Browser POS sale, cancel, print | Drop ventas entries |
| 5 | Productos+Categorías | PR 5 (PR 4) | `cd src; vendor/bin/phpunit --filter "Producto\|Categoria"` | Browser CRUD, search, activate | Drop route-map entries |
| 6 | Usuarios | PR 6 (PR 5) | `cd src; vendor/bin/phpunit --filter "User"` | Browser CRUD as ADMIN | Drop route-map entry |
| 7 | a11y/responsive+regression+cleanup | PR 7 (PR 6) | `cd src; vendor/bin/phpunit && vendor/bin/pint --test && npm run build` | Matrix 320–1600+, keyboard, reduced-motion, print | Cleanup only; deps revertible |

Trace keys: SH/AC/AN/VE specs, R# = requirement order. Threat-matrix rows N/A per design.

## Phase 1: Backend Foundation (RED-first)

- [x] 1.1 RED Unit `ActivityRecorderTest`+`ActivityEventTest`: record only post-commit; 20-newest, 90-day retention (AC-R1)
- [x] 1.2 RED Unit `DashboardAnalyticsTest`: 30-day daily/category from `ventas`/`venta_items`; cancelled excluded; empty arrays (AN-R1,R2)
- [x] 1.3 RED Feature `DashboardActivityEndpointTest`: auth+verified, guest redirect/403, `{data,meta}` limit=20 (AC-R2)
- [x] 1.4 RED Feature `DashboardAnalyticsEndpointTest`: default `?window=30`, 422 invalid, empty arrays (AN-R1)
- [x] 1.5 Migration `activity_events` (actor_id FK, type/title/description, subject_type/id, occurred_at, index) + `ActivityEvent` — `src/database/migrations/`, `src/app/Models/` (AC-R1)
- [x] 1.6 Services `ActivityRecorder`+`DashboardAnalytics`, controllers, routes — `src/app/Services/`, `src/app/Http/Controllers/`, `src/routes/web.php` (AC-R2, AN-R1)
- [x] 1.7 GREEN: record post-success in Venta/Producto/Categoria/User controllers; `cd src; vendor/bin/phpunit` green (AC-R1)

## Phase 2: Tokens + React Scaffold

- [x] 2.1 Single token source `src/tailwind.config.js`+`src/resources/css/app.css`: green primary, neutral canvas/surface/ink/border, Inter, 4/8 rhythm, 8/12px radii, subtle shadow; delete hex+flatpickr override (SH-R2)
- [x] 2.2 Same change: update color/copy-coupled assertions (`ProductoActivateTest` class string, badge classes) (SH-R2)
- [x] 2.3 `src/vite.config.js` React entry; keep react/react-dom/lucide-react/cva/clsx/tailwind-merge; drop `@inertiajs/*`, `@tailwindcss/vite` (SH-R1)
- [x] 2.4 Create `src/resources/js/react/{main.jsx,app.jsx}` + `lib/api.js` fetch/CSRF/422 client + `lib/utils.js` cn() (SH-R1)
- [x] 2.5 Verify `npm run build` + full `cd src; vendor/bin/phpunit` green; Blade renders with new tokens (SH-R2)

## Phase 3: Design System + Shell + Coexistence

- [x] 3.1 `src/resources/js/react/components/ui/{Button,Input,Select,Badge,Card,Table,Modal,Dropdown,EmptyState,LoadingState,Toast}.jsx`: variants, no duplicated styles, 44px targets, focus-visible, disabled/error, 150–250ms + reduced-motion (SH-R2,R5)
- [x] 3.2 `src/config/frontend.php` named-route map: `FRONTEND_DRIVER` default + per-route override (SH-R1, VE-R3)
- [x] 3.3 Blade host `src/resources/views/react/app.blade.php` (mount div, CSRF meta, flash bridge) via driver switch in `src/resources/views/layouts/app.blade.php` (SH-R1)
- [x] 3.4 `src/resources/js/react/layout/{AppShell,Sidebar,Header}.jsx`: grouped role-gated nav, drawer+backdrop (Escape, focus trap/return), tablet collapsible, desktop fixed, compact header (SH-R2,R4,R5)
- [x] 3.5 Rework `DashboardRoleVisibilityTest`: host mount + preserved role gates (SH-R2,R3)

## Phase 4: Dashboard

- [x] 4.1 Dashboard: 4 metrics + role-gated quick actions, Nueva Venta primary (SH-R3)
- [x] 4.2 Activity feed `GET /dashboard/activity` (no cache): loading/empty/error+retry, refetch post-mutation (AC-R3)
- [x] 4.3 Charts `GET /dashboard/analytics?window=30` (no cache): daily+category series, loading/empty/error, readable labels (AN-R3)
- [x] 4.4 Dashboard route override + global and per-route Blade rollback checks (SH-R1)
- [x] Correction PR3: bind `AppShell.open` to the tablet sidebar rail width/state at 768–1023px; preserve desktop and mobile behavior.

## Phase 5: Screen Migrations (route-switch per slice)

- [x] 5.1 Ventas index: filters desde/hasta/estado, 8-col table, Badge states, Ver+⋮ (Cancelar only completada), money format, mobile cards (VE-R1,R2)
- [x] 5.2 POS: stacked zones, `/productos/search`, cart, ARS mask, change calc, delivery types, POST `/ventas` CSRF, validation/stock/payment errors (VE-R1,R2)
- [x] 5.3 Ventas show: printable receipt, `no-print` actions (VE-R1)
- [x] 5.4 Productos: list (`/productos/data`, filters, 15-row pagination, stock ≤5 badge, activate toggle), create/edit (moneyInput, duplicateCheck, inlineCategory, productImage) (SH-R3)
- [x] 5.5 Categorías: grid CRUD, delete via confirm dialog replacing native `confirm()` (SH-R3)
- [x] 5.6 Usuarios (ADMIN): table+mobile cards, role badges, edit flow (SH-R3)
- [x] 5.7 Per slice: rework coupled `assertSee` tests to React-mount + preserved contracts in same change (SH-R1, VE-R1)
- [x] Correction PR4: parse ARS payment input before POS gating, preserve sales filters in pagination URLs, and retain the complete React receipt contract (VE-R1)

## Phase 6: Verification + Cleanup

- [x] 6.1 `cd src; vendor/bin/phpunit && vendor/bin/pint --test && npm run build` green (all)
- [x] 6.2 Browser matrix 320/412/768/1024/1440/1600+: no horizontal scroll/clipped focus, usable forms/tables, zoom, long labels, loading/empty/error (SH-R4,R5; VE-R2)
- [x] 6.3 Keyboard/touch: tab order, Escape, focus return, 44px, reduced-motion, print; remove migrated Blade views post-green; document `FRONTEND_DRIVER` (SH-R5, VE-R3). Nota: la sub-tarea "remove migrated Blade views" quedó superada por la decisión documentada en apply-progress §Blade preservation — los tests de rollback por ruta y global (`FRONTEND_DRIVER=blade`) renderizan esas vistas y son parte de la suite verde; el contrato de rollback del diseño prevalece sobre el micro-paso de limpieza.
