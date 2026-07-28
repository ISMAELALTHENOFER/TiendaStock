# Design: Admin-Only User Creation

## Technical Approach

Add `username` + `is_admin` to User, swap login identity from email→username, remove public registration scaffold, create `CheckAdmin` middleware, and build `Admin/UserController` with admin-only views for user CRUD. Two-phase migration for safe production deploy.

## Architecture Decisions

### Decision: Two-phase migration for username

| Option | Tradeoff | Decision |
|--------|----------|----------|
| Single migration with backfill | Backfill in same migration increases duration/lock risk | **Rejected** |
| Two migrations: (1) nullable add, (2) backfill + not-nullable | Deploy-safe; code works during window between migrations | **Chosen** |

**Rationale**: Existing users need username backfill from email prefix. Making `username` nullable in migration 1 prevents blocking writes. Migration 2 backfills, then adds not-nullable. Code deploys after migration 1.

### Decision: Boolean `is_admin` vs. role system

| Option | Tradeoff | Decision |
|--------|----------|----------|
| Spatie/laravel-permission | Full RBAC — over-engineering for 2 levels | **Rejected** |
| `boolean is_admin` default false | Fits current scope, trivial to check in middleware | **Chosen** |

**Rationale**: Only two privilege levels (admin vs. regular). First seeded user gets `is_admin = true`.

### Decision: Delete vs. repurpose RegisteredUserController

| Option | Tradeoff | Decision |
|--------|----------|----------|
| Repurpose for admin creation | Mixes auth scaffold with admin logic | **Rejected** |
| Delete + create Admin/UserController | Clean separation, follows existing pattern | **Chosen** |

**Rationale**: `Admin/UserController` lives in its own namespace, uses `CheckAdmin` middleware, has different validation/behavior.

### Decision: Middleware registration

**Choice**: Register `CheckAdmin` via alias in `bootstrap/app.php` → `->withMiddleware(fn (Middleware $m) => $m->alias(['admin' => CheckAdmin::class]))`
**Rationale**: Laravel 12 uses `bootstrap/app.php` (no `app/Http/Kernel.php`). Alias `'admin'` for route usage.

## Data Flow

```
Guest ──→ GET/POST /login (username + password)
                   │
            ┌──────┴──────┐
            ▼              ▼
      Admin user      Regular user
      (is_admin=1)    (is_admin=0)
            │              │
            ▼              ▼
   /admin/users        Dashboard
   (auth+admin)        (auth)

Admin creates user:
  Admin/UserController@create → form → @store → DB insert
  Fields: name, username, email, password
  No email notification (admin sets initial password)
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `database/migrations/XXXX_XX_XX_XXXXXX_add_username_and_is_admin_to_users.php` | Create | Add `username` (nullable, unique), `is_admin` (boolean, default false) |
| `database/migrations/XXXX_XX_XX_XXXXXX_backfill_usernames.php` | Create | Backfill `UPDATE users SET username = ...` via SQLite-compatible SUBSTR/INSTR, then `->nullable(false)->change()` |
| `app/Models/User.php` | Modify | Add `username`, `is_admin` to `$fillable` |
| `app/Http/Requests/Auth/LoginRequest.php` | Modify | Change validation `email`→`username`, `Auth::attempt(['username' => ..., 'password' => ...])`, update `throttleKey()`, error bag key |
| `app/Http/Controllers/Auth/RegisteredUserController.php` | Delete | Public registration removed |
| `routes/auth.php` | Modify | Remove register routes (lines 15-18), remove import |
| `resources/views/auth/register.blade.php` | Delete | Public registration form |
| `resources/views/auth/login.blade.php` | Modify | Replace email input with username (id, name, label, placeholder, error bag), remove "Registrarse" section (lines 89-97) |
| `app/Http/Middleware/CheckAdmin.php` | Create | `if (! $request->user()?->is_admin) abort(403)` |
| `bootstrap/app.php` | Modify | Register `admin` middleware alias |
| `routes/web.php` | Modify | Add `/admin/users` resource route group with `auth`+`admin` |
| `app/Http/Controllers/Admin/UserController.php` | Create | index, create, store, edit, update (no show/destroy per spec) |
| `resources/views/admin/users/index.blade.php` | Create | Users table with name, username, email, admin badge, edit links |
| `resources/views/admin/users/create.blade.php` | Create | Form: name, username, email, password + confirm |
| `resources/views/admin/users/edit.blade.php` | Create | Edit form: name, username, email, password (optional) |
| `resources/views/profile/partials/update-profile-information-form.blade.php` | Modify | Add username input + error display |
| `app/Http/Requests/ProfileUpdateRequest.php` | Modify | Add `username` => `['required', 'string', 'max:255', 'lowercase', Rule::unique(User::class)->ignore($this->user()->id)]` |
| `resources/views/layouts/sidebar.blade.php` | Modify | Replace `Auth::user()->email` with `Auth::user()->username` (lines 37, 88) |
| `resources/views/layouts/navigation.blade.php` | Modify | Replace email with username (line 90) |
| `database/factories/UserFactory.php` | Modify | Add `'username' => fake()->unique()->userName()` |
| `database/seeders/DatabaseSeeder.php` | Modify | Add `'username' => 'testuser'`, `'is_admin' => true` |
| `tests/Feature/Auth/AuthenticationTest.php` | Modify | Use `username` instead of `email` in POST payload |
| `tests/Feature/Auth/RegistrationTest.php` | Delete | Registration removed |
| `tests/Feature/Admin/UserManagementTest.php` | Create | Feature tests for admin CRUD + middleware |
| `tests/Feature/ProfileTest.php` | Modify | Add `username` to profile update assertions |

**No changes needed**: `ConfirmablePasswordController` (validates against stored email, still works), `forgot-password`/`reset-password` views (use email for recovery — unchanged).

## Interfaces / Contracts

### User model changes
```php
protected $fillable = ['name', 'username', 'email', 'password', 'is_admin'];
// is_admin NOT added to $hidden — not a secret, useful in admin list
```

### LoginRequest changes
```php
// rules()
'username' => ['required', 'string'],
'password' => ['required', 'string'],

