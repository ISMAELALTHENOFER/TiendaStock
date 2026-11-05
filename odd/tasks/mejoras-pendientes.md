# Pending improvements
Branch: `feat/mejoras-pendientes`; delivery: ask-on-risk; forecast: over 400 authored lines.
Specification: `MEJORAS_PENDIENTES.md` and its referenced responsive roadmap. Runners: `php artisan test`, `composer test`, `npm run build`, browser.

## Specs

- S1: “Ventas + datos” — implement sections 1 and 2.1–2.3 and their tests, preserving sales and transaction integrity.
- S2: “Usuarios y permisos” — implement sections 2.3, 2.7, 5.11 and 12, including role-specific access.
- S3: “Dashboard y zona horaria” — implement section 4.1 and dashboard decisions.
- S4: “Frontend React completo y responsive” — implement sections 5, 6 and 10, with role-specific browser checks.
- S5: “Imágenes y preparación de producción” — implement section 11, respecting backup requirements and remote authorization.
- S6: “Eficiencia, portabilidad y documentación” — address confirmed items in sections 3, 4, 7 and 8.
- S7: “cunado implementes todo verifica con el navegador” — verify completed flows in the browser; report unavailable proof.

## Tasks

- T1 [S1] Inline; in progress; commit pending: sales and data protection; risk items 1, 2 and 4.
- T2 [S2] Inline; pending; commit pending: users and permissions; risk item 2.
- T3 [S3] Inline; pending; commit pending: dashboard and timezone.
- T4 [S4] Inline; pending; commit pending: React and responsive flows.
- T5 [S5] Inline; pending; commit pending: image persistence and deployment preparation; risk items 1 and 5.
- T6 [S6] Inline; pending; commit pending: efficiency, portability and documentation.
- T7 [S7] Inline; pending: full verification including browser.

## Log

- L1: “Vamos a implementar mejoras pendientes completo cunado implementes todo verifica con el navegador”
- L2: “crea una rama para las implementaciones y a medida que vayas modificando anda haciendo commit en español. avanza”
- L3: Created branch from `developer`. No push or remote work authorized. User explicitly requested autonomous local commits without the commit skill.
- L4: Mapping identified the invalid stock-error redirect as the first isolated unit; sales calculations need a secure historical-price design and MySQL is required to verify real locking.
- L5: Commit d5cbb81 fixes the stock-error redirect; 33 VentaPosTest tests passed. Review assessment: medium, under budget; candidate review declined by user. Initial full suite timed out; independent scratch run later found one profile-related regression that was corrected and rechecked.
- L6: Commit fdfa3e3 removes the self-deletion route and form; focused ProfileTest and RootRedirectTest pass, including the three roles and preserved user/sale rows. Independent recheck passed. Database foreign-key protections remain pending. Candidate review declined by user; review mode subsequently disabled at user request.
- L7: `composer test` passed: 313 tests, 1,305 assertions. `npm run build` passed in an isolated verification copy. Full browser verification remains pending.
- L8: Parallel mapping identified that signed search prices alone permit reuse in later sales; a user-owned server-side cart with atomic consumption is a candidate for preserving verified cart prices. Real MySQL concurrency proof remains pending.
- L9: Foreign-key migration is uncommitted and incomplete: RED reproduced cascading parent deletion; SQLite focused checks passed and independent scratch suite passed 315 tests. MySQL DDL, partial-failure recovery and backup/restore remain unverified; do not apply the migration to retained data. Independent verification found no reproduced blocker but could not establish production safety.
- L10: Missing-second-table failure initially caused a partial SQLite migration; added a preflight check and regression test, independently rechecked. This does not prove recovery from arbitrary MySQL DDL failures. Local MySQL Docker image exists; isolated verification was blocked by executor restrictions without starting a container.
- L11: Commit 50abd93 implements section 2.4 with validated category inputs. The unguarded-model regression test failed against request-all behavior and passed after the change; 31 category tests and independent focused recheck passed. Full suite for this candidate remains unverified.
- L12: A sales tampering test reproduced a $25 product saved as $1. The uncommitted provisional guard rejects it but fails the agreed historical-cart-price rule; independent verification also observed fractional-cent values and inactive-product sales. The guard is not ready or committed. A server-owned, atomically consumed quote is still needed, with isolated MySQL concurrency proof.
