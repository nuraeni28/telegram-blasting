<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MessageController;
/*
|--------------------------------------------------------------------------
| API Routes
| API Routes
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/blast-message', [MessageController::class, 'blastMessage']);
Route::get('setWebhook', [MessageController::class, 'setWebhook']);
Route::post('swiftbot/webhook', [MessageController::class, 'commandHandlerWebHook']);
