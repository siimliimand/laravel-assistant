<?php

namespace App\Models;

use App\Enums\MessageRole;
use App\Helpers\Markdown;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
    ];

    protected $casts = [
        'role' => MessageRole::class,
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isUserMessage(): bool
    {
        return $this->role === MessageRole::User;
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
