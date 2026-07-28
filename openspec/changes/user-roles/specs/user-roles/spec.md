# User Roles System — Specification

## Scenarios

### SC-1: User model has role methods
GIVEN a User with role='admin'
WHEN calling isAdmin()
THEN it MUST return true

GIVEN a User with role='editor'
WHEN calling isEditor()
THEN it MUST return true

GIVEN a User with role='viewer'
WHEN calling isViewer()
THEN it MUST return true

GIVEN a User with role='editor'
WHEN calling hasRole(['admin', 'editor'])
THEN it MUST return true

GIVEN a User with role='viewer'
WHEN calling isAtLeast('editor')
THEN it MUST return false

### SC-2: CheckRole middleware restricts access
GIVEN a guest user
WHEN accessing a route with role:admin middleware
THEN it MUST redirect to login (401)

GIVEN a user with role='viewer'
WHEN accessing a route with role:admin middleware
THEN it MUST return 403

GIVEN a user with role='admin'
WHEN accessing a route with role:admin middleware
THEN it MUST return 200

### SC-3: Factory provides role states
GIVEN UserFactory::new()->admin()
WHEN creating
THEN the user SHALL have role='admin'

GIVEN UserFactory::new()->editor()
WHEN creating
THEN the user SHALL have role='editor'

### SC-4: Existing admin routes work with new role system
GIVEN a user with role='admin'
WHEN accessing /admin/users
THEN it MUST return 200

GIVEN a user with role='viewer'
WHEN accessing /admin/users
THEN it MUST return 403

### SC-5: Dashboard visibility
GIVEN a user with role='viewer'
WHEN viewing the dashboard
THEN the sidebar SHALL NOT show the "Usuarios" link

GIVEN a user with role='admin'
WHEN viewing the dashboard
THEN the sidebar SHALL show the "Usuarios" link

### SC-6: Backward compatibility
GIVEN existing users with is_admin=true
WHEN migrating
THEN their role MUST be 'admin'

GIVEN existing users with is_admin=false
WHEN migrating
THEN their role MUST be 'viewer'

### SC-7: Post-cleanup
GIVEN the cleanup migration has run
WHEN checking the users table schema
THEN the is_admin column MUST NOT exist

GIVEN CheckAdmin middleware has been removed
WHEN the bootstrap/app.php loads
THEN the 'admin' alias MUST NOT be registered
