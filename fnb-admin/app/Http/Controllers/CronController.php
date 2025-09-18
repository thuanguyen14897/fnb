<?php

namespace App\Http\Controllers;

use App\Models\ModuleNoti;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\User;
use App\Services\TransactionService;
use App\Services\TransactionPackageService;
use App\Traits\NotificationTrait;
use App\Traits\SocketTrait;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Jobs\SendNotification;
use Illuminate\Support\Str;

class CronController extends Controller
{
    use NotificationTrait,SocketTrait;

    public $fnbTransactionService;
    public $fnbTransactionPackageService;
    public function __construct(Request $request,TransactionService $transactionService,TransactionPackageService $transactionPackageService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbTransactionService = $transactionService;
        $this->fnbTransactionPackageService = $transactionPackageService;
    }

    public function startTransactionTrip()
    {
        $hour_noti_start = get_option('hour_noti_start');
        $hour_noti_end = '07:15';
        if (strtotime(date('H:i')) >= strtotime($hour_noti_start) && strtotime($hour_noti_start) <= strtotime($hour_noti_end)) {
            if ($this->request->user == null) {
                $this->request->user = (object)['token' => Config::get('constant')['token_default']];
            }
            $this->requestTransaction = clone $this->request;


            $this->request->merge(['check_start' => true]);
            $this->request->merge(['status_search' => -1]);
            $this->request->merge(['per_page' => 20]);
            $this->request->merge(['cron' => 1]);
            $response = $this->fnbTransactionService->getListDataTransaction($this->request);
            $dtTransaction = $response->getData(true);
            $dtTransaction = ($dtTransaction['data']['data'] ?? []);
            $dataNoti = [];
            if (!empty($dtTransaction)) {
                foreach ($dtTransaction as $key => $transaction) {
                    $this->requestTransaction->merge(['note' => 'Bắt đầu chuyến đi']);
                    $this->requestTransaction->merge(['status' => 1]);
                    $this->requestTransaction->merge(['staff_status' => Config::get('constant')['user_admin']]);
                    $this->requestTransaction->merge(['transaction_id' => $transaction['id']]);
                    $responseUpdate = $this->fnbTransactionService->changeStatus($this->requestTransaction);
                    $dtUpdate = $responseUpdate->getData(true);
                    $dtUpdate = $dtUpdate['data'] ?? [];
                    if (!empty($dtUpdate) && !empty($dtUpdate['result'])) {
                        $transaction = $dtUpdate['data'];
                        $arr_object_id = [];
                        $dtStaffAdmin = User::select(
                            'tbl_users.name',
                            'tbl_users.id as object_id',
                            'tbl_player_id.player_id as player_id',
                            DB::raw("'staff' as 'object_type'")
                        )
                            ->leftJoin('tbl_player_id', function ($join) {
                                $join->on('tbl_player_id.object_id', '=', 'tbl_users.id');
                                $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                            })
                            ->where('admin', 1)
                            ->where('active', 1)
                            ->get()->toArray();
                        if (!empty($dtStaffAdmin)) {
                            $arr_object_id = array_merge($arr_object_id, $dtStaffAdmin);
                        }
                        $dtCustomer = $transaction['customer']['arr_object_id'] ?? null;
                        if (!empty($dtCustomer)) {
                            $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                        }
                        $arr_object_id = array_values($arr_object_id);
                        $playerId = [];
                        if (!empty($arr_object_id)) {
                            $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                            $content = '';
                            $json_data = json_encode([
                                'transaction_id' => $transaction['id'],
                                'object' => 'transaction'
                            ], JSON_UNESCAPED_UNICODE);
                            $title = '';
                            $title_owen = '';
                            $content = "Chuyến đi " . $transaction['name'] . ', Khách hàng ' . $transaction['customer']['fullname'] . ', ' . _dt_new($transaction['date_start'],
                                    false) . ' - ' . _dt_new($transaction['date_end'],
                                    false) . ' đang trong hành trình, chúc quý khách 1 hành trình trọn vẹn.';
                            $title = 'Bắt đầu chuyến đi';
                            $title_owen = 'Bắt đầu chuyến đi';
                            $data = [
                                'arr_object_id' => $arr_object_id,
                                'player_id' => $playerId,
                                'json_data' => $json_data,
                                'object_id' => $transaction['id'],
                                'content' => $content,
                                'created_by' => 0,
                                'title' => $title,
                                'title_owen' => $title_owen,
                                'type' => 1,
                            ];
                            $dataNoti[] = $data;
                        }
                    }
                }
            }
            $this->sendNotiOnesignalMutile($dataNoti, Config::get('constant')['noti_transaction_start']);
            dd($dataNoti);
        } else {
            die(123);
        }
    }

