# Tasks: Admin-Only User Creation

## Phase 1: Schema Foundation
- [x] 1.1 Create migration to add `username` (nullable) and `is_admin` (boolean, default false) to users table
- [x] 1.2 Create second migration to backfill usernames for existing users (from email prefix), then make username not-nullable
- [x] 1.3 Update User model: add `username` to `$fillable`, add `is_admin` to `$fillable` and `$casts`
- [x] 1.4 Update `DatabaseSeeder` and `UserFactory` to create an admin user with username

## Phase 2: Auth Overhaul
- [x] 2.1 Update `LoginRequest.php`: change `email` to `username` in rules, authenticate(), throttleKey(), and error messages
- [x] 2.2 Update `login.blade.php`: replace email input with username input, remove "Registrarse" link
- [x] 2.3 Remove public registration: delete `RegisteredUserController.php`, delete `register.blade.php`, remove register routes from `routes/auth.php`

## Phase 3: Admin User Management
- [x] 3.1 Create `CheckAdmin` middleware at `app/Http/Middleware/CheckAdmin.php`
- [x] 3.2 Register `admin` middleware alias in `bootstrap/app.php`
- [x] 3.3 Create `Admin/UserController` with index, create, store, edit, update methods
- [x] 3.4 Create form requests: `StoreUserRequest.php`, `UpdateUserRequest.php` under `app/Http/Requests/Admin/`
- [x] 3.5 Create admin views: `resources/views/admin/users/index.blade.php`, `create.blade.php`, `edit.blade.php`
- [x] 3.6 Add admin routes in `routes/web.php` under `auth` + `admin` middleware

## Phase 4: Profile & UI
- [x] 4.1 Add username field to profile edit form and ProfileUpdateRequest
- [x] 4.2 Show username instead of email in sidebar layout
- [x] 4.3 Show username instead of email in navigation layout

## Phase 5: Testing
- [x] 5.1 Update `tests/Feature/Auth/AuthenticationTest.php` to use username instead of email
- [x] 5.2 Delete `tests/Feature/Auth/RegistrationTest.php`
- [x] 5.3 Add admin middleware test (guest gets redirect, non-admin gets 403, admin passes)
- [x] 5.4 Add admin CRUD test (list, create, edit users)
- [x] 5.5 Update `tests/Feature/ProfileTest.php` to include username
