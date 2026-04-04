<?php

namespace App\Actions;

use App\DTOs\ConversationData;
use App\Models\Conversation;

/**
 * Action to create a new conversation.
 *
 * Handles conversation creation with optional title and initial message.
 * Extracts conversation creation logic from the controller.
 *
 * Usage:
 * ```php
 * $action = app(CreateConversationAction::class);
 *
 * // With DTO:
 * $conversation = $action->execute(
 *     ConversationData::fromMessage('Hello!')
 * );
 *
 * // With explicit title:
 * $conversation = $action->execute(
 *     new ConversationData(title: 'My Chat')
 * );
 * ```
 */
class CreateConversationAction extends BaseAction
{
    /**
     * Execute the conversation creation.
     *
     * @param  ConversationData  $data  Conversation data transfer object
     * @return Conversation The created conversation
     */
    public function execute(ConversationData $data): Conversation
    {
        $title = $data->title ?? 'New Chat';

        $conversation = Conversation::create([
            'title' => $title,
        ]);

        // Generate title from first message if provided and using default title
        if ($data->initialMessage && $title === 'New Chat') {
            $conversation->generateTitleFromFirstMessage($data->initialMessage);
        }

        return $conversation;
    }
}
