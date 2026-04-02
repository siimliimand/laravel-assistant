<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GitHubTool implements Tool
{
    protected ?string $token;

    public function __construct()
    {
        $this->token = config('ai.projects.github_token');
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Interact with the GitHub API to create repositories, retrieve repository information, and validate authentication. Requires a valid GitHub personal access token with repo scope.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $action = $request['action'] ?? null;

        try {
            // Validate token
            if (empty($this->token)) {
                return 'Error: GitHub token is not configured. Please set GITHUB_TOKEN in your .env file.';
            }

            return match ($action) {
                'createRepo' => $this->createRepo(
                    $request['name'] ?? '',
                    $request['private'] ?? false
                ),
                'getRepoInfo' => $this->getRepoInfo($request['name'] ?? ''),
                'validateToken' => $this->validateToken(),
                default => 'Error: Invalid action. Available actions: createRepo, getRepoInfo, validateToken',
            };
        } catch (\Exception $e) {
            $errorMessage = "GitHubTool error: {$e->getMessage()}";
            Log::error('GitHubTool: Operation failed', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return $errorMessage;
        }
    }

    /**
     * Create a GitHub repository.
     */
    protected function createRepo(string $name, bool $private = false): string
    {
        if (empty($name)) {
            return 'Error: Repository name is required.';
        }

        $response = Http::withToken($this->token)
            ->timeout(30)
            ->post('https://api.github.com/user/repos', [
                'name' => $name,
                'private' => $private,
                'auto_init' => false,
            ]);

        if ($response->failed()) {
            $body = $response->json();

            // Handle specific errors
            if ($response->status() === 401) {
                return 'Error: GitHub token is invalid or expired. Please regenerate your token.';
            }

            if ($response->status() === 403) {
                return 'Error: GitHub API rate limit exceeded. Please try again later or check your token scopes.';
            }

            if ($response->status() === 422 && isset($body['errors'][0]['message'])) {
                $errorMsg = $body['errors'][0]['message'];
                if (str_contains($errorMsg, 'already exists')) {
                    return "Error: Repository '{$name}' already exists. Consider using a different name.";
                }

                return "Error: {$errorMsg}";
            }

            return "Error: Failed to create repository: {$response->body()}";
        }

        $repo = $response->json();

        Log::info('GitHubTool: Created repository', [
            'name' => $name,
            'url' => $repo['html_url'] ?? null,
        ]);

        return json_encode([
            'success' => true,
            'name' => $repo['name'],
            'url' => $repo['html_url'],
            'clone_url' => $repo['clone_url'],
            'ssh_url' => $repo['ssh_url'],
            'private' => $repo['private'],
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Get repository information.
     */
    protected function getRepoInfo(string $name): string
    {
        if (empty($name)) {
            return 'Error: Repository name is required.';
        }

        // Get authenticated user
        $userResponse = Http::withToken($this->token)
            ->timeout(15)
            ->get('https://api.github.com/user');

        if ($userResponse->failed()) {
            return 'Error: Failed to authenticate with GitHub API.';
        }

        $username = $userResponse->json()['login'] ?? null;

        if (! $username) {
            return 'Error: Could not determine GitHub username.';
        }

        // Get repository info
        $response = Http::withToken($this->token)
            ->timeout(15)
            ->get("https://api.github.com/repos/{$username}/{$name}");

        if ($response->failed()) {
            if ($response->status() === 404) {
                return "Error: Repository '{$name}' not found.";
            }

            return "Error: Failed to fetch repository info: {$response->body()}";
        }

        $repo = $response->json();

        return json_encode([
            'name' => $repo['name'],
            'full_name' => $repo['full_name'],
            'url' => $repo['html_url'],
            'clone_url' => $repo['clone_url'],
            'ssh_url' => $repo['ssh_url'],
            'description' => $repo['description'] ?? '',
            'private' => $repo['private'],
            'default_branch' => $repo['default_branch'],
            'created_at' => $repo['created_at'],
            'updated_at' => $repo['updated_at'],
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Validate GitHub token.
     */
    protected function validateToken(): string
    {
        $response = Http::withToken($this->token)
            ->timeout(15)
            ->get('https://api.github.com/user');

        if ($response->failed()) {
            if ($response->status() === 401) {
                return 'Error: GitHub token is invalid or expired.';
            }

            return "Error: Token validation failed: {$response->body()}";
        }

        $user = $response->json();

        // Get scopes from headers
        $scopes = $response->header('X-OAuth-Scopes', '');

        Log::info('GitHubTool: Token validated', [
            'user' => $user['login'] ?? null,
            'scopes' => $scopes,
        ]);

        return json_encode([
            'valid' => true,
            'username' => $user['login'],
            'name' => $user['name'] ?? '',
            'scopes' => $scopes,
            'message' => 'Token is valid',
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string('The action to perform. One of: createRepo, getRepoInfo, validateToken'),
            'name' => $schema->string('Repository name (for createRepo and getRepoInfo)'),
            'private' => $schema->boolean('Whether the repository should be private (for createRepo, defaults to false)'),
        ];
    }
}
