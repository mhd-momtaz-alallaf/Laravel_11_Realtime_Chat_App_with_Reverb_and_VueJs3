<?php

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/dashboard', function () {
    return view('dashboard',[
        // passing all not logged in users to the dashboard view.
        'users' => User::whereNot('id', auth()->id())->get(),
    ]);
})->middleware(['auth'])->name('dashboard');

// creating the chat with friends route.
Route::get('/chat/{friend}', function(User $friend){
    return view('chat',[
        'friend' => $friend,
    ]);
})->middleware('auth')->name('chat');

// Creating the messages route and getting all the sender and receiver messages to use it later in the ChatComponent as data api.
Route::get('messages/{friend}',function(User $friend){
    return ChatMessage::withRelations()->query()
        // The messages sent by the authenticated user to the friend.
        ->where(function($query) use ($friend){
            $query
            ->where('sender_id', auth()->id())
            ->where('receiver_id', $friend->id);
        })
        // The messages sent by the friend to the authenticated user.
        ->orWhere(function($query) use ($friend){
            $query
            ->where('sender_id', $friend->id)
            ->where('receiver_id',auth()->id());
        })
        // sorting the messages by the message id.
        ->orderBy('id', 'asc')
        // getting the query results.
        ->get();
})->middleware('auth');

require __DIR__.'/auth.php';
