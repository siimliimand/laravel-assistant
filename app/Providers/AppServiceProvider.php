<?php

namespace App\Providers;

use App\Services\McpClientService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register MCP Client Service as singleton
        $this->app->singleton(McpClientService::class, function ($app) {
            return new McpClientService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->validateMcpClientConfiguration();

        // Register graceful shutdown for MCP client
        $this->app->terminating(function () {
            if ($this->app->resolved(McpClientService::class)) {
                $this->app->make(McpClientService::class)->terminate();
            }
        });
    }

    /**
     * Validate MCP client configuration to ensure Artisan command exists and settings are valid.
     */
    protected function validateMcpClientConfiguration(): void
    {
        $timeout = config('services.mcp_client.timeout');
        $maxRetries = config('services.mcp_client.max_retries');
        $retryDelay = config('services.mcp_client.retry_delay');

        // Validate timeout is a positive integer
        if (! is_int($timeout) || $timeout <= 0) {
            Log::warning('MCP Client: Invalid timeout configuration', ['timeout' => $timeout]);
        }

        // Validate max_retries is a non-negative integer
        if (! is_int($maxRetries) || $maxRetries < 0) {
            Log::warning('MCP Client: Invalid max_retries configuration', ['max_retries' => $maxRetries]);
        }

        // Validate retry_delay is a positive integer
        if (! is_int($retryDelay) || $retryDelay <= 0) {
            Log::warning('MCP Client: Invalid retry_delay configuration', ['retry_delay' => $retryDelay]);
        }

        // Note: We skip command validation here to avoid spawning processes on every request
        // The command will be validated when the MCP client actually tries to connect
    }
}
