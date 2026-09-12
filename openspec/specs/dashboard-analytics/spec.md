# Dashboard Analytics Specification

## Purpose

Expose real dashboard chart data derived from completed sales without changing sales semantics.

## Requirements

### Requirement: Authenticated analytics contract

`GET /dashboard/analytics?window=30` MUST require the existing authenticated and verified dashboard access and MUST return `{window:{days,from,to},series:{sales_by_day:[{date,total,count}],sales_by_category:[{category,total,count}]}}`. The default window MUST be 30 days. Invalid windows MUST return 422; no matching data MUST return empty arrays.

#### Scenario: Completed sales are aggregated
- GIVEN completed sales exist within the requested window
- WHEN an authorized user requests analytics
- THEN daily and category series contain totals and counts derived from those sales and their items

#### Scenario: Cancelled sales are excluded
- GIVEN completed and cancelled sales exist in the same window
- WHEN analytics are requested
- THEN cancelled sales do not contribute to either series

### Requirement: Correctness and authorization

The analytics boundary MUST preserve existing Laravel authentication, verification, and role middleware. It MUST use real persisted `ventas` and `venta_items` data, MUST NOT fabricate values, and MUST not introduce browser/server caching. Authorization MUST NOT move into React.

#### Scenario: Unauthorized request
- GIVEN a guest or user without dashboard authorization
- WHEN the user requests analytics
- THEN Laravel returns the existing redirect or forbidden response and no chart data is exposed

### Requirement: Chart states and presentation

The React dashboard MUST present the returned series with readable labels and consistent design tokens. It MUST provide loading, empty, and recoverable error states, preserve accessibility, and refetch after a successful migrated mutation.

#### Scenario: No sales data
- GIVEN the requested window has no completed sales
- WHEN the chart response is rendered
- THEN both series are empty and the dashboard shows an explicit no-data state rather than a zero-filled fictional chart

## Non-Goals

No predictive analytics, live updates, arbitrary report builder, new sales rules, or chart values from client-side guesses are included.
