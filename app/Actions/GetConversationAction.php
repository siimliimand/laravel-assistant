<?php

namespace App\Actions;

use App\Models\Conversation;

/**
 * Action to retrieve a conversation with its messages.
 *
 * Handles loading a conversation with eager-loaded messages
 * to prevent N+1 queries.
 *
 * Usage:
 * ```php
 * $action = app(GetConversationAction::class);
 * $conversation = $action->execute($conversationId);
 *
 * // Access messages without additional queries:
 * foreach ($conversation->messages as $message) {
 *     echo $message->content;
 * }
 * ```
 */
class GetConversationAction extends BaseAction
{
    /**
     * Execute the conversation retrieval.
     *
     * @param  int  $conversationId  The conversation ID
     * @return Conversation|null The conversation with messages, or null if not found or not owned by user
     */
    public function execute(int $conversationId): ?Conversation
    {
        return Conversation::where('user_id', auth()->id())
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])->find($conversationId);
    }
}
