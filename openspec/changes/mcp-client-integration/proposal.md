## Why

The current DevBot implementation uses native PHP tool classes that duplicate functionality already available in the Laravel Boost MCP server. This creates maintenance burden, feature divergence, and prevents DevBot from benefiting from MCP tool updates. By integrating a true MCP client, DevBot can call the Boost MCP server tools directly through the Model Context Protocol, ensuring consistency with IDE tooling and enabling access to all MCP capabilities.

## What Changes

- Add `php-mcp/client` library as a Composer dependency to enable MCP protocol communication
- Create `McpClientService` class that manages MCP server connections and tool calls
- Refactor DevBot tool classes to use MCP client instead of native PHP implementations
- Update tools to call Laravel Boost MCP server via STDIO transport
- Enable DevBot to access all Boost MCP tools: database-query, database-schema, search-docs, tinker, browser-logs, list-routes, get-config, etc.
- Add configuration for MCP server connection settings
- **BREAKING**: Replace native PHP tool implementations with MCP protocol calls (tools will have identical behavior but different execution path)

## Capabilities

### New Capabilities

- `mcp-client-service`: Laravel service that manages MCP client connections to Boost server, handles STDIO transport, JSON-RPC communication, and tool invocation
- `mcp-tool-proxy`: DevBot tool classes that proxy requests to MCP server instead of executing native PHP code, maintaining Laravel AI Tool interface while delegating to MCP

### Modified Capabilities

- `mcp-tool-integration`: Changes from native PHP execution to MCP protocol calls for database-query, database-schema, search-docs, and tinker tools
- `devbot-agent`: DevBot agent now has access to full Boost MCP tool suite through MCP client, not just the 4 manually implemented tools

## Impact

**Affected Code:**

- `composer.json` - Add php-mcp/client dependency
- `config/services.php` - Add MCP client configuration
- `app/Services/McpClientService.php` - New MCP client service
- `app/Ai/Tools/` - Refactor all tool classes to use MCP client
- `app/Ai/Agents/DevBot.php` - Updated tool registration

**Dependencies:**

- php-mcp/client library (new)
- Laravel Boost MCP server (existing, already configured)

**Systems:**

- DevBot tool execution shifts from native PHP to MCP protocol
- All Boost MCP tools become available to DevBot, not just 4 manually implemented ones
- Tool responses may differ slightly in format but functionality remains identical
