# Responsive Profile Page

## Objective
Match the authenticated `/profile` page more closely to the supplied TiendaStock mockup while preserving its existing Laravel/Blade shell, server-backed account actions, and responsive behavior.

## Problem and Why
The live page currently renders the three real profile forms but lacks the mockup's page introduction and several visual details. Its wide-screen cards also consume the full content width, unlike the intended focused form column. The supplied standalone HTML contains simulated submissions and must not replace the real application flows.

## Scope and Constraints
- Authorized scope: the `/profile` Blade view and its existing profile form partials; focused profile feature tests only if needed to enforce the updated contract.
- Keep existing routes, controllers, validation, PATCH/PUT/DELETE behavior, authentication, and shared application shell.
- Follow the supplied design tokens and mockup; maintain a usable stacked layout at narrow viewport widths with no horizontal overflow.
- Use the mockup's tinted input backgrounds, leading field icons, password visibility controls, danger-section treatment, and reference text; do not leave these out as was done in the first pass.
- Password fields must block empty browser submissions with native `required` validation while retaining server-side validation.
- Do not copy mockup-only fake success handlers or confirmation alerts.
- Preserve unrelated worktree state, including the untracked `implementaciones/` reference directory.
- TDD: Strict TDD enabled; source: project testing-capabilities memory; runners: `cd src; php artisan test --filter ProfileTest` and `cd src; php artisan test --filter PasswordUpdateTest`.
- Delivery strategy: `ask-on-risk`; forecast: under 400 authored changed lines.
- Route per task: delegated direct. Trigger: mapping/preparation spans multiple existing templates, shell styles, and tests; implementation modifies multiple non-trivial files.

## Tasks
- [x] RP-1: Add the mockup-aligned page introduction and responsive bounded form-column layout without changing the shared shell. A browser readback caught and drove correction of a duplicate title and an over-wide form column.
- [x] RP-2: Align the existing profile, password, and account-deletion form presentation with the mockup, preserving the real server-backed forms and deletion modal. Corrected the danger border to frame the existing outer card and matched the mockup description.
- [x] RP-3: Add browser-required validation for all password-change fields and verify empty password submissions remain rejected by the server.

## Acceptance Criteria
- `/profile` retains its existing account update, password update, and password-confirmed deletion flows.
- Page header and profile sections follow the supplied mockup's hierarchy and TiendaStock design tokens.
- Inputs use the reference muted-surface fill and leading icons; password inputs have accessible show/hide controls; the danger card includes the reference warning treatment.
- Saving profile/password forms does not open a confirmation dialog; successful saves display the existing transient status message.
- Password changes require current password, new password, and confirmation in both browser constraints and server validation.
- Cards and controls remain legible and usable at mobile, tablet, desktop, and wide viewport sizes; no horizontal page overflow.
- Keyboard focus, labels, and accessible names remain available for all controls.
- Existing focused profile feature tests pass; production assets build successfully if CSS or frontend assets change.

## Checks
- `cd src; php artisan test --filter ProfileTest`
- `cd src; php artisan test --filter PasswordUpdateTest`
- `cd src; npm run build` (if CSS or frontend assets change)
- `git diff --check`
- Browser structural/visual spot checks at narrow mobile and desktop widths.

