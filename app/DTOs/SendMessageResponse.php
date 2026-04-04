<?php

namespace App\DTOs;

use App\Models\Conversation;
use App\Models\Message;

/**
 * DTO for standardized send message response.
 *
 * Encapsulates all response data from message sending operations,
 * providing methods to format responses for different contexts.
 *
 * Usage:
 * ```php
 * $response = new SendMessageResponse(
 *     conversation: $conversation,
 *     assistantMessage: $message,
 *     success: true
 * );
 *
 * // For JSON responses:
 * return response()->json($response->toJsonData());
 *
 * // For checking success:
 * if ($response->isSuccessful()) { ... }
 * ```
 */
class SendMessageResponse
{
    public function __construct(
        public readonly Conversation $conversation,
        public readonly Message $assistantMessage,
        public readonly bool $success = true,
        public readonly ?string $errorMessage = null,
    ) {}

    /**
     * Check if the operation was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Get data formatted for JSON API response.
     *
     * @return array{
     *     success: bool,
     *     response?: string,
     *     conversation_id?: int,
     *     conversation_title?: string,
     *     message?: string
     * }
     */
    public function toJsonData(): array
    {
        if (! $this->success) {
            return [
                'success' => false,
                'message' => $this->errorMessage ?? 'An error occurred processing your message.',
            ];
        }

        return [
            'success' => true,
            'response' => $this->assistantMessage->formattedContent(),
            'conversation_id' => $this->conversation->id,
            'conversation_title' => $this->conversation->title,
        ];
    }

    /**
     * Get error message for failed operations.
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Create a successful response.
     */
    public static function success(Conversation $conversation, Message $assistantMessage): self
    {
        return new self(
            conversation: $conversation,
            assistantMessage: $assistantMessage,
            success: true,
        );
    }

    /**
     * Create a failed response.
     */
    public static function failure(string $errorMessage): self
    {
        return new self(
            conversation: new Conversation,
            assistantMessage: new Message,
            success: false,
            errorMessage: $errorMessage,
        );
    }
}
