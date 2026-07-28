# Username Authentication Specification

## Purpose

Authentication uses `username` instead of `email` as the login credential. Email is retained solely for password-reset flows. Rate-limiting and error bags align with the `username` field.

## Requirements

### Requirement: LoginRequest Validation

The `LoginRequest` form request MUST validate `username` (required, string) in place of `email` (required, email). The `password` validation MUST remain unchanged.

#### Scenario: Login with valid username and password

- GIVEN a user with `username = "jdoe"` and a valid password
- WHEN POSTING to `/login` with `username = "jdoe"` and the correct password
- THEN the user MUST be authenticated
- AND the response MUST redirect to the dashboard

#### Scenario: Login with non-existent username

- GIVEN a non-existent `username`
- WHEN POSTING to `/login` with that `username` and any password
- THEN the system MUST return validation errors on the `username` field
- AND the user MUST NOT be authenticated

#### Scenario: Login with valid username but wrong password

- GIVEN a user with `username = "jdoe"`
- WHEN POSTING to `/login` with `username = "jdoe"` and an incorrect password
- THEN the system MUST return validation errors on the `username` field
- AND the user MUST NOT be authenticated

### Requirement: Authentication Attempt

The `authenticate()` method MUST call `Auth::attempt()` with `username` as the credential key instead of `email`.

#### Scenario: Successful auth uses username credential

- GIVEN a user exists with `username = "jdoe"`
- WHEN `Auth::attempt(['username' => 'jdoe', 'password' => 'correct'])` is called
- THEN the user MUST be authenticated

### Requirement: Throttle Key

The `throttleKey()` method MUST use `$this->string('username')` instead of `$this->string('email')`.

#### Scenario: Rate limiter keys on username

- GIVEN five failed login attempts for `username = "jdoe"`
- WHEN a sixth attempt is made
- THEN the system MUST return a rate-limit error on the `username` field
- AND different usernames from the same IP MUST have independent rate-limit counters

### Requirement: Error Message Bag

The validation error bag in `authenticate()` and `ensureIsNotRateLimited()` MUST reference the `username` field.

#### Scenario: Failed auth error references username

- GIVEN a failed login attempt
- WHEN validation errors are returned
- THEN the errors MUST be keyed under `username`
- AND the login view MUST display the error next to the username input

### Requirement: Login View

The `login.blade.php` view MUST replace the email input with a username input (type text, not email). The register link MUST be removed.

#### Scenario: Login screen shows username field

- GIVEN a guest visits `/login`
- WHEN the page renders
- THEN an input with `name="username"` MUST be present
- AND no input with `name="email"` MUST be present
- AND no link to `/register` MUST be present
