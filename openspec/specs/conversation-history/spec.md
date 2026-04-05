## Requirements

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

#### Scenario: Empty conversation list

- **WHEN** no conversations exist in the database
- **THEN** the endpoint SHALL return an empty JSON array
- **AND** the response SHALL have HTTP status 200

### Requirement: Conversation Sidebar UI

The system SHALL display a left sidebar showing all conversations with their titles and timestamps.

#### Scenario: Sidebar displays conversations

- **WHEN** the chat interface is loaded
- **THEN** a left sidebar SHALL be visible with 300px width
- **AND** it SHALL list all conversations sorted by most recent first
- **AND** each conversation SHALL show its title and relative timestamp (e.g., "2 hours ago")
- **AND** the currently active conversation SHALL be visually highlighted

#### Scenario: Sidebar styling

- **WHEN** the sidebar is displayed
- **THEN** it SHALL have a distinct background color from the main chat area
- **AND** conversation items SHALL have hover effects
- **AND** the active conversation SHALL have a highlighted background
- **AND** the sidebar SHALL be scrollable if there are many conversations

### Requirement: Conversation Switching via AJAX

The system SHALL allow users to switch between conversations without a full page reload using AJAX navigation.

#### Scenario: Switch to different conversation

- **WHEN** a user clicks on a conversation in the sidebar
- **THEN** the system SHALL fetch the conversation data via AJAX
- **AND** it SHALL update the message area with the selected conversation's messages
- **AND** it SHALL update the URL to reflect the new conversation
- **AND** it SHALL update the browser history (back/forward navigation works)
- **AND** the sidebar SHALL highlight the newly selected conversation

#### Scenario: Browser back navigation

- **WHEN** a user clicks the browser back button after switching conversations
- **THEN** the system SHALL navigate to the previous conversation
- **AND** the message history SHALL update accordingly
- **AND** the sidebar SHALL highlight the correct conversation

### Requirement: Conversation Search/Filter

The system SHALL provide a search input in the sidebar to filter conversations by title.

#### Scenario: Filter conversations by title

- **WHEN** a user types in the search input
- **THEN** the conversation list SHALL filter in real-time to show only matching conversations
- **AND** the filter SHALL be case-insensitive
- **AND** it SHALL match partial titles
- **AND** the filter SHALL reset when the search input is cleared

#### Scenario: No matching conversations

- **WHEN** the search filter matches no conversations
- **THEN** the sidebar SHALL display a "No conversations found" message
- **AND** the "New Chat" button SHALL remain visible and functional

### Requirement: Sidebar Responsive Behavior

The system SHALL adapt the sidebar for different screen sizes to maintain usability.

#### Scenario: Desktop sidebar display

- **WHEN** viewed on desktop (width >= 1024px)
- **THEN** the sidebar SHALL be fully visible
- **AND** it SHALL have a fixed width of 300px
- **AND** the main chat area SHALL take the remaining space

#### Scenario: Tablet sidebar display

- **WHEN** viewed on tablet (width >= 768px and < 1024px)
- **THEN** the sidebar SHALL be visible but may be narrower (e.g., 250px)
- **AND** conversation titles SHALL truncate with ellipsis if too long

#### Scenario: Mobile sidebar behavior

- **WHEN** viewed on mobile (width < 768px)
- **THEN** the sidebar SHALL be hidden by default
- **AND** a hamburger menu icon SHALL be visible in the header
- **AND** clicking the hamburger menu SHALL toggle the sidebar as an overlay
- **AND** the overlay SHALL cover the chat area when open
