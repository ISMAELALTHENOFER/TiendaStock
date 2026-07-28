# User Roles System — Design

## Architecture

### Roles
- `admin`: Full system access (manage users, CRUD all entities)
- `editor`: CRUD products and categories
- `viewer`: Read-only access to dashboard, products list, categories list

### Migration Strategy
1. Add `role` column (string, default 'viewer') to users
2. Backfill: `is_admin = true` → role='admin', others → role='viewer'
3. Phase 5 drops `is_admin` column

### Model Methods (App\Models\User)
- `isAdmin(): bool` — role === 'admin'
- `isEditor(): bool` — role === 'editor'
- `isViewer(): bool` — role === 'viewer'
- `hasRole(string|array $roles): bool` — check if user has any of the given roles
- `isAtLeast(string $role): bool` — hierarchy: admin >= editor >= viewer

### Middleware
- `CheckRole` with parameterized constructor
- Usage: `->middleware(['role:admin'])` or `->middleware(['role:editor,admin'])`
- Registered alias: `role`

### Route Changes
- Admin routes (`admin.*`) → `role:admin`
- Product/Category routes remain under `auth` (checked at view level for editors)

### View Changes
- Sidebar: hide "Usuarios" link for non-admin roles
- Dashboard: show admin widgets only for admins

### Cleanup (Phase 5)
- Delete `app/Http/Middleware/CheckAdmin.php`
- Remove `is_admin` column from users table
- Remove `is_admin` from fillable/casts/factory
- Update `bootstrap/app.php` to remove `admin` alias
