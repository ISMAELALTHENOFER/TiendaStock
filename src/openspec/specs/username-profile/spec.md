# Username in Profile Specification

## Purpose

Users can view and edit their `username` from the profile page. The username is displayed in the sidebar and navigation components. The email remains visible for password-reset identification.

## Requirements

### Requirement: Profile Form Includes Username

The `ProfileUpdateRequest` MUST validate `username` (required, string, max:255, unique except current user). The profile edit view MUST display a `username` input alongside the existing `name` and `email` inputs.

#### Scenario: User updates their username

- GIVEN an authenticated user with a unique proposed username
- WHEN PATCH-ing `/profile` with a valid `username`
- THEN the user's `username` MUST be updated
- AND the response MUST redirect with `status = "profile-updated"`

#### Scenario: User submits duplicate username

- GIVEN another user already has `username = "taken"`
- WHEN PATCH-ing `/profile` with `username = "taken"`
- THEN the system MUST return validation errors on the `username` field
- AND the user's username MUST NOT change

#### Scenario: Username field is rendered in profile view

- GIVEN an authenticated user visits `/profile`
- WHEN the profile edit page renders
- THEN an input with `name="username"` pre-filled with the user's current `username` MUST be present
- AND the input label MUST indicate "Username" or "Nombre de usuario"

### Requirement: Sidebar Shows Username

The sidebar (`sidebar.blade.php`) MUST display the authenticated user's `username` instead of or alongside the `email`. The `email` MAY remain in a smaller, secondary position.

#### Scenario: Sidebar renders username for authenticated user

- GIVEN an authenticated user with `username = "jdoe"`
- WHEN the sidebar renders
- THEN the text `"jdoe"` MUST appear in the user info section
- AND the email MAY appear in a smaller font below

### Requirement: Navigation Dropdown Shows Username

The top navigation (`navigation.blade.php`) MUST display the authenticated user's `username` in the settings dropdown trigger and in the responsive mobile menu.

#### Scenario: Navigation trigger shows username

- GIVEN an authenticated user with `username = "jdoe"`
- WHEN the top navigation renders
- THEN the dropdown trigger button MUST display `"jdoe"` (or the user's `name` alongside `username`)
- AND the responsive mobile menu MUST display `"jdoe"` in the user info section

### Requirement: Email Preserved for Password Reset

The email field MUST remain in the profile form, editable. The system MUST NOT remove or hide email from password-reset flows. Changing email MUST still invalidate `email_verified_at`.

#### Scenario: Email remains editable in profile

- GIVEN an authenticated user
- WHEN PATCH-ing `/profile` with a new valid `email`
- THEN the email MUST be updated
- AND `email_verified_at` MUST be set to `null` if the user is a MustVerifyEmail implementation
