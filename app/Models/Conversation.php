<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
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
                    role: $message->role,
                    content: $message->content
                );
            })
            ->toArray();
    }
}
