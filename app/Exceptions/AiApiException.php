<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when AI API calls fail.
 *
 * This exception wraps external AI API errors with domain-specific context
 * including conversation ID and user ID for better error tracking and debugging.
 *
 * Usage:
 * ```php
 * throw new AiApiException(
 *     'Failed to get AI response',
 *     conversationId: 123,
 *     userId: 456,
 *     previous: $originalException
 * );
 * ```
 */
class AiApiException extends RuntimeException
{
    /**
     * Create a new AI API exception instance.
     *
     * @param  string  $message  The exception message
     * @param  int|null  $conversationId  The ID of the conversation being processed
     * @param  int|null  $userId  The ID of the user who initiated the request
     * @param  Throwable|null  $previous  The previous exception used for exception chaining
     */
    public function __construct(
        string $message = 'AI API request failed',
        public readonly ?int $conversationId = null,
        public readonly ?int $userId = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $previous?->getCode() ?? 0, $previous);
    }

    /**
     * Get the exception context for logging.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        $previous = $this->getPrevious();

        return [
            'conversation_id' => $this->conversationId,
            'user_id' => $this->userId,
            'original_exception' => $previous ? get_class($previous) : null,
            'original_message' => $previous?->getMessage(),
        ];
    }
}
