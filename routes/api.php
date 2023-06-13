<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
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

// ------------------------------------Registe/Login--------------------------------------\\

Route::post('register', [UserController::class, 'register']);

Route::post('login', [UserController::class, 'login']);

// ------------------------------------HOTEL----------------------------------------------\\

Route::post('add-hotel', [UserController::class, 'add_hotel']);

Route::get('get-hotels', [UserController::class, 'get_hotels']);

Route::delete('delete-hotel/{id}', [UserController::class, 'delete_hotel']);

Route::post('update-hotel/{id}', [UserController::class, 'update_hotel']);

// ------------------------------------DESTINATIONS-----------------------------------------\\

Route::post('hotel-location', [UserController::class, 'hotel_location']);

Route::get('get-hotel-location', [UserController::class, 'get_hotel_location']);

Route::delete('delete-hotel_location/{id}', [UserController::class, 'delete_hotel_location']);

// ------------------------------------ROOMS------------------------------------------------\\

Route::post('add-rooms', [UserController::class, 'add_room']);

Route::post('get-rooms', [UserController::class, 'get_rooms']);

Route::post('update-rooms/{id}', [UserController::class, 'update_rooms']);

Route::post('delete-rooms/{id}', [UserController::class, 'delete_rooms']);


Route::post('stripe', [UserController::class, 'stripePost']);

Route::get('search', [UserController::class, 'search_hotels']);

Route::group(['middleware' => 'auth:api'], function () {

  Route::post('user-details', [UserController::class, 'userDetails']);

  Route::get('get-users', [UserController::class, 'getUsers']);

  Route::post('user-logout', [UserController::class, 'logout']);
  
  Route::post('book-hotel', [UserController::class, 'book_hotel']);
});
