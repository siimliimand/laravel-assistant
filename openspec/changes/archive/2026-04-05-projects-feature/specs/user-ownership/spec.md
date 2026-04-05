## MODIFIED Requirements

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

### Requirement: User Deletion Cascading

When a user is deleted, all their conversations and projects SHALL be deleted.

#### Scenario: Delete user with conversations and projects

- **WHEN** a user account is deleted from the system
- **THEN** all conversations owned by that user SHALL be deleted
- **AND** all messages in those conversations SHALL be deleted
- **AND** all projects owned by that user SHALL be deleted
- **AND** no orphaned conversation, message, or project records SHALL remain
