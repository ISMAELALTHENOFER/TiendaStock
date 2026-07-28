## Exploration: Admin-Only User Creation

### Current State

Laravel 12 app with Breeze authentication scaffold (session-based, `App\Models\User`). Currently supports public registration and email-based login. The admin UI (sidebar/topbar/dashboard) shows `Auth::user()->email` in two places.

**Auth flow (current):**
- Public registration via `GET /register` + `POST /register` (routes, controller, view)
- Login via `GET /login` + `POST /login` using email + password
- Password reset via email (forgot/reset flow sends links by email)
- Email verification (optional, but scaffolded)

**No admin user management exists** — there is no `UserController`, no admin views for user CRUD, no role/permission system. The only user creation happens through public registration.

---

### Affected Areas

| # | File | Why |
|---|------|-----|
| 1 | `routes/auth.php` | Remove registration routes (lines 15-18); no structural changes to login routes |
| 2 | `resources/views/auth/register.blade.php` | **DELETE** — entire public registration view |
| 3 | `app/Http/Controllers/Auth/RegisteredUserController.php` | **DELETE** — or repurpose for admin user creation (recommended: delete and create dedicated admin controller) |
| 4 | `resources/views/auth/login.blade.php` | Change field: `email` → `username`, remove "Registrarse" section (lines 89-97), update label and validation errors |
| 5 | `app/Http/Requests/Auth/LoginRequest.php` | Change validation from `'email' => ['required', 'string', 'email']` to `'username' => ['required', 'string']`, update `Auth::attempt()` to use `username`, update throttleKey and error bag keys |
| 6 | `app/Models/User.php` | Add `username` to `$fillable`, add `$hidden` if needed, add cast if needed |
| 7 | `database/migrations/0001_01_01_000000_create_users_table.php` | Add `$table->string('username')->unique()->after('name')` — this requires a **new migration** (never modify existing migrations in production) |
| 8 | `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | No changes needed (delegates to `LoginRequest`) |
| 9 | `database/factories/UserFactory.php` | Add `'username' => fake()->unique()->userName()` to definition |
| 10 | `database/seeders/DatabaseSeeder.php` | Add `'username' => 'testuser'` to the seeded user |
| 11 | `resources/views/layouts/sidebar.blade.php` | Replace `Auth::user()->email` with `Auth::user()->username` on lines 37 and 88 (cosmetic — shows username instead of email) |
| 12 | `resources/views/layouts/navigation.blade.php` | Replace `Auth::user()->email` with `Auth::user()->username` on line 90 (responsive menu) |
| 13 | `resources/views/profile/partials/update-profile-information-form.blade.php` | Add username field to profile update form — user should be able to see/change their username |
| 14 | `app/Http/Requests/ProfileUpdateRequest.php` | Add username validation rule (unique, string, max:255) |
| 15 | `tests/Feature/Auth/AuthenticationTest.php` | Update tests: use `username` instead of `email` in login payload (lines 25, 38) |
| 16 | `tests/Feature/Auth/RegistrationTest.php` | **DELETE whole file** — registration is removed |
| 17 | `resources/views/auth/forgot-password.blade.php` | No change needed — password reset still uses email (user needs email to receive the link) |
| 18 | `resources/views/auth/reset-password.blade.php` | No change needed — reset flow uses email |
| 19 | `app/Http/Controllers/Auth/PasswordResetLinkController.php` | No change — identifies user by email for sending links |
| 20 | `app/Http/Controllers/Auth/NewPasswordController.php` | No change — identifies user by email for token verification |
| 21 | `resources/views/auth/confirm-password.blade.php` | Confirm password uses `Auth::user()->email` – change to `Auth::user()->username` or keep email (it's just a display value in the form, not functional) |
| 22 | `app/Http/Controllers/Auth/ConfirmablePasswordController.php` | Returns `email` in the view — change to `username` (line 28) |

**Scope items (not in the original request but discovered):**

| # | File | Why |
|---|------|-----|
| 23 | `tests/Feature/ProfileTest.php` | Profile update test sends `email` — may need to add `username` field |
| 24 | `tests/Feature/Auth/EmailVerificationTest.php` | Uses `$user->email` for hash — keep email in User model, this is fine |
| 25 | `tests/Feature/Auth/PasswordResetTest.php` | Uses `$user->email` — fine, keep email for password reset |

---

### Unresolved / Needs Decision

1. **Admin user management UI** — does an admin panel already exist? **No.** This change only removes public registration and changes login to username. The actual admin user creation screen is NOT in scope unless explicitly requested. Currently there is no role/permission system (no admin role, no middleware), so admin-only creation would mean "any authenticated user" or we need to scope it.

2. **Email field on User** — keep it or remove it? The password reset flow and email verification rely on `email`. If removed, those features break. **Recommendation: keep email, but only as a non-login field** (like a contact/notification address). Make it nullable if desired.

3. **Username uniqueness** — case-sensitive or case-insensitive? Laravel's default string column is case-sensitive in most DB engines. A `lowercase` validation rule can be applied.

4. **Existing users** — if there are existing users in production, a new migration adding `username` requires backfilling. The migration should either make `username` nullable initially or provide default usernames based on the email prefix.

---

### Approaches

**1. Minimal change — only remove registration, swap email for username**
- Delete register view, route, controller
- Modify login view and LoginRequest to use `username`
- New migration for `username` column
- Update User factory and seeder
- Update tests
- **Effort**: Low
- **Scope**: Exact match to the stated requirements
- **Cons**: No admin creation UI yet; if the user needs to create users afterward, they'll need a follow-up change

**2. Full scope — removal + admin user CRUD**
- Everything in approach 1
- Create `UserController` (index, create, store, edit, update) with admin middleware
- Create user management views
- Add role/permission system (admin role)
- **Effort**: High
- **Cons**: Significantly more work, not explicitly requested, may introduce premature complexity

**3. Partial admin — controller without views (API-style)**
- Everything in approach 1
- Create `AdminUserController` with store method only (for tinker / seeder)
- No views, no admin role
- **Effort**: Low-Medium
- **Cons**: Doesn't solve the "admin needs to create users" UX problem

---

### Recommendation

**Approach 1** — implement exactly what's asked: remove public registration, switch login to username, add username column. Skip admin creation UI for now since there's no role/permission system to gate it. The admin can create users via `tinker` or a seeder. If admin UI is needed, it should be a separate change that also introduces roles/permissions.

If the user wants admin creation UI in this same change, we should escalate to discuss role/permission scope first.

---

### Migration Strategy

New migration (do NOT modify the existing `0001_01_01_000000_create_users_table.php`):
- `database/migrations/YYYY_MM_DD_HHMMSS_add_username_to_users_table.php`
- Add `username` column (unique, nullable initially for existing rows, then backfill)
- For fresh installs: make it `nullable(false)` after backfill

---

### Decisions Documented

| Decision | Choice | Rationale |
|----------|--------|-----------|
| **Keep email field** | Yes | Required for password reset and email verification. Change to non-login, non-required if desired. |
| **Registration route removal** | Remove routes from `auth.php` | Cleaner than commenting — git history preserves them |
| **RegisteredUserController** | Delete outright | No longer needed. If admin creation is added later, create a dedicated non-auth controller |
| **Username validation** | `['required', 'string', 'max:255', 'lowercase', 'unique:users,username']` | Standard, matches Laravel conventions |
| **Login error key** | Change from `email` to `username` | Error messages need to match the form field name |

---

### Risks

- **Password reset breaks if email is removed** — mitigated by keeping email as a non-login field
- **Existing users have no username** — mitigated by nullable migration with backfill
- **LoginRequest throttleKey uses username** — rate limiting key changes from email to username, no functional risk
- **Email verification tests still pass** — only if email remains on the User model (it does)

---

### Ready for Proposal

Yes — the analysis is complete. The scope is well-defined. Recommend confirming with the user:
1. Whether they want admin user creation UI in this same change or as a follow-up
2. Whether email should remain on the User model (for password reset) or be removed entirely
3. Whether there's an existing admin role/permission system (there isn't — this should be discussed)
