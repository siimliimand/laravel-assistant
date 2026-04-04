<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat/conversations', [ChatController::class, 'listConversations'])->name('chat.conversations');
Route::get('/chat/{conversation?}', [ChatController::class, 'show'])->name('chat.show');
Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show.conversation');
Route::post('/chat/new', [ChatController::class, 'createConversation'])->name('chat.new');
Route::get('/api/chat/{conversation}', [ChatController::class, 'getConversation'])->name('chat.conversation');
Route::post('/chat/message', [ChatController::class, 'sendMessage'])->name('chat.message');
