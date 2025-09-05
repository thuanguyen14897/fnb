<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api_app\GroupCategoryServiceController;
use App\Http\Controllers\Api_app\CategoryServiceController;
use App\Http\Controllers\Api_app\OtherAmenitiesController;
use App\Http\Controllers\Api_app\ServiceController;
use App\Http\Controllers\Api_app\CategoryController;
use App\Http\Controllers\Api_app\AresController;

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

Route::group(['prefix' => 'group_category_service','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getListGroupCategory', [GroupCategoryServiceController::class, 'getListGroupCategory']);
    Route::get('getListData', [GroupCategoryServiceController::class, 'getListData']);
    Route::get('getDetail', [GroupCategoryServiceController::class, 'getDetail']);
    Route::post('detail', [GroupCategoryServiceController::class, 'detail']);
    Route::post('delete', [GroupCategoryServiceController::class, 'delete']);
    Route::post('active', [GroupCategoryServiceController::class, 'active']);
    Route::get('getListDataHomePage', [GroupCategoryServiceController::class, 'getListDataHomePage']);
});

Route::group(['prefix' => 'category_service','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getListCategory', [CategoryServiceController::class, 'getListCategory']);
    Route::get('getListData', [CategoryServiceController::class, 'getListData']);
    Route::get('getDetail', [CategoryServiceController::class, 'getDetail']);
    Route::post('detail', [CategoryServiceController::class, 'detail']);
    Route::post('delete', [CategoryServiceController::class, 'delete']);
    Route::post('active', [CategoryServiceController::class, 'active']);
});

Route::group(['prefix' => 'other_amenities_service','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getList', [OtherAmenitiesController::class, 'getList']);
    Route::get('getListData', [OtherAmenitiesController::class, 'getListData']);
    Route::get('getDetail', [OtherAmenitiesController::class, 'getDetail']);
    Route::post('detail', [OtherAmenitiesController::class, 'detail']);
    Route::post('delete', [OtherAmenitiesController::class, 'delete']);
});

Route::group(['prefix' => 'service','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getList', [ServiceController::class, 'getList']);
    Route::get('getListData', [ServiceController::class, 'getListData']);
    Route::post('getListDataByTransaction', [ServiceController::class, 'getListDataByTransaction']);
    Route::get('getDetailData/{id}', [ServiceController::class, 'getDetailData']);
    Route::get('getDetail', [ServiceController::class, 'getDetail']);
    Route::post('detail', [ServiceController::class, 'detail']);
    Route::post('delete', [ServiceController::class, 'delete']);
    Route::post('active', [ServiceController::class, 'active']);
    Route::post('changeHot', [ServiceController::class, 'changeHot']);
    Route::get('getListReview', [ServiceController::class, 'getListReview']);
    Route::post('addService', [ServiceController::class, 'addService']);
    Route::get('getReviewService', [ServiceController::class, 'getReviewService']);
    Route::post('changeFavouriteService', [ServiceController::class, 'changeFavouriteService']);
});

Route::group(['prefix' => 'category'], function () {
    Route::get('getListProvince/{id?}', [CategoryController::class, 'getListProvince']);
    Route::get('getListWard', [CategoryController::class, 'getListWard']);
    Route::get('getListAddress', [CategoryController::class, 'getListAddress']);
    Route::get('getListProvinceSixtyFour/{id?}', [CategoryController::class, 'getListProvinceSixtyFour']);
    Route::get('getListWardToAres', [CategoryController::class, 'getListWardToAres']);
});

Route::group(['prefix' => 'ares'], function () {
    Route::get('getList', [AresController::class, 'getList']);
    Route::get('getDetail', [AresController::class, 'getDetail']);
    Route::get('getListData', [AresController::class, 'getListData']);
    Route::post('detail', [AresController::class, 'detail']);
    Route::post('delete', [AresController::class, 'delete']);
    Route::post('active', [AresController::class, 'active']);
    Route::get('ChangeStatus', [AresController::class, 'ChangeStatus']);
    Route::get('getSetup', [AresController::class, 'getSetup']);
    Route::post('updateSetup', [AresController::class, 'updateSetup']);
    Route::get('getDetailWhere', [AresController::class, 'getDetailWhere']);
    Route::get('getWardsWhereAres', [AresController::class, 'getWardsWhereAres']);
    Route::get('getListDataWhereName', [AresController::class, 'getListDataWhereName']);
    Route::get('create_auto_ares', [AresController::class, 'create_auto_ares']);
});
