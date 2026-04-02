## ADDED Requirements

### Requirement: DevBot Agent Configuration

The system SHALL provide a DevBot AI agent configured specifically for development-related assistance with appropriate model settings and system instructions.

#### Scenario: Agent initialization with development focus

- **WHEN** the DevBot agent is instantiated
- **THEN** it SHALL have system instructions identifying it as a development assistant
- **AND** it SHALL be configured to use the Anthropic Claude model
- **AND** it SHALL have max steps set to 10 for tool usage
- **AND** it SHALL have temperature set to 0.7 for balanced creativity

#### Scenario: Agent processes development question

- **WHEN** a user asks a development-related question
- **THEN** the agent SHALL provide accurate, helpful responses about programming
- **AND** the response SHALL follow Laravel and PHP best practices when applicable

#### Scenario: Agent handles non-development questions

- **WHEN** a user asks a non-development question
- **THEN** the agent SHALL politely redirect the conversation to development topics
- **AND** it SHALL maintain its focus on programming assistance

## MODIFIED Requirements

### Requirement: DevBot Agent Tool Access via MCP Client

The system SHALL provide DevBot with tool-calling capabilities through the MCP client, enabling access to Laravel Boost MCP server tools including database-query, database-schema, search-docs, tinker, and other Boost tools.

#### Scenario: Agent has access to MCP tools through client

- **WHEN** the DevBot agent is instantiated
- **THEN** it SHALL have access to DatabaseQueryTool, DatabaseSchemaTool, SearchDocsTool, and TinkerTool
- **AND** each tool SHALL implement the Laravel\Ai\Contracts\Tool interface
- **AND** tools SHALL be returned by the DevBot's tools() method
- **AND** each tool SHALL proxy calls to the McpClientService instead of executing native PHP code

#### Scenario: Agent uses database query tool via MCP

- **WHEN** DevBot needs to inspect database data
- **THEN** it SHALL call the DatabaseQueryTool with a read-only SQL query
- **AND** the tool SHALL call McpClientService::callTool('database-query', $arguments)
- **AND** the MCP server SHALL execute only SELECT, SHOW, EXPLAIN, or DESCRIBE statements
- **AND** results SHALL be limited to 100 rows maximum by the MCP server

#### Scenario: Agent uses documentation search tool via MCP

- **WHEN** DevBot needs to find documentation
- **THEN** it SHALL call the SearchDocsTool with relevant queries
- **AND** the tool SHALL call McpClientService::callTool('search-docs', $arguments)
- **AND** it SHALL receive documentation snippets with links from the MCP server
- **AND** it SHALL scope results to specific packages when provided

#### Scenario: Agent uses tinker tool via MCP

- **WHEN** DevBot needs to execute PHP code
- **THEN** it SHALL call the TinkerTool with PHP code
- **AND** the tool SHALL call McpClientService::callTool('tinker', $arguments)
- **AND** the MCP server SHALL execute the code in the Laravel application context
- **AND** it SHALL return the output and return value

#### Scenario: Agent uses database schema tool via MCP

- **WHEN** DevBot needs to inspect database schema
- **THEN** it SHALL call the DatabaseSchemaTool with optional table name
- **AND** the tool SHALL call McpClientService::callTool('database-schema', $arguments)
- **AND** the MCP server SHALL return table names, columns, data types, indexes, and foreign keys
