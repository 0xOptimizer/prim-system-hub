<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AIController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoomController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    // AuthController
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('/token/validate', [AuthController::class, 'validateToken'])->name('api.auth.token.validate');
    });

    // UserController
    Route::apiResource('users', UserController::class); // RESTful endpoints

    // AI
    Route::prefix('ai')->group(function () {
        Route::post('convert/request', [AIController::class, 'getChatResponse'])->name('api.ai.convert.request');
    });

    // Room Controller
    Route::prefix('rooms')->group(function () {
        Route::get('/', [RoomController::class, 'show_all'])->name('api.rooms.show_all');
        Route::post('/', [RoomController::class, 'store'])->name('api.rooms.store');
        Route::get('/{uuid}', [RoomController::class, 'show'])->name('api.rooms.show');
        Route::put('/{uuid}', [RoomController::class, 'update'])->name('api.rooms.update');
        Route::delete('/{uuid}', [RoomController::class, 'destroy'])->name('api.rooms.destroy');
        Route::get('/{uuid}/user-count', [RoomController::class, 'userCount'])->name('api.rooms.user.count');
    });
});