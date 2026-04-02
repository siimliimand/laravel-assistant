## ADDED Requirements

### Requirement: Conversations Table

The system SHALL provide a database table to store chat conversations with metadata and timestamps.

#### Scenario: Migration creates conversations table

- **WHEN** database migrations are executed
- **THEN** a `conversations` table SHALL be created
- **AND** it SHALL have columns: id (primary key), user_id (nullable foreign key), title (string), created_at, updated_at
- **AND** user_id SHALL be nullable to support anonymous conversations
- **AND** an index SHALL exist on created_at for sorting

#### Scenario: Store new conversation

- **WHEN** a new conversation is initiated
- **THEN** a record SHALL be inserted into the conversations table
- **AND** the title SHALL be auto-generated from the first message
- **AND** timestamps SHALL be set automatically by Eloquent

### Requirement: Messages Table

The system SHALL provide a database table to store individual messages within conversations with role identification and content.

#### Scenario: Migration creates messages table

- **WHEN** database migrations are executed
- **THEN** a `messages` table SHALL be created
- **AND** it SHALL have columns: id (primary key), conversation_id (foreign key), role (enum: user/assistant), content (text), created_at, updated_at
- **AND** conversation_id SHALL have a foreign key constraint to conversations table with cascade on delete
- **AND** indexes SHALL exist on conversation_id and created_at

#### Scenario: Store user message

- **WHEN** a user sends a message
- **THEN** a record SHALL be inserted with role='user'
- **AND** the content SHALL be the exact text entered by the user
- **AND** it SHALL be linked to the current conversation

#### Scenario: Store assistant message

- **WHEN** DevBot generates a response
- **THEN** a record SHALL be inserted with role='assistant'
- **AND** the content SHALL be the AI-generated response text
- **AND** it SHALL be linked to the same conversation as the user message

### Requirement: Conversation Model

The system SHALL provide an Eloquent model for conversations with relationships to messages and mass assignment protection.

#### Scenario: Define Conversation model

- **WHEN** the Conversation model is used
- **THEN** it SHALL have a hasMany relationship to Message model
- **AND** it SHALL define fillable fields: user_id, title
- **AND** it SHALL have a method to generate title from first message
- **AND** it SHALL order messages by created_at ascending by default

#### Scenario: Create conversation with first message

- **WHEN** a new conversation is created
- **THEN** the title SHALL be automatically generated from the first 50 characters of the first message
- **AND** the relationship to messages SHALL be properly established

#### Scenario: Retrieve messages for conversation

- **WHEN** messages are accessed via conversation relationship
- **THEN** they SHALL be returned in chronological order (oldest first)
- **AND** only the most recent 50 messages SHALL be loaded to manage context window
- **AND** eager loading SHALL be used to prevent N+1 queries

### Requirement: Message Model

The system SHALL provide an Eloquent model for messages with role validation and relationship to conversation.

#### Scenario: Define Message model

- **WHEN** the Message model is used
- **THEN** it SHALL have a belongsTo relationship to Conversation model
- **AND** it SHALL define fillable fields: conversation_id, role, content
- **AND** it SHALL validate that role is either 'user' or 'assistant'
- **AND** it SHALL cast role to a string type

#### Scenario: Create message with validation

- **WHEN** a message is created
- **THEN** the role SHALL be validated to ensure it's 'user' or 'assistant'
- **AND** the content SHALL not be empty
- **AND** the conversation_id SHALL reference an existing conversation

#### Scenario: Format message for display

- **WHEN** a message is prepared for display
- **THEN** it SHALL provide a method to determine if it's a user message
- **AND** it SHALL provide formatted timestamp for display
- **AND** it SHALL support markdown content rendering
