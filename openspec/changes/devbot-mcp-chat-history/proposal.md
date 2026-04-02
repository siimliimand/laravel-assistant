## Why

DevBot currently lacks two critical features that limit its usability: (1) it cannot leverage MCP tools to perform real development tasks like reading files, running commands, or querying databases, and (2) users cannot create new conversations or access their chat history, forcing them into a single conversation flow. These limitations prevent DevBot from being a practical development assistant.

## What Changes

- **Add MCP tool integration to DevBot**: Integrate Laravel Boost MCP tools (database-query, database-schema, search-docs, tinker, etc.) into DevBot's tool system, enabling it to perform real development tasks
- **Enable new chat creation**: Add UI and backend support for creating new conversations from the chat interface
- **Display conversation history**: Add a sidebar showing all previous conversations with the ability to switch between them
- **Improve conversation management**: Allow users to view, select, and resume any previous conversation

## Capabilities

### New Capabilities

- `mcp-tool-integration`: DevBot can access and use MCP tools from the Laravel Boost server to query databases, read documentation, run tinker commands, and inspect application state
- `chat-creation`: Users can create new conversations from the chat interface via a dedicated UI control
- `conversation-history`: Users can view a list of all previous conversations, search/filter them, and switch between conversations
- `conversation-switching`: Users can resume any previous conversation and continue the chat context

### Modified Capabilities
<!-- No existing capabilities are being modified at the requirement level -->

## Impact

**Affected Code:**

- `app/Ai/Agents/DevBot.php` - Add MCP tool implementations
- `app/Http/Controllers/ChatController.php` - Add endpoints for listing conversations, creating new chats
- `resources/views/chat.blade.php` - Add sidebar UI for conversation history, new chat button
- `routes/web.php` - Add new routes for conversation management

**Dependencies:**

- Laravel Boost MCP server integration
- Laravel AI tool system

**Systems:**

- Chat UI/UX will shift from single-conversation to multi-conversation model
- DevBot agent will gain tool-calling capabilities
