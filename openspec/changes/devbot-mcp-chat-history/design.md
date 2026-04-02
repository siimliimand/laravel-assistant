## Context

DevBot is currently a basic conversational agent without tool-calling capabilities. The application has a single-conversation UI that auto-loads the most recent chat, with no ability to create new conversations or browse history. The Laravel Boost MCP server is already configured and available but not integrated into DevBot's tool system.

**Current State:**

- DevBot implements `HasTools` interface but returns empty tool array
- Chat UI shows only one conversation at a time
- No UI controls for creating new chats or switching between conversations
- MCP server tools available: database-query, database-schema, search-docs, tinker, browser-logs, list-routes, get-config, etc.

**Constraints:**

- Must use Laravel AI package's tool system (implements `Tool` interface)
- Must maintain backward compatibility with existing conversations
- UI must remain responsive and mobile-friendly

## Goals / Non-Goals

**Goals:**

- Enable DevBot to call MCP tools for real development tasks
- Provide UI for creating new conversations
- Display conversation history in a sidebar with search/filter
- Allow switching between conversations seamlessly
- Maintain existing conversation data and functionality

**Non-Goals:**

- User authentication (conversations remain anonymous for now)
- Conversation deletion or archival
- Real-time streaming responses
- MCP tool response caching
- Mobile hamburger menu for sidebar (future enhancement)

## Decisions

### 1. MCP Tool Integration Approach

**Decision:** Create individual tool classes that wrap MCP server calls using Laravel AI's `Tool` interface, rather than a monolithic MCP proxy.

**Rationale:**

- Each tool can have proper type hints, validation, and documentation
- Better error handling per tool
- Easier to test individual tools
- Follows Laravel AI package conventions

**Alternatives Considered:**

- Single MCP proxy tool: Would be simpler but harder to validate, test, and document individual tools
- Direct HTTP calls in agent: Would bypass Laravel AI tool system, losing step tracking and validation

**Implementation:**

- Create `app/Ai/Tools/` directory for tool classes
- Each tool implements `Laravel\Ai\Contracts\Tool`
- Tools use `CallMcpTool` capability via controller or service layer
- DevBot's `tools()` method returns array of tool instances

### 2. Conversation History UI Pattern

**Decision:** Use a collapsible left sidebar (300px width) showing conversation list, with the main chat area taking remaining space.

**Rationale:**

- Industry standard pattern (ChatGPT, Claude, etc.)
- Doesn't disrupt existing chat layout
- Can be hidden on mobile via CSS (future enhancement)
- Allows quick conversation switching without page reload

**Alternatives Considered:**

- Dropdown menu: Would hide conversation titles, hard to scan
- Separate history page: Breaks conversation flow, poor UX
- Modal overlay: Blocks chat context, awkward for frequent switching

### 3. New Chat Creation

**Decision:** Provide a prominent "New Chat" button at the top of the sidebar, which creates a fresh conversation via AJAX and redirects to it.

**Rationale:**

- Clear, discoverable action
- Doesn't require page reload
- Creates conversation before first message (allows immediate switching)

**Alternatives Considered:**

- Create on first message (current behavior): No explicit "new chat" action, confusing
- Clear current conversation: Loses conversation data, poor UX

### 4. Conversation Loading Strategy

**Decision:** Use Turbo/HTMX-style AJAX navigation for conversation switching - update URL and chat content without full page reload.

**Rationale:**

- Faster than full page reload
- Maintains browser history (back/forward work)
- Can be implemented with minimal JavaScript using fetch + history API

**Alternatives Considered:**

- Full page reload: Simpler but slower, jarring UX
- SPA with JavaScript router: Overkill for this use case, more complex

## Risks / Trade-offs

### [Risk] MCP Tool Security

**Trade-off:** Exposing database query and tinker tools could allow unintended data access or modifications.

**Mitigation:**

- Database tools are read-only by design
- Tinker runs in sandboxed context
- Future: Add user authentication and permission checks
- Log all tool usage for auditing

### [Risk] Tool Response Size

**Trade-off:** Some MCP tools (e.g., database queries with many rows) may return large responses that exceed AI context window.

**Mitigation:**

- Database query tool has built-in row limits
- Tools can paginate results
- DevBot's max steps (10) limits total tool usage per conversation turn

### [Risk] Sidebar Performance with Many Conversations

**Trade-off:** Loading all conversations could be slow if user has hundreds.

**Mitigation:**

- Limit initial load to 50 most recent conversations
- Add pagination or "load more" for older conversations
- Index on `created_at` ensures fast sorting

### [Risk] JavaScript Complexity

**Trade-off:** Adding AJAX conversation switching increases frontend complexity.

**Mitigation:**

- Use simple fetch API, no heavy frameworks
- Graceful degradation: full page reload if JS disabled
- Keep JavaScript modular and well-documented

## Migration Plan

**Deployment Steps:**

1. Create MCP tool classes (no behavior change until added to DevBot)
2. Add new routes and controller methods for conversation management
3. Update chat view with sidebar UI (backward compatible with existing conversations)
4. Add DevBot tools integration
5. Deploy and test with existing data

**Rollback Strategy:**

- All changes are additive - no destructive migrations
- Can revert by removing tool classes and sidebar UI
- Existing conversations remain intact
- Feature flag not needed (low risk, additive changes)

**Data Migration:**

- No data migration required
- Existing conversations work without modification
- New columns not needed

## Open Questions

1. **Should we add conversation deletion?** - Out of scope for this change, but should be considered for future enhancement
2. **Should we add user authentication?** - Currently all conversations are anonymous; adding auth would require significant changes
3. **Should we implement conversation search?** - Basic title filtering is included, but full-text message search is deferred
4. **Mobile sidebar behavior?** - Will use hidden sidebar on mobile for now; hamburger menu can be added later