// authenticate()
Auth::attempt($this->only('username', 'password'), $this->boolean('remember'));

// throttleKey()
Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());

// Error bag key: 'username' (not 'email')
```

### CheckAdmin middleware
```php
public function handle(Request $request, Closure $next): Response
{
    if (! $request->user()?->is_admin) {
        abort(403);
    }
    return $next($request);
}
```

### Admin routes
```php
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', Admin\UserController::class)
            ->except(['show', 'destroy']);
    });
```

### Admin/UserController
- **index**: paginate all users, return `admin.users.index`
- **create**: return `admin.users.create`
- **store**: validate `name`, `username` (unique), `email` (unique), `password` (confirmed) — create user, redirect to index with flash
- **edit**: route-model-bind `User`, return view
- **update**: validate same fields minus password-optional, `username`/`email` unique ignore current ID

## Testing Strategy

| Layer | What | How |
|-------|------|-----|
| Feature | Auth via username | Factory creates user with `username`, POST to `/login` with `username` + `password` |
| Feature | Registration returns 404 | `GET /register` → 404 |
| Feature | Admin middleware blocks non-admin | Auth as regular user, `GET /admin/users` → 403 |
| Feature | Admin middleware allows admin | Auth as `is_admin=true`, `GET /admin/users` → 200 |
| Feature | Admin creates user | Store valid data → user in DB, redirect with success |
| Feature | Admin creates user duplicate | Post existing username → validation error |
| Feature | Profile update with username | Update name + username + email → persists, reflects new username |

## Migration / Rollout

1. **Migration 1** — `add_username_and_is_admin_to_users`: adds `username` (nullable, unique), `is_admin` (default false). Zero-downtime for existing rows.
2. **Deploy code** — new code reads/writes `username`, handles both null and populated.
3. **Migration 2** — `backfill_usernames`: `UPDATE users SET username = LOWER(SUBSTR(email, 1, INSTR(email, '@') - 1)) WHERE username IS NULL`. Then `Schema::table('users', fn => $table->string('username')->nullable(false)->change())`.
4. **Rollback**: Reverse both migrations + git revert. Login falls back to email-based if code is reverted. Deleted files restored from git.

## Open Questions

None — scope and implementation are fully specified.
