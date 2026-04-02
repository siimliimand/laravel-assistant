<?php

namespace App\Models;

use App\Helpers\Markdown;
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
     * Format message content with markdown support using league/commonmark.
     */
    public function formattedContent(): string
    {
        return Markdown::convert($this->content);
    }
}
