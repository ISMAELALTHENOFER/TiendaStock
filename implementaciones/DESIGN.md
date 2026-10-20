---
name: TiendaStock Enterprise
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#3d4a42'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#6d7a72'
  outline-variant: '#bccac0'
  surface-tint: '#006c4a'
  primary: '#006948'
  on-primary: '#ffffff'
  primary-container: '#00855d'
  on-primary-container: '#f5fff7'
  inverse-primary: '#68dba9'
  secondary: '#565e74'
  on-secondary: '#ffffff'
  secondary-container: '#dae2fd'
  on-secondary-container: '#5c647a'
  tertiary: '#006947'
  on-tertiary: '#ffffff'
  tertiary-container: '#00855b'
  on-tertiary-container: '#f5fff6'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#85f8c4'
  primary-fixed-dim: '#68dba9'
  on-primary-fixed: '#002114'
  on-primary-fixed-variant: '#005137'
  secondary-fixed: '#dae2fd'
  secondary-fixed-dim: '#bec6e0'
  on-secondary-fixed: '#131b2e'
  on-secondary-fixed-variant: '#3f465c'
  tertiary-fixed: '#6ffbbe'
  tertiary-fixed-dim: '#4edea3'
  on-tertiary-fixed: '#002113'
  on-tertiary-fixed-variant: '#005236'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.025em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.015em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.015em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
  label-lg:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.005em
  label-md:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 14px
    letterSpacing: 0.03em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-sm: 1rem
  gutter-lg: 2rem
  margin: 2rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
  space-2xl: 3rem
---

## Brand & Style
The design system establishes a high-trust, operational-grade B2B enterprise aesthetic engineered for retail operations, high-velocity inventory tracking, and warehouse logistics. It balances uncompromising enterprise stability with crisp, contemporary digital craftsmanship.

The visual style is **Corporate / Modern** elevated by precision micro-surfacing:
- **Atmosphere:** Controlled, methodical, highly legible, and reassuringly robust. Data density is treated as a first-class citizen without inducing cognitive fatigue.
- **Visual Rhythm:** Balanced split-screen architectures, pristine borders, structural contrast between deep slate navigation shells, crisp white analytical cards, and focused emerald/forest accents denoting active states, stock health, and primary execution points.
- **Emotional Response:** Inspires operational confidence, financial clarity, and effortless mastery over multi-location stock workflows.

## Colors
The palette leverages a deep forest-to-emerald continuum juxtaposed against slate and cool gray architectural structures.

- **Primary Emerald/Forest Range:**
  - Base Primary (`#059669`): Standard state for key actionable items, badges, and primary controls.
  - Primary Hover / Vibrant (`#10B981`): Hover states, positive stock indicators, live inventory pings.
  - Primary Deep / Pressed (`#064E3B`): Active interaction states, high-priority status fills.
  - Primary Midnight (`#022C22`): Hero iconography containers, deep badges, enterprise branding accents.
- **Neutral Dark & Structure:**
  - Ink Solid (`#0F172A`): Primary headings, critical data values, and dark operational sidebars.
  - Ink Muted (`#1E293B` and `#334155`): Secondary data points, table headings, and muted navigation labels.
  - Ink Faint (`#64748B`): Helper text, metadata timestamps, input icons, and subtle dividers.
- **Surfaces & Borders:**
  - Canvas Base (`#F8FAFC`): Screen background canvas delivering high contrast against content blocks.
  - Surface Card (`#FFFFFF`): Pristine content cards, data tables, and modal dialogs.
  - Surface Subtle (`#F1F5F9`): Table zebra stripes, input inactive fills, disabled chip backgrounds.
  - Border Pristine (`#E2E8F0`): Crisp 1px division lines, standard input perimeters, and card boundaries.

## Typography
The system employs a dual-typeface typographic hierarchy engineered for enterprise clarity:
- **Headings & Branding (Plus Jakarta Sans):** Geometric precision with contemporary, confident forms. Tight tracking (`-0.02em` to `-0.01em`) provides grounded authority across dashboard KPI titles, authentication banners, and panel headers.
- **Body, UI Controls & Data Tables (Inter):** Highly legible grotesque sans-serif with tall x-height, clear optical distinctions between numerals, and robust tabular alignment characteristics essential for dense inventory stock tables, SKUs, and pricing sheets.

## Layout & Spacing
The layout relies on a structured 12-column grid system paired with strict 4px/8px modular rhythm tokens:

