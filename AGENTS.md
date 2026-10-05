# TiendaStock working guide

## Work from `src/`

- The application lives in `src/`, not the repository root. Use PHP 8.2+ and the `src/composer.lock` and `src/package-lock.json` dependency sets. The root README's `src/config.php`, `src/db.php`, and `src/index.php` description is obsolete; use `SETUP.md` and current application files instead.
- Initial setup: `composer install`, copy `.env.example` to `.env`, set the Laravel `DB_*` credentials for your database, then `php artisan key:generate`, `npm ci`, `php artisan migrate`, `php artisan storage:link`, `npm run build`.
  Do not commit `.env`. Product images need the public storage link.
- Development: run `php artisan serve` and `npm run dev` in separate terminals, or `composer dev` to start the server, queue listener, log viewer, and Vite together. `composer dev` requires the npm dependencies for `npx concurrently`.
- Production assets: `npm run build`; Vite builds `resources/css/app.css`, `resources/js/app.js`, and `resources/js/react/main.jsx`. If compiled assets still load from a dev server, stop Vite and check for stale `public/hot` (see `src/README.md`). Vite LAN/HMR settings are in `vite.config.js` (`VITE_DEV_HOST`, `VITE_DEV_PORT`, `VITE_DEV_ORIGIN`, `VITE_HMR_HOST`, `VITE_HMR_PORT`).

## Verify changes

- `composer test` clears Laravel's config cache before running `php artisan test`; `php artisan test` alone runs the PHPUnit `Unit` and `Feature` suites. `phpunit.xml` uses SQLite `:memory:` plus array session/cache and a sync queue for tests, not the development database.
- Focus a feature slice with `php artisan test --filter=ProductoReactViewContractTest` (replace the class name with the relevant test in `tests/Feature/`); then run `composer test` and `npm run build` for cross-stack changes. `package.json` has no JavaScript test or lint script.

## Presentation and server boundaries

- HTTP routes and role gates live in `routes/web.php`: dashboard requires `auth` + `verified`; sales require `ADMIN` or `Ventas`; category/product mutations require `ADMIN` or `Control Stock`; user administration requires `ADMIN`. Keep authorization on the server, not only in the React navigation.
- `config/frontend.php` defaults to React on migrated pages. `FRONTEND_DRIVER=blade` rolls them back globally; route-specific `FRONTEND_*_DRIVER=blade` switches a single page when the global driver remains React.
  `resources/views/layouts/app.blade.php` selects the host from the route name and driver; `resources/views/react/app.blade.php` serializes `data-props`; `resources/js/react/main.jsx` mounts `app.jsx`. Authentication/profile and other non-migrated surfaces remain Blade.
- Server controllers still supply page data and process mutations. Product list data is fetched from `/productos/data`; sales and product forms use server routes. When changing a migrated page, preserve its Blade fallback and the corresponding `tests/Feature/*ReactViewContractTest.php` contract.
- Product deletion is a deactivation (`activo=false`), not a row removal; zero-stock products cannot be reactivated. Sales mutations and access rules are covered in `tests/Feature/VentaPosTest.php`; product lifecycle tests live beside the React view contract tests.
- Tailwind scans `resources/views/**/*.blade.php` and `resources/js/react/**/*.{js,jsx}` per `tailwind.config.js`; add new template paths there if utility classes do not build.

Preserve unrelated worktree changes: existing tracked deletions in docs, OpenSpec, and tooling are not an instruction to restore or endorse those deletions.
