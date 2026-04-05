## MODIFIED Requirements

### Requirement: New Chat Creation Endpoint

The system SHALL provide an HTTP endpoint that creates a new empty conversation associated with the authenticated user and returns its details.

#### Scenario: Create new conversation

- **WHEN** an authenticated user makes a POST request to `/chat/new`
- **THEN** the system SHALL create a new conversation with the user's ID set as `user_id`
- **AND** the conversation SHALL be associated with the authenticated user
- **AND** it SHALL return the conversation ID and title as JSON
- **AND** the response SHALL have HTTP status 201
- **AND** the conversation SHALL have zero messages initially

#### Scenario: Create conversation without authentication

- **WHEN** an unauthenticated user makes a POST request to `/chat/new`
- **THEN** the system SHALL redirect to the login page
- **AND** no conversation SHALL be created

### Requirement: New Chat UI Control

The system SHALL provide a visible "New Chat" button in the chat interface for authenticated users to create a new conversation without requiring a page reload.

#### Scenario: Click new chat button

- **WHEN** an authenticated user clicks the "New Chat" button
- **THEN** the system SHALL create a new conversation via AJAX
- **AND** the conversation SHALL be associated with the authenticated user
- **AND** it SHALL navigate to the new conversation
- **AND** the message history SHALL be empty
- **AND** the welcome message SHALL be displayed
- **AND** the URL SHALL update to reflect the new conversation
