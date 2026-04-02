## ADDED Requirements

### Requirement: Conversation Context Preservation

The system SHALL maintain full conversation context when switching between conversations, ensuring message history is preserved and accessible.

#### Scenario: Switch to conversation with messages

- **WHEN** a user switches to an existing conversation with message history
- **THEN** all messages SHALL be loaded and displayed in chronological order
- **AND** the conversation title SHALL be displayed in the header
- **AND** the input field SHALL be ready to send new messages to that conversation
- **AND** the hidden conversation_id field SHALL be updated to the selected conversation

#### Scenario: Continue conversation after switching

- **WHEN** a user sends a message after switching to a different conversation
- **THEN** the message SHALL be saved to the selected conversation
- **AND** DevBot's response SHALL include the full conversation context
- **AND** the message history SHALL reflect the continued conversation

### Requirement: Conversation State Management

The system SHALL track and manage the currently active conversation state across UI interactions.

#### Scenario: Active conversation tracking

- **WHEN** a conversation is selected or created
- **THEN** the system SHALL mark it as the active conversation
- **AND** all new messages SHALL be associated with the active conversation
- **AND** the sidebar SHALL visually highlight the active conversation
- **AND** the URL SHALL reflect the active conversation ID

#### Scenario: No active conversation

- **WHEN** no conversation is selected (e.g., after creating a new chat)
- **THEN** the system SHALL display a welcome message
- **AND** sending the first message SHALL create the conversation
- **AND** the conversation SHALL become the active conversation

### Requirement: Conversation Loading Indicator

The system SHALL display a loading indicator when switching between conversations to provide feedback during data fetching.

#### Scenario: Show loading during conversation switch

- **WHEN** a user clicks on a conversation in the sidebar
- **THEN** a loading indicator SHALL be displayed in the message area
- **AND** the previous messages SHALL remain visible until new messages load
- **AND** the loading indicator SHALL disappear once messages are loaded
- **AND** the message area SHALL scroll to the bottom

#### Scenario: Loading error handling

- **WHEN** conversation loading fails (e.g., network error)
- **THEN** an error message SHALL be displayed
- **AND** the previous conversation SHALL remain active
- **AND** the user SHALL be able to retry the switch

### Requirement: URL State Synchronization

The system SHALL synchronize the conversation state with the URL to enable bookmarking, sharing, and browser navigation.

#### Scenario: URL updates on conversation switch

- **WHEN** a user switches to a different conversation
- **THEN** the URL SHALL update to `/chat/{conversation_id}`
- **AND** the update SHALL use the History API (no full page reload)
- **AND** the URL SHALL be shareable and bookmarkable

#### Scenario: Load conversation from URL

- **WHEN** a user navigates to `/chat/{conversation_id}` directly
- **THEN** the system SHALL load the specified conversation
- **AND** all messages SHALL be displayed
- **AND** the sidebar SHALL highlight the loaded conversation
- **AND** if the conversation does not exist, the user SHALL be redirected to `/chat`

### Requirement: Message Context Window Management

The system SHALL manage the conversation context window sent to DevBot to ensure optimal AI responses while respecting token limits.

#### Scenario: Load recent messages for AI context

- **WHEN** DevBot processes a message in a conversation
- **THEN** it SHALL receive the most recent 50 messages as context
- **AND** messages SHALL be in chronological order (oldest to newest)
- **AND** the messages SHALL be formatted as Laravel\Ai\Messages\Message objects
- **AND** the current user message SHALL be included

#### Scenario: Long conversation handling

- **WHEN** a conversation has more than 50 messages
- **THEN** only the most recent 50 messages SHALL be sent to DevBot
- **AND** all messages SHALL remain visible in the UI
- **AND** the user SHALL be able to scroll through the full history
- **AND** DevBot SHALL acknowledge that older context may not be available
