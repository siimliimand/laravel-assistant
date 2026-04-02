<?php

namespace App\Ai\Tools;

use App\Services\McpClientService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class DatabaseQueryTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Execute a read-only SQL query against the application database. Only SELECT, SHOW, EXPLAIN, and DESCRIBE statements are allowed. Results are limited to 100 rows.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = $request['query'] ?? null;
        $connection = $request['database'] ?? null;

        try {
            // Validate that the query is read-only
            $trimmedQuery = strtoupper(trim($query));
            $allowedPrefixes = ['SELECT', 'SHOW', 'EXPLAIN', 'DESCRIBE'];
            $isAllowed = false;

            foreach ($allowedPrefixes as $prefix) {
                if (str_starts_with($trimmedQuery, $prefix)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (! $isAllowed) {
                $message = 'Error: Only read-only queries are allowed (SELECT, SHOW, EXPLAIN, DESCRIBE).';
                Log::warning('DatabaseQueryTool: Blocked non-read-only query', ['query' => $query]);

                return $message;
            }

            // Call MCP server via McpClientService
            $mcpClient = app(McpClientService::class);
            $arguments = ['query' => $query];

            if ($connection) {
                $arguments['database'] = $connection;
            }

            return $mcpClient->callTool('database-query', $arguments);
        } catch (\Exception $e) {
            $errorMessage = "Database query error: {$e->getMessage()}";
            Log::error('DatabaseQueryTool: Query execution failed', [
                'query' => $query,
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
            'query' => $schema->string('The SQL query to execute. Only read-only queries are allowed (SELECT, SHOW, EXPLAIN, DESCRIBE).'),
            'database' => $schema->string('Optional database connection name to use. Defaults to the application\'s default connection.'),
        ];
    }
}
