## MODIFIED Requirements

### Requirement: Conversation List Endpoint

The system SHALL provide an authenticated HTTP endpoint that returns a list of the authenticated user's conversations sorted by most recent first.

#### Scenario: Retrieve conversation list

- **WHEN** an authenticated user makes a GET request to `/chat/conversations`
- **THEN** the system SHALL return a JSON array of conversations owned by the authenticated user
- **AND** conversations SHALL be sorted by created_at descending (most recent first)
- **AND** each conversation SHALL include id, title, and created_at
- **AND** the list SHALL be limited to the 50 most recent conversations
- **AND** the response SHALL have HTTP status 200
- **AND** conversations belonging to other users SHALL NOT be included

#### Scenario: Retrieve conversation list without authentication

- **WHEN** an unauthenticated user makes a GET request to `/chat/conversations`
- **THEN** the system SHALL redirect to the login page
- **AND** no conversation data SHALL be returned
