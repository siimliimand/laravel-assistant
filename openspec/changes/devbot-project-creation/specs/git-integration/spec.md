## ADDED Requirements

### Requirement: Git Repository Initialization

The system SHALL allow DevBot to initialize Git repositories in project directories.

#### Scenario: Initialize Git repository

- **WHEN** DevBot calls GitTool init with a project path
- **THEN** the system SHALL execute `git init` in the project directory
- **AND** the system SHALL configure the default branch as `main`
- **AND** the system SHALL return success status and repository path

#### Scenario: Git not available

- **WHEN** Git is not installed on the system
- **THEN** the system SHALL return an error indicating Git is unavailable
- **AND** the system SHALL suggest installing Git

### Requirement: Git Staging and Committing

The system SHALL allow DevBot to stage and commit files.

#### Scenario: Stage all files

- **WHEN** DevBot calls GitTool add with a project path
- **THEN** the system SHALL execute `git add .` in the project directory
- **AND** the system SHALL return the list of staged files

#### Scenario: Stage specific files

- **WHEN** DevBot calls GitTool add with a project path and file paths
- **THEN** the system SHALL stage only the specified files
- **AND** the system SHALL return the list of staged files

#### Scenario: Commit changes

- **WHEN** DevBot calls GitTool commit with a project path and message
- **THEN** the system SHALL execute `git commit -m "{message}"` in the project directory
- **AND** the system SHALL configure Git user.name and user.email from config
- **AND** the system SHALL return the commit hash and summary

### Requirement: Git Remote Management

The system SHALL allow DevBot to manage Git remotes.

#### Scenario: Add remote

- **WHEN** DevBot calls GitTool remoteAdd with project path, remote name, and URL
- **THEN** the system SHALL execute `git remote add {name} {url}`
- **AND** the system SHALL verify the remote was added
- **AND** the system SHALL return success status

#### Scenario: Push to remote

- **WHEN** DevBot calls GitTool push with project path and branch
- **THEN** the system SHALL execute `git push -u origin {branch}`
- **AND** the system SHALL use configured credentials for authentication
- **AND** the system SHALL return success status and remote URL

### Requirement: Git Status Reporting

The system SHALL provide Git status information to DevBot.

#### Scenario: Get repository status

- **WHEN** DevBot calls GitTool status with a project path
- **THEN** the system SHALL return current branch name
- **AND** the system SHALL return list of untracked files
- **AND** the system SHALL return list of modified files
- **AND** the system SHALL return list of staged files