    public function cancelTransactionTrip()
    {

        if ($this->request->user == null) {
            $this->request->user = (object)['token' => Config::get('constant')['token_default']];
        }
        $this->requestTransaction = clone $this->request;


        $this->request->merge(['check_cancel' => true]);
        $this->request->merge(['status_search' => -1]);
        $this->request->merge(['cron' => 1]);
        $response = $this->fnbTransactionService->getListDataTransaction($this->request);
        $dtTransaction = $response->getData(true);
        $dtTransaction = ($dtTransaction['data']['data'] ?? []);
        $cancel_end = 1;
        $count = 0;
        if (!empty($dtTransaction)) {
            foreach ($dtTransaction as $key => $transaction) {
                $this->requestTransaction->merge(['note' => 'Quá thời chuyến đi']);
                $this->requestTransaction->merge(['status' => 2]);
                $this->requestTransaction->merge(['cancel_end' => $cancel_end]);
                $this->requestTransaction->merge(['staff_status' => Config::get('constant')['user_admin']]);
                $this->requestTransaction->merge(['transaction_id' => $transaction['id']]);
                $responseUpdate =  $this->fnbTransactionService->changeStatus($this->requestTransaction);
                $dtUpdate = $responseUpdate->getData(true);
                $dtUpdate = $dtUpdate['data'] ?? [];
                if (!empty($dtUpdate)) {
                    $transaction = $dtUpdate['data'];
                    $count++;
                    $arr_object_id = [];
                    $dtStaffAdmin = User::select(
                        'tbl_users.name',
                        'tbl_users.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'staff' as 'object_type'")
                    )
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_users.id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                        })
                        ->where('admin', 1)
                        ->where('active', 1)
                        ->get()->toArray();
                    if (!empty($dtStaffAdmin)) {
                        $arr_object_id = array_merge($arr_object_id, $dtStaffAdmin);
                    }
                    $dtCustomer = $transaction['customer']['arr_object_id'] ?? null;
                    if (!empty($dtCustomer)){
                        $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                    }
                    $arr_object_id = array_values($arr_object_id);
                    Notification::notiEndTransaction($transaction, Config::get('constant')['noti_transaction_end'],
                        0, 2, $arr_object_id);
                }
            }
        }
        echo $count;
    }

    public function addGroupPermistionByPermission()
    {
        $dtGroupPermission = DB::table('tbl_group_permissions')->whereNotIn('id', [1, 2, 3])->get();
        if (!empty($dtGroupPermission)) {
            foreach ($dtGroupPermission as $key => $value) {
                foreach (Config::get('permission')['permissions'] as $k => $v) {
                    if ($v['id'] == 'approve') {
                        continue;
                    }
                    $permission = new Permission();
                    $permission->name = $v['id'];
                    $permission->display_name = $v['id'];
                    $permission->group_permission_id = $value->id;
                    $permission->save();
                }
            }
        }
    }

    public function updateCodeClient()
    {
        $client = Clients::get();
        $field = 'client';
        if (!empty($client)) {
            foreach ($client as $key => $value) {
                $q = DB::table('tbl_order_ref')->where('ref_id', 1)->first();
                $ref_no = '';
                if (!empty($q)) {
                    $ref = $q;
                    $prefix = 'KH';
                    $separator = get_option('separator');
                    $ref_no = (!empty($prefix)) ? $prefix . "$separator" : '';
                    $ref_no .= date('dmy', strtotime(to_sql_date(_dthuan($value->created_at)))) . sprintf("%02s",
                            $ref->{$field});
                    $value->code = $ref_no;
                    $value->save();
                    updateReference('client');
                }
            }
        }
    }

    public function sendNotificationModule(){
        $date = date('Y-m-d');
        $day = date('D');
        $hour_run = "05:00";
        $hour_run_end = date('H:i',strtotime("+1 hour",strtotime($hour_run)));
        $dtModuleNoti = ModuleNoti::where('active',1)->orderByRaw('id desc')->get();
        $dataNoti = [];
        $limit = 400;
        if (!empty($dtModuleNoti)){
            foreach ($dtModuleNoti as $key => $value){
                $arr_object_id = [];
                $object_id = $value['id'];
                $check = false;
                if ($value->type == 1){
                    if (!empty($value->day)){
                        foreach ($value->day as $k => $v){
                            $check = true;
                        }
                    }
                    $hour_run = "05:00";
                    $hour_run_end = date('H:i',strtotime("+1 hour",strtotime($hour_run)));
                } else {
                    if ($date == to_sql_date(_dthuan($value->date_send))){
                       $check = true;
                    }
                    $hour_run = strftime("%H:%M", strtotime($value->date_send));
                    $hour_run_end = date('H:i',strtotime("+1 hour",strtotime($hour_run)));
                }
                if ($check == true){
                    if (strtotime(date('H:i')) >= strtotime($hour_run) && strtotime(date('H:i')) <= strtotime($hour_run_end)) {
                        if ($value->type_user == 1 ){
                            $dtClient = Clients::select(
                                'tbl_clients.fullname as name',
                                'tbl_clients.id as object_id',
                                'tbl_player_id.player_id as player_id',
                                DB::raw("'customer' as 'object_type'")
                            )
                                ->where(function ($query) use ($date,$object_id){
                                    $query->where('type_client',1);
                                    $query->where('active',1);
                                    $query->whereNotExists(function ($query) use ($date,$object_id) {
                                        $query->select(DB::raw(1))
                                            ->from('tbl_notification_staff')
                                            ->join('tbl_notification','tbl_notification.id','=','tbl_notification_staff.notification_id')
                                            ->whereColumn('tbl_notification_staff.object_id', 'tbl_clients.id')
                                            ->where('tbl_notification_staff.object_type', "customer")
                                            ->where('tbl_notification.object_type', '401')
                                            ->where('tbl_notification.object_id','=', $object_id)
                                            ->where(DB::raw('DATE_FORMAT(tbl_notification.created_at, "%Y-%m-%d")'),'=', $date);
                                    });
                                })
                                ->leftJoin('tbl_player_id', function ($join) {
                                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                                })
                                ->limit($limit)->get()->toArray();
                            if (!empty($dtClient)) {
                                $arr_object_id = array_merge($arr_object_id, $dtClient);
                            }
                        } elseif ($value->type_user == 5){
                            $customer_id = $value->customer_id;
                            $dtClient = Clients::select(
                                'tbl_clients.fullname as name',
                                'tbl_clients.id as object_id',
                                'tbl_player_id.player_id as player_id',
                                DB::raw("'customer' as 'object_type'")
                            )
                                ->where(function ($query) use ($date,$object_id,$customer_id){
                                    $query->where('tbl_clients.id',$customer_id);
                                    $query->whereNotExists(function ($query) use ($date,$object_id) {
                                        $query->select(DB::raw(1))
                                            ->from('tbl_notification_staff')
                                            ->join('tbl_notification','tbl_notification.id','=','tbl_notification_staff.notification_id')
                                            ->whereColumn('tbl_notification_staff.object_id', 'tbl_clients.id')
                                            ->where('tbl_notification_staff.object_type',"customer")
                                            ->where('tbl_notification.object_type', '401')
                                            ->where('tbl_notification.object_id','=', $object_id)
                                            ->where(DB::raw('DATE_FORMAT(tbl_notification.created_at, "%Y-%m-%d")'),'=', $date);
                                    });
                                })
                                ->leftJoin('tbl_player_id', function ($join) {
                                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                                })
                                ->get()->toArray();
                            if (!empty($dtClient)) {
                                $arr_object_id = array_merge($arr_object_id, $dtClient);
                            }
                        }
                    }
                }

                if ($value->type == 1){
                    if (!empty($value->day)){
                        foreach ($value->day as $k => $v){
                            if ($v->day == $day){
                                $arr_object_id = array_values($arr_object_id);
                                $playerId = [];
                                if (!empty($arr_object_id)) {
                                    $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                                    $content = '';
                                    $json_data = json_encode(['module_noti_id' => $value->id, 'object' => 'module_noti'], JSON_UNESCAPED_UNICODE);
                                    $title = '';
                                    $title_owen = '';

                                    $content = !empty($value->detail) ? $value->detail : $value->name;

                                    $title = $value->name;
                                    $title_owen = $value->name;
                                    $data = [
                                        'arr_object_id' => $arr_object_id,
                                        'player_id' => $playerId,
                                        'json_data' => $json_data,
                                        'content' => $content,
                                        'content_html' => !empty($value) ? $value->content : null,
                                        'created_by' => 0,
                                        'title' => $title,
                                        'title_owen' => $title_owen,
                                        'type' => 0,
                                        'object_id' => $value->id,
                                    ];
                                    $dataNoti[] = $data;
                                }
                            }
                        }
                    }
                } else {
                    if ($date == to_sql_date(_dt($value->date_send))){
                        $arr_object_id = array_values($arr_object_id);
                        $playerId = [];
                        if (!empty($arr_object_id)) {
                            $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                            $content = '';
                            $json_data = json_encode(['module_noti_id' => $value->id, 'object' => 'module_noti'], JSON_UNESCAPED_UNICODE);
                            $title = '';
                            $title_owen = '';

                            $content = !empty($value->detail) ? $value->detail : $value->name;

                            $title = $value->name;
                            $title_owen = $value->name;
                            $data = [
                                'arr_object_id' => $arr_object_id,
                                'player_id' => $playerId,
                                'json_data' => $json_data,
                                'content' => $content,
                                'content_html' => !empty($value) ? $value->content : null,
                                'created_by' => 0,
                                'title' => $title,
                                'title_owen' => $title_owen,
                                'type' => 0,
                                'object_id' => $value->id,
                            ];
                            $dataNoti[] = $data;
                        }
                    }
                }
            }
        }
        $this->sendNotiOnesignalMutile($dataNoti, Config::get('constant')['noti_module']);
    }

    public function webhookPay2s(){
        $expectedToken = '48749480338a3551eebcf62fece8908836aca072e8462f2134';

        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            // Tách token từ chuỗi Bearer
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $receivedToken = $matches[1];
            } else {
                // Nếu header Authorization không đúng định dạng
                $response = [
                    'success' => false,
                    'message' => 'Invalid Authorization header format'
                ];
                \Log::info('pay2s',$response);
                exit();
            }
        } else {
            // Nếu không có header Authorization
            $response = [
                'success' => false,
                'message' => 'Authorization header not found'
            ];
            \Log::info('pay2s',$response);
            exit();
        }

        if ($receivedToken !== $expectedToken) {
            $response = [
                'success' => false,
                'message' => 'Invalid token'
            ];
            \Log::info('pay2s',$response);
            exit();
        }

        $requestBody = file_get_contents('php://input');

        $data = json_decode($requestBody, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            \Log::info('pay2s',$data['transactions']);
            if (isset($data['transactions']) && is_array($data['transactions'])) {
                foreach ($data['transactions'] as $transaction) {
                    $content = $transaction['content'];
                    $reference_no = extractAndNormalizeTransactionCode($content);
                    $this->request->merge(['reference_no' => $reference_no]);
                    if ($transaction['transferType'] == "IN") {
                        if ($this->request->client == null) {
                            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
                        }
                        $response = $this->fnbTransactionPackageService->getDetail($this->request);
                        $data = $response->getData(true);
                        $dtData = $data['dtData'] ?? [];
                        $arr_object_id = [];
                        $dtCustomer[] = [
                            'name' => $dtData['data_customer']['fullname'],
                            'object_id' => $dtData['customer_id'],
                            'player_id' => null,
                            'object_type' => 'customer',
                        ];
                        if (!empty($dtCustomer)){
                            $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                        }
                        $arr_object_id = array_values($arr_object_id);
                        try {
                            if (!empty($dtData)){
                                $payment = $transaction['transferAmount'] ?? 0;
                                if ($payment < $dtData['grand_total']){
                                    $this->request->merge(['id' => $dtData['id']]);
                                    $this->request->merge(['payment' => $payment]);
                                    $this->request->merge(['payment_error' => 1]);
                                    $this->fnbTransactionPackageService->updateTransaction($this->request);
                                    $this->sendNotificationSocket([
                                        'channels' => $arr_object_id,
                                        'event' => 'check-payment',
                                        'data' => [
                                            'message' => 'Số tiền thanh toán đã thay đổi. Vui lòng liên hệ Admin để được hỗ trợ',
                                            'payment_error' => true,
                                            'data' => $dtData
                                        ],
                                        'db_name' => config('database.connections.mysql.database')
                                    ], 'change-status');
                                    $response = [
                                        'success' => false,
                                        'message' => 'Số tiền thanh toán đã thay đổi. Vui lòng liên hệ Admin để được hỗ trợ'
                                    ];
                                    return response()->json($response);
                                }
                                $this->request->merge(['id' => $dtData['id']]);
                                $this->request->merge(['payment' => $payment]);
                                $this->request->merge(['check_pay2s' => 1]);
                                $this->request->merge(['staff_status' => Config::get('constant')['user_admin']]);
                                $response = $this->fnbTransactionPackageService->changeStatus($this->request);
                                $dataRes = $response->getData(true);
                                $data = $dataRes['data'];
                                $response = [
                                    'result' => $data['message'] ?? true,
                                    'message' => $data['message'] ?? null
                                ];
                                \Log::info('pay2s',$response);
                            }
                        } catch (\Exception $exception){
                            $response = [
                                'success' => false,
                                'message' => $exception->getMessage()
                            ];
                            \Log::info('pay2s',$response);
                        }
                    }
                }
                $response = [
                    'success' => true,
                    'message' => 'Transactions processed successfully'
                ];
                \Log::info('pay2s',$response);
                http_response_code(200);
            } else {
                // Phản hồi lỗi nếu không có 'transactions'
                $response = [
                    'success' => false,
                    'message' => 'Invalid payload, transactions not found'
                ];
                \Log::info('pay2s',$response);
                http_response_code(400);
            }
        } else {
            // Phản hồi lỗi nếu JSON không hợp lệ
            $response = [
                'success' => false,
                'message' => 'Invalid JSON'
            ];
            \Log::info('pay2s',$response);
            http_response_code(400);
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

}
