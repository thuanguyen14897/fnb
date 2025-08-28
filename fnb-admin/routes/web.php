<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\GroupPermissionController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PaymentModeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\AdminWebsiteController;
use App\Http\Controllers\ModuleNotiController;
use App\Http\Controllers\GroupCategoryServiceController;
use App\Http\Controllers\CategoryServiceController;
use App\Http\Controllers\OtherAmenitiesServiceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AresController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\MemberShipLevelController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});
Route::group(['prefix' => 'cron'], function () {
    Route::get('noti_remind_transaction', [CronController::class, 'noti_remind_transaction']);
    Route::get('noti_one_hour_remind_transaction', [CronController::class, 'noti_one_hour_remind_transaction']);
    Route::get('updateCodeClient', [CronController::class, 'updateCodeClient']);
    Route::get('sendSmsTransaction', [CronController::class, 'sendSmsTransaction']);
    Route::get('addAutoReview', [CronController::class, 'addAutoReview']);
    Route::get('remindFinishOwner', [CronController::class, 'remindFinishOwner']);
    Route::get('autoFinishTransaction', [CronController::class, 'autoFinishTransaction']);
    Route::get('cronCloseBalance', [CronController::class, 'cronCloseBalance']);
    Route::get('cronCloseBalanceMonth', [CronController::class, 'cronCloseBalanceMonth']);
    Route::get('getListBanks', [CronController::class, 'getListBanks']);
    Route::get('cancelTransactionNotDepoist', [CronController::class, 'cancelTransactionNotDepoist']);
    Route::get('cancelTransactionNotApprove', [CronController::class, 'cancelTransactionNotApprove']);
    Route::get('addGroupPermistionByPermission', [CronController::class, 'addGroupPermistionByPermission']);
    Route::get('getListBankNew', [CronController::class, 'getListBankNew']);
    Route::get('cancelTransactionDriverNotDriver', [CronController::class, 'cancelTransactionDriverNotDriver']);
    Route::get('getCancelSystemTransactionDriver', [CronController::class, 'getCancelSystemTransactionDriver']);
    Route::get('sendNotificationModule', [CronController::class, 'sendNotificationModule']);
    Route::get('noti_remind_transaction_driver_province', [CronController::class, 'noti_remind_transaction_driver_province']);
    Route::get('noti_remind_use_point_client', [CronController::class, 'noti_remind_use_point_client']);
    Route::get('noti_reset_use_point_client', [CronController::class, 'noti_reset_use_point_client']);
    Route::get('moveFileToS3', [CronController::class, 'moveFileToS3']);
    Route::get('sendNotiTransaction', [CronController::class, 'sendNotiTransaction']);
    Route::get('cronCustomerRewardDay', [CronController::class, 'cronCustomerRewardDay']);
    Route::get('cronCustomerClassDay', [CronController::class, 'cronCustomerClassDay']);
    Route::get('getWarningWithDraw', [CronController::class, 'getWarningWithDraw']);
    Route::get('updateStatusCustomerWarning', [CronController::class, 'updateStatusCustomerWarning']);
    Route::get('createTransactionCertificate', [CronController::class, 'createTransactionCertificate']);
});

Route::get('/clear', function () {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:cache');
});

Route::get('/clear1', function () {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
});

Route::group(['prefix' => 'webhook'], function () {
    Route::post('webhookAlepay', [WebhookController::class, 'webhookAlepay']);
    Route::post('webhookStripe', [WebhookController::class, 'webhookStripe']);
});

