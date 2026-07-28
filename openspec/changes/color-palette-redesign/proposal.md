# Proposal: Color Palette Redesign

## Intent

Replace the purple/pink gradient theme with a warm gold+sky palette that better fits a clothing store (TiendaStock). The current purple theme was a Breeze default and has no product/brand connection — gold suggests value and fashion, sky and blush complement it for a clean inventory app.

## Scope

### In Scope

- Tailwind config: add brand (`#FFD16D`), sky (`#AAD6E2`), blush (`#FCD2DF`) colors; replace gradients and shadows
- All Blade views: auth (6), dashboard, productos (4), categorías (4), profile (1 + partials)
- All components: primary-button, secondary-button, danger-button, text-input, nav-link, responsive-nav-link, alert, application-logo
- Layouts: app, guest, sidebar, topbar
- CSS: `app.css` stays minimal (no change needed)

### Out of Scope

- Dark mode
- Component restructuring or Blade refactoring
- Logo SVG redesign (only color swap)
- Third-party pagination styles (Tailwind defaults suffice)

## Capabilities

### New Capabilities

None — this is a pure theme swap with no new features.

### Modified Capabilities

None — no spec-level behavior changes. Pure visual redesign.

## Approach

1. **Tailwind config**: remove custom purple/pink keys, add `brand`, `sky`, `blush` color objects with hex values from the palette. Replace `gradientColorStops` and `boxShadow` (purple/pink glow → brand glow).
2. **Semantic replacement**: `purple-600/700` → `brand`, `purple-100/50` → light `brand` tones, `pink-*` + `indigo-*` → `sky`, `pink-50/100` → `blush`. Gradients `from-purple-600 to-pink-500` → `from-brand to-sky`.
3. **Per-file pass**: touch every view in the affected list, swapping Tailwind class tokens. No Blade logic changes.
4. **Verify**: visual diff review in browser and `php artisan pint` for style.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `tailwind.config.js` | Modified | Replace color tokens, gradients, shadows |
| `resources/css/app.css` | Unchanged | Only `@tailwind` directives — no change needed |
| `resources/views/layouts/` (4 files) | Modified | app, guest, sidebar, topbar |
| `resources/views/auth/` (6 files) | Modified | login, register, verify-email, forgot-password, confirm-password, reset-password |
| `resources/views/dashboard.blade.php` | Modified | Header, stat cards, activity section |
| `resources/views/productos/` (4 files) | Modified | index, create, edit, show |
| `resources/views/categorias/` (4 files) | Modified | index, create, edit, show |
| `resources/views/components/` (8 files) | Modified | primary, secondary, danger buttons, text-input, nav-link, responsive-nav-link, alert, logo |
| `resources/views/profile/` (1+ partials) | Modified | edit.blade.php + partials |

## Design Rationale

For a clothing store inventory system, the palette communicates:
- **Gold/brand** signals value and premium inventory — replaces aggressive purple
- **Sky** is calm, readable for data-heavy tables and secondary action areas
- **Blush** adds warmth to backgrounds and badges without competing with gold
- White backgrounds stay for readability and table density

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Missed hardcoded `purple/pink/indigo` classes | Medium | `rg` search across all Blade files before marking done |
| `text-purple-900` doesn't have a tight `brand` substitute | Low | Use neutral gray-800 or derive a brand-dark shade from gold |
| Contrast ratio on gold text over light backgrounds | Low | Keep gold for accents/buttons; use gray-800/900 for body text |

## Rollback Plan

Revert `tailwind.config.js` to the previous color block and `git checkout -- resources/views/` to restore all Blade files from the commit before this change.

## Dependencies

None.

## Success Criteria

- [ ] No `purple-`, `pink-`, or `indigo-` classes remain in any Blade file
- [ ] Brand gold `#FFD16D` appears on primary buttons, active nav, headings
- [ ] Sky `#AAD6E2` appears on secondary accents, hover states, gradient complements
- [ ] Blush `#FCD2DF` appears on backgrounds and soft highlights
- [ ] `php artisan pint` passes
- [ ] All pages render without visual breakage (manual review)
