## Requirements

### Requirement: New Chat Creation Endpoint

The system SHALL provide an HTTP endpoint that creates a new empty conversation and returns its details.

#### Scenario: Create new conversation

- **WHEN** a POST request is made to `/chat/new`
- **THEN** the system SHALL create a new conversation with a default title
- **AND** it SHALL return the conversation ID and title as JSON
- **AND** the response SHALL have HTTP status 201
- **AND** the conversation SHALL have zero messages initially

#### Scenario: New conversation defaults

- **WHEN** a new conversation is created
- **THEN** the title SHALL be "New Chat" by default
- **AND** the title SHALL be updated when the first message is sent
- **AND** the created_at timestamp SHALL be set automatically

### Requirement: New Chat UI Control

The system SHALL provide a visible "New Chat" button in the chat interface that creates a new conversation without requiring a page reload.

#### Scenario: Click new chat button

- **WHEN** a user clicks the "New Chat" button
- **THEN** the system SHALL create a new conversation via AJAX
- **AND** it SHALL navigate to the new conversation
- **AND** the message history SHALL be empty
- **AND** the welcome message SHALL be displayed
- **AND** the URL SHALL update to reflect the new conversation

#### Scenario: New chat button visibility

- **WHEN** the chat interface is displayed
- **THEN** the "New Chat" button SHALL be visible at the top of the sidebar
- **AND** it SHALL have a distinctive icon (e.g., plus sign or pencil)
- **AND** it SHALL be styled as a prominent action button

### Requirement: New Chat from Empty State

The system SHALL allow users to start a new conversation when no previous conversations exist.

#### Scenario: First-time user creates chat

- **WHEN** a user visits `/chat` with no existing conversations
- **THEN** the system SHALL display the welcome message
- **AND** the user SHALL be able to send a message to create the first conversation
- **AND** the "New Chat" button SHALL still be available in the sidebar

### Requirement: Conversation Title Generation

The system SHALL automatically generate a conversation title from the first message when a new chat is started.

#### Scenario: Title generated from first message

- **WHEN** the first message is sent to a new conversation
- **THEN** the system SHALL set the conversation title to the first 50 characters of the message
- **AND** the title SHALL be saved to the database
- **AND** the updated title SHALL be reflected in the sidebar

#### Scenario: Title preserves existing value

- **WHEN** a message is sent to an existing conversation with a custom title
- **THEN** the title SHALL NOT be changed
- **AND** the conversation SHALL continue with its current title
