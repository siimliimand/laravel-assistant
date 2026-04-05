<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Project routes - require authentication
    Route::resource('projects', ProjectController::class);

    // Chat routes - require authentication
    Route::get('/chat/{conversation?}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/api/conversations', [ChatController::class, 'listConversations'])->name('chat.conversations.list');
    Route::post('/chat/api/conversations', [ChatController::class, 'createConversation'])->name('chat.conversations.create');
    Route::get('/chat/api/conversations/{conversation}', [ChatController::class, 'getConversation'])->name('chat.conversations.get');
    Route::post('/chat/api/messages', [ChatController::class, 'sendMessage'])->name('chat.messages.send');
});

require __DIR__.'/auth.php';
