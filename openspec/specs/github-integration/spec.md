## ADDED Requirements

### Requirement: GitHub Repository Creation

The system SHALL allow DevBot to create GitHub repositories via the GitHub API.

#### Scenario: Create public repository

- **WHEN** DevBot calls GitHubTool createRepo with a name
- **THEN** the system SHALL create a public GitHub repository via the REST API
- **AND** the system SHALL use the configured GitHub personal access token for authentication
- **AND** the system SHALL return the repository URL and clone URL

#### Scenario: Create private repository

- **WHEN** DevBot calls GitHubTool createRepo with a name and private=true
- **THEN** the system SHALL create a private GitHub repository
- **AND** the system SHALL return the repository URL and clone URL

#### Scenario: Repository already exists

- **WHEN** a GitHub repository with the same name already exists
- **THEN** the system SHALL return an error indicating the repository exists
- **AND** the system SHALL suggest a unique alternative name

#### Scenario: GitHub token invalid

- **WHEN** the GitHub personal access token is invalid or expired
- **THEN** the system SHALL return an authentication error
- **AND** the system SHALL indicate that the token needs to be regenerated

### Requirement: GitHub Repository Information

The system SHALL allow DevBot to retrieve GitHub repository information.

#### Scenario: Get repository info

- **WHEN** DevBot calls GitHubTool getRepoInfo with a repository name
- **THEN** the system SHALL fetch repository details from GitHub API
- **AND** the system SHALL return repository URL, description, visibility, and default branch

### Requirement: GitHub Authentication

The system SHALL securely manage GitHub authentication.

#### Scenario: Token configuration

- **WHEN** the GitHubTool is instantiated
- **THEN** it SHALL read the GitHub token from `config('ai.projects.github_token')`
- **AND** the token SHALL be stored securely and not exposed in logs

#### Scenario: Token validation

- **WHEN** DevBot calls GitHubTool validateToken
- **THEN** the system SHALL verify the token has appropriate scopes (repo)
- **AND** the system SHALL return the token owner username and scopes
