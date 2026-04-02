## Context

DevBot currently implements 4 native PHP tool classes (DatabaseQueryTool, DatabaseSchemaTool, SearchDocsTool, TinkerTool) that duplicate functionality available in the Laravel Boost MCP server. The Boost MCP server is already configured and running via STDIO transport, providing 12+ tools including database-query, database-schema, search-docs, tinker, browser-logs, list-routes, get-config, and more.

**Current State:**

- Laravel Boost MCP server configured in `.mcp.json` with STDIO transport
- DevBot has 4 manually implemented tool classes
- Tools execute native PHP code instead of calling MCP server
- Maintenance burden to keep tools in sync with Boost updates

**Constraints:**

- Must use php-mcp/client library for MCP protocol communication
- Must maintain Laravel AI `Tool` interface for DevBot compatibility
- STDIO transport requires spawning artisan command as subprocess
- Tool calls must be synchronous (AI conversation flow)

## Goals / Non-Goals

**Goals:**

- Integrate php-mcp/client library to enable MCP protocol communication
- Create McpClientService to manage Boost server connections
- Refactor all DevBot tools to proxy calls to MCP server via MCP client
- Enable DevBot to access all 12+ Boost MCP tools automatically
- Maintain backward compatibility with existing DevBot conversation flow
- Keep tool response format consistent with current behavior

**Non-Goals:**

- Adding new MCP tools beyond what Boost already provides
- Supporting multiple MCP servers (only Boost for now)
- Asynchronous tool execution or streaming responses
- MCP tool caching or response optimization
- User authentication for MCP tool calls

## Decisions

### 1. Use php-mcp/client Library

**Decision:** Install and use the `php-mcp/client` library from <https://github.com/php-mcp/client> for MCP protocol communication.

**Rationale:**

- Official PHP implementation of MCP client
- Supports STDIO transport (required for Boost server)
- Handles JSON-RPC 2.0 protocol details
- Active development and community support

**Alternatives Considered:**

- Build custom MCP client: Would require implementing JSON-RPC 2.0 and STDIO transport from scratch
- HTTP-based MCP: Boost uses STDIO, not HTTP, so this wouldn't work without server changes

### 2. Centralized MCP Client Service

**Decision:** Create a single `McpClientService` that manages the Boost MCP server connection and provides a method to call tools.

**Rationale:**

- Single point of connection management (avoid spawning multiple processes)
- Centralized error handling and logging
- Easy to mock in tests
- Connection pooling potential for future optimization

**Implementation:**

```php
class McpClientService
{
    protected ?McpClient $client = null;
    
    public function initialize(): void
    public function callTool(string $name, array $arguments): string
    public function disconnect(): void
}
```

### 3. STDIO Transport with Process Management

**Decision:** Use STDIO transport by spawning `php artisan boost:mcp` as a subprocess, communicating via stdin/stdout pipes.

**Rationale:**

- Matches how Qoder and other IDEs connect to Boost
- No additional server configuration needed
- Laravel app context already bootstrapped in artisan command

**Risk:** Process lifecycle management can be tricky (zombie processes, crashes)
**Mitigation:**

- Use Symfony Process component with timeout
- Implement graceful shutdown in service provider
- Add health checks and auto-reconnect logic

### 4. Tool Proxy Pattern

**Decision:** Each DevBot tool class becomes a thin proxy that:

1. Receives parameters from Laravel AI system
2. Calls McpClientService with tool name and arguments
3. Returns MCP server response as string

**Rationale:**

- Maintains Laravel AI `Tool` interface compatibility
- Single responsibility: proxy logic only
- Easy to add new tools (just create proxy class)
- Validation and schema still defined in tool class

**Example:**

```php
class DatabaseQueryTool implements Tool
{
    public function handle(Request $request): string
    {
        return app(McpClientService::class)->callTool(
            'database-query',
            ['query' => $request['query']]
        );
    }
}
```

### 5. Dynamic Tool Discovery (Future)

**Decision:** For now, manually create proxy classes for each Boost tool. In future, implement dynamic tool discovery by calling `tools/list` on MCP server.

**Rationale:**

- Simpler initial implementation
- Allows custom validation and documentation per tool
- Dynamic discovery adds complexity (schema mapping, error handling)

## Risks / Trade-offs

### [Risk] STDIO Process Management

**Trade-off:** Spawning subprocess for each tool call is expensive vs maintaining persistent connection.

**Mitigation:**

- Maintain persistent connection in McpClientService (singleton)
- Initialize connection on first tool call, reuse for lifetime of request
- Implement connection pooling for concurrent requests (future)

### [Risk] MCP Server Crashes

**Trade-off:** If Boost MCP server crashes, all tool calls fail.

**Mitigation:**

- Implement auto-reconnect logic with exponential backoff
- Add circuit breaker pattern after repeated failures
- Log errors comprehensively for debugging
- Fallback to error message explaining the issue

### [Risk] Performance Overhead

**Trade-off:** MCP protocol adds serialization/deserialization overhead vs direct PHP calls.

**Mitigation:**

- Benchmark tool call latency (expected <100ms overhead)
- Profile and optimize if overhead exceeds 200ms
- Consider caching for idempotent tool calls (future)

### [Risk] Dependency on Third-Party Library

**Trade-off:** php-mcp/client library may have bugs or become unmaintained.

**Mitigation:**

- Library is official PHP MCP implementation
- Wrap in service layer to ease replacement if needed
- Monitor library updates and community activity

## Migration Plan

**Deployment Steps:**

1. Install php-mcp/client via Composer
2. Create McpClientService class
3. Register service in AppServiceProvider
4. Refactor existing 4 tool classes to use MCP client
5. Test each tool individually with MCP server
6. Update DevBot agent integration tests
7. Deploy and monitor for errors

**Rollback Strategy:**

- Keep native PHP tool implementations in git history
- Can revert by restoring previous tool class implementations
- No database migrations required
- Feature flag not needed (tools either work or don't)

**Testing Strategy:**

- Unit test McpClientService with mocked MCP client
- Integration test each tool class with real Boost server
- End-to-end test DevBot conversation with tool usage
- Load test multiple concurrent tool calls

## Open Questions

1. **Should we implement tool response caching?** - Defer to future optimization if needed
2. **How to handle Boost server updates?** - Monitor library updates and test compatibility
3. **Should we expose all 12+ Boost tools or subset?** - Start with 4 current tools, expand based on usage
4. **Error message formatting?** - Keep MCP server error messages as-is for now, customize if needed
