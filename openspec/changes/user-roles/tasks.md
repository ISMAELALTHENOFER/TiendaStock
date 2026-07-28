# User Roles System — Tasks

## Review Workload Forecast
- 400-line budget risk: High
- Chained PRs recommended: Yes
- Decision needed before apply: No (size:exception granted)

## Chain strategy
- pending (single PR with exception)

---

## Phase 1: Foundation

- [x] 1.1 Create migration to add `role` column to users table with backfill
- [x] 1.2 Add role constants and helper methods to User model
- [x] 1.3 Update UserFactory with role states (admin, editor, viewer)
- [ ] 1.4 Create RoleSeeder for initial role assignment

## Phase 2: Middleware + Routes

- [x] 2.1 Create CheckRole middleware with parameterized constructor
- [x] 2.2 Register `role` alias in bootstrap/app.php
- [x] 2.3 Update admin routes in web.php to use role middleware

## Phase 3: Views

- [x] 3.1 Update sidebar to conditionally show Usuarios link based on role
- [ ] 3.2 Update dashboard to show role-appropriate content

## Phase 4: Tests

- [x] 4.1 Write tests for User model role methods
- [x] 4.2 Write tests for CheckRole middleware
- [x] 4.3 Update existing UserManagementTest to use role instead of is_admin

## Phase 5: Cleanup

- [x] 5.1 Create migration to remove is_admin column
- [x] 5.2 Remove CheckAdmin middleware
- [x] 5.3 Remove is_admin from User model (fillable, casts)
- [x] 5.4 Remove is_admin from UserFactory
- [x] 5.5 Remove is_admin from StoreUserRequest / UpdateUserRequest
- [x] 5.6 Remove is_admin from admin views (create, edit, index)
- [x] 5.7 Remove admin alias from bootstrap/app.php
- [x] 5.8 Remove is_admin from UserManagementTest
