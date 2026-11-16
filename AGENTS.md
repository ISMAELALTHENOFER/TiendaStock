# TiendaStock working guide

## Work from `src/`

- The application lives in `src/`, not the repository root. Use PHP 8.2+ and the `src/composer.lock` and `src/package-lock.json` dependency sets. The root README's `src/config.php`, `src/db.php`, and `src/index.php` description is obsolete; use `SETUP.md` and current application files instead.
- Initial setup: `composer install`, copy `.env.example` to `.env`, set the Laravel `DB_*` credentials for your database, then `php artisan key:generate`, `npm ci`, `php artisan migrate`, `php artisan storage:link`, `npm run build`.
  Do not commit `.env`. Product images need the public storage link.
- Development: run `php artisan serve` and `npm run dev` in separate terminals, or `composer dev` to start the server, queue listener, log viewer, and Vite together. `composer dev` requires the npm dependencies for `npx concurrently`.
- Production assets: `npm run build`; Vite builds `resources/css/app.css` and `resources/js/react/main.jsx`. If compiled assets still load from a dev server, stop Vite and check for stale `public/hot` (see `src/README.md`). Vite LAN/HMR settings are in `vite.config.js` (`VITE_DEV_HOST`, `VITE_DEV_PORT`, `VITE_DEV_ORIGIN`, `VITE_HMR_HOST`, `VITE_HMR_PORT`).

## Verify changes

- `composer test` clears Laravel's config cache before running `php artisan test`; `php artisan test` alone runs the PHPUnit `Unit` and `Feature` suites. `phpunit.xml` uses SQLite `:memory:` plus array session/cache and a sync queue for tests, not the development database.
- Focus a feature slice with `php artisan test --filter=ProductoReactViewContractTest` (replace the class name with the relevant test in `tests/Feature/`); then run `composer test` and `npm run build` for cross-stack changes. `package.json` has no JavaScript test or lint script.

## Presentation and server boundaries

- HTTP routes and role gates live in `routes/web.php`: dashboard requires `auth` + `role:ADMIN`; sales require `ADMIN` or `Ventas`; categories and products require `ADMIN` or `Control Stock`; user administration requires `ADMIN`. `tests/Feature/ModuleAccessMatrixTest.php` pins this matrix. Keep authorization on the server, not only in the React navigation; forbidden pages render `resources/views/errors/403.blade.php`. Purchase cost (`precio_compra`) is ADMIN-only: hide it with `makeHidden` wherever a product is serialized into `data-props`.
- Every page renders only through React; there are no Blade page views or Blade driver switch. Controllers return `layouts.app` (authenticated pages) or `layouts.guest` (login, confirm password) with the page data; the layout builds the props from the route name, `resources/views/react/app.blade.php` serializes `data-props`, and `resources/js/react/main.jsx` mounts `app.jsx`.
- Server controllers still supply page data and process mutations. Product list data is fetched from `/productos/data`; sales and product forms use server routes. When changing a page, preserve the corresponding `tests/Feature/*ReactViewContractTest.php` contract.
- Product deletion is a deactivation (`activo=false`), not a row removal; zero-stock products cannot be reactivated. Sales mutations and access rules are covered in `tests/Feature/VentaPosTest.php`; product lifecycle tests live beside the React view contract tests.
- Tailwind scans `resources/views/**/*.blade.php` and `resources/js/react/**/*.{js,jsx}` per `tailwind.config.js`; add new template paths there if utility classes do not build.

Preserve unrelated worktree changes: existing tracked deletions in docs, OpenSpec, and tooling are not an instruction to restore or endorse those deletions.

## GitHub account

- This repository belongs to the `ISMAELALTHENOFER` account; the global `gh` account (`Ismael-Althenofer`) stays active for everything else. Never run `gh auth switch` here.
- Git already uses the right identity and SSH key via the repo/conditional git config (`github-personal` alias through `core.sshCommand`). Push with plain `git push`; do not test access with bare `ssh`.
- Run every `gh` command in this repo with the personal token, scoped to that command: `GH_TOKEN="$(gh auth token -u ISMAELALTHENOFER)" gh pr create ...`.

## Project skills

- React responsive interfaces: load `.opencode/skills/react-responsive/SKILL.md` when implementing or changing responsive React UI.
