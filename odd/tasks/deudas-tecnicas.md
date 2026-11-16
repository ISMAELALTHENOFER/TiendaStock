# Technical debt: cost visibility, JS logic tests, explicit stock rule, dark mode tokens
Branch: `feat/deudas-tecnicas` (from `developer` 6b8fd3f); delivery: ask-on-risk; runners (from `src/`): `php artisan test`, `npm test` (node --test), `npm run build`.

## Specs
- S1 (deuda 1): "Ocultar el costo depende de acordarse. precio_compra se oculta con makeHidden en cada lugar que serializa un producto. ... Hay que centralizarlo: un API Resource, o que el modelo decida qué mostrar según el rol. Así olvidarse no es posible." — cost (`precio_compra`, `ganancia`) must be hidden by default (fail-safe); only ADMIN sees it, decided in one place.
- S2 (deuda 2): "Los tests de React son frágiles y la lógica de React no tiene tests. Los tests de contrato buscan textos literales en el JSX ... la lógica que más importa en el front (cómo se interpretan los montos, el paginador, el chequeo de duplicados) no tiene ningún test ... Sumar Vitest y testear esas funciones vale más que diez tests que buscan strings." — Correction (verified): a JS runner already exists (`npm test` = `node --test`, 20 tests in `resources/js/react/lib/*.test.js`); use it instead of adding Vitest.
- S3 (deuda 3a): "activo se calcula solo a partir del stock, en un hook saving. Quien edita un producto no lo espera, y los tests tuvieron que saltearlo con un update directo a la base." — make the rule explicit where stock changes, same observable behavior.
- S4 (deuda 3b): "Control Stock carga productos con costo 0, entonces el ADMIN ve todo el precio de venta como ganancia. Ese dato es engañoso" — PENDING user product decision.
- S5 (menor): "Modo oscuro: se resuelve pisando clases de Tailwind (.dark .bg-white {…}). Funciona, pero es frágil. Lo prolijo es darkMode: 'class' con tokens."
- S6 (menor): "Archivos JSX gigantes con líneas de cientos de caracteres" — not done as a bulk reformat (pure churn, conflicts with parallel work); split lines when a file is touched.

## Tasks
- T1 [S1, S3] Delegated (parallel, PHP surface): fail-safe cost visibility + explicit active/stock rule. Risk items 2 and 1-adjacent (persisted flag semantics). Status: in progress.
- T2 [S2] Delegated (parallel, lib/** + contract tests): extract and test money/pagination/duplicate-check logic with node:test; replace literal-count JSX assertions. Status: in progress.
- T3 [S5] Delegated (parallel, isolated worktree): darkMode 'class' + tokens, remove `.dark .<utility>` overrides. Status: in progress.
- T4 [S4] Pending user decision.
- T5 Inline: merge T3, seam check (php test, npm test, build, browser sweep), independent verifier for T1 (risk item 2).

## Log
- L1 (2026-11-16): "ok ataquemos esto en paralelo" + pasted list of the three debts and two minor items (quoted in Specs).
- L2: Finding — `npm test` runs node:test, 20/20 passing; AGENTS.md "no JavaScript test script" is outdated.