//Route::get('.well-known/acme-challenge/{key?}', function (string $key) {
//    return require_once __DIR__.'/../.well-known/acme-challenge/'.$key;
//});
Route::get('/admin/123', function () {
    return view('admin.email-template.new_customer_register');
});
Route::get('/', function () {
    return view('welcome');
})->middleware('checkLogin');
Route::get('admin/login', [AdminController::class, 'get_login'])->name('admin.login');
Route::post('admin/login', [AdminController::class, 'post_login']);
Route::get('admin/logout', [AdminController::class, 'get_logout']);
Route::group(['prefix' => 'admin', 'middleware' => 'checkLogin:admin'], function () {
    Route::get('dashboard', [AdminController::class, 'index']);
    Route::post('loadDataChartDashboard', [AdminController::class, 'loadDataChartDashboard']);
    Route::get('loadDataCustomerClass/{id}/{type}', [AdminController::class, 'loadDataCustomerClass']);
    Route::group(['prefix' => 'user'], function () {
        Route::get('list', [UserController::class, 'get_list']);
        Route::post('getUsers', [UserController::class, 'getUsers']);
        Route::get('detail/{id?}', [UserController::class, 'get_detail']);
        Route::post('submit/{id}', [UserController::class, 'submit']);
        Route::post('getPermissonByRole', [UserController::class, 'getPermissonByRole']);
        Route::get('delete/{id}', [UserController::class, 'delete']);
        Route::get('active/{id}', [UserController::class, 'active']);
        Route::post('updatePriority', [UserController::class, 'updatePriority']);
    });
    Route::group(['prefix' => 'department'], function () {
        Route::get('list', [DepartmentController::class, 'get_list']);
        Route::post('getDepartment', [DepartmentController::class, 'getDepartment']);
        Route::get('detail/{id?}', [DepartmentController::class, 'get_detail']);
        Route::post('submit/{id}', [DepartmentController::class, 'submit']);
        Route::get('delete/{id}', [DepartmentController::class, 'delete']);
        Route::get('changeStatus/{id}', [DepartmentController::class, 'changeStatus']);
    });

    Route::group(['prefix' => 'role'], function () {
        Route::get('list', [RoleController::class, 'get_list']);
        Route::post('getRole', [RoleController::class, 'getRole']);
        Route::get('detail/{id?}', [RoleController::class, 'get_detail']);
        Route::post('submit/{id}', [RoleController::class, 'submit']);
        Route::get('delete/{id}', [RoleController::class, 'delete']);
    });
    Route::group(['prefix' => 'group_permission'], function () {
        Route::get('list', [GroupPermissionController::class, 'get_list']);
        Route::post('getGroupPermission', [GroupPermissionController::class, 'getGroupPermission']);
        Route::get('detail/{id?}', [GroupPermissionController::class, 'get_detail']);
        Route::post('submit/{id}', [GroupPermissionController::class, 'submit']);
        Route::get('delete/{id}', [GroupPermissionController::class, 'delete']);
    });
    Route::group(['prefix' => 'permission'], function () {
        Route::get('list', [PermissionController::class, 'get_list']);
        Route::post('getPermission', [PermissionController::class, 'getPermission']);
        Route::get('detail/{id?}', [PermissionController::class, 'get_detail']);
        Route::post('submit/{id}', [PermissionController::class, 'submit']);
        Route::get('delete/{id}', [PermissionController::class, 'delete']);
    });

    Route::group(['prefix' => 'clients'], function () {
        Route::get('list', [ClientsController::class, 'get_list']);
        Route::get('detail/{id?}', [ClientsController::class, 'get_detail']);
        Route::get('view/{id}', [ClientsController::class, 'view']);
        Route::post('getListCustomer', [ClientsController::class, 'getListCustomer']);
        Route::post('countAll', [ClientsController::class, 'countAll']);
        Route::get('getDetailCustomer', [ClientsController::class, 'getDetailCustomer']);
        Route::post('detail', [ClientsController::class, 'detail']);
        Route::get('delete/{id}', [ClientsController::class, 'delete']);
        Route::get('active/{id}', [ClientsController::class, 'active']);
    });


    Route::group(['prefix' => 'payment_mode'], function () {
        Route::get('list', [PaymentModeController::class, 'get_list']);
        Route::post('getPaymentMode', [PaymentModeController::class, 'getPaymentMode']);
        Route::get('detail/{id?}', [PaymentModeController::class, 'get_detail']);
        Route::post('submit/{id}', [PaymentModeController::class, 'submit']);
        Route::get('delete/{id}', [PaymentModeController::class, 'delete']);
        Route::get('changeStatus/{id}', [PaymentModeController::class, 'changeStatus']);
    });


    Route::group(['prefix' => 'settings'], function () {
        Route::get('', [SettingsController::class, 'get_list']);
        Route::get('down/{id}', [SettingsController::class, 'download']);
        Route::post('submit/{id?}', [SettingsController::class, 'submit']);
        Route::get('changeStatus/{id?}', [SettingsController::class, 'changeStatus']);
        Route::get('changeStatusDisplay/{id?}', [SettingsController::class, 'changeStatusDisplay']);
        Route::get('changeStatusCheckOtp', [SettingsController::class, 'changeStatusCheckOtp']);
        Route::get('changeTypeTransferAddress', [SettingsController::class, 'changeTypeTransferAddress']);
        Route::post('loadCustomerClass', [SettingsController::class, 'loadCustomerClass']);
        Route::post('loadCustomerLeaderShip', [SettingsController::class, 'loadCustomerLeaderShip']);
    });


    Route::group(['prefix' => 'category'], function () {
        Route::get('searchCustomer/{id?}', [CategoryController::class, 'searchCustomer']);
        Route::get('searchDriver/{id?}', [CategoryController::class, 'searchDriver']);
        Route::get('searchTransaction/{id?}', [CategoryController::class, 'searchTransaction']);
        Route::get('searchTransactionDriver/{id?}', [CategoryController::class, 'searchTransactionDriver']);
        Route::get('searchTransactionAll/{id?}', [CategoryController::class, 'searchTransactionAll']);
        Route::get('searchTransferMoney/{id?}', [CategoryController::class, 'searchTransferMoney']);
        Route::get('searchRequestWithdrawMoney/{id?}', [CategoryController::class, 'searchRequestWithdrawMoney']);
        Route::get('searchBlog/{id?}', [CategoryController::class, 'searchBlog']);
        Route::get('searchGroupCategoryService/{id?}', [CategoryController::class, 'searchGroupCategoryService']);
        Route::get('searchCategoryService/{id?}', [CategoryController::class, 'searchCategoryService']);
        Route::get('searchOtherAmenities/{id?}', [CategoryController::class, 'searchOtherAmenities']);
    });


    Route::group(['prefix' => 'notification'], function () {
        Route::post('loadNoti', [NotificationController::class, 'loadNoti']);
        Route::post('loadMoreNoti', [NotificationController::class, 'loadMoreNoti']);
        Route::post('readSingleNoti', [NotificationController::class, 'readSingleNoti']);
        Route::post('readAllNoti', [NotificationController::class, 'readAllNoti']);
    });


    Route::group(['prefix' => 'blog'], function () {
        Route::get('list', [BlogController::class, 'get_list']);
        Route::post('getBlog', [BlogController::class, 'getBlog']);
        Route::get('detail/{id?}', [BlogController::class, 'get_detail']);
        Route::post('submit/{id}', [BlogController::class, 'submit']);
        Route::get('delete/{id}', [BlogController::class, 'delete']);
        Route::get('changeStatus/{id}', [BlogController::class, 'changeStatus']);
        Route::get('changeHomePage/{id}', [BlogController::class, 'changeHomePage']);
        Route::get('changeHot/{id}', [BlogController::class, 'changeHot']);
    });


    Route::group(['prefix' => 'pdf'], function () {
        Route::get('contractPdf/{id}', [PDFController::class, 'contractPdf']);
        Route::get('handoverRecordPdf/{id}', [PDFController::class, 'handoverRecordPdf']);
    });

    Route::group(['prefix' => 'module_noti'], function () {
        Route::get('list', [ModuleNotiController::class, 'getList']);
        Route::post('getModuleNoti', [ModuleNotiController::class, 'getModuleNoti']);
        Route::get('detail/{id?}', [ModuleNotiController::class, 'get_detail']);
        Route::post('submit/{id}', [ModuleNotiController::class, 'submit']);
        Route::get('delete/{id}', [ModuleNotiController::class, 'delete']);
        Route::get('changeStatus/{id}', [ModuleNotiController::class, 'changeStatus']);
    });

    Route::group(['prefix' => 'admin_website'], function () {
        Route::get('homepage', [AdminWebsiteController::class, 'homepage']);
        Route::post('submit_homepage', [AdminWebsiteController::class, 'submit_homepage']);
        Route::get('privilege', [AdminWebsiteController::class, 'privilege']);
        Route::post('submit_privilege', [AdminWebsiteController::class, 'submit_privilege']);
        Route::get('white_paper', [AdminWebsiteController::class, 'white_paper']);
        Route::post('submit_white_paper', [AdminWebsiteController::class, 'submit_white_paper']);
        Route::get('page_not_found', [AdminWebsiteController::class, 'page_not_found']);
        Route::post('submit_page_not_found', [AdminWebsiteController::class, 'submit_page_not_found']);
    });


    Route::group(['prefix' => 'group_category_service'], function () {
        Route::get('list', [GroupCategoryServiceController::class, 'get_list']);
        Route::post('getListGroupCategoryService', [GroupCategoryServiceController::class, 'getListGroupCategoryService']);
        Route::get('detail/{id?}', [GroupCategoryServiceController::class, 'get_detail']);
        Route::post('detail/{id?}', [GroupCategoryServiceController::class, 'detail']);
        Route::get('delete/{id}', [GroupCategoryServiceController::class, 'delete']);
        Route::get('active/{id}', [GroupCategoryServiceController::class, 'active']);
    });

    Route::group(['prefix' => 'category_service'], function () {
        Route::get('list', [CategoryServiceController::class, 'get_list']);
        Route::post('getListCategoryService', [CategoryServiceController::class, 'getListCategoryService']);
        Route::get('detail/{id?}', [CategoryServiceController::class, 'get_detail']);
        Route::post('detail/{id?}', [CategoryServiceController::class, 'detail']);
        Route::get('delete/{id}', [CategoryServiceController::class, 'delete']);
        Route::get('active/{id}', [CategoryServiceController::class, 'active']);
    });

    Route::group(['prefix' => 'other_amenities_service'], function () {
        Route::get('list', [OtherAmenitiesServiceController::class, 'get_list']);
        Route::post('getList', [OtherAmenitiesServiceController::class, 'getList']);
        Route::get('detail/{id?}', [OtherAmenitiesServiceController::class, 'get_detail']);
        Route::post('detail/{id?}', [OtherAmenitiesServiceController::class, 'detail']);
        Route::get('delete/{id}', [OtherAmenitiesServiceController::class, 'delete']);
    });

    Route::group(['prefix' => 'service'], function () {
        Route::get('list', [ServiceController::class, 'get_list']);
        Route::post('getList', [ServiceController::class, 'getList']);
        Route::get('detail/{id?}', [ServiceController::class, 'get_detail']);
        Route::post('detail/{id?}', [ServiceController::class, 'detail']);
        Route::get('delete/{id}', [ServiceController::class, 'delete']);
        Route::post('active', [ServiceController::class, 'active']);
        Route::get('changeHot/{id}', [ServiceController::class, 'changeHot']);
    });


    Route::group(['prefix' => 'transaction'], function () {
        Route::get('list', [TransactionController::class, 'get_list']);
        Route::post('getList', [TransactionController::class, 'getList']);
        Route::get('detail/{id?}', [TransactionController::class, 'get_detail']);
        Route::post('detail/{id?}', [TransactionController::class, 'detail']);
        Route::get('delete/{id}', [TransactionController::class, 'delete']);
    });

    Route::group(['prefix' => 'ares'], function () {
        Route::get('list', [AresController::class, 'get_list']);
        Route::post('getList', [AresController::class, 'getList']);
        Route::get('detail/{id?}', [AresController::class, 'getDetail']);
        Route::post('detail/{id?}', [AresController::class, 'detail']);
        Route::get('delete/{id}', [AresController::class, 'delete']);
        Route::get('setup/{id}', [AresController::class, 'setup']);
        Route::get('changeStatus/{id}', [AresController::class, 'changeStatus']);
        Route::post('updateSetup', [AresController::class, 'updateSetup']);
    });

    Route::group(['prefix' => 'partner'], function () {
        Route::get('list', [PartnerController::class, 'get_list']);
        Route::get('detail/{id?}', [PartnerController::class, 'get_detail']);
        Route::get('view/{id}', [PartnerController::class, 'view']);
        Route::post('getListCustomer', [PartnerController::class, 'getListCustomer']);
        Route::get('getDetailCustomer', [PartnerController::class, 'getDetailCustomer']);
        Route::post('detail', [PartnerController::class, 'detail']);
        Route::get('delete/{id}', [PartnerController::class, 'delete']);
        Route::get('active/{id}', [PartnerController::class, 'active']);
        Route::post('detailRepresentativePartner/{id}', [PartnerController::class, 'detailRepresentativePartner']);
    });

    Route::group(['prefix' => 'membership_level'], function () {
        Route::get('list', [MemberShipLevelController::class, 'get_list']);
        Route::post('updateMember', [MemberShipLevelController::class, 'updateMember']);
    });

});
