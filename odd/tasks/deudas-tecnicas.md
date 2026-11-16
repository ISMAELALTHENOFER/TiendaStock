# Technical debt: cost visibility, JS logic tests, explicit stock rule, dark mode tokens
Branch: `feat/deudas-tecnicas` (from `developer` 6b8fd3f); delivery: ask-on-risk; runners (from `src/`): `php artisan test`, `npm test` (node --test), `npm run build`.

## Specs
- S1 (deuda 1): "Ocultar el costo depende de acordarse. precio_compra se oculta con makeHidden en cada lugar que serializa un producto. ... Hay que centralizarlo: un API Resource, o que el modelo decida qué mostrar según el rol. Así olvidarse no es posible." — cost (`precio_compra`, `ganancia`) must be hidden by default (fail-safe); only ADMIN sees it, decided in one place.
- S2 (deuda 2): "Los tests de React son frágiles y la lógica de React no tiene tests. Los tests de contrato buscan textos literales en el JSX ... la lógica que más importa en el front (cómo se interpretan los montos, el paginador, el chequeo de duplicados) no tiene ningún test ... Sumar Vitest y testear esas funciones vale más que diez tests que buscan strings." — Correction (verified): a JS runner already exists (`npm test` = `node --test`, 20 tests in `resources/js/react/lib/*.test.js`); use it instead of adding Vitest.
- S3 (deuda 3a): "activo se calcula solo a partir del stock, en un hook saving. Quien edita un producto no lo espera, y los tests tuvieron que saltearlo con un update directo a la base." — make the rule explicit where stock changes, same observable behavior.
- S4 (deuda 3b): "Control Stock carga productos con costo 0, entonces el ADMIN ve todo el precio de venta como ganancia. Ese dato es engañoso" — User decision (L3): "Control Stock lo carga": Control Stock enters `precio_compra` when CREATING a product (required, validated like ADMIN) but never sees it afterwards (lists, detail, edit); on edit Control Stock still cannot see or change it. No migration.
- S5 (menor): "Modo oscuro: se resuelve pisando clases de Tailwind (.dark .bg-white {…}). Funciona, pero es frágil. Lo prolijo es darkMode: 'class' con tokens."
- S6 (menor): "Archivos JSX gigantes con líneas de cientos de caracteres" — not done as a bulk reformat (pure churn, conflicts with parallel work); split lines when a file is touched.

## Tasks
- T1 [S1, S3] Delegated: fail-safe cost visibility + explicit active/stock rule. Status: done (97c2e6b), verifier PASS.
- T2 [S2] Delegated: node:test for money/pagination/duplicate check; brittle asserts removed. Status: done (6c7a690).
- T3 [S5] Delegated (worktree): darkMode class + dark: variants. Status: done (1dbdfb8, merge d46f681).
- T4 [S4] Inline: Control Stock enters cost on create. Status: done (e5e329d).
- T5 Inline: merge, seam, verifier, sweep. Status: done (L4).

## Log
- L1 (2026-11-16): "ok ataquemos esto en paralelo" + pasted list of the three debts and two minor items (quoted in Specs).
- L3 (2026-11-16): S4 answer — "Control Stock lo carga" (option: Control Stock escribe el costo al crear el producto, pero después no lo ve en listados ni detalles). Also: "guarda todo en engram tambien" — saved feature doc, dev workflow and app review to Engram.
- L2: Finding — `npm test` runs node:test, 20/20 passing; AGENTS.md "no JavaScript test script" is outdated.
- L4 (2026-11-16): CLOSE. T2 6c7a690 (31→30 node tests after T3 merged two theme tests; RED shown by breaking helpers). T3 1dbdfb8 merged d46f681 (worktree was created from main; agent reset to feat branch; 19 `.dark .<utility>` overrides removed; worktree + branch deleted). T1 97c2e6b (Producto::$hidden + revealCostTo single grant; also fixed ganancia never reaching ADMIN detail — RED/GREEN; syncActivoWithStock at 6 call sites). Independent verifier: S1 PASS, S3 PASS, 77 tests. T4 e5e329d (old test pinned cost=0 → updated, RED/GREEN; browser: Control Stock create shows Costo, edit hides it, /productos/data has no precio_compra). AGENTS.md 3935538. Checkbox dark fix f41b0e7. Browser sweep (logged in as test-DB ADMIN e2eadmin) 14 pages × 5 widths × 2 themes: NO ISSUES; first sweep was invalid (logged out). Final: php 369 passed, npm test 30 passed, build ok, browser closed. Push/PR: user decision.
