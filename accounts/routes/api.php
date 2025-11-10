<?php

use App\Http\Controllers\Api_app\TransactionController;
use App\Http\Controllers\Api_app\ClientController;
use App\Http\Controllers\Api_app\LoginApi;
use App\Http\Controllers\Api_app\PackageController;
use App\Http\Controllers\Api_app\TransactionPackageController;
use App\Http\Controllers\Api_app\TransactionBillController;
use App\Http\Controllers\Api_app\PaymentController;
use App\Http\Controllers\Api_app\ReportController;
use App\Http\Controllers\Api_app\PointController;
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
Route::post('updateProvinceWard', [LoginApi::class, 'updateProvinceWard'])->middleware(\App\Http\Middleware\CheckLoginApi::class);
Route::post('checkReferralCode', [LoginApi::class, 'checkReferralCode']);

Route::group(['prefix' => 'customer','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getListCustomer', [ClientController::class, 'getListCustomer']);
    Route::get('getListData', [ClientController::class, 'getListData']);
    Route::get('countAll', [ClientController::class, 'countAll']);
    Route::get('getDetailCustomer', [ClientController::class, 'getDetailCustomer']);
    Route::post('detail', [ClientController::class, 'detail']);
    Route::post('deleteCustomer', [ClientController::class, 'deleteCustomer']);
    Route::post('active', [ClientController::class, 'active']);
    Route::post('updateTypeClient', [ClientController::class, 'updateTypeClient']);
    Route::post('detailRepresentativePartner', [ClientController::class, 'detailRepresentativePartner']);
    Route::get('requestPaymentPay2s', [ClientController::class, 'requestPaymentPay2s']);
    Route::post('updateBankPartnerRepresentative', [ClientController::class, 'updateBankPartnerRepresentative']);
    Route::get('cronUpgradeMemberShip', [ClientController::class, 'cronUpgradeMemberShip']);
    Route::get('getReportReferral', [ClientController::class, 'getReportReferral']);
    Route::get('getParentReferralByCustomer', [ClientController::class, 'getParentReferralByCustomer']);
    Route::get('getListDataRepresentative', [ClientController::class, 'getListDataRepresentative']);
    Route::post('addCustomerPackage', [ClientController::class, 'addCustomerPackage']);
    Route::get('getListLogUpgradeClient', [ClientController::class, 'getListLogUpgradeClient']);
    Route::post('updateLogUpgradeClient', [ClientController::class, 'updateLogUpgradeClient']);
    Route::post('updateDateActive', [ClientController::class, 'updateDateActive']);
});

Route::group(['prefix' => 'transaction','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
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
    Route::post('changeStatus', [TransactionController::class, 'changeStatus']);
    Route::post('changeStatusItem', [TransactionController::class, 'changeStatusItem']);
    Route::post('checkService', [TransactionController::class, 'checkService']);
});

Route::group(['prefix' => 'package','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getListPackage', [PackageController::class, 'getListPackage']);
    Route::get('getListData', [PackageController::class, 'getListData']);
    Route::get('getDetail', [PackageController::class, 'getDetail']);
    Route::post('detail', [PackageController::class, 'detail']);
    Route::post('delete', [PackageController::class, 'delete']);
    Route::post('addTransactionPackage', [PackageController::class, 'addTransactionPackage']);
});

Route::group(['prefix' => 'transaction_package','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getListTransactionPackage', [TransactionPackageController::class, 'getListTransactionPackage']);
    Route::get('getListData', [TransactionPackageController::class, 'getListData']);
    Route::get('getDetail', [TransactionPackageController::class, 'getDetail']);
    Route::post('detail', [TransactionPackageController::class, 'detail']);
    Route::post('delete', [TransactionPackageController::class, 'delete']);
    Route::post('changeStatus', [TransactionPackageController::class, 'changeStatus']);
    Route::post('updateTransaction', [TransactionPackageController::class, 'updateTransaction']);
});

Route::group(['prefix' => 'transaction_bill','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getList', [TransactionBillController::class, 'getList']);
    Route::get('getListData', [TransactionBillController::class, 'getListData']);
    Route::get('getListDataDetail/{id}', [TransactionBillController::class, 'getListDataDetail']);
    Route::post('addTransaction', [TransactionBillController::class, 'addTransaction']);
    Route::get('countAll', [TransactionBillController::class, 'countAll']);
    Route::post('delete', [TransactionBillController::class, 'delete']);
    Route::get('getListStatusTransaction', [TransactionBillController::class, 'getListStatusTransaction']);
    Route::post('countTransaction', [TransactionBillController::class, 'countTransaction']);
    Route::post('changeStatus', [TransactionBillController::class, 'changeStatus']);
    Route::get('getListDataTransactionBill', [TransactionBillController::class, 'getListDataTransactionBill']);
    Route::get('getListService', [TransactionBillController::class, 'getListService']);
    Route::post('checkService', [TransactionBillController::class, 'checkService']);
    Route::get('getListMonthTransaction', [TransactionBillController::class, 'getListMonthTransaction']);
});

Route::group(['prefix' => 'payment','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getList', [PaymentController::class, 'getList']);
    Route::get('getListData', [PaymentController::class, 'getListData']);
    Route::get('getListDataDetail/{id}', [PaymentController::class, 'getListDataDetail']);
    Route::post('delete', [PaymentController::class, 'delete']);
    Route::post('changeStatus', [PaymentController::class, 'changeStatus']);
});

Route::group(['prefix' => 'report','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getListReportRevenuePartner', [ReportController::class, 'getListReportRevenuePartner']);
    Route::get('getListReportRevenuePartnerDetail', [ReportController::class, 'getListReportRevenuePartnerDetail']);
    Route::get('getRevenuePartner', [ReportController::class, 'getRevenuePartner']);
    Route::get('getSyntheticRevenue', [ReportController::class, 'getSyntheticRevenue']);
    Route::get('getSyntheticRevenueDetail', [ReportController::class, 'getSyntheticRevenueDetail']);
    Route::get('getSyntheticMemberRose', [ReportController::class, 'getSyntheticMemberRose']);
    Route::get('getSyntheticKpiUser', [ReportController::class, 'getSyntheticKpiUser']);
    Route::get('getListSyntheticRevenuePartner', [ReportController::class, 'getListSyntheticRevenuePartner']);
    Route::get('getListSyntheticCustomer', [ReportController::class, 'getListSyntheticCustomer']);
    Route::get('getListSyntheticSpendingCustomer', [ReportController::class, 'getListSyntheticSpendingCustomer']);
    Route::get('getListSyntheticDiscountPartner', [ReportController::class, 'getListSyntheticDiscountPartner']);
    Route::get('getListSyntheticUpgradeMembership', [ReportController::class, 'getListSyntheticUpgradeMembership']);
    Route::get('getListSyntheticFeePartner', [ReportController::class, 'getListSyntheticFeePartner']);
    Route::get('getListSyntheticRosePartner', [ReportController::class, 'getListSyntheticRosePartner']);
});

Route::group(['prefix' => 'point','middleware' => App\Http\Middleware\CheckLoginApi::class], function () {
    Route::get('getListHistoryPoint', [PointController::class, 'getListHistoryPoint']);
    Route::get('getListMonthPoint', [PointController::class, 'getListMonthPoint']);
});

