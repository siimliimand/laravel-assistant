<?php

namespace App\DTOs;

use Illuminate\Http\Request;

/**
 * Data Transfer Object for chat messages.
 *
 * Immutable data object for transferring message data between layers.
 * Use this instead of passing raw arrays.
 *
 * Usage:
 * ```php
 * // From request:
 * $data = MessageData::fromRequest($request);
 *
 * // Manual instantiation:
 * $data = new MessageData(
 *     content: 'Hello, world!',
 *     conversationId: 123,
 * );
 *
 * // Access properties:
 * echo $data->content;
 * echo $data->conversationId;
 * ```
 */
final readonly class MessageData
{
    public function __construct(
        public string $content,
        public ?int $conversationId = null,
    ) {}

    /**
     * Create DTO from HTTP request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            content: $request->input('message'),
            conversationId: $request->integer('conversation_id'),
        );
    }
}
