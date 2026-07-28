# Proposal: Admin-Only User Creation

## Intent

Eliminate public registration. Login by username instead of email. Admin creates all users. Keeps email for password resets.

## Scope

### In Scope
- Add `username` (unique, not null) and `is_admin` (bool, default false) to users
- Remove public registration (view, route, controller, login link)
- Login by username (LoginRequest, throttleKey)
- Admin-only UserController + create user view
- Username in profile edit; show in sidebar/navigation
- Backfill usernames for existing users
- Fix AuthenticationTest, remove RegistrationTest

### Out of Scope
- Role packages — `is_admin` suffices
- User listing or editing
- Forgot-username flow
- Email-based login toggle

## Capabilities

`openspec/specs/` empty — all new.

### New Capabilities
- `admin-user-management`: Admin creates users
- `username-auth`: Login + throttle by username
- `username-profile`: Username in profile form and UI chrome

### Modified Capabilities
None.

## Approach

1. Migration: add `username` + `is_admin`, backfill from name/email prefix
2. Remove registration: delete controller, view, routes, login link
3. Login by username: `LoginRequest` validates `username`, throttleKey uses it
4. Admin CRUD: `UserController` with `create`/`store` behind admin middleware
5. Profile: add username to form request, profile view, sidebar, navigation
6. Tests: remove RegistrationTest, update AuthenticationTest

## Affected Areas

- Migration — new (username + is_admin)
- `User.php` — fillable + casts
- `RegisteredUserController` + `register.blade.php` — deleted
- `routes/auth.php` — remove register routes
- `auth/login.blade.php` — username input, remove register link
- `LoginRequest.php` — username field + throttleKey
- `UserController.php` + `CreateUserRequest.php` — new
- `admin/users/create.blade.php` — new
- `routes/web.php` — UserController routes
- `ProfileUpdateRequest.php` + profile form — add username
- `sidebar.blade.php` + `navigation.blade.php` — show username
- `RegistrationTest.php` — deleted
- `AuthenticationTest.php` — use username

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Existing users locked out (no username) | Low | Backfill runs BEFORE auth change |
| Admin lockout (no admin to create users) | Low | Seed admin in migration |
| Password reset breaks if email changes | Low | Keep email unmodified |

## Rollback Plan

Down-migration drops columns. Restore registration files from git. Revert LoginRequest. Rollback release.

## Dependencies

- `password_reset_tokens` keys on `email` — must stay.

## Success Criteria

- [ ] Migration adds columns + backfills all existing users
- [ ] Login by username works; existing users log in with backfilled username
- [ ] Register route returns 404; no register link on login
- [ ] Admin creates accounts; non-admin gets 403
- [ ] Profile edit includes username; sidebar shows username
- [ ] All tests pass; RegistrationTest removed; AuthenticationTest green
