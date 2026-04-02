## Why

DevBot currently lacks the ability to create and manage projects. Users must manually set up project directories, initialize Git repositories, create GitHub repos, and generate project specifications. This creates friction when users want to quickly transform their micro-SaaS ideas into structured projects. Enabling DevBot to orchestrate this entire workflow will significantly improve the developer experience and reduce time-to-code.

## What Changes

- Add project creation capabilities to DevBot agent
- Create `storage/projects/` directory structure for project isolation
- Implement new Laravel AI tools: `FileSystemTool`, `GitTool`, `GitHubTool`, `OpenSpecTool`
- Create Project Creation skill that guides the workflow
- Update DevBot instructions to include project creation capabilities
- Add GitHub API token configuration for repository creation
- Enable automatic OpenSpec proposal generation for new projects

## Capabilities

### New Capabilities

- `project-creation`: Core orchestration capability that guides DevBot through the project creation workflow - gathering requirements, creating directories, generating specs, initializing Git, and pushing to GitHub
- `project-filesystem`: Secure file system operations scoped to `storage/projects/` - create project directories, write/read files within project boundaries
- `git-integration`: Git repository operations - initialize, stage, commit, add remotes, and push changes
- `github-integration`: GitHub API integration for creating repositories, managing settings, and retrieving repository information

### Modified Capabilities

- `devbot-agent`: Extend agent instructions to include project creation capabilities and tool access

## Impact

- **New Tools**: `app/Ai/Tools/FileSystemTool.php`, `GitTool.php`, `GitHubTool.php`, `OpenSpecTool.php`
- **New Skill**: `.agents/skills/project-creation/SKILL.md`
- **Configuration**: Add `projects` section to `config/ai.php` with base path and GitHub token settings
- **Storage**: New `storage/projects/` directory (gitignored for security)
- **Dependencies**: May require `github/api-client` or similar package for GitHub API
- **DevBot Agent**: Updated instructions for project creation workflow
