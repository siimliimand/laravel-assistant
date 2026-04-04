## Context

DevBot is an AI-powered assistant integrated into a Laravel application using the Laravel AI package. It currently supports chat interactions and MCP tool integration but lacks project creation capabilities. Users want to transform their micro-SaaS ideas into structured projects with minimal manual setup.

The current system has:

- DevBot agent configured via `app/Ai/Agents/DevBot.php`
- Existing MCP tools in `app/Ai/Tools/`
- OpenSpec workflow for specification management
- Chat interface for user-agent interaction

## Goals / Non-Goals

**Goals:**

- Enable DevBot to create new projects from user descriptions
- Implement secure, scoped file system operations within `storage/projects/`
- Integrate Git operations for repository initialization and version control
- Integrate GitHub API for repository creation and remote management
- Orchestrate OpenSpec workflow to generate project specifications
- Provide a guided skill that leads DevBot through the complete workflow

**Non-Goals:**

- Project templates or boilerplate generation (future enhancement)
- Multi-user project isolation (single-user scope for now)
- CI/CD pipeline configuration
- Deployment automation

## Decisions

### 1. Tool Architecture: Laravel AI Tools

**Decision:** Create four new Laravel AI tools: `FileSystemTool`, `GitTool`, `GitHubTool`, and `OpenSpecTool`.

**Rationale:** This follows the existing pattern used by `DatabaseQueryTool`, `DatabaseSchemaTool`, `SearchDocsTool`, and `TinkerTool`. Laravel AI tools are the standard way to extend agent capabilities and integrate seamlessly with the chat flow.

**Alternatives Considered:**

- MCP tools: Would require external server setup; Laravel AI tools are simpler and more direct
- Service classes: Less discoverable by the agent; tools have built-in documentation

### 2. File System Scoping

**Decision:** Restrict all file operations to `storage/projects/` directory with path validation.

**Rationale:** Security is paramount. By scoping operations to a dedicated directory, we prevent accidental or malicious access to application code or sensitive files. Each project gets its own subdirectory.

**Alternatives Considered:**

- Allow any path with permissions: Too risky
- Chroot-style isolation: Overkill for this use case

### 3. Git Operations: Shell Commands

**Decision:** Execute Git commands via Symfony Process component.

**Rationale:** Git is a shell tool; the Process component provides safe command execution with proper escaping and error handling. No need for a PHP Git library.

**Alternatives Considered:**

- `github/gitlab-api` package: Adds unnecessary dependency for simple operations
- PhpGit library: Less maintained, additional dependency

### 4. GitHub API: HTTP Client

**Decision:** Use Laravel's HTTP client with GitHub REST API.

**Rationale:** Simple, well-documented API that only requires a personal access token. Laravel's HTTP client provides retry logic, timeout handling, and clean request/response handling.

**Alternatives Considered:**

- `knplabs/github-api`: Full-featured but heavy for our needs
- GitHub GraphQL API: More complex, not needed for basic operations

### 5. OpenSpec Integration: Skill Invocation

**Decision:** The OpenSpecTool will guide DevBot to use existing OpenSpec skills rather than duplicating their logic.

**Rationale:** OpenSpec already has `openspec-propose` skill that handles proposal/design/specs/tasks creation. DevBot should leverage this through skill invocation.

**Alternatives Considered:**

- Re-implement OpenSpec logic in tool: DRY violation
- Direct file creation: Loses OpenSpec validation and structure

## Risks / Trade-offs

| Risk                           | Mitigation                                            |
| ------------------------------ | ----------------------------------------------------- |
| GitHub API rate limits         | Cache repository info; implement backoff retry        |
| File system permissions        | Clear error messages; validate directory creation     |
| Git not installed              | Detect Git availability; graceful degradation         |
| Invalid GitHub token           | Validate token on tool registration; cache result     |
| Project name collisions        | Generate unique slugs; check for existing directories |
| Large projects filling storage | Implement disk quota checks in future                 |

## Migration Plan

1. Add `storage/projects/` to `.gitignore`
2. Create new tools and register in DevBot
3. Add configuration to `config/ai.php`
4. Create Project Creation skill
5. Update DevBot instructions
6. Run existing test suite to ensure no regressions

No rollback needed - this is additive functionality.
