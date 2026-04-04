## 1. Foundation Setup

- [ ] 1.1 Create directory structure: `app/Actions/`, `app/DTOs/`, `app/Enums/`, `app/ViewModels/`, `app/Casts/`
- [ ] 1.2 Create base Action class with common patterns (execute method signature, error handling)
- [ ] 1.3 Update `composer.json` autoload to include new directories (if needed)
- [ ] 1.4 Verify PHP 8.3+ is configured in project

## 2. Enums Implementation

- [ ] 2.1 Create `MessageRole` enum with `User` and `Assistant` cases, backed by strings
- [ ] 2.2 Add metadata methods to `MessageRole`: `label()`, `color()`, `icon()`
- [ ] 2.3 Update `Message` model to cast `role` field to `MessageRole` enum
- [ ] 2.4 Create `ConversationStatus` enum (Active, Archived, Deleted) with metadata methods
- [ ] 2.5 Update `Conversation` model to cast `status` field to `ConversationStatus` enum
- [ ] 2.6 Update validation rules to use `Rule::enum()` for role and status fields
- [ ] 2.7 Update existing code that uses magic strings to use enum cases
- [ ] 2.8 Write unit tests for enum metadata methods and casting

## 3. DTOs Implementation

- [ ] 3.1 Create `MessageData` DTO with readonly properties: `content`, `conversationId`
- [ ] 3.2 Add `MessageData::fromRequest()` factory method
- [ ] 3.3 Create `ConversationData` DTO with readonly properties: `title`, `initialMessage`
- [ ] 3.4 Add `ConversationData::fromRequest()` factory method
- [ ] 3.5 Create `ApiResponseData` DTO for standardized API responses
- [ ] 3.6 Write unit tests for DTO instantiation and immutability

## 4. Actions Implementation - Chat

- [ ] 4.1 Create `CreateConversationAction` with constructor injection
- [ ] 4.2 Implement `CreateConversationAction::execute(ConversationData $data): Conversation`
- [ ] 4.3 Create `SendMessageAction` with DevBot and Message dependencies
- [ ] 4.4 Implement `SendMessageAction::execute(MessageData $data, User $user): Message`
- [ ] 4.5 Add error handling in `SendMessageAction` for AI API failures
- [ ] 4.6 Create `GetConversationAction` for retrieving conversation with messages
- [ ] 4.7 Create `ListConversationsAction` for sidebar conversation list
- [ ] 4.8 Write unit tests for all chat Actions with mocked dependencies

## 5. ViewModels Implementation

- [ ] 5.1 Create `ChatViewModel` class with constructor accepting conversation, messages, conversations list
- [ ] 5.2 Implement `ChatViewModel::getFormattedMessages()` with timestamp and role formatting
- [ ] 5.3 Implement `ChatViewModel::getSidebarConversations()` with metadata
- [ ] 5.4 Implement `ChatViewModel::getCurrentConversation()` with computed properties
- [ ] 5.5 Write unit tests for ViewModel data transformations

## 6. Refactor ChatController

- [ ] 6.1 Refactor `show()` method to use `ChatViewModel` (reduce to 1-2 lines)
- [ ] 6.2 Refactor `listConversations()` to use `ListConversationsAction`
- [ ] 6.3 Refactor `createConversation()` to use DTO and `CreateConversationAction`
- [ ] 6.4 Refactor `getConversation()` to use `GetConversationAction`
- [ ] 6.5 Refactor `sendMessage()` to use DTO and `SendMessageAction`
- [ ] 6.6 Remove business logic from all controller methods
- [ ] 6.7 Verify all routes still work with refactored controller
- [ ] 6.8 Update or rewrite controller tests to work with new architecture

## 7. Update Existing Code

- [ ] 7.1 Search for and replace all magic strings for message roles with `MessageRole` enum
- [ ] 7.2 Update Message model's `formattedContent()` method if needed
- [ ] 7.3 Update Conversation model's `generateTitleFromFirstMessage()` method to use enum
- [ ] 7.4 Review and update any other models with business logic that should be in Actions
- [ ] 7.5 Update Blade views to work with enum values and ViewModel data

## 8. Testing & Validation

- [ ] 8.1 Run full test suite: `php artisan test`
- [ ] 8.2 Fix any failing tests from refactoring
- [ ] 8.3 Add integration tests for Action classes
- [ ] 8.4 Add feature tests for refactored controller endpoints
- [ ] 8.5 Test chat interface manually in browser
- [ ] 8.6 Test conversation creation, switching, and message sending
- [ ] 8.7 Run Pint formatter: `vendor/bin/pint --format agent`
- [ ] 8.8 Verify no N+1 queries in refactored code

## 9. Documentation & Cleanup

- [ ] 9.1 Update AGENTS.md with new architecture patterns and examples
- [ ] 9.2 Add PHPDoc blocks to all new classes with usage examples
- [ ] 9.3 Create example code snippets in comments for Actions, DTOs, ViewModels
- [ ] 9.4 Remove any unused code from old implementation
- [ ] 9.5 Verify all new code follows Laravel 13 best practices
- [ ] 9.6 Run static analysis (if configured): `phpstan analyse` or `larastan`
