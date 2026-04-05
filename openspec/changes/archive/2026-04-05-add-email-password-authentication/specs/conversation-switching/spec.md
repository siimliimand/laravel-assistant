## MODIFIED Requirements

### Requirement: Conversation Context Preservation

The system SHALL maintain full conversation context when switching between conversations owned by the authenticated user, ensuring message history is preserved and accessible.

#### Scenario: Switch to conversation with messages

- **WHEN** an authenticated user switches to an existing conversation they own
- **THEN** all messages SHALL be loaded and displayed in chronological order
- **AND** the conversation title SHALL be displayed in the header
- **AND** the input field SHALL be ready to send new messages to that conversation
- **AND** the hidden conversation_id field SHALL be updated to the selected conversation

#### Scenario: Attempt to switch to another user's conversation

- **WHEN** an authenticated user attempts to switch to a conversation they don't own
- **THEN** the system SHALL return a 404 error
- **AND** the conversation SHALL NOT be loaded
- **AND** the user SHALL remain on their current conversation

### Requirement: URL State Synchronization

The system SHALL synchronize the conversation state with the URL to enable bookmarking, sharing, and browser navigation for authenticated users only.

#### Scenario: Load conversation from URL

- **WHEN** an authenticated user navigates to `/chat/{conversation_id}` directly
- **THEN** the system SHALL verify the conversation belongs to the user
- **AND** if ownership is confirmed, the conversation SHALL be loaded
- **AND** all messages SHALL be displayed
- **AND** the sidebar SHALL highlight the loaded conversation
- **AND** if the conversation does not exist or belongs to another user, the user SHALL be redirected to `/chat` with a 404

#### Scenario: Load conversation from URL without authentication

- **WHEN** an unauthenticated user navigates to `/chat/{conversation_id}`
- **THEN** the system SHALL redirect to the login page
- **AND** the intended URL SHALL be preserved for redirect after login
