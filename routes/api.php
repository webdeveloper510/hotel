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

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::post('add-hotel',[UserController::class, 'add_hotel']);
Route::get('get-hotels', [UserController::class, 'get_hotels']);
Route::delete('delete-hotel/{id}', [UserController::class, 'delete_hotel']);
Route::post('update-hotel/{id}', [UserController::class, 'update_hotel']);


Route::group(['middleware' => 'auth:api'], function(){
Route::post('user-details', [UserController::class, 'userDetails']);
Route::get('get-users', [UserController::class, 'getUsers']);
Route::post('user-logout', [UserController::class, 'logout']);
});
