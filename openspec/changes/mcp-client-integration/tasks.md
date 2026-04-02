## 1. Dependency Installation and Configuration

- [x] 1.1 Install `php-mcp/client` library via Composer: `composer require php-mcp/client`
- [x] 1.2 Add MCP client configuration to `config/services.php` with command, timeout, max_retries, and retry_delay options
- [x] 1.3 Create configuration validation to verify Artisan command exists and settings are valid
- [x] 1.4 Update `.env.example` with MCP client configuration options (optional overrides)

## 2. MCP Client Service Implementation

- [x] 2.1 Create `app/Services/McpClientService.php` class with initialize(), callTool(), and disconnect() methods
- [x] 2.2 Implement STDIO transport using Symfony Process component to spawn `php artisan boost:mcp`
- [x] 2.3 Implement MCP `initialize` handshake on first connection
- [x] 2.4 Implement `tools/call` JSON-RPC 2.0 request/response handling
- [x] 2.5 Add connection state management (singleton pattern, reuse existing connection)
- [x] 2.6 Implement graceful shutdown in service provider's terminate() method
- [x] 2.7 Add comprehensive logging for all MCP operations (tool calls, errors, connection events)
- [x] 2.8 Implement timeout handling with configurable timeout per tool call
- [x] 2.9 Implement auto-reconnect logic with exponential backoff on connection failure
- [x] 2.10 Add health check to verify subprocess is running before each tool call
- [x] 2.11 Register McpClientService as singleton in AppServiceProvider

## 3. Tool Proxy Refactoring

- [x] 3.1 Refactor `DatabaseQueryTool` to call `McpClientService::callTool('database-query', $args)` instead of native PHP execution
- [x] 3.2 Refactor `DatabaseSchemaTool` to call `McpClientService::callTool('database-schema', $args)` instead of native PHP execution
- [x] 3.3 Refactor `SearchDocsTool` to call `McpClientService::callTool('search-docs', $args)` instead of native PHP execution
- [x] 3.4 Refactor `TinkerTool` to call `McpClientService::callTool('tinker', $args)` instead of native PHP execution
- [x] 3.5 Update all tool classes to maintain Laravel AI `Tool` interface (description, schema, handle methods)
- [x] 3.6 Add parameter validation in each tool proxy before calling MCP client
- [x] 3.7 Add error handling in each tool proxy to catch MCP client exceptions
- [x] 3.8 Test each tool proxy individually with real Boost MCP server

## 4. DevBot Agent Integration

- [x] 4.1 Verify DevBot's `tools()` method returns all refactored tool proxy instances
- [x] 4.2 Test DevBot agent conversation with tool calls through MCP client
- [x] 4.3 Verify tool calls are tracked in conversation message history
- [x] 4.4 Test multiple tool calls in single conversation turn
- [x] 4.5 Test tool call error handling doesn't crash DevBot conversation

## 5. Unit and Integration Tests

- [x] 5.1 Write unit tests for McpClientService with mocked MCP client
- [x] 5.2 Write unit tests for connection initialization and state management
- [x] 5.3 Write unit tests for tool call request/response handling
- [x] 5.4 Write unit tests for error handling and retry logic
- [x] 5.5 Write unit tests for each tool proxy class with mocked McpClientService
- [x] 5.6 Write integration tests with real Boost MCP server for each tool
- [x] 5.7 Write feature test for DevBot conversation with MCP tool usage
- [x] 5.8 Test graceful shutdown and process cleanup

## 6. Documentation and Specs

- [x] 6.1 Update `openspec/specs/devbot-agent/spec.md` with MODIFIED requirements for MCP client tool access
- [x] 6.2 Update `openspec/specs/mcp-tool-integration/spec.md` with MODIFIED requirements for MCP protocol calls
- [x] 6.3 Add inline PHPDoc comments to McpClientService methods
- [x] 6.4 Add README section explaining MCP client architecture (if project has architectural docs)

## 7. Integration Testing and Verification

- [x] 7.1 Test end-to-end flow: Ask DevBot to query database → Verify it calls database-query tool via MCP
- [x] 7.2 Test end-to-end flow: Ask DevBot to search docs → Verify it calls search-docs tool via MCP
- [x] 7.3 Test end-to-end flow: Ask DevBot to execute PHP → Verify it calls tinker tool via MCP
- [x] 7.4 Test error scenarios: Invalid SQL query, non-existent table, PHP exception
- [x] 7.5 Test connection resilience: Kill MCP server process, verify auto-reconnect on next tool call
- [x] 7.6 Test timeout handling: Execute long-running tinker command, verify timeout error
- [x] 7.7 Run full test suite: `php artisan test --compact`
- [x] 7.8 Run Pint formatter: `vendor/bin/pint --dirty --format agent`
- [x] 7.9 Verify all specs are satisfied by manual testing against each scenario
