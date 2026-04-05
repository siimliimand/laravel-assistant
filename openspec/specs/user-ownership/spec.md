## Requirements

### Requirement: Conversation User Ownership

Every conversation SHALL be owned by exactly one authenticated user.

#### Scenario: Create conversation with user association

- **WHEN** an authenticated user creates a new conversation
- **THEN** the conversation's `user_id` SHALL be set to the authenticated user's ID
- **AND** the conversation SHALL be saved to the database with the user relationship
- **AND** the user SHALL be able to retrieve the conversation via `user()->conversations()`

#### Scenario: Conversation requires valid user_id

- **WHEN** a conversation is created without an authenticated user
- **THEN** the system SHALL reject the creation attempt
- **AND** the system SHALL redirect to the login page
- **AND** no orphaned conversation SHALL be created

### Requirement: User-Model Relationships

The User model SHALL have one-to-many relationships with both conversations and projects.

#### Scenario: Access user's conversations

- **WHEN** calling `$user->conversations()` on a User model
- **THEN** the system SHALL return all conversations owned by that user
- **AND** the conversations SHALL be queryable with Eloquent relationship methods
- **AND** the relationship SHALL support eager loading

#### Scenario: Access user's projects

- **WHEN** calling `$user->projects()` on a User model
- **THEN** the system SHALL return all projects owned by that user
- **AND** the projects SHALL be queryable with Eloquent relationship methods
- **AND** the relationship SHALL support eager loading

#### Scenario: Access conversation's owner

- **WHEN** calling `$conversation->user()` on a Conversation model
- **THEN** the system SHALL return the User who owns the conversation
- **AND** the relationship SHALL be accessible as `$conversation->user`

### Requirement: User-Scoped Conversation Queries

All conversation queries SHALL be scoped to the authenticated user.

#### Scenario: List only user's conversations

- **WHEN** an authenticated user requests their conversation list
- **THEN** the system SHALL return only conversations where `user_id` equals the authenticated user's ID
- **AND** the system SHALL NOT return conversations belonging to other users
- **AND** the query SHALL use `where('user_id', auth()->id())`

#### Scenario: Retrieve specific conversation with ownership check

- **WHEN** an authenticated user requests a specific conversation by ID
- **THEN** the system SHALL verify the conversation belongs to the user
- **AND** if the conversation belongs to the user, it SHALL be returned
- **AND** if the conversation belongs to another user, the system SHALL return a 404 error

#### Scenario: Send message to user's conversation

- **WHEN** an authenticated user sends a message to a conversation
- **THEN** the system SHALL verify the conversation belongs to the user
- **AND** if ownership is confirmed, the message SHALL be saved
- **AND** if the conversation belongs to another user, the system SHALL return a 403 or 404 error

### Requirement: User Deletion Cascading

When a user is deleted, all their conversations and projects SHALL be deleted.

#### Scenario: Delete user with conversations and projects

- **WHEN** a user account is deleted from the system
- **THEN** all conversations owned by that user SHALL be deleted
- **AND** all messages in those conversations SHALL be deleted
- **AND** all projects owned by that user SHALL be deleted
- **AND** no orphaned conversation, message, or project records SHALL remain

### Requirement: User Isolation Enforcement

Users SHALL NOT be able to access, modify, or view other users' conversations under any circumstances.

#### Scenario: Attempt to access another user's conversation via URL

- **WHEN** an authenticated user navigates to `/chat/{id}` where the conversation belongs to another user
- **THEN** the system SHALL return a 404 Not Found response
- **AND** the user SHALL NOT see the conversation content
- **AND** the system SHALL NOT leak information about the conversation's existence

#### Scenario: Attempt to send message to another user's conversation

- **WHEN** an authenticated user attempts to POST a message to a conversation they don't own
- **THEN** the system SHALL reject the request
- **AND** the system SHALL return a 403 Forbidden or 404 Not Found response
- **AND** no message SHALL be saved to the conversation

#### Scenario: Attempt to list another user's conversations via API

- **WHEN** an authenticated user makes a request to `/chat/conversations`
- **THEN** the system SHALL return only the requesting user's conversations
- **AND** the response SHALL NOT include conversations from other users
- **AND** the query SHALL be automatically scoped by the authenticated user's ID
