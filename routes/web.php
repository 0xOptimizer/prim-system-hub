<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ai/download/{filename}', function ($filename) {
    $path = storage_path('app/ai-code/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->download($path);
})->name('ai.download');

// Chat
Route::prefix('chat')->group(function () {
    Route::post('/chat', [RoomController::class, 'chatShowAll'])->name('api.rooms.chat');
    Route::post('/chat/send', [RoomController::class, 'chatCreate'])->name('api.rooms.chat.send');
});

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');

Route::post('/reset-password', [UserController::class, 'resetPassword'])->name('password.update');