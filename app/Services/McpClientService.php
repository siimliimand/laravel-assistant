<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpMcp\Client\Client;
use PhpMcp\Client\Enum\TransportType;
use PhpMcp\Client\Exception\McpClientException;
use PhpMcp\Client\Model\Capabilities as ClientCapabilities;
use PhpMcp\Client\ServerConfig;
use Throwable;

/**
 * MCP Client Service for managing connections to the Laravel Boost MCP server.
 *
 * This service provides a centralized interface for calling MCP tools through
 * the php-mcp/client library. It manages connection lifecycle, implements
 * auto-reconnect logic, and provides comprehensive logging.
 */
class McpClientService
{
    /**
     * The MCP client instance.
     */
    protected ?Client $client = null;

    /**
     * Whether the client has been initialized.
     */
    protected bool $initialized = false;

    /**
     * Create a new McpClientService instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Initialize the MCP client connection to the Boost server.
     *
     * Creates a new client instance with STDIO transport, configures
     * server parameters, and performs the MCP handshake.
     *
     * @throws McpClientException If connection or handshake fails
     */
    public function initialize(): void
    {
        if ($this->initialized && $this->client?->isReady()) {
            return;
        }

        try {
            $command = config('services.mcp_client.command');
            $timeout = config('services.mcp_client.timeout', 60);

            // Parse the command string into command and args
            $parts = explode(' ', $command);
            $executable = array_shift($parts);
            $args = $parts;

            $serverConfig = new ServerConfig(
                name: 'laravel-boost',
                transport: TransportType::Stdio,
                timeout: (float) $timeout,
                command: $executable,
                args: $args,
                workingDir: base_path()
            );

            $this->client = Client::make()
                ->withClientInfo('Laravel-Assistant-DevBot', '1.0')
                ->withCapabilities(ClientCapabilities::forClient())
                ->withServerConfig($serverConfig)
                ->build();

            $this->client->initialize();
            $this->initialized = true;

            Log::info('MCP Client: Connected to Boost server', [
                'server_name' => $this->client->getServerName(),
                'server_version' => $this->client->getServerVersion(),
            ]);
        } catch (Throwable $e) {
            Log::error('MCP Client: Failed to initialize connection', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->initialized = false;
            $this->client = null;

            throw $e;
        }
    }

    /**
     * Call an MCP tool on the Boost server.
     *
     * Automatically initializes the connection if not already connected.
     * Implements auto-reconnect logic on connection failures.
     *
     * @param  string  $toolName  The name of the tool to call
     * @param  array  $arguments  The arguments to pass to the tool
     * @return string The tool response as a string
     *
     * @throws McpClientException If the tool call fails
     */
    public function callTool(string $toolName, array $arguments = []): string
    {
        $maxRetries = config('services.mcp_client.max_retries', 3);
        $retryDelay = config('services.mcp_client.retry_delay', 1000);

        $attempt = 0;

        while ($attempt <= $maxRetries) {
            try {
                // Ensure client is initialized
                if (! $this->initialized || ! $this->client?->isReady()) {
                    $this->initialize();
                }

                Log::info('MCP Client: Calling tool', [
                    'tool' => $toolName,
                    'arguments' => $arguments,
                    'attempt' => $attempt + 1,
                ]);

                $result = $this->client->callTool($toolName, $arguments);

                // Extract text content from the result
                $textContent = $this->extractTextContent($result);

                Log::info('MCP Client: Tool call successful', [
                    'tool' => $toolName,
                    'response_length' => strlen($textContent),
                ]);

                return $textContent;
            } catch (McpClientException $e) {
                $attempt++;

                Log::warning('MCP Client: Tool call failed', [
                    'tool' => $toolName,
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage(),
                ]);

                // If we've exhausted retries, throw the exception
                if ($attempt > $maxRetries) {
                    Log::error('MCP Client: Max retries exceeded', [
                        'tool' => $toolName,
                        'error' => $e->getMessage(),
                    ]);

                    throw $e;
                }

                // Reset client state to force reconnection
                $this->disconnect();

                // Wait before retrying (exponential backoff)
                $delayMs = $retryDelay * pow(2, $attempt - 1);
                usleep($delayMs * 1000);
            } catch (Throwable $e) {
                Log::error('MCP Client: Unexpected error during tool call', [
                    'tool' => $toolName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        }

        throw new McpClientException("Tool call failed after {$maxRetries} retries");
    }

    /**
     * Disconnect from the MCP server and clean up resources.
     *
     * Gracefully closes the connection and resets the client state.
     */
    public function disconnect(): void
    {
        if ($this->client) {
            try {
                $this->client->disconnect();

                Log::info('MCP Client: Disconnected from Boost server');
            } catch (Throwable $e) {
                Log::warning('MCP Client: Error during disconnect', [
                    'error' => $e->getMessage(),
                ]);
            }

            $this->client = null;
            $this->initialized = false;
        }
    }

    /**
     * Check if the client is connected and ready.
     */
    public function isConnected(): bool
    {
        return $this->initialized && $this->client?->isReady();
    }

    /**
     * Get the MCP client instance.
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * Extract text content from CallToolResult.
     *
     * The MCP client returns a CallToolResult object which may contain
     * multiple content items. This method extracts all text content
     * and concatenates it into a single string.
     */
    protected function extractTextContent(mixed $result): string
    {
        if (is_string($result)) {
            return $result;
        }

        if (is_object($result) && property_exists($result, 'content')) {
            $texts = [];

            foreach ($result->content as $item) {
                if (is_string($item)) {
                    $texts[] = $item;
                } elseif (is_object($item) && property_exists($item, 'text')) {
                    $texts[] = $item->text;
                } elseif (is_object($item) && property_exists($item, 'data')) {
                    $texts[] = json_encode($item->data);
                }
            }

            return implode("\n", $texts);
        }

        // Fallback: convert to JSON
        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Handle graceful shutdown.
     *
     * Called by the service provider's terminate method to ensure
     * the MCP connection is properly closed.
     */
    public function terminate(): void
    {
        $this->disconnect();
    }

    /**
     * Destroy the client instance.
     */
    public function __destruct()
    {
        // Only disconnect if client was initialized (avoid facade issues during early destruction)
        if ($this->client !== null) {
            try {
                $this->client->disconnect();
            } catch (Throwable $e) {
                // Silently ignore errors during destruction
            }
        }
    }
}
