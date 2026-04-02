<?php

namespace App\Ai\Tools;

use App\Services\McpClientService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class TinkerTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Execute PHP code in the Laravel application context, similar to artisan tinker. Useful for debugging, checking if functions exist, and testing code snippets. Returns the output and return value. Prefer using existing Artisan commands over custom code.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $code = $request['code'] ?? null;
        $timeout = $request['timeout'] ?? 30;

        // Validate code parameter
        if (empty($code)) {
            return 'Error: The "code" parameter is required and must contain PHP code to execute.';
        }

        try {
            // Strip PHP opening tags before sending to MCP server
            $code = $this->stripPhpTags($code);

            // Call MCP server via McpClientService
            $mcpClient = app(McpClientService::class);
            $arguments = [
                'code' => $code,
                'timeout' => min((int) $timeout, 60),
            ];

            return $mcpClient->callTool('tinker', $arguments);
        } catch (\Throwable $e) {
            $errorMessage = "Tinker execution error: {$e->getMessage()}";
            Log::error('TinkerTool: Code execution failed', [
                'code' => $code,
                'error' => $e->getMessage(),
            ]);

            return $errorMessage;
        }
    }

    /**
     * Strip PHP opening tags from code.
     *
     * Removes <?php and <? opening tags to ensure clean code
     * is sent to the MCP server.
     */
    protected function stripPhpTags(string $code): string
    {
        // Strip <?php tag (with optional whitespace after)
        $code = preg_replace('/^<\?php\s*/i', '', $code);

        // Strip short <? tag (with optional whitespace after)
        $code = preg_replace('/^<\?\s*/', '', $code);

        return trim($code);
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'code' => $schema->string('PHP code to execute (without opening <?php tags)'),
            'timeout' => $schema->integer('Maximum execution time in seconds (default: 30, max: 60)'),
        ];
    }
}
