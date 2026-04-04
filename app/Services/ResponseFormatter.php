<?php

namespace App\Services;

use App\DTOs\SendMessageResponse;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Formats data for JSON responses.
 *
 * Extracts formatting logic from controllers to keep them thin
 * and focused on request orchestration.
 */
class ResponseFormatter
{
    /**
     * Format a collection of conversations for JSON response.
     *
     * @param  Collection<int, Conversation>  $conversations
     * @return array<int, array{
     *     id: int,
     *     title: string,
     *     created_at: string,
     *     updated_at: string
     * }>
     */
    public function formatConversationsList(Collection $conversations): array
    {
        return $conversations->map(function (Conversation $conversation): array {
            return [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'created_at' => $conversation->created_at->diffForHumans(),
                'updated_at' => $conversation->updated_at->diffForHumans(),
            ];
        })->all();
    }

    /**
     * Format a single conversation for JSON response.
     *
     * @return array{
     *     id: int,
     *     title: string,
     *     created_at: string
     * }
     */
    public function formatConversation(Conversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'title' => $conversation->title,
            'created_at' => $conversation->created_at->diffForHumans(),
        ];
    }

    /**
     * Format a collection of messages for JSON response.
     *
     * @param  Collection<int, Message>  $messages
     * @return array<int, array{
     *     id: int,
     *     role: string,
     *     content: string,
     *     created_at: string
     * }>
     */
    public function formatMessages(Collection $messages): array
    {
        return $messages->map(function ($message): array {
            return [
                'id' => $message->id,
                'role' => $message->role->value,
                'content' => $message->role->isAssistant() ? $message->formattedContent() : $message->content,
                'created_at' => $message->created_at->format('g:i A'),
            ];
        })->all();
    }

    /**
     * Handle successful send message response.
     */
    public function handleSendMessageResponse(SendMessageResponse $response, Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($response->toJsonData());
        }

        return redirect()->route('chat.show.conversation', ['conversation' => $response->conversation]);
    }

    /**
     * Handle send message error.
     */
    public function handleSendMessageError(Request $request, string $errorMessage): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get a response from DevBot. Please try again later.',
            ], 500);
        }

        return redirect()->back()->with('error', 'Failed to get a response from DevBot. Please try again later.');
    }
}
