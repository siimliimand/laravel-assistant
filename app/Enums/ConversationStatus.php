<?php

namespace App\Enums;

/**
 * Conversation status enum for tracking conversation lifecycle.
 *
 * Used to strongly-type the status field in the conversations table.
 * Provides metadata methods for UI display and filtering.
 *
 * Usage:
 * ```php
 * $status = ConversationStatus::Active;
 * echo $status->label(); // 'Active'
 * echo $status->color(); // 'green'
 *
 * // In model casting:
 * protected $casts = [
 *     'status' => ConversationStatus::class,
 * ];
 * ```
 */
enum ConversationStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
    case Deleted = 'deleted';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Archived => 'Archived',
            self::Deleted => 'Deleted',
        };
    }

    /**
     * Get the Tailwind CSS color class for the status.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Archived => 'gray',
            self::Deleted => 'red',
        };
    }

    /**
     * Get the icon identifier for the status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Active => 'chat',
            self::Archived => 'archive',
            self::Deleted => 'trash',
        };
    }

    /**
     * Check if the conversation is active.
     */
    public function isActive(): bool
    {
        return $this === self::Active;
    }

    /**
     * Check if the conversation is archived.
     */
    public function isArchived(): bool
    {
        return $this === self::Archived;
    }

    /**
     * Check if the conversation is deleted.
     */
    public function isDeleted(): bool
    {
        return $this === self::Deleted;
    }
}
