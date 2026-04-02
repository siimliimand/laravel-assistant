<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
    ];

    protected $casts = [
        'role' => 'string',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isUserMessage(): bool
    {
        return $this->role === 'user';
    }

    public function formattedTimestamp(): string
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    /**
     * Format message content with basic markdown support.
     * Handles code blocks, inline code, and basic text formatting.
     */
    public function formattedContent(): string
    {
        $content = e($this->content);

        // Code blocks with language (```language\ncode\n```)
        $content = preg_replace(
            '/```(\w+)?\n(.*?)```/s',
            '<pre class="bg-gray-800 text-gray-100 rounded-lg p-3 my-2 overflow-x-auto"><code class="text-sm font-mono">$2</code></pre>',
            $content
        );

        // Inline code (`code`)
        $content = preg_replace(
            '/`([^`]+)`/',
            '<code class="bg-gray-200 text-gray-800 px-1.5 py-0.5 rounded text-xs font-mono">$1</code>',
            $content
        );

        // Bold (**text**)
        $content = preg_replace(
            '/\*\*(.+?)\*\*/',
            '<strong>$1</strong>',
            $content
        );

        // Italic (*text*)
        $content = preg_replace(
            '/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/',
            '<em>$1</em>',
            $content
        );

        // Convert newlines to <br> tags
        $content = nl2br($content);

        return $content;
    }
}
