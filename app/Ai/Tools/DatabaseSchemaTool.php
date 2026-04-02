<?php

namespace App\Ai\Tools;

use App\Services\McpClientService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class DatabaseSchemaTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Read the database schema for this application, including table names, columns, data types, indexes, and foreign keys. Can list all tables or get details for a specific table.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $tableName = $request['table'] ?? null;
        $connection = $request['database'] ?? null;

        try {
            // Call MCP server via McpClientService
            $mcpClient = app(McpClientService::class);
            $arguments = [];

            if ($tableName) {
                $arguments['table'] = $tableName;
            }

            if ($connection) {
                $arguments['database'] = $connection;
            }

            return $mcpClient->callTool('database-schema', $arguments);
        } catch (\Exception $e) {
            $errorMessage = "Database schema error: {$e->getMessage()}";
            Log::error('DatabaseSchemaTool: Schema retrieval failed', [
                'table' => $tableName ?? null,
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
            'table' => $schema->string('Optional table name to get schema for. If not provided, lists all tables.'),
            'database' => $schema->string('Optional database connection name. Defaults to the application\'s default connection.'),
        ];
    }
}
