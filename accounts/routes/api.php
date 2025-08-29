<?php

use App\Http\Controllers\Api_app\TransactionController;
use App\Http\Controllers\Api_app\ClientController;
use App\Http\Controllers\Api_app\LoginApi;
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

Route::post('otpDangKyThuanFoso', [LoginApi::class, 'otpDangKyThuanFoso']); // gửi mã otp
Route::post('check_otp_forgot_password', [LoginApi::class, 'check_otp_forgot_password']); // check otp quên mật khẩu
Route::post('forgot_password', [LoginApi::class, 'forgot_password']); // quên mật khẩu
Route::post('login', [LoginApi::class, 'login']); // đăng nhập
Route::post('verifyOtp', [LoginApi::class, 'verifyOtp']); // Kiểm tra OTP
Route::post('sign_up', [LoginApi::class, 'sign_up']); // đăng ký với số điện thoại - email
Route::post('logout', [LoginApi::class, 'logout']); // đăng xuất phiên đăng nhập
Route::post('update_account', [LoginApi::class, 'update_account'])->middleware(\App\Http\Middleware\CheckLoginApi::class);
Route::post('get_info_account', [LoginApi::class, 'get_info_account'])->middleware(\App\Http\Middleware\CheckLoginApi::class);
Route::post('lockAccount', [LoginApi::class, 'lockAccount'])->middleware(\App\Http\Middleware\CheckLoginApi::class);
Route::post('checkPassword', [LoginApi::class, 'checkPassword'])->middleware(\App\Http\Middleware\CheckLoginApi::class);
Route::post('changePassword', [LoginApi::class, 'changePassword'])->middleware(\App\Http\Middleware\CheckLoginApi::class);

Route::group(['prefix' => 'customer','middleware' => \App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getListCustomer', [ClientController::class, 'getListCustomer']);
    Route::get('getListData', [ClientController::class, 'getListData']);
    Route::get('countAll', [ClientController::class, 'countAll']);
    Route::get('getDetailCustomer', [ClientController::class, 'getDetailCustomer']);
    Route::post('detail', [ClientController::class, 'detail']);
    Route::post('deleteCustomer', [ClientController::class, 'deleteCustomer']);
    Route::post('active', [ClientController::class, 'active']);
    Route::post('detailRepresentativePartner', [ClientController::class, 'detailRepresentativePartner']);
});

Route::group(['prefix' => 'transaction','middleware' => \App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getList', [TransactionController::class, 'getList']);
    Route::get('getListData', [TransactionController::class, 'getListData']);
    Route::get('getListDataDetail/{id}', [TransactionController::class, 'getListDataDetail']);
    Route::post('addTransaction', [TransactionController::class, 'addTransaction']);
    Route::get('countAll', [TransactionController::class, 'countAll']);
    Route::get('getDetail', [TransactionController::class, 'getDetail']);
    Route::post('detail', [TransactionController::class, 'detail']);
    Route::post('delete', [TransactionController::class, 'delete']);
    Route::get('getListStatusTransaction', [TransactionController::class, 'getListStatusTransaction']);
    Route::post('countTransaction', [TransactionController::class, 'countTransaction']);
});


