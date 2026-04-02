## ADDED Requirements

### Requirement: MCP Tool Proxy Pattern

The system SHALL implement DevBot tool classes as thin proxies that delegate execution to the Laravel Boost MCP server via McpClientService.

#### Scenario: Tool proxy delegates to MCP client

- **WHEN** DevBot calls a tool's `handle()` method
- **THEN** the tool SHALL extract parameters from the Laravel AI Request object
- **AND** it SHALL call `McpClientService::callTool()` with the appropriate MCP tool name and arguments
- **AND** it SHALL return the MCP server's response as a string
- **AND** it SHALL NOT execute any business logic locally

#### Scenario: Tool maintains Laravel AI Tool interface

- **WHEN** the tool class is inspected
- **THEN** it SHALL implement `Laravel\Ai\Contracts\Tool` interface
- **AND** it SHALL provide `description()` method with tool documentation
- **AND** it SHALL provide `schema()` method with parameter definitions
- **AND** it SHALL provide `handle()` method that proxies to MCP client

### Requirement: Database Query Tool Proxy

The system SHALL provide a DatabaseQueryTool that proxies database query requests to the Boost MCP server's `database-query` tool.

#### Scenario: Proxy database query execution

- **WHEN** DevBot calls DatabaseQueryTool with a SQL query parameter
- **THEN** the tool SHALL call `McpClientService::callTool('database-query', ['query' => $query])`
- **AND** it SHALL pass the optional `database` parameter if provided
- **AND** it SHALL return the MCP server's response
- **AND** the response SHALL contain query results or error message

#### Scenario: Tool validates required parameters

- **WHEN** DatabaseQueryTool is called without a query parameter
- **THEN** the tool SHALL return an error message requesting the query parameter
- **AND** it SHALL NOT call the MCP client
- **AND** it SHALL log the validation error

### Requirement: Database Schema Tool Proxy

The system SHALL provide a DatabaseSchemaTool that proxies schema inspection requests to the Boost MCP server's `database-schema` tool.

#### Scenario: Proxy schema retrieval

- **WHEN** DevBot calls DatabaseSchemaTool with optional table and database parameters
- **THEN** the tool SHALL call `McpClientService::callTool('database-schema', $arguments)`
- **AND** it SHALL pass all provided parameters to the MCP server
- **AND** it SHALL return the MCP server's response
- **AND** the response SHALL contain table schema information or error message

#### Scenario: List all tables without filter

- **WHEN** DatabaseSchemaTool is called without a table parameter
- **THEN** the tool SHALL call the MCP server with empty arguments
- **AND** the MCP server SHALL return a list of all database tables
- **AND** the tool SHALL return the list as formatted JSON

### Requirement: Search Docs Tool Proxy

The system SHALL provide a SearchDocsTool that proxies documentation search requests to the Boost MCP server's `search-docs` tool.

#### Scenario: Proxy documentation search

- **WHEN** DevBot calls SearchDocsTool with queries array
- **THEN** the tool SHALL call `McpClientService::callTool('search-docs', ['queries' => $queries])`
- **AND** it SHALL pass optional `packages` and `token_limit` parameters if provided
- **AND** it SHALL return the MCP server's response
- **AND** the response SHALL contain documentation snippets with links

#### Scenario: Search with package scoping

- **WHEN** SearchDocsTool is called with specific package names
- **THEN** the tool SHALL include the `packages` array in the MCP call
- **AND** the MCP server SHALL scope results to those packages
- **AND** the tool SHALL return the scoped results

### Requirement: Tinker Tool Proxy

The system SHALL provide a TinkerTool that proxies PHP code execution requests to the Boost MCP server's `tinker` tool.

#### Scenario: Proxy tinker execution

- **WHEN** DevBot calls TinkerTool with PHP code parameter
- **THEN** the tool SHALL call `McpClientService::callTool('tinker', ['code' => $code])`
- **AND** it SHALL pass the optional `timeout` parameter if provided
- **AND** it SHALL return the MCP server's response
- **AND** the response SHALL contain execution output or error message

#### Scenario: Remove PHP opening tags

- **WHEN** TinkerTool receives code with `<?php` opening tag
- **THEN** the tool SHALL strip the opening tag before sending to MCP server
- **AND** it SHALL preserve the rest of the code unchanged
- **AND** it SHALL pass the cleaned code to the MCP client

### Requirement: Additional Boost Tools (Future Expansion)

The system SHALL support adding new tool proxies for any tool available on the Boost MCP server.

#### Scenario: Add new tool proxy

- **WHEN** a developer creates a new tool proxy class
- **THEN** they SHALL implement `Laravel\Ai\Contracts\Tool` interface
- **AND** they SHALL call `McpClientService::callTool()` with the correct tool name
- **AND** they SHALL define the tool's schema matching the MCP server's expected parameters
- **AND** they SHALL register the tool in DevBot's `tools()` method

#### Scenario: Tool proxy error handling

- **WHEN** an MCP tool call fails
- **THEN** the tool proxy SHALL catch the exception
- **AND** it SHALL return the error message as a string
- **AND** it SHALL log the error with tool name and parameters
- **AND** it SHALL NOT crash the DevBot conversation

## MODIFIED Requirements

### Requirement: DevBot Agent Tool Registration

The system SHALL register all MCP tool proxy instances in DevBot's `tools()` method instead of native PHP implementations.

#### Scenario: DevBot returns tool proxy instances

- **WHEN** DevBot agent's `tools()` method is called
- **THEN** it SHALL return instances of all MCP tool proxy classes
- **AND** each tool SHALL be a proxy that calls McpClientService
- **AND** the tools SHALL include: DatabaseQueryTool, DatabaseSchemaTool, SearchDocsTool, TinkerTool
- **AND** additional tools SHALL be easily addable

#### Scenario: Tool proxies have same interface as native tools

- **WHEN** the Laravel AI system inspects DevBot's tools
- **THEN** each proxy tool SHALL provide identical interface to native implementations
- **AND** the AI system SHALL NOT be able to distinguish between proxy and native tools
- **AND** tool descriptions and schemas SHALL match MCP server capabilities
