## 1. Configuration Setup

- [x] 1.1 Add `projects` configuration to `config/ai.php` with base_path, github_token, default_branch settings
- [x] 1.2 Add `GITHUB_TOKEN` to `.env.example`
- [x] 1.3 Create `storage/projects/` directory and add to `.gitignore`

## 2. FileSystemTool Implementation

- [x] 2.1 Create `app/Ai/Tools/FileSystemTool.php` implementing Laravel\Ai\Contracts\Tool
- [x] 2.2 Implement `createProject(string $name)` method with path validation
- [x] 2.3 Implement `writeFile(string $project, string $path, string $content)` method
- [x] 2.4 Implement `readFile(string $project, string $path)` method
- [x] 2.5 Implement `listFiles(string $project)` method
- [x] 2.6 Implement `projectExists(string $project)` method
- [x] 2.7 Add path traversal validation to prevent access outside `storage/projects/`
- [x] 2.8 Write unit tests for FileSystemTool

## 3. GitTool Implementation

- [x] 3.1 Create `app/Ai/Tools/GitTool.php` implementing Laravel\Ai\Contracts\Tool
- [x] 3.2 Implement `init(string $projectPath)` method using Symfony Process
- [x] 3.3 Implement `add(string $projectPath, ?array $files = null)` method
- [x] 3.4 Implement `commit(string $projectPath, string $message)` method
- [x] 3.5 Implement `remoteAdd(string $projectPath, string $name, string $url)` method
- [x] 3.6 Implement `push(string $projectPath, string $branch = 'main')` method
- [x] 3.7 Implement `status(string $projectPath)` method
- [x] 3.8 Add Git availability check and error handling
- [x] 3.9 Write unit tests for GitTool

## 4. GitHubTool Implementation

- [x] 4.1 Create `app/Ai/Tools/GitHubTool.php` implementing Laravel\Ai\Contracts\Tool
- [x] 4.2 Implement `createRepo(string $name, bool $private = false)` method using Laravel HTTP client
- [x] 4.3 Implement `getRepoInfo(string $name)` method
- [x] 4.4 Implement `validateToken()` method
- [x] 4.5 Add error handling for API rate limits and authentication errors
- [x] 4.6 Write unit tests for GitHubTool

## 5. OpenSpecTool Implementation

- [x] 5.1 Create `app/Ai/Tools/OpenSpecTool.php` implementing Laravel\Ai\Contracts\Tool
- [x] 5.2 Implement `getStatus(string $projectPath)` method
- [x] 5.3 Implement `getInstructions(string $projectPath)` method
- [x] 5.4 Write unit tests for OpenSpecTool

## 6. Project Creation Skill

- [x] 6.1 Create `.agents/skills/project-creation/SKILL.md` with workflow guidance
- [x] 6.2 Document the complete workflow steps
- [x] 6.3 Add error recovery guidance for each step
- [x] 6.4 List available tools for each workflow stage

## 7. DevBot Agent Updates

- [x] 7.1 Update `app/Ai/Agents/DevBot.php` to include new tools in tools() method
- [x] 7.2 Add project creation instructions to DevBot system prompt
- [x] 7.3 Reference Project Creation skill in instructions

## 8. Testing and Verification

- [x] 8.1 Write feature test for complete project creation workflow
- [x] 8.2 Write feature test for FileSystemTool integration with DevBot
- [x] 8.3 Write feature test for GitTool integration with DevBot
- [x] 8.4 Write feature test for GitHubTool integration with DevBot
- [x] 8.5 Run full test suite to ensure no regressions
- [x] 8.6 Run `vendor/bin/pint --dirty` to format all new PHP files
