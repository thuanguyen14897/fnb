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
use App\Http\Controllers\KPIController;
use App\Http\Controllers\QuestionOftenController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\TransactionPackageController;
use App\Http\Controllers\SocketController;
use App\Http\Controllers\TransactionBillController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SampleMessageController;
use App\Http\Controllers\ReportController;

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
    Route::get('cancelTransactionTrip', [CronController::class, 'cancelTransactionTrip']);
    Route::get('startTransactionTrip', [CronController::class, 'startTransactionTrip']);
    Route::get('updateCodeClient', [CronController::class, 'updateCodeClient']);
    Route::get('addGroupPermistionByPermission', [CronController::class, 'addGroupPermistionByPermission']);
    Route::get('sendNotificationModule', [CronController::class, 'sendNotificationModule']);
    Route::get('cronUpgradeMemberShip', [CronController::class, 'cronUpgradeMemberShip']);
    Route::get('autoIncreaseMember', [CronController::class, 'autoIncreaseMember']);
    Route::post('webhookPay2s', [CronController::class, 'webhookPay2s']);
    Route::get('getListLogUpgradeClient', [CronController::class, 'getListLogUpgradeClient']);
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
        Route::get('import_excel', [UserController::class, 'import_excel']);
        Route::post('action_import', [UserController::class, 'action_import']);
        Route::get('view_user_parent/{id?}', [UserController::class, 'view_user_parent']);
        Route::post('getUserParent/{id?}', [UserController::class, 'getUserParent']);
        Route::post('getUserChild/{id?}', [UserController::class, 'getUserChild']);
        Route::get('profile/{id?}', [UserController::class, 'profile']);
        Route::post('profile/{id?}', [UserController::class, 'profile']);
        Route::get('changeStatusNVKD/{id}', [UserController::class, 'changeStatusNVKD']);
        Route::get('changeStatusManager/{id}', [UserController::class, 'changeStatusManager']);
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
        Route::post('getPermissonByRole', [RoleController::class, 'getPermissonByRole']);
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
        Route::get('updateDateActive/{id}', [ClientsController::class, 'updateDateActive']);
        Route::post('updateDateActive/{id}', [ClientsController::class, 'updateDateActive']);
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
        Route::get('changeStatusIsApple/{id}', [SettingsController::class, 'changeStatusIsApple']);
    });


    Route::group(['prefix' => 'category'], function () {
        Route::get('searchCustomer/{id?}', [CategoryController::class, 'searchCustomer']);
        Route::get('searchRepresentativer/{id?}', [CategoryController::class, 'searchRepresentativer']);
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
        Route::get('searchService/{id?}', [CategoryController::class, 'searchService']);
        Route::get('searchPackage/{id?}', [CategoryController::class, 'searchPackage']);
        Route::get('getListMemberShip/{id?}', [CategoryController::class, 'getListMemberShip']);
        Route::get('searchTransactionBill/{id?}', [CategoryController::class, 'searchTransactionBill']);
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
        Route::get('view/{id}', [ServiceController::class, 'view']);
        Route::post('getReviewService/{id}', [ServiceController::class, 'getReviewService']);
        Route::post('loadTransaction', [ServiceController::class, 'loadTransaction']);
        Route::post('loadMoreTransaction', [ServiceController::class, 'loadMoreTransaction']);
    });


    Route::group(['prefix' => 'transaction'], function () {
        Route::get('list', [TransactionController::class, 'get_list']);
        Route::post('getList', [TransactionController::class, 'getList']);
        Route::get('detail/{id?}', [TransactionController::class, 'get_detail']);
        Route::post('detail/{id?}', [TransactionController::class, 'detail']);
        Route::get('delete/{id}', [TransactionController::class, 'delete']);
        Route::post('countAll', [TransactionController::class, 'countAll']);
        Route::get('view/{id}', [TransactionController::class, 'view']);
        Route::post('changeStatus', [TransactionController::class, 'changeStatus']);
        Route::post('changeStatusItem', [TransactionController::class, 'changeStatusItem']);
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
        Route::get('createQr/{id}', [PartnerController::class, 'createQr']);
        Route::get('updateDateActive/{id}', [PartnerController::class, 'updateDateActive']);
        Route::post('updateDateActive/{id}', [PartnerController::class, 'updateDateActive']);
    });

    Route::group(['prefix' => 'membership_level'], function () {
        Route::get('list', [MemberShipLevelController::class, 'get_list']);
        Route::post('updateMember', [MemberShipLevelController::class, 'updateMember']);

        Route::get('list_level', [MemberShipLevelController::class, 'list_level']);
        Route::post('getListLevel', [MemberShipLevelController::class, 'getListLevel']);
        Route::get('detail/{id}', [MemberShipLevelController::class, 'detail']);
        Route::post('submit_detail/{id}', [MemberShipLevelController::class, 'submit_detail']);
    });

    Route::group(['prefix' => 'kpi'], function () {
        Route::get('violation_ticket', [KPIController::class, 'violation_ticket']);
        Route::post('getViolationTicket', [KPIController::class, 'getViolationTicket']);
        Route::get('detail_violation_ticket/{id?}', [KPIController::class, 'detail_violation_ticket']);
        Route::post('detail_violation_ticket/{id?}', [KPIController::class, 'detail_violation_ticket']);
        Route::get('delete_violation_ticket/{id?}', [KPIController::class, 'delete_violation_ticket']);
        Route::get('kpi_user', [KPIController::class, 'kpi_user']);
        Route::post('submitKPI', [KPIController::class, 'submitKPI']);
        Route::get('kpi_manager', [KPIController::class, 'kpi_manager']);
        Route::post('submitKPIManager', [KPIController::class, 'submitKPIManager']);
        Route::get('report_synthetic_kpi_user', [KPIController::class, 'report_synthetic_kpi_user']);
        Route::post('getReportSyntheticKpiUser', [KPIController::class, 'getReportSyntheticKpiUser']);
        Route::get('detail_report_synthetic_kpi_user', [KPIController::class, 'detail_report_synthetic_kpi_user']);
        Route::post('detail_report_synthetic_kpi_user', [KPIController::class, 'detail_report_synthetic_kpi_user']);
        Route::get('load_add_report_synthetic_kpi_user', [KPIController::class, 'load_add_report_synthetic_kpi_user']);
        Route::get('load_add_report_synthetic_kpi_manager', [KPIController::class, 'load_add_report_synthetic_kpi_manager']);
        Route::post('deleteKPI', [KPIController::class, 'deleteKPI']);
    });

    Route::group(['prefix' => 'question_often'], function () {
        Route::get('list', [QuestionOftenController::class, 'get_list']);
        Route::post('getList', [QuestionOftenController::class, 'getList']);
        Route::get('detail/{id?}', [QuestionOftenController::class, 'getDetail']);
        Route::post('detail/{id?}', [QuestionOftenController::class, 'detail']);
        Route::get('delete/{id}', [QuestionOftenController::class, 'delete']);
        Route::get('setup/{id}', [QuestionOftenController::class, 'setup']);
        Route::get('changeStatus/{id}', [QuestionOftenController::class, 'changeStatus']);
        Route::post('order_by', [QuestionOftenController::class, 'order_by']);
    });

    Route::group(['prefix' => 'package'], function () {
        Route::get('list', [PackageController::class, 'get_list']);
        Route::post('getListPackage', [PackageController::class, 'getListPackage']);
        Route::get('detail/{id?}', [PackageController::class, 'get_detail']);
        Route::post('detail/{id?}', [PackageController::class, 'detail']);
        Route::get('delete/{id}', [PackageController::class, 'delete']);
    });

    Route::group(['prefix' => 'transaction_package'], function () {
        Route::get('list', [TransactionPackageController::class, 'get_list']);
        Route::post('getListTransactionPackage', [TransactionPackageController::class, 'getListTransactionPackage']);
        Route::get('detail/{id?}', [TransactionPackageController::class, 'get_detail']);
        Route::post('detail/{id?}', [TransactionPackageController::class, 'detail']);
        Route::get('delete/{id}', [TransactionPackageController::class, 'delete']);
        Route::get('changeStatus/{id}', [TransactionPackageController::class, 'changeStatus']);
    });


    Route::group(['prefix' => 'socket'], function () {
        Route::post('login_socket', [SocketController::class, 'login_socket']);
        Route::post('sendNotification', [SocketController::class, 'sendNotification']);
    });

    Route::group(['prefix' => 'transaction_bill'], function () {
        Route::get('list', [TransactionBillController::class, 'get_list']);
        Route::post('getList', [TransactionBillController::class, 'getList']);
        Route::get('detail/{id?}', [TransactionBillController::class, 'get_detail']);
        Route::get('delete/{id}', [TransactionBillController::class, 'delete']);
        Route::post('countAll', [TransactionBillController::class, 'countAll']);
        Route::get('view/{id}', [TransactionBillController::class, 'view']);
        Route::post('changeStatus', [TransactionBillController::class, 'changeStatus']);
    });

    Route::group(['prefix' => 'payment'], function () {
        Route::get('list', [PaymentController::class, 'get_list']);
        Route::post('getList', [PaymentController::class, 'getList']);
        Route::get('detail/{id?}', [PaymentController::class, 'get_detail']);
        Route::get('delete/{id}', [PaymentController::class, 'delete']);
        Route::get('view/{id}', [PaymentController::class, 'view']);
        Route::get('changeStatus/{id}', [PaymentController::class, 'changeStatus']);
    });


    Route::group(['prefix' => 'sample_message'], function () {
        Route::post('getSampleMessage', [SampleMessageController::class, 'getSampleMessage']);
        Route::get('detail/{id?}', [SampleMessageController::class, 'get_detail']);
        Route::post('submit/{id}', [SampleMessageController::class, 'submit']);
        Route::get('delete/{id}', [SampleMessageController::class, 'delete']);
    });

    Route::group(['prefix' => 'report'], function () {
        Route::get('report_referral_by_partner', [ReportController::class, 'report_referral_by_partner']);
        Route::post('getReportReferralByPartner', [ReportController::class, 'getReportReferralByPartner']);
        Route::get('report_referral_by_customer', [ReportController::class, 'report_referral_by_customer']);
        Route::post('getReportReferralByCustomer', [ReportController::class, 'getReportReferralByCustomer']);
        Route::get('report_synthetic_payment', [ReportController::class, 'report_synthetic_payment']);
        Route::post('getReportSyntheticPayment', [ReportController::class, 'getReportSyntheticPayment']);
        Route::get('detailReportSyntheticPayment/{month?}/{year?}/{parent_id?}/{customer_id?}', [ReportController::class, 'detailReportSyntheticPayment']);
        Route::post('getDetailReportSyntheticPayment', [ReportController::class, 'getDetailReportSyntheticPayment']);
        Route::get('getReportKPIUser', [ReportController::class, 'getReportKPIUser']);
        Route::get('report_synthetic_partner', [ReportController::class, 'report_synthetic_partner']);
        Route::post('getListSyntheticRevenuePartner', [ReportController::class, 'getListSyntheticRevenuePartner']);
        Route::get('report_synthetic_customer', [ReportController::class, 'report_synthetic_customer']);
        Route::get('report_synthetic_customer_locked', [ReportController::class, 'report_synthetic_customer_locked']);
        Route::get('report_synthetic_customer_payment_due', [ReportController::class, 'report_synthetic_customer_payment_due']);
        Route::post('getListSyntheticCustomer', [ReportController::class, 'getListSyntheticCustomer']);
        Route::get('report_synthetic_spending_customer', [ReportController::class, 'report_synthetic_spending_customer']);
        Route::post('getListSyntheticSpendingCustomer', [ReportController::class, 'getListSyntheticSpendingCustomer']);
        Route::get('report_synthetic_discount_partner', [ReportController::class, 'report_synthetic_discount_partner']);
        Route::post('getListSyntheticDiscountPartner', [ReportController::class, 'getListSyntheticDiscountPartner']);
        Route::get('report_synthetic_upgrade_membership', [ReportController::class, 'report_synthetic_upgrade_membership']);
        Route::post('getListSyntheticUpgradeMembership', [ReportController::class, 'getListSyntheticUpgradeMembership']);
        Route::get('report_synthetic_fee_partner', [ReportController::class, 'report_synthetic_fee_partner']);
        Route::post('getListSyntheticFeePartner', [ReportController::class, 'getListSyntheticFeePartner']);
        Route::get('report_synthetic_fee_customer', [ReportController::class, 'report_synthetic_fee_customer']);
        Route::post('getListSyntheticFeeCustomer', [ReportController::class, 'getListSyntheticFeeCustomer']);
        Route::get('report_synthetic_rose_partner', [ReportController::class, 'report_synthetic_rose_partner']);
        Route::post('getListSyntheticRosePartner', [ReportController::class, 'getListSyntheticRosePartner']);
        Route::get('report_synthetic_kpi', [ReportController::class, 'report_synthetic_kpi']);
        Route::post('getListSyntheticKPI', [ReportController::class, 'getListSyntheticKPI']);
    });

});
