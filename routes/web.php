<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', [ChatController::class, 'show'])->name('chat.show');
Route::post('/chat/message', [ChatController::class, 'sendMessage'])->name('chat.message');
