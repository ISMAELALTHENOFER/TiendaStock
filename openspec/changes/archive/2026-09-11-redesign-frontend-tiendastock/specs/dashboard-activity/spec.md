# Dashboard Activity Specification

## Purpose

Provide a persisted, authenticated recent-activity feed for the dashboard.

## Requirements

### Requirement: Persist successful authenticated activity

The system MUST record an `ActivityEvent` only after a supported authenticated mutation commits successfully. Events MUST identify type, title, description, actor, subject, and occurrence time. The feed MUST retain 90 days and expose at most the 20 newest events. It MUST be an activity feed, not a notification system.

#### Scenario: Successful mutation is recorded
- GIVEN an authorized mutation completes successfully
- WHEN its transaction commits
- THEN one corresponding activity event is queryable by the authenticated actor's dashboard

#### Scenario: Failed mutation is not recorded
- GIVEN an authorized mutation fails validation or rolls back
- WHEN the response is returned
- THEN no activity event is created for that attempt

### Requirement: Authenticated activity endpoint

`GET /dashboard/activity` MUST require the existing authenticated and verified dashboard access. It MUST return `{data:[{id,type,title,description,actor,subject,occurred_at}],meta:{limit}}` with `limit` equal to 20. Unauthorized requests MUST retain current Laravel redirect/forbidden behavior.

#### Scenario: Activity is available
- GIVEN an authenticated authorized user
- WHEN the user requests `/dashboard/activity`
- THEN the response is JSON with the documented shape and no more than 20 newest events

#### Scenario: Empty activity
- GIVEN the user has no retained events
- WHEN the endpoint is requested
- THEN `data` is an empty array and the dashboard shows an explicit empty state, not sample content

### Requirement: React activity presentation

The React dashboard MUST render real endpoint data with loading, empty, and recoverable error states. It MUST show event time and readable hierarchy, MUST NOT fabricate entries, and MUST refetch after a successful migrated mutation. Dashboard JSON MUST NOT use browser or server caching.

#### Scenario: Endpoint failure
- GIVEN the activity request fails
- WHEN the dashboard renders the response state
- THEN it shows an error state with a retry action and does not show fabricated activity

## Non-Goals

No notifications, realtime delivery, user-configurable retention, historical backfill, or unrelated domain events are required.
