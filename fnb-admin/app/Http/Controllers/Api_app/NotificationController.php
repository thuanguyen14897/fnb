<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\NotificationCollection;
use App\Http\Resources\Notification as NotificationResource;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificationController extends AuthController
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getListNotification(){
        $current_page = 1;
        $per_page = 10;
        $status = $this->request->input('status',-1);
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
            ->where(function ($query) use ($customer_id,$status){
                $query->where('tbl_notification_staff.object_id',$customer_id);
                $query->where(function ($q){
                    $q->where('tbl_notification_staff.object_type','customer');
                });
                if ($status != -1){
                    if ($status == 2){
                        $query->where('tbl_notification.object_type',Config::get('constant')['noti_module']);
                    } else {
                        $query->where('tbl_notification.object_type','!=',Config::get('constant')['noti_module']);
                    }
                }
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
}
