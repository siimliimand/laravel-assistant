<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class GitTool implements Tool
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = config('ai.projects.base_path', storage_path('projects'));
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Execute Git operations within project directories. Supports initializing repositories, staging files, committing changes, managing remotes, and pushing to GitHub. All operations are scoped to storage/projects/.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $action = $request['action'] ?? null;

        try {
            // Check Git availability
            if (! $this->isGitAvailable()) {
                return 'Error: Git is not installed on this system. Please install Git to use this tool.';
            }

            return match ($action) {
                'init' => $this->init($request['project'] ?? ''),
                'add' => $this->add(
                    $request['project'] ?? '',
                    $request['files'] ?? null
                ),
                'commit' => $this->commit(
                    $request['project'] ?? '',
                    $request['message'] ?? ''
                ),
                'remoteAdd' => $this->remoteAdd(
                    $request['project'] ?? '',
                    $request['name'] ?? 'origin',
                    $request['url'] ?? ''
                ),
                'push' => $this->push(
                    $request['project'] ?? '',
                    $request['branch'] ?? 'main'
                ),
                'status' => $this->status($request['project'] ?? ''),
                default => 'Error: Invalid action. Available actions: init, add, commit, remoteAdd, push, status',
            };
        } catch (\Exception $e) {
            $errorMessage = "GitTool error: {$e->getMessage()}";
            Log::error('GitTool: Operation failed', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return $errorMessage;
        }
    }

    /**
     * Initialize a Git repository.
     */
    protected function init(string $project): string
    {
        if (empty($project)) {
            return 'Error: Project name is required.';
        }

        $projectPath = $this->getProjectPath($project);
        if (! $this->validateProjectPath($projectPath)) {
            return 'Error: Invalid project path.';
        }

        $defaultBranch = config('ai.projects.default_branch', 'main');

        $process = new Process(['git', 'init', '-b', $defaultBranch], $projectPath);
        $process->run();

        if (! $process->isSuccessful()) {
            return "Error: Failed to initialize Git repository: {$process->getErrorOutput()}";
        }

        // Configure Git user if not set
        $this->configureGitUser($projectPath);

        Log::info('GitTool: Initialized repository', ['project' => $project]);

        return "Git repository initialized in {$project} with default branch '{$defaultBranch}'";
    }

    /**
     * Stage files.
     */
    protected function add(string $project, ?array $files = null): string
    {
        if (empty($project)) {
            return 'Error: Project name is required.';
        }

        $projectPath = $this->getProjectPath($project);
        if (! $this->validateProjectPath($projectPath)) {
            return 'Error: Invalid project path.';
        }

        $filesToAdd = $files ?? ['.'];
        $process = new Process(array_merge(['git', 'add'], $filesToAdd), $projectPath);
        $process->run();

        if (! $process->isSuccessful()) {
            return "Error: Failed to stage files: {$process->getErrorOutput()}";
        }

        Log::info('GitTool: Staged files', [
            'project' => $project,
            'files' => $filesToAdd,
        ]);

        $fileList = is_array($files) ? implode(', ', $files) : 'all files';

        return "Staged {$fileList}";
    }

    /**
     * Commit changes.
     */
    protected function commit(string $project, string $message): string
    {
        if (empty($project) || empty($message)) {
            return 'Error: Project name and commit message are required.';
        }

        $projectPath = $this->getProjectPath($project);
        if (! $this->validateProjectPath($projectPath)) {
            return 'Error: Invalid project path.';
        }

        $process = new Process(['git', 'commit', '-m', $message], $projectPath);
        $process->run();

        if (! $process->isSuccessful()) {
            return "Error: Failed to commit: {$process->getErrorOutput()}";
        }

        $output = trim($process->getOutput());

        Log::info('GitTool: Committed changes', [
            'project' => $project,
            'message' => $message,
        ]);

        return "Committed: {$message}\n{$output}";
    }

    /**
     * Add a remote.
     */
    protected function remoteAdd(string $project, string $name, string $url): string
    {
        if (empty($project) || empty($url)) {
            return 'Error: Project name and remote URL are required.';
        }

        $projectPath = $this->getProjectPath($project);
        if (! $this->validateProjectPath($projectPath)) {
            return 'Error: Invalid project path.';
        }

        $process = new Process(['git', 'remote', 'add', $name, $url], $projectPath);
        $process->run();

        if (! $process->isSuccessful()) {
            return "Error: Failed to add remote: {$process->getErrorOutput()}";
        }

        Log::info('GitTool: Added remote', [
            'project' => $project,
            'name' => $name,
            'url' => $url,
        ]);

        return "Remote '{$name}' added: {$url}";
    }

    /**
     * Push to remote.
     */
    protected function push(string $project, string $branch = 'main'): string
    {
        if (empty($project)) {
            return 'Error: Project name is required.';
        }

        $projectPath = $this->getProjectPath($project);
        if (! $this->validateProjectPath($projectPath)) {
            return 'Error: Invalid project path.';
        }

        $process = new Process(['git', 'push', '-u', 'origin', $branch], $projectPath);
        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            return "Error: Failed to push: {$process->getErrorOutput()}";
        }

        Log::info('GitTool: Pushed to remote', [
            'project' => $project,
            'branch' => $branch,
        ]);

        return "Pushed to origin/{$branch}";
    }

    /**
     * Get repository status.
     */
    protected function status(string $project): string
    {
        if (empty($project)) {
            return 'Error: Project name is required.';
        }

        $projectPath = $this->getProjectPath($project);
        if (! $this->validateProjectPath($projectPath)) {
            return 'Error: Invalid project path.';
        }

        $process = new Process(['git', 'status', '--short'], $projectPath);
        $process->run();

        if (! $process->isSuccessful()) {
            return "Error: Failed to get status: {$process->getErrorOutput()}";
        }

        $statusOutput = trim($process->getOutput());

        if (empty($statusOutput)) {
            return 'Repository is clean (no changes)';
        }

        return $statusOutput;
    }

    /**
     * Check if Git is available.
     */
    protected function isGitAvailable(): bool
    {
        $finder = new ExecutableFinder;

        return $finder->find('git') !== null;
    }

    /**
     * Configure Git user for the repository.
     */
    protected function configureGitUser(string $projectPath): void
    {
        $userName = config('ai.projects.git_user_name', 'DevBot');
        $userEmail = config('ai.projects.git_user_email', 'devbot@localhost');

        (new Process(['git', 'config', 'user.name', $userName], $projectPath))->run();
        (new Process(['git', 'config', 'user.email', $userEmail], $projectPath))->run();
    }

    /**
     * Get the full project path.
     */
    protected function getProjectPath(string $project): string
    {
        return $this->basePath.'/'.$project;
    }

    /**
     * Validate that the project path is safe.
     */
    protected function validateProjectPath(string $path): bool
    {
        $realBase = realpath($this->basePath);
        $realPath = realpath($path);

        if ($realPath === false) {
            return false;
        }

        return str_starts_with($realPath, $realBase.'/') || $realPath === $realBase;
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string('The Git action to perform. One of: init, add, commit, remoteAdd, push, status'),
            'project' => $schema->string('Project slug'),
            'files' => $schema->array('Optional list of specific files to stage (defaults to all files)'),
            'message' => $schema->string('Commit message (for commit action)'),
            'name' => $schema->string('Remote name (for remoteAdd action, defaults to "origin")'),
            'url' => $schema->string('Remote URL (for remoteAdd action)'),
            'branch' => $schema->string('Branch name to push (defaults to "main")'),
        ];
    }
}
