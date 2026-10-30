---
name: react-responsive
description: "Trigger: React responsive, mobile layout, tablet, desktop, viewport. Implement and verify accessible responsive React interfaces in TiendaStock."
license: Apache-2.0
metadata:
  author: "TiendaStock"
  version: "1.0"
---

# React Responsive

## Activation Contract

Use when creating or changing responsive React interfaces in this project. Apply within the user-authorized scope; do not redesign unrelated pages.

## Hard Rules

- Work in `src/`; reuse existing React components, Tailwind utilities, and layout conventions before adding code or dependencies.
- Start with a usable narrow layout; use fluid sizing and content-driven breakpoints rather than targeting specific device models.
- Keep essential content and actions available at every width. Avoid page-level horizontal overflow; give wide tables an intentional, accessible scroll region when reflow is unsuitable.
- Preserve keyboard access, visible focus, touch-friendly controls, readable zoomable text, semantic HTML, and reduced-motion preferences where animation is added.
- Keep authorization and mutations server-side. Preserve Laravel routes, React page contracts, and Blade fallbacks.
- Do not claim support for every device from a limited test matrix; report tested viewports and unverified cases.

## Decision Gates

- Prefer CSS and Tailwind for layout changes. Use React state or `matchMedia` only when behavior, not presentation alone, depends on viewport; clean up listeners.
- Reflow grids and forms where practical; keep data-heavy tables usable with labeled local scrolling when their relationships would be lost by reflow.
- When browser execution is unavailable, perform available automated checks and explicitly report the missing visual verification.

## Execution Steps

1. Inspect the affected page, shared layout and components, Blade fallback, and relevant feature tests before editing.
2. Implement the smallest responsive change, covering navigation, forms, tables, media, long text, loading and error states where affected. Check intermediate widths and both orientations.
3. Verify narrow and wide phones, tablet, desktop, and a wide viewport; resize between breakpoints. Check overflow, overlapping content, focus, touch, zoom, and keyboard navigation.
4. Run the relevant `php artisan test --filter=...` from `src/`; for cross-stack changes also run `composer test` and `npm run build`. Report unavailable or failed checks.

## Output Contract

Report changed surfaces, viewport and accessibility checks actually performed, test results, and remaining device or browser limitations.

## References

- `AGENTS.md` — project setup, server boundaries, and verification commands.
- `src/resources/js/react/layout/AppShell.jsx` — shared React shell.
- `src/tailwind.config.js` — Tailwind content scanning and theme.
