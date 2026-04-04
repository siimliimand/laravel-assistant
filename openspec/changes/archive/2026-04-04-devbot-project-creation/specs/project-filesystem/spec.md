## ADDED Requirements

### Requirement: Secure Project Directory Creation

The system SHALL allow DevBot to create project directories scoped to `storage/projects/` only.

#### Scenario: Create new project directory

- **WHEN** DevBot calls FileSystemTool createProject with a project name
- **THEN** the system SHALL create a directory at `storage/projects/{slug}/`
- **AND** the slug SHALL be a valid kebab-case string
- **AND** the system SHALL return the absolute path to the created directory
- **AND** the directory SHALL be created with appropriate permissions (0755)

#### Scenario: Prevent directory traversal

- **WHEN** a file path contains `..` or absolute path components
- **THEN** the system SHALL reject the operation
- **AND** the system SHALL return an error message explaining the path is invalid
- **AND** the system SHALL NOT create any files outside `storage/projects/`

#### Scenario: Prevent duplicate project names

- **WHEN** a project directory with the same name already exists
- **THEN** the system SHALL return an error indicating the project exists
- **AND** the system SHALL suggest a unique alternative name

### Requirement: File Operations Within Projects

The system SHALL allow DevBot to write and read files within project directories.

#### Scenario: Write file to project

- **WHEN** DevBot calls FileSystemTool writeFile with a relative path and content
- **THEN** the system SHALL create the file at `storage/projects/{project}/{relative-path}`
- **AND** the system SHALL create any necessary parent directories
- **AND** the system SHALL validate the path remains within the project directory
- **AND** the system SHALL return the absolute path to the created file

#### Scenario: Read file from project

- **WHEN** DevBot calls FileSystemTool readFile with a relative path
- **THEN** the system SHALL read the file at `storage/projects/{project}/{relative-path}`
- **AND** the system SHALL validate the path remains within the project directory
- **AND** the system SHALL return the file contents

#### Scenario: List project files

- **WHEN** DevBot calls FileSystemTool listFiles with a project slug
- **THEN** the system SHALL return a recursive list of all files in the project directory
- **AND** the list SHALL include relative paths and file sizes

### Requirement: File System Security

The system SHALL enforce strict security boundaries for all file operations.

#### Scenario: Reject operations outside project scope

- **WHEN** any file operation targets a path outside `storage/projects/`
- **THEN** the system SHALL reject the operation
- **AND** the system SHALL log a security warning
- **AND** the system SHALL return an appropriate error message
