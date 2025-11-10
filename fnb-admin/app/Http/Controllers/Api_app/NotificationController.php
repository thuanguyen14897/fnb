<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\NotificationCollection;
use App\Http\Resources\Notification as NotificationResource;
use App\Models\Notification;
use App\Models\User;
use App\Traits\SocketTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificationController extends AuthController
{
    use SocketTrait;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getListNotification(){
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page =$this->request->query('per_page');
        }
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $dtNotification = Notification::select(
                'tbl_notification.*',
                'tbl_notification_staff.is_read',
                'tbl_notification_staff.object_id as customer_id',
                'tbl_notification_staff.object_type as type_customer',
            )
            ->join('tbl_notification_staff','tbl_notification_staff.notification_id','=','tbl_notification.id')
            ->where(function ($query) use ($customer_id){
                $query->where('tbl_notification_staff.object_id',$customer_id);
                $query->where(function ($q){
                    $q->where('tbl_notification_staff.object_type','customer');
                    $q->orWhere('tbl_notification_staff.object_type','owen');
                });
            })
            ->orderByRaw('tbl_notification.created_at desc')
            ->paginate($per_page, ['*'], '', $current_page);
        return new NotificationCollection($dtNotification);
    }

    public function getDetail($id = 0){
        $dtNotification = Notification::select(
            'tbl_notification.*',
            'tbl_notification_staff.is_read',
            'tbl_notification_staff.object_id as customer_id',
            'tbl_notification_staff.object_type as type_customer',
        )
            ->join('tbl_notification_staff','tbl_notification_staff.notification_id','=','tbl_notification.id')
            ->where(function ($query) use ($id){
                $query->where('tbl_notification.id',$id);
            })->first();
        return NotificationResource::make($dtNotification);
    }

    public function readAllNotification(){
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $type = !empty($this->request->input('type')) ? $this->request->input('type') : null;
        if (empty($customer_id)){
            $data['result'] = false;
            $data['message'] = lang('khong_ton_tai_nguoi_dung');
            return response()->json($data);
        }
        $success = DB::table('tbl_notification_staff')
            ->where(function ($query) use ($customer_id,$type){
                $query->where('is_read',0);
                $query->where('object_id',$customer_id);
                $query->where(function ($q){
                    $q->where('tbl_notification_staff.object_type','customer');
                    $q->orWhere('tbl_notification_staff.object_type','owen');
                });
            })
            ->update(['is_read' => 1]);

        $data['result'] = 1;
        $data['message'] = lang('dt_success');
        return response()->json($data);

    }

    public function readSingleNotification(){
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $notification_id = $this->request->input('notification_id') ? $this->request->input('notification_id') : 0;
        $notification = Notification::find($notification_id);
        if (empty($notification)){
            $data['result'] = false;
            $data['message'] = lang('khong_ton_tai_thong_bao');
            return response()->json($data);
        }
        if (empty($customer_id)){
            $data['result'] = false;
            $data['message'] = lang('khong_ton_tai_nguoi_dung');
            return response()->json($data);
        }
        $success = DB::table('tbl_notification_staff')
            ->where(function ($query) use ($customer_id,$notification_id){
                $query->where('is_read',0);
                $query->where('object_id',$customer_id);
                $query->where('notification_id',$notification_id);
            })
            ->update(['is_read' => 1]);

        $data['result'] = 1;
        $data['message'] = lang('dt_success');
        return response()->json($data);
    }

    public function checkReadNoti(){
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $type = !empty($this->request->input('type')) ? $this->request->input('type') : null;
        $dtNoti = Notification::whereHas('notification_staff',function ($query) use ($customer_id,$type){
            $query->where('is_read',0);
            $query->where('object_id',$customer_id);
            $query->where(function ($q){
                $q->where('tbl_notification_staff.object_type','customer');
                $q->orWhere('tbl_notification_staff.object_type','owen');
            });
        })->count();
        $data['check'] = $dtNoti;
        return response()->json($data);
    }

    public function getNotiUpgradeMembership(){
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $dtNoti = Notification::whereHas('notification_staff',function ($query) use ($customer_id){
            $query->where('is_read',0);
            $query->where('object_id',$customer_id);
            $query->where(function ($q){
                $q->where('tbl_notification_staff.object_type','customer');
                $q->orWhere('tbl_notification_staff.object_type','owen');
            });
        })
            ->where('object_type','=',Config::get('constant')['noti_upgrade_membership'])
            ->orderByRaw('tbl_notification.created_at desc')
            ->first();
        if (empty($dtNoti)){
            return response()->json(['data' => null]);
        } else {
            return NotificationResource::make($dtNoti);
        }
    }

    public function addNoti(){
        $type_noti = $this->request->input('type_noti') ?? null;
        $arr_object_id = $this->request->input('arr_object_id');
        $dtData = $this->request->input('dtData');
        $customer_id = $this->request->input('customer_id');
        $type = $this->request->input('type');
        $staff_id = $this->request->input('staff_id');
        $point = $this->request->input('point') ?? 0;
        $point_client = $this->request->input('point_client') ?? 0;
        $title_point = $this->request->input('title_point') ?? null;
        $type_check = $this->request->input('type_check') ?? 1;
        $customer_id_new = $customer_id;
        if ($type == 'staff'){
            $check = 1;
            $customer_id = get_staff_user_id() ?? $staff_id;
        } else {
            $check = 2;
            $customer_id = $customer_id;
        }

        $arrType = ['change_point','change_balance'];
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
        if (!in_array($type_noti,$arrType)) {
            if (!empty($dtStaffAdmin)) {
                $arr_object_id = array_merge($arr_object_id, $dtStaffAdmin);
            }
        }
        if ($type_noti == 'change_status_transaction'){
            Notification::notiCancelTransaction($dtData,
                Config::get('constant')['noti_transaction_end'], $customer_id, $check, $arr_object_id);
        } elseif ($type_noti == 'remind_payment'){
            Notification::notiRemindPaymentTransaction($dtData,
                Config::get('constant')['noti_remind_payment'], $customer_id, $check, $arr_object_id);
        } elseif ($type_noti == 'change_point'){
            Notification::notiChangePointClient($customer_id_new,$arr_object_id,$point,$point_client,$title_point);
        } elseif ($type_noti == 'change_balance'){
            Notification::notiChangeBalance($customer_id_new,$arr_object_id,$point,$point_client,$title_point);
        } elseif ($type_noti == 'noti_upgrade_membership'){
            Notification::notiUpgradeMembership($customer_id_new,$dtData, $type_check, $arr_object_id);
        } elseif ($type_noti == 'change_status_payment'){
            $this->sendNotificationSocket([
                'channels' => $arr_object_id,
                'event' => 'check-payment-bill',
                'data' => $dtData,
                'db_name' => config('database.connections.mysql.database')
            ], 'change-status');
            Notification::notiPaymentTransactionBill($dtData,
                Config::get('constant')['noti_transaction_package_payment'], $customer_id, $check, $arr_object_id);
        } elseif ($type_noti == 'change_status_service'){
            if ($dtData['active'] == 1){
                Notification::notiApproveService($dtData,
                    Config::get('constant')['noti_approve_service_active'], $customer_id, $arr_object_id);
            } elseif ($dtData['active'] == 2){
                Notification::notiApproveService($dtData,
                    Config::get('constant')['noti_approve_service_refuse'], $customer_id, $arr_object_id);
            } elseif ($dtData['active'] == 3){
                Notification::notiApproveService($dtData,
                    Config::get('constant')['noti_approve_service_pause'], $customer_id, $arr_object_id);
            }

        } else {
            $this->sendNotificationSocket([
                'channels' => $arr_object_id,
                'event' => 'check-payment',
                'data' => $dtData,
                'db_name' => config('database.connections.mysql.database')
            ], 'change-status');
            Notification::notiPaymentTransactionPackage($dtData,
                Config::get('constant')['noti_transaction_package_payment'], $customer_id, $check, $arr_object_id);
        }
        $data['result'] = true;
        $data['message'] = lang('dt_success');
        return response()->json($data);
    }
}
