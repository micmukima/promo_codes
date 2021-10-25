<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EventController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('login', [LoginController::class, 'login']);
Route::post('register', [RegisterController::class, 'register']);

Route::group(['middleware' => 'auth:api'], function() {

    Route::post('events', [EventController::class, 'store']);
    Route::get('events/details/{event}', [EventController::class, 'show']);
    Route::put('events/{event}', [EventController::class, 'update']);
    Route::get('events', [EventController::class, 'index']);
    Route::delete('events/{event}', [EventController::class, 'delete']);

    Route::post('promocodes/{event}', [PromoCodeController::class, 'store']);
    Route::get('promocodes/details/{promoCode}', [PromoCodeController::class, 'show']);
    Route::put('promocodes/{promoCode}', [PromoCodeController::class, 'update']);
    Route::get('promocodes', [PromoCodeController::class, 'index']);
    Route::get('promocodes/active', [PromoCodeController::class, 'active']);
    Route::get('promocodes/event/{event}', [PromoCodeController::class, 'eventIndex']);
    Route::get('promocodes/{event}/active', [PromoCodeController::class, 'eventActive']);
    Route::put('promocodes/deactivate/{promoCode}', [PromoCodeController::class, 'deactivate']);
    Route::delete('promocodes/{promoCode}', [PromoCodeController::class, 'delete']);
    Route::get('promocodes/validate/{promo_code}/{originLat}/{originLng}/{destinationLat}/{destinationLng}', [PromoCodeController::class, 'validateCode']);

    Route::post('logout', [LoginController::class, 'logout']);

});
