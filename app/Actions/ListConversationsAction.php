<?php

namespace App\Actions;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Collection;

/**
 * Action to list conversations for the sidebar.
 *
 * Retrieves recent conversations with sensible limits
 * to prevent loading too much data.
 *
 * Usage:
 * ```php
 * $action = app(ListConversationsAction::class);
 * $conversations = $action->execute(limit: 50);
 *
 * foreach ($conversations as $conversation) {
 *     echo $conversation->title;
 * }
 * ```
 */
class ListConversationsAction extends BaseAction
{
    /**
     * Execute the conversation listing.
     *
     * @param  int  $limit  Maximum number of conversations to return (default: 50)
     * @return Collection<int, Conversation> Collection of conversations
     */
    public function execute(int $limit = 50): Collection
    {
        return Conversation::latest()
            ->limit($limit)
            ->get();
    }
}
