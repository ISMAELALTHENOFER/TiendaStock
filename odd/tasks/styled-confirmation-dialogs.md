# Consistent styled confirmation dialogs

## Objective
Ensure every application action that asks the user to accept or cancel uses the existing styled confirmation dialog pattern.

## Problem and why
Some routes already use the shared confirmation UI while React sale cancellation and the Blade category deletion fallback still invoke native browser confirmations. Those native prompts break visual consistency and the product activation/deactivation dialogs provide the incumbent reference.

## Scope and constraints
- Preserve the existing React `ConfirmDialog`, Blade `<x-confirm-dialog />`/`confirmDialogShow(...)`, product confirmation visuals, and all existing server-side actions and validation.
- Replace only unstyled accept/cancel prompts; retain non-confirmation alerts and validation feedback unless required to preserve an existing flow.
- Keep Spanish UI copy and existing mobile/desktop behavior.
- Preserve all unrelated worktree changes; do not stage, commit, push, or create a branch.
- TDD: strict mode enabled; source: project testing-capability observation `sdd-init/tiendastock`; runner: `php artisan test`.
- Delivery strategy: `ask-on-risk`; forecast: under 400 authored changed lines.

## Authorized scope
- `src/resources/js/react/sales.jsx`
- `src/resources/views/categorias/index.blade.php`
- Relevant existing feature/view contract tests
- This task record and its Engram mirror

## Acceptance criteria
- Sale cancellation on the React sales history/detail surfaces uses the same styled React confirmation as other React actions and still submits the existing cancellation form only after confirmation.
- Category deletion on the Blade fallback uses the existing styled Blade confirmation, matching the React category behavior and preserving its DELETE request.
- Existing product, category, sales, and other styled confirmation flows remain unchanged.
- Informational/validation alerts are not misclassified as accept/cancel confirmations.
- Focused tests and `npm run build` pass; unavailable checks are recorded honestly.

## Tasks
- [x] SC-1 — Added failing view-contract coverage, observed RED for both native-confirm paths, replaced them with the existing stack-specific styled dialogs, and verified the focused tests/build.

## Verification
- `php artisan test --filter=VentaReactViewContractTest`
- `php artisan test --filter=CategoriaReactViewContractTest`
- `npm run build`
- Structural readback of both confirmation call paths and `git diff --check`.

## Progress and next step
- React sale cancellation now opens the shared `ConfirmDialog` and submits its existing POST form only after confirmation. Blade category deletion uses the app layout's existing `<x-confirm-dialog />` through `confirmDialogShow(...)`, retaining the DELETE form. Informational `alert()` feedback and all unrelated worktree changes remain untouched.
- RED observed: both focused suites failed first on the expected missing styled-confirmation contracts. GREEN: `php artisan test --filter=VentaReactViewContractTest` (8 passed, 68 assertions) and `php artisan test --filter=CategoriaReactViewContractTest` (6 passed, 26 assertions). The category test initially needed a PHP literal-escaping correction; the final focused rerun passed.
- `npm run build` passed (Vite 7.3.1; 1818 modules; stale Browserslist data warning only). `git diff --check` passed with no output. No checks remain unavailable.
- Next: review the final diff; do not stage, commit, push, or create a branch.
