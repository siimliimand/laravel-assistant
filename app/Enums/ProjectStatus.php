<?php

namespace App\Enums;

/**
 * Project status enum for tracking project lifecycle.
 *
 * Used to strongly-type the status field in the projects table.
 * Provides metadata methods for UI display and filtering.
 *
 * Usage:
 * ```php
 * $status = ProjectStatus::Active;
 * echo $status->label(); // 'Active'
 * echo $status->color(); // 'blue'
 *
 * // In model casting:
 * protected $casts = [
 *     'status' => ProjectStatus::class,
 * ];
 * ```
 */
enum ProjectStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
        };
    }

    /**
     * Get the Tailwind CSS color class for the status.
     */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'blue',
            self::Completed => 'green',
            self::Archived => 'purple',
        };
    }

    /**
     * Get the icon identifier for the status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'document',
            self::Active => 'spark',
            self::Completed => 'check',
            self::Archived => 'archive',
        };
    }

    /**
     * Check if the project is in draft status.
     */
    public function isDraft(): bool
    {
        return $this === self::Draft;
    }

    /**
     * Check if the project is active.
     */
    public function isActive(): bool
    {
        return $this === self::Active;
    }

    /**
     * Check if the project is completed.
     */
    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    /**
     * Check if the project is archived.
     */
    public function isArchived(): bool
    {
        return $this === self::Archived;
    }
}
