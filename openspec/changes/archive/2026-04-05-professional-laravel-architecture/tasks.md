## 1. Foundation Setup

- [x] 1.1 Create directory structure: `app/Actions/`, `app/DTOs/`, `app/Enums/`, `app/ViewModels/`, `app/Casts/`
- [x] 1.2 Create base Action class with common patterns (execute method signature, error handling)
- [x] 1.3 Update `composer.json` autoload to include new directories (if needed)
- [x] 1.4 Verify PHP 8.3+ is configured in project

## 2. Enums Implementation

- [x] 2.1 Create `MessageRole` enum with `User` and `Assistant` cases, backed by strings
- [x] 2.2 Add metadata methods to `MessageRole`: `label()`, `color()`, `icon()`
- [x] 2.3 Update `Message` model to cast `role` field to `MessageRole` enum
- [x] 2.4 Create `ConversationStatus` enum (Active, Archived, Deleted) with metadata methods
- [x] 2.5 Update `Conversation` model to cast `status` field to `ConversationStatus` enum
- [x] 2.6 Update validation rules to use `Rule::enum()` for role and status fields
- [x] 2.7 Update existing code that uses magic strings to use enum cases
- [x] 2.8 Write unit tests for enum metadata methods and casting

## 3. DTOs Implementation

- [x] 3.1 Create `MessageData` DTO with readonly properties: `content`, `conversationId`
- [x] 3.2 Add `MessageData::fromRequest()` factory method
- [x] 3.3 Create `ConversationData` DTO with readonly properties: `title`, `initialMessage`
- [x] 3.4 Add `ConversationData::fromRequest()` factory method
- [x] 3.5 Create `ApiResponseData` DTO for standardized API responses
- [x] 3.6 Write unit tests for DTO instantiation and immutability

## 4. Actions Implementation - Chat

- [x] 4.1 Create `CreateConversationAction` with constructor injection
- [x] 4.2 Implement `CreateConversationAction::execute(ConversationData $data): Conversation`
- [x] 4.3 Create `SendMessageAction` with DevBot and Message dependencies
- [x] 4.4 Implement `SendMessageAction::execute(MessageData $data, User $user): Message`
- [x] 4.5 Add error handling in `SendMessageAction` for AI API failures
- [x] 4.6 Create `GetConversationAction` for retrieving conversation with messages
- [x] 4.7 Create `ListConversationsAction` for sidebar conversation list
- [x] 4.8 Write unit tests for all chat Actions with mocked dependencies

## 5. ViewModels Implementation

- [x] 5.1 Create `ChatViewModel` class with constructor accepting conversation, messages, conversations list
- [x] 5.2 Implement `ChatViewModel::getFormattedMessages()` with timestamp and role formatting
- [x] 5.3 Implement `ChatViewModel::getSidebarConversations()` with metadata
- [x] 5.4 Implement `ChatViewModel::getCurrentConversation()` with computed properties
- [x] 5.5 Write unit tests for ViewModel data transformations

## 6. Refactor ChatController

- [x] 6.1 Refactor `show()` method to use `ChatViewModel` (reduce to 1-2 lines)
- [x] 6.2 Refactor `listConversations()` to use `ListConversationsAction`
- [x] 6.3 Refactor `createConversation()` to use DTO and `CreateConversationAction`
- [x] 6.4 Refactor `getConversation()` to use `GetConversationAction`
- [x] 6.5 Refactor `sendMessage()` to use DTO and `SendMessageAction`
- [x] 6.6 Remove business logic from all controller methods
- [x] 6.7 Verify all routes still work with refactored controller
- [x] 6.8 Update or rewrite controller tests to work with new architecture

## 7. Update Existing Code

- [x] 7.1 Search for and replace all magic strings for message roles with `MessageRole` enum
- [x] 7.2 Update Message model's `formattedContent()` method if needed
- [x] 7.3 Update Conversation model's `generateTitleFromFirstMessage()` method to use enum
- [x] 7.4 Review and update any other models with business logic that should be in Actions
- [x] 7.5 Update Blade views to work with enum values and ViewModel data

## 8. Testing & Validation

- [x] 8.1 Run full test suite: `php artisan test`
- [x] 8.2 Fix any failing tests from refactoring
- [x] 8.3 Add integration tests for Action classes
- [x] 8.4 Add feature tests for refactored controller endpoints
- [x] 8.5 Test chat interface manually in browser
- [x] 8.6 Test conversation creation, switching, and message sending
- [x] 8.7 Run Pint formatter: `vendor/bin/pint --format agent`
- [x] 8.8 Verify no N+1 queries in refactored code

## 9. Documentation & Cleanup

- [x] 9.1 Update AGENTS.md with new architecture patterns and examples
- [x] 9.2 Add PHPDoc blocks to all new classes with usage examples
- [x] 9.3 Create example code snippets in comments for Actions, DTOs, ViewModels
- [x] 9.4 Remove any unused code from old implementation
- [x] 9.5 Verify all new code follows Laravel 13 best practices
- [x] 9.6 Run static analysis (if configured): `phpstan analyse` or `larastan`
