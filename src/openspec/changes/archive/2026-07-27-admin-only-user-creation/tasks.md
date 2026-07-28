# Tasks: Admin-Only User Creation

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 600–800 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | 4 PRs (Migration → Auth → Admin CRUD → Profile) |
| Delivery strategy | auto-chain |

Decision needed before apply: No
Chained PRs recommended: Yes
Chain strategy: pending
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Migration + User model | PR 1 | Foundation: all other tasks depend on username + is_admin existing |
| 2 | Login by username + Remove registration | PR 2 | Depends on PR 1 (column exists). Auth behavior + view + cleanup |
| 3 | Admin middleware + UserController | PR 3 | Depends on PR 1 (is_admin column) and PR 2 (removed reg) |
| 4 | Profile & UI username surface | PR 4 | Depends on PR 1 (column exists). Independent from PR 2/3 |

## Phase 1: Schema Foundation

- [x] 1.1 **Migration**: Add `username` (nullable) + `is_admin` (bool, default false) — Phase 1
- [x] 1.2 **Migration**: Backfill usernames from name/email prefix, then make username not-nullable — Phase 2
- [x] 1.3 **Model**: Add `username` to `$fillable`, `is_admin` to `$casts` in `User.php`
- [x] 1.4 **Seeder**: Seed default admin user (document credentials in `.env.example`)

## Phase 2: Auth Overhaul

- [x] 2.1 **Backend — LoginRequest**: Change validation to `username`, update `authenticate()` and `throttleKey()`
- [x] 2.2 **Frontend — login view**: Replace email input with username input, remove register link
- [x] 2.3 **Cleanup**: Delete `RegisteredUserController.php`, `register.blade.php`, register routes from `routes/auth.php`

## Phase 3: Admin User Management

- [x] 3.1 **Backend — Middleware**: Create `CheckAdmin.php` returning 403 for non-admin users
- [x] 3.2 **Backend — Bootstrap**: Register `admin` alias in `bootstrap/app.php`
- [x] 3.3 **Backend — Form Requests**: Create `StoreUserRequest.php` + `UpdateUserRequest.php`
- [x] 3.4 **Backend — Controller**: Create `Admin/UserController.php` with index/create/store/edit/update
- [x] 3.5 **Frontend — Admin views**: Create `index.blade.php`, `create.blade.php`, `edit.blade.php`
- [x] 3.6 **Backend — Routes**: Add admin routes group in `routes/web.php` with `auth` + `admin`

## Phase 4: Profile & UI

- [x] 4.1 **Backend — ProfileUpdateRequest**: Add `username` validation (required, unique except self)
- [x] 4.2 **Frontend — Profile view**: Add username input to `profile/edit.blade.php`
- [x] 4.3 **Frontend — Chrome**: Show username instead of email in `sidebar.blade.php` and `navigation.blade.php`

## Phase 5: Testing

- [x] 5.1 **Test — Auth**: Update `AuthenticationTest.php` to use username; delete `RegistrationTest.php`
- [x] 5.2 **Test — Middleware**: Test admin gets 200, non-admin 403, guest 401
- [x] 5.3 **Test — Admin CRUD**: Test store success, duplicate username validation, profile username update
- [x] 5.4 **Test — Migration**: Verify rollback drops columns without data loss
