# Admin User Management Specification

## Purpose

Admin-only user management replaces public self-registration. Admins create, edit, and manage all user accounts. Only users with `is_admin = true` can access the management interface.

## Requirements

### Requirement: Schema Changes

The users table MUST add `username` (string, unique, not nullable) and `is_admin` (boolean, default `false`). The `email` column MUST remain unique and nullable-free for password-reset support.

#### Scenario: Migration adds new columns

- GIVEN the migration runs
- WHEN inspecting the `users` table schema
- THEN it MUST contain `username` (string, unique, not null) and `is_admin` (boolean, default `false`)
- AND the existing columns (`name`, `email`, `password`) MUST remain unchanged

#### Scenario: Backfill generates usernames for existing rows

- GIVEN existing users with no `username` set
- WHEN the backfill migration runs
- THEN each user MUST receive a unique `username` derived from the `name` column (slugified)
- AND duplicate usernames MUST be disambiguated (e.g., append incrementing suffix)

#### Scenario: Rollback reverses the migration

- GIVEN the down migration runs
- THEN the `username` and `is_admin` columns MUST be dropped
- AND no data loss MUST occur on the remaining columns

### Requirement: Seed Admin User

The migration or seeder MUST create at least one admin user so an administrator can log in and manage users after deployment.

#### Scenario: Default admin exists after migration

- GIVEN fresh migrations and seeders run
- WHEN querying users with `is_admin = true`
- THEN exactly one user MUST exist with known credentials documented in `.env.example`

### Requirement: Admin Middleware

The system MUST define middleware (inline or dedicated) that checks `Auth::user()->is_admin` and returns a 403 response for non-admin users.

#### Scenario: Admin user accesses user management

- GIVEN a user with `is_admin = true`
- WHEN requesting any user management route
- THEN the system MUST return 200

#### Scenario: Non-admin user is rejected

- GIVEN a user with `is_admin = false`
- WHEN requesting any user management route
- THEN the system MUST return 403
- AND the user MUST NOT see admin links in the UI

### Requirement: Admin UserController

The system MUST provide a `UserController` with `create` and `store` actions behind admin middleware. It SHOULD provide `edit` and `update` for modifying existing users.

#### Scenario: Admin creates a new user

- GIVEN an admin user is authenticated
- WHEN POSTING to the user store route with valid `name`, `username`, `email`, `password`, and `password_confirmation`
- THEN a new user MUST be created with the submitted data
- AND `is_admin` MUST default to `false`
- AND the admin MUST be redirected with a success message

#### Scenario: Admin creates user with duplicate username

- GIVEN an admin user is authenticated
- WHEN POSTING to the user store route with a `username` that already exists
- THEN the system MUST return validation errors on the `username` field
- AND no user MUST be created

#### Scenario: Guest accesses user management

- GIVEN a guest (unauthenticated)
- WHEN requesting any user management route
- THEN the system MUST redirect to the login page (401)

### Requirement: Remove Public Registration

The system MUST delete `RegisteredUserController`, `auth.register` route, `register.blade.php`, the register link from `login.blade.php`, and `RegistrationTest.php`. The `/register` route MUST return 404.

#### Scenario: Register route returns 404

- GIVEN a guest
- WHEN GET-ing `/register`
- THEN the system MUST return 404
