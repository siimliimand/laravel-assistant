<?php

namespace App\Models;

use App\Enums\ConversationStatus;
use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'status',
    ];

    protected $casts = [
        'status' => ConversationStatus::class,
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the user that owns the conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generateTitleFromFirstMessage(string $message): void
    {
        $this->title = substr($message, 0, 50);
        $this->save();
    }

    public function getRecentMessagesAttribute()
    {
        return $this->messages()->limit(50)->get();
    }

    public function getMessagesForAgent(): array
    {
        return $this->messages()
            ->limit(50)
            ->get()
            ->map(function ($message) {
                return new \Laravel\Ai\Messages\Message(
                    role: $message->role->value,
                    content: $message->content
                );
            })
            ->toArray();
    }
}
