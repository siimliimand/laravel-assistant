<?php

use App\Models\Conversation;
use App\Models\Message;
use App\ViewModels\ChatViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

test('chat view model returns null when no conversation', function () {
    $viewModel = new ChatViewModel;

    expect($viewModel->getCurrentConversation())->toBeNull();
    expect($viewModel->getCurrentConversationId())->toBeNull();
    expect($viewModel->getCurrentConversationTitle())->toBeNull();
});

test('chat view model returns current conversation', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test Chat']);
    $viewModel = new ChatViewModel(conversation: $conversation);

    expect($viewModel->getCurrentConversation())->toBe($conversation);
    expect($viewModel->getCurrentConversationId())->toBe($conversation->id);
    expect($viewModel->getCurrentConversationTitle())->toBe('Test Chat');
});

test('chat view model returns empty collection for formatted messages when no conversation', function () {
    $viewModel = new ChatViewModel;

    $messages = $viewModel->getFormattedMessages();

    expect($messages)->toBeInstanceOf(Collection::class);
    expect($messages)->toHaveCount(0);
});

test('chat view model formats messages correctly', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test Chat']);

    $userMsg = Message::factory()->userMessage('Hello')
        ->create(['conversation_id' => $conversation->id]);

    $assistantMsg = Message::factory()->assistantMessage('**Hi there!**')
        ->create(['conversation_id' => $conversation->id]);

    $conversation->load('messages');
    $viewModel = new ChatViewModel(conversation: $conversation);

    $messages = $viewModel->getFormattedMessages();

    expect($messages)->toHaveCount(2);
    expect($messages[0]['id'])->toBe($userMsg->id);
    expect($messages[0]['role'])->toBe('user');
    expect($messages[0]['role_label'])->toBe('You');
    expect($messages[0]['content'])->toBe('Hello');
    expect($messages[0]['created_at'])->toBeString();

    expect($messages[1]['role'])->toBe('assistant');
    expect($messages[1]['role_label'])->toBe('DevBot');
    expect($messages[1]['content'])->toContain('<strong>Hi there!</strong>');
});

test('chat view model returns sidebar conversations with metadata', function () {
    $conv1 = Conversation::factory()->create(['title' => 'Chat 1']);
    $conv2 = Conversation::factory()->create(['title' => 'Chat 2']);
    $conversations = collect([$conv1, $conv2]);

    $viewModel = new ChatViewModel(conversation: $conv1, conversations: $conversations);

    $sidebarConvs = $viewModel->getSidebarConversations();

    expect($sidebarConvs)->toHaveCount(2);
    expect($sidebarConvs[0]['id'])->toBe($conv1->id);
    expect($sidebarConvs[0]['title'])->toBe('Chat 1');
    expect($sidebarConvs[0]['is_active'])->toBeTrue();
    expect($sidebarConvs[1]['is_active'])->toBeFalse();
});

test('chat view model marks active conversation correctly', function () {
    $activeConv = Conversation::factory()->create(['title' => 'Active']);
    $inactiveConv = Conversation::factory()->create(['title' => 'Inactive']);
    $conversations = collect([$activeConv, $inactiveConv]);

    $viewModel = new ChatViewModel(conversation: $activeConv, conversations: $conversations);
    $sidebarConvs = $viewModel->getSidebarConversations();

    $activeItems = $sidebarConvs->where('is_active', true);
    expect($activeItems)->toHaveCount(1);
    expect($activeItems->first()['id'])->toBe($activeConv->id);
});

test('chat view model handles empty conversations collection', function () {
    $viewModel = new ChatViewModel(conversations: collect());

    $sidebarConvs = $viewModel->getSidebarConversations();

    expect($sidebarConvs)->toHaveCount(0);
});

test('chat view model formats timestamps correctly', function () {
    $conversation = Conversation::factory()->create(['title' => 'Test']);
    $conversations = collect([$conversation]);

    $viewModel = new ChatViewModel(conversation: null, conversations: $conversations);
    $sidebarConvs = $viewModel->getSidebarConversations();

    expect($sidebarConvs[0])->toHaveKey('created_at');
    expect($sidebarConvs[0])->toHaveKey('updated_at');
    expect($sidebarConvs[0]['created_at'])->toBeString();
    expect($sidebarConvs[0]['updated_at'])->toBeString();
});
