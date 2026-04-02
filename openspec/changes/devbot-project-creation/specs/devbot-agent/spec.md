## ADDED Requirements

### Requirement: DevBot Project Creation Tool Access

The system SHALL provide DevBot with access to project creation tools for enabling the micro-SaaS project creation workflow.

#### Scenario: Agent has access to project creation tools

- **WHEN** the DevBot agent is instantiated
- **THEN** it SHALL have access to FileSystemTool, GitTool, GitHubTool, and OpenSpecTool
- **AND** each tool SHALL implement the Laravel\\Ai\\Contracts\\Tool interface
- **AND** tools SHALL be returned by the DevBot's tools() method

#### Scenario: Agent uses FileSystemTool for project creation

- **WHEN** DevBot needs to create a project directory or files
- **THEN** it SHALL call the FileSystemTool with appropriate parameters
- **AND** the tool SHALL validate all paths are scoped to `storage/projects/`
- **AND** the tool SHALL return operation results with absolute paths

#### Scenario: Agent uses GitTool for repository management

- **WHEN** DevBot needs to initialize or manage a Git repository
- **THEN** it SHALL call the GitTool with appropriate commands
- **AND** the tool SHALL execute Git commands safely via Symfony Process
- **AND** the tool SHALL return command output and status

#### Scenario: Agent uses GitHubTool for repository creation

- **WHEN** DevBot needs to create or query GitHub repositories
- **THEN** it SHALL call the GitHubTool with appropriate parameters
- **AND** the tool SHALL use the configured GitHub token for authentication
- **AND** the tool SHALL return repository information from the GitHub API

### Requirement: DevBot Project Creation Instructions

The system SHALL provide DevBot with instructions for the project creation workflow.

#### Scenario: Instructions include project creation workflow

- **WHEN** the DevBot agent is instantiated
- **THEN** its instructions SHALL include guidance for project creation
- **AND** instructions SHALL explain the workflow: gather requirements → create directory → generate OpenSpec → Git init → GitHub push
- **AND** instructions SHALL reference the Project Creation skill for detailed guidance

#### Scenario: Agent recognizes project creation requests

- **WHEN** a user describes a micro-SaaS idea or requests project creation
- **THEN** DevBot SHALL recognize this as a project creation request
- **AND** DevBot SHALL activate the Project Creation skill
- **AND** DevBot SHALL guide the user through the workflow
