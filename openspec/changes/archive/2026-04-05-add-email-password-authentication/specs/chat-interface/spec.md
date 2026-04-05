## MODIFIED Requirements

### Requirement: Chat Page Route

The system SHALL provide an authenticated web route that displays the chat interface for interacting with DevBot.

#### Scenario: Access chat page

- **WHEN** an authenticated user navigates to `/chat`
- **THEN** the system SHALL display the chat interface
- **AND** it SHALL load the user's most recent conversation or create a new one
- **AND** the page title SHALL be "DevBot - Development Assistant"

#### Scenario: Access chat page without authentication

- **WHEN** an unauthenticated user navigates to `/chat`
- **THEN** the system SHALL redirect to the login page
- **AND** the intended `/chat` URL SHALL be preserved for redirect after login

### Requirement: Message Submission Endpoint

The system SHALL provide an authenticated HTTP endpoint that accepts user messages, processes them through DevBot, and returns the AI response.

#### Scenario: Submit message successfully

- **WHEN** an authenticated user makes a POST request to `/chat/message` with message content
- **THEN** the system SHALL verify the conversation belongs to the user
- **AND** it SHALL save the user message to the database
- **AND** it SHALL pass the message to DevBot agent
- **AND** it SHALL save the AI response to the database
- **AND** it SHALL return the conversation updated with both messages
- **AND** the response SHALL have HTTP status 200

#### Scenario: Submit message to another user's conversation

- **WHEN** an authenticated user makes a POST request to `/chat/message` with a conversation_id they don't own
- **THEN** the system SHALL reject the request
- **AND** the system SHALL return a 403 or 404 error
- **AND** no message SHALL be saved

#### Scenario: Submit message without authentication

- **WHEN** an unauthenticated user makes a POST request to `/chat/message`
- **THEN** the system SHALL redirect to the login page
- **AND** no message SHALL be processed
