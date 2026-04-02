# Project Creation Skill

Guide DevBot through creating a complete micro-SaaS project from idea to GitHub repository.

## When to Use

Activate this skill when:

- User describes a micro-SaaS idea they want to build
- User explicitly requests project creation
- User wants to transform an idea into a structured project

## Workflow Steps

### Step 1: Gather Requirements

**Goal**: Understand what the user wants to build

**Actions**:

1. Ask clarifying questions about the project:
    - What problem does it solve?
    - Who is the target audience?
    - What are the core features?
    - Any specific technology preferences?
2. Suggest a project name in kebab-case format
3. Confirm requirements with the user before proceeding

**Tools**: None needed (conversation only)

**Error Recovery**:

- If user is unsure about features, suggest common patterns for their use case
- If project name conflicts, suggest alternatives with numbers or descriptors

---

### Step 2: Create Project Directory

**Goal**: Create a secure, isolated project directory

**Actions**:

1. Use `FileSystemTool` to create the project:
    ```
    action: createProject
    name: <project-name>
    ```
2. Verify the directory was created successfully
3. Confirm the project path with the user

**Tools**:

- `FileSystemTool` (createProject, projectExists)

**Error Recovery**:

- If directory creation fails, check file permissions
- If name conflict, suggest alternative: `{name}-{random-suffix}`
- If path validation fails, ensure name is kebab-case and valid

---

### Step 3: Generate OpenSpec Artifacts

**Goal**: Create structured project specifications

**Actions**:

1. Use the `openspec-propose` skill to generate:
    - `proposal.md` - Why this project exists
    - `design.md` - Technical architecture
    - `specs/` - Detailed requirements
    - `tasks.md` - Implementation checklist
2. Verify all artifacts were created using `OpenSpecTool`:
    ```
    action: getStatus
    project: <project-slug>
    ```
3. Review artifacts with the user and refine if needed

**Tools**:

- `OpenSpecTool` (getStatus, getInstructions)
- `openspec-propose` skill (invoke directly)

**Error Recovery**:

- If artifacts are incomplete, re-run `openspec-propose` with more details
- If specs need refinement, use `openspec-continue` to update them
- Check `storage/projects/{project}/openspec/` directory for issues

---

### Step 4: Initialize Git Repository

**Goal**: Set up version control for the project

**Actions**:

1. Initialize Git repository:
    ```
    action: init
    project: <project-slug>
    ```
2. Stage all files:
    ```
    action: add
    project: <project-slug>
    ```
3. Commit with descriptive message:
    ```
    action: commit
    project: <project-slug>
    message: "Initial commit: OpenSpec project setup"
    ```
4. Verify commit was successful using:
    ```
    action: status
    project: <project-slug>
    ```

**Tools**:

- `GitTool` (init, add, commit, status)

**Error Recovery**:

- If Git not installed, instruct user to install it: `sudo apt install git`
- If commit fails with "nothing to commit", verify files exist in project directory
- If Git config missing, tool will auto-configure user.name and user.email

---

### Step 5: Create GitHub Repository

**Goal**: Create a remote repository on GitHub

**Actions**:

1. Validate GitHub token (optional but recommended):
    ```
    action: validateToken
    ```
2. Create GitHub repository:
    ```
    action: createRepo
    name: <project-slug>
    private: <true/false>
    ```
3. Store the repository URL and clone URL for next step

**Tools**:

- `GitHubTool` (validateToken, createRepo, getRepoInfo)

**Error Recovery**:

- If token invalid, instruct user to create one at: https://github.com/settings/tokens
    - Required scopes: `repo`
    - Set in `.env` as `GITHUB_TOKEN`
- If repository exists, suggest different name or use existing
- If rate limited, wait and retry (GitHub API: 5000 requests/hour for authenticated)

---

### Step 6: Push to GitHub

**Goal**: Push local repository to GitHub

**Actions**:

1. Add GitHub remote:
    ```
    action: remoteAdd
    project: <project-slug>
    name: origin
    url: <clone-url-from-step-5>
    ```
2. Push to GitHub:
    ```
    action: push
    project: <project-slug>
    branch: main
    ```
3. Provide user with the repository URL

**Tools**:

- `GitTool` (remoteAdd, push)
- `GitHubTool` (getRepoInfo - to verify)

**Error Recovery**:

- If authentication fails, verify GITHUB_TOKEN is correct
- If push rejected, check if remote URL is correct
- If branch name mismatch, verify default branch is 'main'
- For SSH key issues, suggest using HTTPS clone URL instead

---

### Step 7: Provide Summary

**Goal**: Give user a complete overview of what was created

**Actions**:

1. Summarize the project creation:
    - Project name and path
    - GitHub repository URL
    - OpenSpec artifacts created
    - Next steps for implementation
2. Offer to start implementing tasks with `/opsx:apply`
3. Provide links to relevant documentation

**Tools**: None needed (conversation only)

---

## Available Tools Reference

### FileSystemTool

- `createProject` - Create new project directory
- `writeFile` - Write file within project
- `readFile` - Read file from project
- `listFiles` - List all project files
- `projectExists` - Check if project exists

### GitTool

- `init` - Initialize Git repository
- `add` - Stage files
- `commit` - Commit changes
- `remoteAdd` - Add remote repository
- `push` - Push to remote
- `status` - Get repository status

### GitHubTool

- `createRepo` - Create GitHub repository
- `getRepoInfo` - Get repository details
- `validateToken` - Validate GitHub token

### OpenSpecTool

- `getStatus` - Check OpenSpec artifact status
- `getInstructions` - Get OpenSpec workflow guidance

---

## Common Issues & Solutions

### GitHub Token Not Configured

**Problem**: GitHubTool returns "token not configured"
**Solution**:

1. Create token at https://github.com/settings/tokens
2. Add to `.env`: `GITHUB_TOKEN=your-token-here`
3. Restart Laravel server

### Path Traversal Blocked

**Problem**: FileSystemTool rejects path
**Solution**: Ensure project name is kebab-case (e.g., `my-project`, not `my/project`)

### Git Not Found

**Problem**: GitTool returns "Git not installed"
**Solution**: Install Git: `sudo apt install git` (Ubuntu) or `brew install git` (macOS)

### OpenSpec Artifacts Missing

**Problem**: OpenSpecTool shows artifacts missing
**Solution**: Run `/opsx:propose` with detailed project description

---

## Security Notes

- All file operations are scoped to `storage/projects/`
- Directory traversal attempts are blocked and logged
- GitHub token is read from environment, never exposed in responses
- Git operations only work within valid project directories
