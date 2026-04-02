## Context

This is a fresh Laravel 13 application with the Laravel AI SDK (laravel/ai v0) already installed and configured. The default AI provider is Anthropic (Claude), with API key configuration in place. The application has a standard Laravel structure with Vite 8 for asset compilation and Tailwind CSS 4 for styling. Currently, there are no chat interfaces or AI agents implemented.

## Goals / Non-Goals

**Goals:**

- Create a development-focused AI agent (DevBot) that can answer programming questions
- Build a clean, responsive chat interface using Blade templates and Tailwind CSS
- Persist conversation history in the database for context continuity
- Enable real-time message sending and receiving via HTTP requests
- Follow Laravel best practices for code organization and architecture

**Non-Goals:**

- Real-time WebSocket broadcasting (will use synchronous HTTP requests initially)
- User authentication (assumes single-user or will be added later)
- Multi-agent support or agent switching
- File uploads or image sharing in chat
- Mobile app or API-only interface

## Decisions

### 1. Synchronous HTTP vs WebSockets

**Decision**: Use synchronous HTTP requests with polling instead of WebSockets
**Rationale**: Simpler implementation, adequate for initial version, no need for real-time infrastructure. Can upgrade to Laravel Reverb/WebSockets later if needed.
**Alternatives considered**:

- Laravel Reverb with broadcasting (more complex, requires additional setup)
- Server-sent events (SSE) (better than polling but more complex than sync)

### 2. Conversation Storage Strategy

**Decision**: Store conversations and messages in relational database tables
**Rationale**: Laravel's Eloquent ORM makes this straightforward, enables easy querying and filtering, and aligns with existing migration at `2026_04_02_115916_create_agent_conversations_table.php`
**Alternatives considered**:

- Session-based storage (loses history on session expiry)
- Redis/chat-specific store (overkill for this use case)

### 3. Agent Implementation Approach

**Decision**: Create a dedicated DevBot agent class implementing the Agent contract
**Rationale**: Follows Laravel AI SDK best practices, enables dependency injection, configuration via attributes, and future tool integration
**Alternatives considered**:

- Anonymous agent function (less maintainable, no reusability)
- Service class wrapping AI calls (duplicates Laravel AI SDK functionality)

### 4. UI Framework Choice

**Decision**: Use Blade templates with Tailwind CSS and vanilla JavaScript
**Rationale**: Matches existing tech stack, no additional dependencies, lightweight. Laravel Boost guidelines confirm Tailwind CSS v4 is already configured.
**Alternatives considered**:

- Livewire (adds complexity, not currently in stack)
- Vue/React SPA (overkill, requires significant frontend setup)
- Inertia.js (unnecessary for simple chat interface)

### 5. Message Delivery Pattern

**Decision**: Traditional form submission with page reload initially, then enhance with AJAX
**Rationale**: Works without JavaScript, progressive enhancement approach, simpler debugging
**Alternatives considered**:

- HTMX (additional dependency)
- Full SPA approach (too complex for initial version)

## Risks / Trade-offs

### [Risk] API Rate Limiting

**Impact**: Anthropic API has rate limits that could affect user experience
**Mitigation**: Implement request queuing, add loading states, consider caching common responses

### [Risk] Long Conversation Context

**Impact**: Sending full conversation history with each request increases token usage and cost
**Mitigation**: Limit messages sent to agent (last 20-30 messages), implement conversation summarization later

### [Risk] No Real-Time Updates

**Impact**: User must wait for full page reload or manually refresh
**Mitigation**: Add AJAX submission with JavaScript enhancement, show loading indicators

### [Risk] Token Cost Accumulation

**Impact**: Extended conversations with Claude can become expensive
**Mitigation**: Use cheaper model variants (Claude Haiku) for simple queries, implement message limits

### [Trade-off] Synchronous Blocking

**Impact**: User waits for AI response (can take 5-30 seconds)
**Mitigation**: Show clear loading states, consider background job processing for future versions

## Migration Plan

1. **Database**: Run migration to create conversations and messages tables
2. **Agent**: Create DevBot agent class with development-focused instructions
3. **Routes**: Add chat routes to web.php
4. **Controller**: Implement ChatController with message handling
5. **Views**: Create chat interface Blade template
6. **Assets**: Compile Tailwind CSS styles with Vite
7. **Testing**: Verify end-to-end chat functionality

**Rollback Strategy**: Drop new tables, remove routes/controller/views, no data migration needed as tables are new

## Open Questions

1. Should conversations be tied to authenticated users or allow anonymous usage?
2. What's the maximum conversation length before starting a new conversation?
3. Should we implement conversation search functionality?
4. Do we need to support markdown/code syntax highlighting in messages?
