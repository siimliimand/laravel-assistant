## ADDED Requirements

### Requirement: MCP Tool Availability

The system SHALL provide MCP tools from the Laravel Boost server to the DevBot agent, enabling it to perform development tasks beyond conversational responses.

#### Scenario: DevBot has access to MCP tools

- **WHEN** the DevBot agent is instantiated
- **THEN** it SHALL have access to database-query, database-schema, search-docs, and tinker tools
- **AND** each tool SHALL implement the Laravel\Ai\Contracts\Tool interface
- **AND** tools SHALL be returned by the DevBot's tools() method

#### Scenario: Tool metadata is available

- **WHEN** a tool is inspected
- **THEN** it SHALL provide a name, description, and parameter schema
- **AND** the description SHALL clearly explain what the tool does
- **AND** the parameter schema SHALL define required and optional parameters with types

### Requirement: Database Query Tool

The system SHALL provide a tool that allows DevBot to execute read-only database queries using the Laravel Boost database-query MCP tool.

#### Scenario: Execute database query

- **WHEN** DevBot calls the database-query tool with a SQL SELECT statement
- **THEN** the system SHALL execute the query against the application database
- **AND** it SHALL return the results as a JSON array
- **AND** the query SHALL be limited to read-only operations (SELECT only)
- **AND** the results SHALL be limited to a maximum of 100 rows

#### Scenario: Query execution error

- **WHEN** the database query fails (e.g., syntax error, table not found)
- **THEN** the tool SHALL return a descriptive error message
- **AND** DevBot SHALL receive the error and can explain it to the user
- **AND** the error SHALL be logged for debugging

### Requirement: Database Schema Tool

The system SHALL provide a tool that allows DevBot to inspect database table structure using the Laravel Boost database-schema MCP tool.

#### Scenario: Retrieve table schema

- **WHEN** DevBot calls the database-schema tool with a table name
- **THEN** the system SHALL return the table's column definitions
- **AND** it SHALL include column names, types, nullable status, and defaults
- **AND** it SHALL include index information
- **AND** it SHALL return an error message if the table does not exist

#### Scenario: List all tables

- **WHEN** DevBot calls the database-schema tool without a table name
- **THEN** the system SHALL return a list of all database tables
- **AND** the list SHALL be sorted alphabetically

### Requirement: Documentation Search Tool

The system SHALL provide a tool that allows DevBot to search Laravel and package documentation using the Laravel Boost search-docs MCP tool.

#### Scenario: Search documentation

- **WHEN** DevBot calls the search-docs tool with a query string
- **THEN** the system SHALL return relevant documentation snippets
- **AND** it SHALL include links to the full documentation
- **AND** the results SHALL be scoped to installed packages when specified
- **AND** it SHALL return an empty result set if no matches are found

#### Scenario: Search with package scoping

- **WHEN** DevBot calls search-docs with specific package names
- **THEN** the results SHALL be limited to those packages only
- **AND** it SHALL use version-specific documentation based on installed versions

### Requirement: Tinker Tool

The system SHALL provide a tool that allows DevBot to execute PHP code in the application context using the Laravel Boost tinker MCP tool.

#### Scenario: Execute tinker command

- **WHEN** DevBot calls the tinker tool with PHP code
- **THEN** the system SHALL execute the code in the Laravel application context
- **AND** it SHALL return the result or output
- **AND** it SHALL have access to Laravel facades, models, and helper functions
- **AND** execution SHALL be limited to 30 seconds timeout

#### Scenario: Tinker execution error

- **WHEN** the tinker code throws an exception
- **THEN** the tool SHALL return the exception message and stack trace
- **AND** DevBot SHALL receive the error and can explain it to the user
- **AND** the error SHALL not crash the application

### Requirement: Tool Execution Tracking

The system SHALL track tool usage within DevBot's conversation to enforce max steps and provide transparency.

#### Scenario: Tool call is recorded

- **WHEN** DevBot calls a tool during a conversation
- **THEN** the tool call and its result SHALL be added to the conversation message history
- **AND** it SHALL count toward the max steps limit (10 steps)
- **AND** the user SHALL see tool usage in the conversation context (indirectly via AI responses)

#### Scenario: Max steps exceeded

- **WHEN** DevBot exceeds the max steps limit during tool usage
- **THEN** the system SHALL stop tool execution
- **AND** it SHALL return the current response to the user
- **AND** it SHALL log a warning about the step limit
