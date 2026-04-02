## Requirements

### Requirement: MCP Client Service Initialization

The system SHALL provide an McpClientService that manages connections to the Laravel Boost MCP server via STDIO transport.

#### Scenario: Service initialization on first tool call

- **WHEN** the McpClientService is first invoked to call a tool
- **THEN** it SHALL spawn the Laravel Boost MCP server as a subprocess using `php artisan boost:mcp`
- **AND** it SHALL establish STDIO communication channels (stdin/stdout)
- **AND** it SHALL send an MCP `initialize` request to the server
- **AND** it SHALL store the client instance for reuse in subsequent calls

#### Scenario: Service maintains persistent connection

- **WHEN** multiple tools are called in the same request lifecycle
- **THEN** the service SHALL reuse the existing MCP client connection
- **AND** it SHALL NOT spawn a new subprocess for each tool call
- **AND** it SHALL track connection state to prevent duplicate initialization

#### Scenario: Service graceful shutdown

- **WHEN** the Laravel application terminates
- **THEN** the service SHALL send an MCP shutdown notification to the server
- **AND** it SHALL close STDIO pipes gracefully
- **AND** it SHALL terminate the subprocess without leaving zombie processes

### Requirement: MCP Tool Invocation

The system SHALL allow DevBot tools to call any tool available on the Laravel Boost MCP server through the McpClientService.

#### Scenario: Successful tool call

- **WHEN** a tool calls `McpClientService::callTool('database-query', ['query' => 'SELECT 1'])`
- **THEN** the service SHALL send a JSON-RPC 2.0 `tools/call` request to the MCP server
- **AND** it SHALL pass the tool name and arguments correctly
- **AND** it SHALL wait for the server's response with a configurable timeout (default: 30 seconds)
- **AND** it SHALL return the tool result as a string
- **AND** it SHALL log the tool call for debugging purposes

#### Scenario: Tool call with timeout

- **WHEN** a tool call exceeds the configured timeout
- **THEN** the service SHALL abort the request
- **AND** it SHALL return an error message indicating the timeout
- **AND** it SHALL log the timeout event with tool name and duration
- **AND** it SHALL keep the connection alive for subsequent calls

#### Scenario: Tool call error handling

- **WHEN** the MCP server returns an error for a tool call
- **THEN** the service SHALL return the error message to the calling tool
- **AND** it SHALL log the error with full details
- **AND** it SHALL NOT crash the Laravel application
- **AND** the error SHALL be user-readable for display in chat

### Requirement: MCP Server Connection Management

The system SHALL handle MCP server lifecycle events including crashes, restarts, and connection failures.

#### Scenario: Server crash detection

- **WHEN** the MCP server subprocess exits unexpectedly
- **THEN** the service SHALL detect the process termination
- **AND** it SHALL clear the cached client instance
- **AND** it SHALL attempt to reinitialize the connection on the next tool call
- **AND** it SHALL log the crash with exit code and error output

#### Scenario: Auto-reconnect on failure

- **WHEN** a tool call fails due to connection error
- **THEN** the service SHALL attempt to reconnect automatically
- **AND** it SHALL retry the tool call up to 3 times with exponential backoff
- **AND** it SHALL return an error message if all retries fail
- **AND** it SHALL log each retry attempt

#### Scenario: Health check before tool call

- **WHEN** a tool call is made and a client instance exists
- **THEN** the service SHALL verify the subprocess is still running
- **AND** if the process is dead, it SHALL reinitialize the connection
- **AND** it SHALL proceed with the tool call only after successful initialization

### Requirement: MCP Client Configuration

The system SHALL provide configuration options for MCP client behavior.

#### Scenario: Configuration from services.php

- **WHEN** the McpClientService is instantiated
- **THEN** it SHALL read configuration from `config('services.mcp')`
- **AND** it SHALL support the following options:
  - `command`: Artisan command to run (default: `php artisan boost:mcp`)
  - `timeout`: Maximum seconds to wait for tool response (default: 30)
  - `max_retries`: Number of reconnect attempts (default: 3)
  - `retry_delay`: Base delay between retries in seconds (default: 1)

#### Scenario: Configuration validation

- **WHEN** the service initializes
- **THEN** it SHALL validate that the Artisan command exists
- **AND** it SHALL validate that timeout is a positive integer
- **AND** it SHALL log a warning if configuration is missing or invalid
