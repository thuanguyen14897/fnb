<?php

use App\Http\Controllers\Api_app\Api_info;
use App\Http\Controllers\Api_app\CategoryController;
use App\Http\Controllers\Api_app\OrderRefController;
use App\Http\Controllers\Api_app\AresController;
use App\Http\Controllers\Api_app\HomepageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('get_info_settings', [Api_info::class, 'get_info_settings']);

Route::get('getOrderRef/{ref?}', [OrderRefController::class, 'getOrderRef']);
Route::get('updateOrderRef/{ref?}', [OrderRefController::class, 'updateOrderRef']);
Route::get('getOption/{ref?}', [Api_info::class, 'getOption']);
Route::post('send_zalo', [Api_info::class, 'send_zalo']);


Route::group(['prefix' => 'category'], function () {
    Route::get('getListProvince/{id?}', [CategoryController::class, 'getListProvince']);
    Route::get('getListWard/{id?}', [CategoryController::class, 'getListWard']);
    Route::get('getListAddress', [CategoryController::class, 'getListAddress']);
});

Route::group(['prefix' => 'homepage'], function () {
    Route::get('getData', [HomepageController::class, 'getData']);
});

