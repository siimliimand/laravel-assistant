<?php

namespace App\Actions;

use Throwable;

/**
 * Base Action class for single-responsibility business logic.
 *
 * All actions should extend this class and implement an execute() method.
 * This provides common error handling patterns and conventions.
 *
 * Usage:
 * ```php
 * class CreateConversationAction extends BaseAction
 * {
 *     public function execute(ConversationData $data): Conversation
 *     {
 *         // Implementation
 *     }
 * }
 *
 * // Usage in controller:
 * $action = app(CreateConversationAction::class);
 * $conversation = $action->execute($data);
 * ```
 */
abstract class BaseAction
{
    /**
     * Handle exceptions that occur during action execution.
     * Override this method in child classes for custom error handling.
     *
     * @throws Throwable
     */
    protected function handleException(Throwable $exception): never
    {
        throw $exception;
    }

    /**
     * Execute the action with error handling.
     * Wraps the execute call in try-catch.
     *
     * @param  callable  $callback  The execute method to run
     *
     * @throws Throwable
     */
    protected function run(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }
}
