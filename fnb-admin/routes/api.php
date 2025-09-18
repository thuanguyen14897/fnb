<?php

use App\Http\Controllers\Api_app\Api_info;
use App\Http\Controllers\Api_app\CategoryController;
use App\Http\Controllers\Api_app\OrderRefController;
use App\Http\Controllers\Api_app\AresController;
use App\Http\Controllers\Api_app\HomepageController;
use App\Http\Controllers\Api_app\NotificationController;
use App\Http\Controllers\Api_app\Pay2sController;
use App\Http\Controllers\Api_app\SocketController;
use App\Http\Controllers\Api_app\BlogController;
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
Route::get('getSetting/{value?}/{type?}', [Api_info::class, 'getSetting']);
Route::post('send_zalo', [Api_info::class, 'send_zalo']);


Route::group(['prefix' => 'category'], function () {
    Route::get('getListProvince/{id?}', [CategoryController::class, 'getListProvince']);
    Route::get('getListWard/{id?}', [CategoryController::class, 'getListWard']);
    Route::get('getListAddress', [CategoryController::class, 'getListAddress']);
    Route::get('getListProvinceSixtyFour/{id?}', [CategoryController::class, 'getListProvinceSixtyFour']);
    Route::get('getListWardToAres/{id?}', [CategoryController::class, 'getListWardToAres']);
    Route::get('getListWardToUser/{id?}', [CategoryController::class, 'getListWardToUser']);
    Route::get('getListMemberShip/{id?}', [CategoryController::class, 'getListMemberShip']);
});

Route::group(['prefix' => 'notification'], function () {
    Route::get('getListNotification', [NotificationController::class, 'getListNotification'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('readAllNotification', [NotificationController::class, 'readAllNotification'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('readSingleNotification', [NotificationController::class, 'readSingleNotification'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::get('checkReadNoti', [NotificationController::class, 'checkReadNoti'])->middleware('App\Http\Middleware\CheckLoginApi::class');
    Route::post('addNoti', [NotificationController::class, 'addNoti']);
});

Route::group(['prefix' => 'homepage'], function () {
    Route::get('getData', [HomepageController::class, 'getData']);
});

Route::group(['prefix' => 'pay2s'], function () {
    Route::get('resultPay2s', [Pay2sController::class, 'resultPay2s']);
    Route::post('requestPaymentPay2s', [Pay2sController::class, 'requestPaymentPay2s'])->middleware('App\Http\Middleware\CheckLoginApi::class');
});

Route::group(['prefix' => 'socket'], function () {
    Route::get('login_socket', [SocketController::class, 'login_socket']);
});

Route::group(['prefix' => 'blog'], function () {
    Route::get('getListBlog', [BlogController::class, 'getListBlog']);
    Route::get('getDetail/{id}', [BlogController::class, 'getDetail']);
});