## Progress and Evidence
- Initial mapping confirmed `/profile` is Blade-backed and uses three existing server-backed partials; the supplied `code.html` has fake client-only handlers and will be treated as visual reference only.
- Baseline browser check at 390px showed stacked cards and full-width form buttons; desktop showed the forms expanding across a much wider canvas than the mockup.
- RP-1 TDD: added assertions for the mockup introduction and `max-w-4xl` form column; `php artisan test --filter ProfileTest` failed first on the missing introduction, then passed after the Blade view update (5 tests, 33 assertions).
- RP-2 TDD: added assertions for responsive card padding and section-heading hierarchy; `php artisan test --filter ProfileTest` failed first on the missing responsive padding, then passed after the view/partial updates (5 tests, 35 assertions). Existing profile update and password-confirmed deletion feature tests pass.
- `npm run build` passed (Vite 7.3.1, 1,818 modules, built in 4.20s); it emitted a warning that the local `caniuse-lite` dataset is seven months old.
- No browser check was run during implementation; responsive behavior is implemented with the existing Tailwind breakpoints, fluid full-width controls, and a max-width form column.
- Parent browser readback at 1280px found a duplicate `Mi perfil` heading from the shared header slot and a form column wider than the mockup. Adjust RP-1 to remove only the redundant page-level slot output, add the mockup breadcrumb, and constrain the form column to roughly two-thirds at large widths while retaining full width on mobile.
- Reopened RP-1 TDD: updated the feature contract for `aria-label="Breadcrumb"`, `Configuración personal`, and `w-full lg:w-2/3`; the updated assertion first failed on the missing breadcrumb (5 tests, 1 failed, 28 assertions). The initial implementation run then exposed assertion-order mismatch for the width classes; ordering them together resolved it. Final `php artisan test --filter ProfileTest` passed (5 tests, 37 assertions).
- Reopened RP-1 correction removes the page's duplicate header slot, adds a semantic `Cuenta > Configuración personal` breadcrumb before the sole page H1, and uses full-width layout until `lg`, then `lg:w-2/3`; card padding is unchanged.
- `npm run build` passed after correction (Vite 7.3.1, 1,818 modules, built in 4.43s); it emitted the same seven-month-old `caniuse-lite` warning. `git diff --check` passed with no output.
- Parent browser verification after correction: at 1280px, one page H1, breadcrumb, and bounded 2/3 desktop form column match the mockup; at 390px and 768px, the cards stack and `document.scrollWidth` equals the viewport width (no horizontal overflow). Browser console showed no errors.
- Native review preflight ran with the repository review switch on, then stopped at `collect` / `intended_untracked_selection_required`. The returned next transition named `external.select_intended_untracked` but exposed no executable capture invocation through the available tools; no guessed selection or lifecycle continuation was submitted.
- Code readback: profile save sends PATCH `/profile` and password save sends PUT `/password`; neither opens a confirmation dialog. On success the existing `Guardado.` message is shown for two seconds. Backend password rules require the current password, a policy-compliant confirmed new password, but the password controls lacked HTML `required`, so blank forms could be sent to the server.
- Browser inspection confirmed profile name/username/email already use native `required`; the three password-change inputs did not. This correction will add client-side required constraints without weakening backend checks.
- RP-2/RP-3 strict TDD: expanded `ProfileTest` for the referenced muted input surface, all six leading icons, all three visibility toggles and changing accessible labels/pressed state, plus the danger-zone treatment. Before source changes, `php artisan test --filter ProfileTest` failed as expected on missing `login-input-icon` (1 failed, 4 passed, 34 assertions). Added an empty-password update case first; `php artisan test --filter PasswordUpdateTest` already passed (3 tests, 13 assertions), confirming backend validation was already present.
- RP-2 implementation keeps all existing profile/password/delete routes and Blade components, uses `surface-muted` and the existing login icon/toggle classes, ties input validation state to the existing error bags, and retains the Alpine deletion modal and useful copy. Save feedback remains `Guardado.` for two seconds with no added confirmation dialog.
- RP-3 adds native `required` to current, new, and confirmation password controls. After implementation, `php artisan test --filter ProfileTest` passed (5 tests, 49 assertions) and `php artisan test --filter PasswordUpdateTest` passed (3 tests, 13 assertions).
- `npm run build` passed (Vite 7.3.1, 1,818 modules, built in 2.84s); it emitted the existing warning that `caniuse-lite` data is seven months old. `git diff --check` passed with no output.
- No browser form was submitted and no live account data was changed. The new markup was verified by the focused rendered-view contract test; this pass did not perform a fresh viewport browser readback.
- Parent browser readback at 753px confirmed six field icons, muted-blue input fill (`rgb(239, 244, 255)`), three password toggles, all password fields `required`, empty password form invalidity, and no horizontal overflow. A safe dummy value verified show/hide works without submission. It also exposed an unwanted inset danger-card border; move that treatment to the existing outer panel and match the mockup's shorter danger description.
- RP-2 follow-up strict TDD: added a focused assertion for the outer `.surface-panel` red border, absence of the nested outlined section, and exact mockup copy. Before source changes `php artisan test --filter ProfileTest` failed as expected on the missing outer-panel border (1 failed, 5 passed, 51 assertions). Moved `border border-red-200` onto only the deletion panel in `profile/edit.blade.php`, removed the nested section outline/padding, and shortened the warning copy; icon, `Zona de peligro`, danger button, modal, and delete endpoint were unchanged.
- Final RP-2 follow-up checks: `php artisan test --filter ProfileTest` passed (6 tests, 54 assertions); `git diff --check` passed with no output; `npm run build` passed (Vite 7.3.1, 1,818 modules, 3.54s), with the same seven-month-old `caniuse-lite` warning.
- No forms were submitted and no live user data was changed. Final parent browser readback at 1280px and 375px confirmed the outer red danger card, exact reference warning copy, six leading icons, tinted input backgrounds, and three working password toggles. At 375px all three password fields are required, the empty form is invalid, and document width equals viewport width (no horizontal overflow).

## Next Step
RP-1, RP-2, RP-3, functional checks, and browser verification are complete. Native RDD review remains blocked at intended-untracked-selection collection; no guessed selection was submitted.
