## Why

The application currently has no authentication system, meaning all users share the same conversation history and there's no user isolation. Adding email/password authentication will enable multi-user support, protect user data, and provide a foundation for user-specific features like personalized conversation history and preferences.

## What Changes

- Install Laravel Breeze with Blade stack for authentication scaffolding
- Add login, registration, password reset, and email verification flows
- Protect all chat-related routes with authentication middleware
- Add `user_id` foreign key to conversations table for user ownership
- Scope all conversation queries to authenticated user
- Update welcome page with authentication navigation links
- Add user dashboard or redirect authenticated users to chat
- Update tests to use authenticated user context

## Capabilities

### New Capabilities

- `user-authentication`: Email/password authentication with login, registration, password reset, and session management using Laravel Breeze
- `user-ownership`: User-based ownership and isolation of conversations, ensuring users can only access their own data

### Modified Capabilities

- `chat-creation`: Conversations must now be created by authenticated users and associated with a user_id
- `conversation-history`: Conversation listing and retrieval must be scoped to the authenticated user
- `conversation-switching`: Users can only switch between their own conversations
- `chat-interface`: Chat interface requires authentication; unauthenticated users are redirected to login

## Impact

**Affected Code:**

- Routes: All chat routes wrapped in `auth` middleware
- Models: Conversation and User models gain new relationships
- Actions: All conversation-scoped actions filter by authenticated user
- Database: New migration adds user_id to conversations table
- Tests: All feature tests require authenticated user context
- Views: Welcome page and chat views updated with auth navigation

**Dependencies:**

- laravel/breeze package added
- Alpine.js added for frontend interactivity (via Breeze)

**Breaking Changes:**

- **BREAKING**: All chat endpoints now require authentication. Existing integrations or tests must provide authenticated user context.
