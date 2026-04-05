## ADDED Requirements

### Requirement: User Registration

The system SHALL allow users to create an account using email and password credentials.

#### Scenario: Successful registration

- **WHEN** a user submits the registration form with valid email, name, and password
- **THEN** the system SHALL create a new user account
- **AND** the password SHALL be securely hashed
- **AND** the user SHALL be automatically authenticated
- **AND** the user SHALL be redirected to the chat interface
- **AND** a session SHALL be created

#### Scenario: Registration validation - duplicate email

- **WHEN** a user attempts to register with an email that already exists
- **THEN** the system SHALL reject the registration
- **AND** the system SHALL display a validation error message
- **AND** no new user SHALL be created

#### Scenario: Registration validation - weak password

- **WHEN** a user submits a password that does not meet minimum requirements
- **THEN** the system SHALL reject the registration
- **AND** the system SHALL display a validation error explaining password requirements
- **AND** no new user SHALL be created

### Requirement: User Login

The system SHALL authenticate users with email and password credentials.

#### Scenario: Successful login

- **WHEN** a user submits valid email and password credentials
- **THEN** the system SHALL authenticate the user
- **AND** the system SHALL create a session
- **AND** the user SHALL be redirected to the chat interface
- **AND** the user's conversations SHALL be accessible

#### Scenario: Login failure - invalid credentials

- **WHEN** a user submits incorrect email or password
- **THEN** the system SHALL reject the login attempt
- **AND** the system SHALL display an authentication error message
- **AND** the user SHALL remain on the login page
- **AND** no session SHALL be created

#### Scenario: Login throttling

- **WHEN** a user makes too many failed login attempts
- **THEN** the system SHALL temporarily lock login for that email/IP
- **AND** the system SHALL display a message indicating how long to wait
- **AND** further login attempts SHALL be blocked during the cooldown period

### Requirement: User Logout

The system SHALL allow authenticated users to end their session.

#### Scenario: User logs out

- **WHEN** an authenticated user clicks the logout button
- **THEN** the system SHALL invalidate the user's session
- **AND** the user SHALL be redirected to the home page
- **AND** the user SHALL no longer have access to protected routes
- **AND** attempting to access chat SHALL redirect to login

### Requirement: Password Reset

The system SHALL allow users to reset their forgotten passwords via email.

#### Scenario: Request password reset

- **WHEN** a user submits their email on the password reset page
- **THEN** the system SHALL generate a password reset token
- **AND** the system SHALL send a password reset email with a link
- **AND** the link SHALL include the token and email
- **AND** the user SHALL see a confirmation message

#### Scenario: Reset password with valid token

- **WHEN** a user clicks the password reset link and submits a new password
- **THEN** the system SHALL validate the token
- **AND** the system SHALL update the user's password
- **AND** the user SHALL be redirected to login
- **AND** the old password SHALL no longer work

#### Scenario: Reset password with expired token

- **WHEN** a user attempts to reset password with an expired or invalid token
- **THEN** the system SHALL reject the reset attempt
- **AND** the system SHALL display an error message
- **AND** the user SHALL be prompted to request a new reset link

### Requirement: Authentication Middleware Protection

The system SHALL protect all chat-related routes with authentication middleware.

#### Scenario: Access protected route without authentication

- **WHEN** an unauthenticated user attempts to access `/chat` or any chat endpoint
- **THEN** the system SHALL redirect the user to the login page
- **AND** the system SHALL preserve the intended destination URL
- **AND** after login, the user SHALL be redirected to the intended destination

#### Scenario: Access protected route with authentication

- **WHEN** an authenticated user accesses `/chat` or any chat endpoint
- **THEN** the system SHALL grant access to the requested resource
- **AND** the user SHALL see the chat interface
- **AND** the user's data SHALL be available

### Requirement: Session Persistence

The system SHALL maintain user authentication state across page requests and browser sessions.

#### Scenario: Session persists across page navigation

- **WHEN** a logged-in user navigates between pages
- **THEN** the user SHALL remain authenticated
- **AND** the user's identity SHALL be available via `auth()->user()`
- **AND** protected routes SHALL remain accessible

#### Scenario: Remember me functionality

- **WHEN** a user logs in with the "Remember me" checkbox checked
- **THEN** the system SHALL create a persistent cookie
- **AND** the user SHALL remain authenticated after closing and reopening the browser
- **AND** the session SHALL persist according to Laravel's remember token configuration
