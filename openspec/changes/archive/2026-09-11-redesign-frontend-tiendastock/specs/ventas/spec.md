# Delta for Ventas

## MODIFIED Requirements

### Requirement: Sales routes retain Laravel contracts while using React presentation

The React sales slices MUST preserve the existing `ventas.index`, `ventas.create`/POS, `ventas.store`, `ventas.show`, and `ventas.cancel` routes, `auth`, `verified`, and `role:ADMIN,Ventas` middleware, validation, redirects, Spanish flash messages, atomic stock behavior, cancellation/restoration, and printable receipt behavior. GET filters `desde`, `hasta`, and `estado`, `/productos/search`, cart validation, and all current form fields MUST remain compatible. (Previously: Sales behavior was specified for Blade/Alpine views and must now be presented by React without changing its Laravel contract.)

#### Scenario: Authorized history and filters
- GIVEN an authenticated ADMIN or Ventas user
- WHEN the user opens history and submits existing date/status filters
- THEN the same route and parameters produce the same filtered results and pagination semantics

#### Scenario: POS transaction remains authoritative on Laravel
- GIVEN a permitted user submits a valid React POS form with CSRF
- WHEN Laravel processes it
- THEN existing validation, transaction, stock deduction, redirect, and success flash behavior occur

#### Scenario: Cancellation and print remain available
- GIVEN a completed sale visible to an authorized user
- WHEN the user confirms cancellation or chooses print
- THEN Laravel cancellation/restoration and the existing printable detail with `no-print` navigation remain functional

#### Scenario: Unauthorized or invalid request
- GIVEN a guest, disallowed role, missing CSRF, invalid form, insufficient stock, or insufficient payment
- WHEN the request is submitted
- THEN Laravel retains its current redirect/403/validation/error behavior and no unauthorized mutation occurs

### Requirement: Responsive sales workflows preserve complete information

The React history, POS, and detail screens MUST be mobile-first. At 320px and 767px, POS zones MUST stack, filters MUST wrap, and tables MUST use intentional scrolling or complete mobile cards. At 768px, 1023px, 1024px, and 1440+ the layout MUST remain usable without clipping. No action, status, monetary value, or required field MAY be hidden solely for viewport width.

#### Scenario: Mobile POS
- GIVEN a permitted user at 320px or 767px
- WHEN the POS is used
- THEN search, results, cart, payment, validation, and submit remain reachable without unintended horizontal page scrolling

## ADDED Requirements

### Requirement: Sales migration rollback

Each migrated sales route MUST be switchable back to its prior Blade/Alpine entrypoint independently, and global `FRONTEND_DRIVER=blade` MUST restore all legacy surfaces. Rollback MUST preserve sales, inventory, route names, and activity records.

#### Scenario: Route rollback
- GIVEN a React sales slice has a rollout failure
- WHEN its route override is changed to Blade
- THEN the prior Blade/Alpine surface serves the same route and persisted records remain unchanged

## Non-Goals

No new sales rules, payment methods, permissions, filters, notifications, or replacement of Laravel mutations is included.
