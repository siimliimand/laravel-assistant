<?php

namespace App\Actions;

use App\Models\Conversation;
use App\ViewModels\ChatViewModel;
use Illuminate\Support\Collection;

/**
 * Action to prepare chat view data.
 *
 * Handles conversation loading logic, ensuring proper eager loading
 * and fallback to the most recent conversation when needed.
 *
 * Usage:
 * ```php
 * $action = app(PrepareChatViewAction::class);
 * $viewModel = $action->execute($conversation, $conversations);
 * ```
 */
class PrepareChatViewAction extends BaseAction
{
    /**
     * Execute the chat view preparation.
     *
     * @param  Conversation|null  $conversation  The requested conversation (may be null or empty)
     * @param  Collection<int, Conversation>  $conversations  List of conversations for sidebar
     * @return ChatViewModel Prepared view model with all necessary data
     */
    public function execute(?Conversation $conversation, Collection $conversations): ChatViewModel
    {
        $conversation = $this->resolveConversation($conversation);

        return new ChatViewModel($conversation, $conversations);
    }

    /**
     * Resolve the conversation to display.
     *
     * Falls back to the most recent conversation if none is specified.
     * Ensures messages are eager loaded to prevent N+1 queries.
     */
    protected function resolveConversation(?Conversation $conversation): ?Conversation
    {
        if (! $conversation || ! $conversation->exists) {
            return Conversation::with('messages')->latest()->first();
        }

        $conversation->load('messages');

        return $conversation;
    }
}
