## 1. MCP Tool Classes

- [x] 1.1 Create `app/Ai/Tools/` directory structure
- [x] 1.2 Create `DatabaseQueryTool` class implementing `Laravel\Ai\Contracts\Tool` that wraps the Laravel Boost `database-query` MCP tool
- [x] 1.3 Create `DatabaseSchemaTool` class implementing `Laravel\Ai\Contracts\Tool` that wraps the Laravel Boost `database-schema` MCP tool
- [x] 1.4 Create `SearchDocsTool` class implementing `Laravel\Ai\Contracts\Tool` that searches Laravel documentation via HTTP API
- [x] 1.5 Create `TinkerTool` class implementing `Laravel\Ai\Contracts\Tool` that wraps the Laravel Boost `tinker` MCP tool
- [x] 1.6 Write unit tests for each tool class to verify proper interface implementation and parameter validation

## 2. DevBot Agent Integration

- [x] 2.1 Update `DevBot::tools()` method to return instances of all MCP tool classes
- [x] 2.2 Test DevBot agent can access and call tools during conversation
- [x] 2.3 Verify tool calls are tracked in conversation message history
- [x] 2.4 Update `openspec/specs/devbot-agent/spec.md` with MODIFIED requirements for tool availability

## 3. Backend Routes and Controllers

- [ ] 3.1 Add `GET /chat/conversations` route to return JSON list of conversations (limited to 50, sorted by created_at desc)
- [ ] 3.2 Add `POST /chat/new` route to create a new empty conversation and return JSON response
- [ ] 3.3 Add `GET /chat/{conversation}` route to return conversation details and messages as JSON (for AJAX loading)
- [ ] 3.4 Update `ChatController::show()` to pass all conversations to the view for sidebar rendering
- [ ] 3.5 Update `ChatController::sendMessage()` to return updated conversation title in JSON response
- [ ] 3.6 Write feature tests for all new endpoints

## 4. Sidebar UI Implementation

- [ ] 4.1 Update `chat.blade.php` layout to include a left sidebar (300px width) alongside the main chat area
- [ ] 4.2 Add "New Chat" button at the top of the sidebar with plus icon
- [ ] 4.3 Render conversation list in sidebar showing title and relative timestamp (e.g., "2 hours ago")
- [ ] 4.4 Highlight the currently active conversation in the sidebar with distinct background
- [ ] 4.5 Add search/filter input at the top of the sidebar to filter conversations by title
- [ ] 4.6 Style sidebar with distinct background color, hover effects, and scrollable area
- [ ] 4.7 Implement responsive behavior: hide sidebar on mobile, show hamburger menu toggle

## 5. Conversation Switching (AJAX)

- [ ] 5.1 Add JavaScript event listener for conversation clicks in sidebar
- [ ] 5.2 Implement AJAX fetch to load conversation messages when clicked
- [ ] 5.3 Update message area DOM with loaded messages without page reload
- [ ] 5.4 Update URL using History API (`history.pushState`) when switching conversations
- [ ] 5.5 Handle browser back/forward navigation with `popstate` event listener
- [ ] 5.6 Add loading indicator during conversation switch
- [ ] 5.7 Update hidden `conversation_id` field in chat form when switching
- [ ] 5.8 Update sidebar active state to reflect selected conversation

## 6. New Chat Creation

- [ ] 6.1 Add JavaScript event listener for "New Chat" button click
- [ ] 6.2 Implement AJAX POST to `/chat/new` to create new conversation
- [ ] 6.3 Clear message area and display welcome message after new chat creation
- [ ] 6.4 Update URL to `/chat/{new_conversation_id}` using History API
- [ ] 6.5 Reset hidden `conversation_id` field in chat form
- [ ] 6.6 Highlight new conversation in sidebar as active

## 7. Integration and Testing

- [ ] 7.1 Test end-to-end flow: create new chat → send message → switch to different chat → send another message
- [ ] 7.2 Test conversation filtering by title in sidebar
- [ ] 7.3 Test responsive layout on desktop, tablet, and mobile viewports
- [ ] 7.4 Test browser back/forward navigation between conversations
- [ ] 7.5 Test DevBot tool calling with database queries and documentation search
- [ ] 7.6 Run full test suite: `php artisan test --compact`
- [ ] 7.7 Run Pint formatter: `vendor/bin/pint --dirty --format agent`
- [ ] 7.8 Verify all specs are satisfied by running manual testing against each scenario
