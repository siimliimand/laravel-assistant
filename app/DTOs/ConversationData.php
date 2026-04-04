<?php

namespace App\DTOs;

use Illuminate\Http\Request;

/**
 * Data Transfer Object for conversation creation.
 *
 * Immutable data object for transferring conversation data between layers.
 * Use this instead of passing raw arrays.
 *
 * Usage:
 * ```php
 * // From request:
 * $data = ConversationData::fromRequest($request);
 *
 * // Manual instantiation:
 * $data = new ConversationData(
 *     title: 'My Chat',
 *     initialMessage: 'Hello!',
 * );
 *
 * // Access properties:
 * echo $data->title;
 * echo $data->initialMessage;
 * ```
 */
final readonly class ConversationData
{
    public function __construct(
        public ?string $title = null,
        public ?string $initialMessage = null,
    ) {}

    /**
     * Create DTO from HTTP request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            title: $request->input('title'),
            initialMessage: $request->input('message'),
        );
    }

    /**
     * Create DTO with just an initial message (for auto-generated conversations).
     */
    public static function fromMessage(string $message): self
    {
        return new self(
            title: null,
            initialMessage: $message,
        );
    }
}
