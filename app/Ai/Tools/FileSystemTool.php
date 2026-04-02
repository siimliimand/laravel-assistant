<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Symfony\Component\Finder\Finder;

class FileSystemTool implements Tool
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
        return 'Manage project files and directories within the secure storage/projects/ directory. Supports creating projects, reading/writing files, listing project contents, and checking project existence. All paths are validated to prevent directory traversal attacks.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $action = $request['action'] ?? null;

        try {
            return match ($action) {
                'createProject' => $this->createProject($request['name'] ?? ''),
                'writeFile' => $this->writeFile(
                    $request['project'] ?? '',
                    $request['path'] ?? '',
                    $request['content'] ?? ''
                ),
                'readFile' => $this->readFile(
                    $request['project'] ?? '',
                    $request['path'] ?? ''
                ),
                'listFiles' => $this->listFiles($request['project'] ?? ''),
                'projectExists' => $this->projectExists($request['project'] ?? ''),
                default => 'Error: Invalid action. Available actions: createProject, writeFile, readFile, listFiles, projectExists',
            };
        } catch (\Exception $e) {
            $errorMessage = "FileSystemTool error: {$e->getMessage()}";
            Log::error('FileSystemTool: Operation failed', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return $errorMessage;
        }
    }

    /**
     * Create a new project directory.
     */
    protected function createProject(string $name): string
    {
        if (empty($name)) {
            return 'Error: Project name is required.';
        }

        // Convert to kebab-case slug
        $slug = Str::kebab($name);

        // Validate slug
        if (! preg_match('/^[a-z0-9-]+$/', $slug)) {
            return 'Error: Project name must contain only alphanumeric characters and hyphens.';
        }

        $projectPath = $this->basePath.'/'.$slug;

        // Check if project already exists
        if (is_dir($projectPath)) {
            $alternative = $slug.'-'.Str::random(4);

            return "Error: Project '{$slug}' already exists. Consider using '{$alternative}' instead.";
        }

        // Create directory
        if (! mkdir($projectPath, 0755, true)) {
            return "Error: Failed to create project directory at {$projectPath}";
        }

        Log::info('FileSystemTool: Created project', ['project' => $slug, 'path' => $projectPath]);

        return "Project '{$slug}' created successfully at {$projectPath}";
    }

    /**
     * Write a file within a project.
     */
    protected function writeFile(string $project, string $path, string $content): string
    {
        if (empty($project) || empty($path)) {
            return 'Error: Project name and file path are required.';
        }

        // Validate paths
        $validatedPath = $this->validatePath($project, $path);
        if (str_starts_with($validatedPath, 'Error:')) {
            return $validatedPath;
        }

        // Create parent directories if they don't exist
        $parentDir = dirname($validatedPath);
        if (! is_dir($parentDir)) {
            if (! mkdir($parentDir, 0755, true)) {
                return "Error: Failed to create directory {$parentDir}";
            }
        }

        // Write file
        if (file_put_contents($validatedPath, $content) === false) {
            return "Error: Failed to write file at {$validatedPath}";
        }

        Log::info('FileSystemTool: Wrote file', [
            'project' => $project,
            'path' => $path,
            'full_path' => $validatedPath,
        ]);

        return "File written successfully at {$validatedPath}";
    }

    /**
     * Read a file from a project.
     */
    protected function readFile(string $project, string $path): string
    {
        if (empty($project) || empty($path)) {
            return 'Error: Project name and file path are required.';
        }

        // Validate paths
        $validatedPath = $this->validatePath($project, $path);
        if (str_starts_with($validatedPath, 'Error:')) {
            return $validatedPath;
        }

        // Check if file exists
        if (! file_exists($validatedPath)) {
            return "Error: File not found at {$path}";
        }

        // Check if it's a file (not a directory)
        if (! is_file($validatedPath)) {
            return "Error: Path is a directory, not a file: {$path}";
        }

        $content = file_get_contents($validatedPath);
        if ($content === false) {
            return "Error: Failed to read file at {$validatedPath}";
        }

        return $content;
    }

    /**
     * List all files in a project.
     */
    protected function listFiles(string $project): string
    {
        if (empty($project)) {
            return 'Error: Project name is required.';
        }

        $projectPath = $this->basePath.'/'.$project;

        // Validate project path
        if (! $this->isPathSafe($projectPath)) {
            return 'Error: Invalid project path. Directory traversal is not allowed.';
        }

        if (! is_dir($projectPath)) {
            return "Error: Project '{$project}' does not exist.";
        }

        $finder = new Finder;
        $finder->files()->in($projectPath)->ignoreDotFiles(false);

        $files = [];
        foreach ($finder as $file) {
            $relativePath = str_replace($projectPath.'/', '', $file->getPathname());
            $files[] = [
                'path' => $relativePath,
                'size' => $file->getSize(),
            ];
        }

        if (empty($files)) {
            return "Project '{$project}' is empty.";
        }

        return json_encode($files, JSON_PRETTY_PRINT);
    }

    /**
     * Check if a project exists.
     */
    protected function projectExists(string $project): string
    {
        if (empty($project)) {
            return 'Error: Project name is required.';
        }

        $projectPath = $this->basePath.'/'.$project;

        // Validate project path
        if (! $this->isPathSafe($projectPath)) {
            return 'Error: Invalid project path. Directory traversal is not allowed.';
        }

        $exists = is_dir($projectPath);

        return json_encode([
            'exists' => $exists,
            'project' => $project,
            'path' => $projectPath,
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Validate and resolve a file path, ensuring it stays within the project directory.
     */
    protected function validatePath(string $project, string $path): string
    {
        // Check for directory traversal attempts
        if (str_contains($path, '..') || str_starts_with($path, '/')) {
            return 'Error: Invalid path. Directory traversal and absolute paths are not allowed.';
        }

        $fullPath = realpath($this->basePath.'/'.$project.'/'.$path);

        // If file doesn't exist yet, construct the path and validate it
        if ($fullPath === false) {
            $fullPath = $this->basePath.'/'.$project.'/'.$path;
        }

        // Ensure the resolved path is within the base path
        if (! $this->isPathSafe($fullPath)) {
            Log::warning('FileSystemTool: Blocked path traversal attempt', [
                'project' => $project,
                'path' => $path,
                'resolved' => $fullPath,
            ]);

            return 'Error: Invalid path. Access outside the project directory is not allowed.';
        }

        return $fullPath;
    }

    /**
     * Check if a path is safely within the base directory.
     */
    protected function isPathSafe(string $path): bool
    {
        $realBase = realpath($this->basePath);
        $realPath = realpath($path);

        // If path doesn't exist yet, check the constructed path
        if ($realPath === false) {
            return str_starts_with($path, $realBase.'/') || $path === $realBase;
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
            'action' => $schema->string('The action to perform. One of: createProject, writeFile, readFile, listFiles, projectExists'),
            'name' => $schema->string('Project name for createProject action (will be converted to kebab-case)'),
            'project' => $schema->string('Project slug for file operations'),
            'path' => $schema->string('Relative file path within the project (for writeFile and readFile)'),
            'content' => $schema->string('File content (for writeFile)'),
        ];
    }
}
