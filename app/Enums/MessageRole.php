<?php

namespace App\Enums;

/**
 * Message role enum for chat messages.
 *
 * Used to strongly-type the role field in the messages table.
 * Provides metadata methods for UI display.
 *
 * Usage:
 * ```php
 * $role = MessageRole::User;
 * echo $role->label(); // 'You'
 * echo $role->color(); // 'blue'
 *
 * // In model casting:
 * protected $casts = [
 *     'role' => MessageRole::class,
 * ];
 * ```
 */
enum MessageRole: string
{
    case User = 'user';
    case Assistant = 'assistant';

    /**
     * Get the human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::User => 'You',
            self::Assistant => 'DevBot',
        };
    }

    /**
     * Get the Tailwind CSS color class for the role.
     */
    public function color(): string
    {
        return match ($this) {
            self::User => 'blue',
            self::Assistant => 'green',
        };
    }

    /**
     * Get the icon identifier for the role.
     */
    public function icon(): string
    {
        return match ($this) {
            self::User => 'user',
            self::Assistant => 'robot',
        };
    }

    /**
     * Check if this is a user message.
     */
    public function isUser(): bool
    {
        return $this === self::User;
    }

    /**
     * Check if this is an assistant message.
     */
    public function isAssistant(): bool
    {
        return $this === self::Assistant;
    }
}
