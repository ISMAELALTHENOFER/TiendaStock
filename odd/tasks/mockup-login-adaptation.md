# Mockup login adaptation

## Objective
Adapt the existing Laravel/Blade login to the supplied `stitch_enterprise_responsive_login_system` screenshot while preserving authentication, validation, recovery, and the current technology stack.

## Scope and constraints
- Only login-specific presentation, its guest wrapper when necessary, and focused authentication-view tests; do not change backend authentication or unrelated guest screens.
- Preserve all existing uncommitted work. The mockup is visual evidence, not application source code.
- Route: delegated direct. Mapping required four or more source/reference files; writing spans non-trivial Blade/CSS/test files.
- TDD mode: unresolved for ODD; the historical strict-TDD cache is SDD-specific. Run focused Laravel tests and the Vite build; capture actual results.
- Delivery: ask-on-risk. Estimated authored change: roughly 150–250 lines, below the advisory 400-line planning heuristic. No remote delivery authorized.

## Work units
- [x] L1 — Reopened: match the complete screenshot, including its below-card footer and responsive layout across mobile, tablet and desktop; retain every real login control. Acceptance: no horizontal overflow; footer remains visible/reachable at all widths and short heights. Checks: responsive browser inspection, `npm run build`.
- [x] L2 — Reopened: on failed authentication, visually mark both username and password inputs invalid as in the screenshot without changing the backend error contract or duplicating the alert. Acceptance: both fields have accessible descriptions and failed/empty-credentials states remain distinct. Checks: `php artisan test --filter=AuthenticationTest`, browser error-state inspection.

## Progress
- Exploration: screenshot depicts a centered single-column card; its concrete screenshot overrides the generic split-screen guidance in DESIGN.md. Current logo is inside the card and existing login files contain uncommitted user work.
- Implemented in the existing Blade guest layout/login, CSS, and authentication test. `php artisan test --filter=AuthenticationTest`: 6 passed, 29 assertions. `npm run build`: passed (stale Browserslist warning). Browser checks at 1280px and 320px: no horizontal overflow; short mobile screens scroll vertically. Impeccable detector: `[]`.
- Follow-up: the user requested removing the remaining Laravel logo. The shared `application-logo` now renders the TiendaStock archive mark, with a matching explicit SVG favicon on guest, app, and landing layouts. `PasswordResetTest`: 4 passed; component and landing view render checks passed. `PublicLandingPageTest`: 7 failures caused by the pre-existing `/` → `/login` redirect versus tests expecting the landing page; outside this feature's scope. `git diff --check`: passed.
- Commit evidence: pending. The branch was already on `developer` with overlapping uncommitted user changes in the same files before implementation. No staging, branching, commit, push, or unrelated cleanup was performed; do not conflate this work with the older diff.
- User-reported regression: authentication errors only paint username red; screenshot also paints password red. Footer is missing the pictured copyright, terms, and privacy line. No terms/privacy routes currently exist; do not create dead links or invent legal content. User requires all UI to be responsive across mobile and PC.
- Follow-up verified: both fields expose `aria-invalid=true` and refer to the single visible alert on failed authentication, with no repeated field error; missing inputs retain independent errors. The login-only footer now displays year, brand, rights and non-clickable legal labels (legal destinations do not exist). `php artisan test --filter=AuthenticationTest`: 6 passed, 40 assertions (independently rerun); `npm run build`: passed with stale Browserslist warning. Browser checks at 320, 390, 768 and 1280px found no horizontal overflow, both red borders on credential failure, a neutral password-eye icon and vertical scrolling on short screens. Scoped `git diff --check`: passed; Impeccable detector: `[]`.
- Next: isolate pre-existing overlapping changes before making work-unit commits; preserve current source work.
