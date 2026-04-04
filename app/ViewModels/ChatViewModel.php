<?php

namespace App\ViewModels;

use App\Models\Conversation;
use Illuminate\Support\Collection;

/**
 * ViewModel for the chat interface.
 *
 * Prepares and transforms data for the chat view, keeping the controller thin.
 * Handles formatting of messages, conversations, and computed properties.
 *
 * Usage:
 * ```php
 * $viewModel = new ChatViewModel(
 *     conversation: $conversation,
 *     conversations: $conversationsList
 * );
 *
 * // In Blade view:
 *
 * @foreach ($viewModel->getFormattedMessages() as $message)
 *     {{ $message['content'] }}
 *
 * @endforeach
 * ```
 */
class ChatViewModel
{
    public function __construct(
        protected ?Conversation $conversation = null,
        protected ?Collection $conversations = null,
    ) {
        $this->conversations = $conversations ?? collect();
    }

    /**
     * Get the current conversation.
     */
    public function getCurrentConversation(): ?Conversation
    {
        return $this->conversation;
    }

    /**
     * Get formatted messages for the current conversation.
     *
     * Returns an array of formatted message data ready for display.
     *
     * @return Collection<int, array{
     *     id: int,
     *     role: string,
     *     role_label: string,
     *     content: string,
     *     created_at: string
     * }>
     */
    public function getFormattedMessages(): Collection
    {
        if (! $this->conversation) {
            return collect();
        }

        return $this->conversation->messages
            ->sortBy('created_at')
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role->value,
                    'role_label' => $message->role->label(),
                    'content' => $message->role->isAssistant()
                        ? $message->formattedContent()
                        : $message->content,
                    'created_at' => $message->created_at->format('g:i A'),
                ];
            });
    }

    /**
     * Get conversations for the sidebar with metadata.
     *
     * @return Collection<int, array{
     *     id: int,
     *     title: string,
     *     created_at: string,
     *     updated_at: string,
     *     is_active: bool
     * }>
     */
    public function getSidebarConversations(): Collection
    {
        return $this->conversations->map(function ($conversation) {
            return [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'created_at' => $conversation->created_at->diffForHumans(),
                'updated_at' => $conversation->updated_at->diffForHumans(),
                'is_active' => $this->conversation?->id === $conversation->id,
            ];
        });
    }

    /**
     * Get current conversation ID for JavaScript.
     */
    public function getCurrentConversationId(): ?int
    {
        return $this->conversation?->id;
    }

    /**
     * Get current conversation title for display.
     */
    public function getCurrentConversationTitle(): ?string
    {
        return $this->conversation?->title;
    }
}
