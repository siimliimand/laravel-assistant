<?php

namespace App\Ai\Tools;

use App\Services\McpClientService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchDocsTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search Laravel and package documentation for up-to-date version-specific documentation. Returns relevant documentation snippets with links. Can scope results to specific packages.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $queries = $request['queries'] ?? null;
        $packages = $request['packages'] ?? null;
        $tokenLimit = $request['token_limit'] ?? 3000;

        // Validate queries parameter
        if (! is_array($queries) || empty($queries)) {
            return 'Error: The "queries" parameter is required and must be a non-empty array of search queries.';
        }

        try {
            // Call MCP server via McpClientService
            $mcpClient = app(McpClientService::class);
            $arguments = [
                'queries' => $queries,
                'token_limit' => (int) $tokenLimit,
            ];

            if (is_array($packages) && ! empty($packages)) {
                $arguments['packages'] = $packages;
            }

            return $mcpClient->callTool('search-docs', $arguments);
        } catch (\Exception $e) {
            $errorMessage = "Documentation search error: {$e->getMessage()}";
            Log::error('SearchDocsTool: Search failed', [
                'queries' => $queries,
                'error' => $e->getMessage(),
            ]);

            return $errorMessage;
        }
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'queries' => $schema->array('List of search queries to perform. Pass multiple queries if unsure about terminology.'),
            'packages' => $schema->array('Optional package names to limit searching to (e.g., ["laravel/framework", "pestphp/pest"]).'),
            'token_limit' => $schema->integer('Optional maximum number of tokens to return. Defaults to 3000.'),
        ];
    }
}
