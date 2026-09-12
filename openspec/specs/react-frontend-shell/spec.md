# React Frontend Shell Specification

## Purpose

Define the React presentation boundary and consistent responsive experience without transferring Laravel authority to the browser.

## Requirements

### Requirement: Route-level React coexistence

React MUST own one migrated named route at a time through a Blade host and MUST NOT share DOM ownership with Blade/Alpine on that route. Unmigrated routes MUST remain Blade/Alpine. The route switch MUST support per-route rollback and `FRONTEND_DRIVER=blade` global rollback.

#### Scenario: Migrated route mounts
- GIVEN a named route is configured for React
- WHEN its Laravel response is rendered
- THEN the Blade host mounts the shared React shell and preserves the existing URL and route name

#### Scenario: Legacy route remains available
- GIVEN a route is not configured for React
- WHEN a user visits it
- THEN the existing Blade/Alpine surface renders without React ownership

### Requirement: Shared design system and shell

The React UI MUST use Inter, green primary, neutral canvas/surface/ink/border tokens, a 4/8 spacing rhythm, 8px control radius, 12px card radius, and subtle shadow. Shared Button, Input, Select, Badge, Card, Table, Dropdown, Modal, Empty, Loading, Error, and Toast primitives MUST provide consistent states and MUST NOT duplicate equivalent styles. The shell MUST provide grouped, role-filtered Sidebar, compact Header, active route state, current user menu, and main content.

#### Scenario: Desktop shell
- GIVEN a permitted user at a migrated desktop route
- WHEN the page loads at 1024px or wider
- THEN the sidebar is fixed, the header is compact, and the active navigation item is identifiable

#### Scenario: Role visibility
- GIVEN a user lacks permission for a module
- WHEN the shell renders
- THEN its navigation action is absent while server authorization remains authoritative

### Requirement: Principal screen migration

Dashboard, Ventas/POS/detail, Productos, Categorías, and Usuarios MUST be available through React slices while preserving their current forms, filters, JSON searches, CRUD actions, statuses, flash messages, cancellation, and print behavior. Dashboard MUST retain the four existing metrics and role-gated quick actions; Nueva Venta MUST have primary emphasis.

#### Scenario: Inventory and administration screens
- GIVEN an authorized user visits Productos, Categorías, or Usuarios
- WHEN the React slice renders
- THEN current fields, validation outcomes, pagination/search behavior, and permitted actions remain available

### Requirement: Mobile-first responsive behavior

The base layout MUST stack and reflow without horizontal page overflow. At 320px and 767px, the sidebar MUST be a drawer with backdrop; at 768px and 1023px it MUST be collapsible; at 1024px, 1440px, and wider it MUST use the desktop shell. Cards MUST stack, filters MUST wrap, and tables MUST use intentional horizontal scrolling or an equivalent complete mobile presentation; essential information MUST NOT be clipped or hidden.

#### Scenario: Boundary viewports
- GIVEN the same migrated screen
- WHEN it is tested at 320, 767, 768, 1023, 1024, and 1440+ CSS pixels
- THEN content remains usable, focus is not clipped, and no unintended page overflow occurs

### Requirement: Accessible interaction and states

Interactive controls MUST have visible keyboard focus and at least 44px touch targets. Drawer and modal interactions MUST support Escape, focus trapping, focus return, and safe scroll locking. Loading, empty, and error states MUST be explicit. Motion MAY use 150–250ms transitions but MUST be disabled by `prefers-reduced-motion`; zoom and long labels MUST remain usable.

#### Scenario: Keyboard and reduced motion
- GIVEN a keyboard user with reduced motion enabled
- WHEN the user opens and closes navigation or a modal
- THEN focus order and return are deterministic and no non-essential transition is applied

## Non-Goals

This change MUST NOT add global search or notifications, rewrite domain rules, alter permissions, remove all legacy surfaces atomically, or invent data.
