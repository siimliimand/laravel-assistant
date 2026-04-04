## ADDED Requirements

### Requirement: Action classes SHALL encapsulate single-responsibility business logic

Each Action class SHALL handle one specific business operation (e.g., creating a conversation, sending a message, processing a project). Actions SHALL be located in `app/Actions/` namespace and follow the naming convention `{Verb}{Noun}Action.php`.

#### Scenario: Action executes business logic

- **WHEN** an Action's `execute()` method is called with valid data
- **THEN** it performs the specific business operation and returns the result

#### Scenario: Action is reusable across different contexts

- **WHEN** the same business logic is needed from a controller, CLI command, and API endpoint
- **THEN** the same Action class can be injected and executed in all three contexts

### Requirement: Actions SHALL use dependency injection via constructor

All dependencies (models, services, external APIs) SHALL be injected through the Action's constructor. Business data SHALL be passed via the `execute()` method parameters.

#### Scenario: Action receives dependencies from service container

- **WHEN** an Action is type-hinted in a controller method parameter
- **THEN** Laravel's service container automatically resolves and injects all constructor dependencies

#### Scenario: Action receives business data via execute method

- **WHEN** calling `$action->execute($data, $user)`
- **THEN** the Action uses the provided data and user context to perform its operation

### Requirement: Actions SHALL be independently testable

Each Action SHALL be testable in isolation without requiring HTTP requests or full application boot. Tests SHALL mock dependencies and verify the Action's behavior.

#### Scenario: Unit test Action with mocked dependencies

- **WHEN** testing an Action that sends messages via DevBot
- **THEN** the test mocks the DevBot agent and verifies the Action calls it with correct parameters

### Requirement: Actions SHALL handle their own error handling

Actions SHALL catch and handle domain-specific exceptions, converting them to appropriate responses or re-throwing with context. Controller error handling SHALL be minimal.

#### Scenario: Action catches and wraps external API errors

- **WHEN** an Action calls an external AI API that throws an exception
- **THEN** the Action catches it and throws a domain-specific exception with context (conversation ID, user ID)