- **Desktop (>= 1280px):** Fixed or bounded fluid layouts using a 280px persistent sidebar for navigation, a main operations canvas with 32px (`space-xl`) outer margins, and 24px (`gutter`) column spacing.
- **Tablet (768px - 1279px):** Collapsible sidebar rail (72px), unified 24px (`space-lg`) section padding, and responsive 8-column layout.
- **Mobile (< 768px):** Single-column stack, edge-to-edge containers with 16px (`margin-mobile`) horizontal safe areas, fixed floating bottom navigation for essential scan/search actions.
- **Split-Screen Enterprise Architecture:** On authentication and high-context workflows (e.g., batch reconciliation, POS sign-in), the screen cleanly divides 50/50 on desktop: left panel dedicated to operational illustrations/metrics and the right panel containing centered, elevated utility cards.

## Elevation & Depth
Elevation is maintained through low-contrast outlines coupled with ambient, cool-slate drop shadows:

- **Level 0 (Flat/Subtle):** Default canvas (`#F8FAFC`) without elevation. Inset controls and subtle table cells.
- **Level 1 (Card & Containers):** Standard resting elevation for cards, analytical modules, and forms.
  - Border: `1px solid #E2E8F0`
  - Shadow: `0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 1px 2px -1px rgba(15, 23, 42, 0.03)`
- **Level 2 (Hovered Cards & Dropdowns):** Interactive card focus and small context menus.
  - Border: `1px solid #CBD5E1`
  - Shadow: `0 4px 6px -1px rgba(15, 23, 42, 0.08), 0 2px 4px -2px rgba(15, 23, 42, 0.05)`
- **Level 3 (Modals & Flyouts):** Slide-out inventory drawers, alert modals, batch edit dialogs.
  - Shadow: `0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.06)`
- **Focus Rings:** Accessible, crisp double-ring with `0 0 0 2px #FFFFFF, 0 0 0 4px #10B981`.

## Shapes
The design adopts `roundedness: 2` (Moderate Rounded), balancing enterprise discipline with polished consumer-grade ergonomics.

- **Inputs, Buttons, and Badges:** `rounded-md` (0.5rem / 8px). Creates precise, well-aligned clickable touchpoints.
- **Cards, Modals, and Main Panels:** `rounded-lg` (1rem / 16px). Softens large surface boundaries and frames content cleanly.
- **Pills & Status Indicators:** `rounded-full` (9999px) for operational inventory tags (e.g., "In Stock", "Critical Low", "Dispatched").

## Components

### Buttons
- **Primary:** Background `#059669`, text `#FFFFFF`, font `label-lg`, radius `0.5rem`, padding `0.625rem 1.25rem`. Hover state `#10B981` with transition `150ms ease-in-out`. Active state `#064E3B`. Accommodates leading/trailing iconography (e.g., arrow, barcode, plus).
- **Secondary / Outline:** Background `#FFFFFF`, border `1px solid #E2E8F0`, text `#1E293B`. Hover state background `#F8FAFC`, border `#CBD5E1`.
- **Ghost:** Transparent background, text `#475569`, hover `#F1F5F9`.

### Input Fields & Controls
- **Text Inputs:** Height 44px, background `#FFFFFF`, border `1px solid #E2E8F0`, radius `0.5rem`, padding `0.5rem 0.875rem`. Left icon colored `#94A3B8`.
- **Focus State:** Border `#059669`, outline `2px solid rgba(16, 185, 129, 0.2)`.
- **Placeholder:** `#94A3B8`, text font `body-md`.

### Checkboxes & Radios
- Size 18px x 18px, border `1.5px solid #CBD5E1`, radius `0.25rem` (checkboxes) and `9999px` (radios).
- Checked state: Fill `#059669`, stroke `#FFFFFF`, border `#059669`.

### Chips & Stock Status Badges
- Height 24px, padding `0.125rem 0.625rem`, radius `9999px`, font `label-sm`.
- **Stock Optimal:** Background `rgba(16, 185, 129, 0.1)`, text `#065F46`, border `1px solid rgba(16, 185, 129, 0.2)`.
- **Stock Warning / Low:** Background `#FEF3C7`, text `#92400E`, border `1px solid #FDE68A`.
- **Stock Depleted:** Background `#FEE2E2`, text `#991B1B`, border `1px solid #FECACA`.

### Cards & Panels
- Background `#FFFFFF`, border `1px solid #E2E8F0`, radius `1rem`, padding `1.5rem` to `2rem`.
- Header section separated by clean 1px divider or subtle hierarchy gap, footer with neutral `#F8FAFC` action strip when encapsulating forms.

### Data Tables (Enterprise Inventory Matrix)
- Header row: Background `#F8FAFC`, text uppercase `label-sm` in `#64748B`, height 40px, bottom border `1px solid #E2E8F0`.
- Data rows: Height 52px, font `body-md` in `#1E293B`, bottom border `1px solid #F1F5F9`, hover background `#F8FAFC`.
- Monospace SKU accents using font variants for zero-ambiguity character differentiation.