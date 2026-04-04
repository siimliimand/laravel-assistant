<?php

namespace App\DTOs;

/**
 * Data Transfer Object for standardized API responses.
 *
 * Immutable data object for structuring API responses consistently.
 * Use this to ensure all API responses follow the same format.
 *
 * Usage:
 * ```php
 * // Success response:
 * $response = ApiResponseData::success([
 *     'conversation_id' => 123,
 *     'title' => 'My Chat',
 * ]);
 *
 * // Error response:
 * $response = ApiResponseData::error('Something went wrong', 500);
 *
 * // With metadata:
 * $response = new ApiResponseData(
 *     success: true,
 *     data: ['id' => 123],
 *     message: 'Created successfully',
 *     meta: ['conversation_id' => 123],
 * );
 * ```
 */
final readonly class ApiResponseData
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public ?string $message = null,
        public array $meta = [],
        public int $statusCode = 200,
    ) {}

    /**
     * Create a success response.
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        array $meta = [],
        int $statusCode = 200,
    ): self {
        return new self(
            success: true,
            data: $data,
            message: $message,
            meta: $meta,
            statusCode: $statusCode,
        );
    }

    /**
     * Create an error response.
     */
    public static function error(
        string $message,
        int $statusCode = 400,
        mixed $data = null,
        array $meta = [],
    ): self {
        return new self(
            success: false,
            data: $data,
            message: $message,
            meta: $meta,
            statusCode: $statusCode,
        );
    }

    /**
     * Convert to array for JSON response.
     */
    public function toArray(): array
    {
        return array_filter([
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'meta' => $this->meta,
        ], fn ($value) => $value !== null);
    }
}
