<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->per_page = 10;
        DB::enableQueryLog();
    }

    public function loadNoti(){
        $current_page = 1;
        $staff_id = get_staff_user_id();
        $dtNotification = Notification::select(
                'tbl_notification.*',
                'tbl_notification_staff.is_read',
                'tbl_notification_staff.object_id as customer_id',
            )
            ->join('tbl_notification_staff','tbl_notification_staff.notification_id','=','tbl_notification.id')
            ->where(function ($query) use ($staff_id){
                $query->where('tbl_notification_staff.object_id',$staff_id);
                $query->where('tbl_notification_staff.object_type','staff');
            })
            ->orderByRaw('tbl_notification.created_at desc')
            ->paginate($this->per_page, ['*'], '', $current_page);
        $next = !empty($dtNotification->hasMorePages()) ? 1 : 0;
        return view('admin.notification.list_noti',[
            'dtNotification' => $dtNotification,
            'next' => $next,
        ]);
    }

    public function loadMoreNoti(){
        $current_page = $this->request->input('page');
        $staff_id = get_staff_user_id();
        $dtNotification = Notification::select(
            'tbl_notification.*',
            'tbl_notification_staff.is_read',
            'tbl_notification_staff.object_id as customer_id',
        )
            ->join('tbl_notification_staff','tbl_notification_staff.notification_id','=','tbl_notification.id')
            ->where(function ($query) use ($staff_id){
                $query->where('tbl_notification_staff.object_id',$staff_id);
                $query->where('tbl_notification_staff.object_type','staff');
            })
            ->orderByRaw('tbl_notification.created_at desc')
            ->paginate($this->per_page, ['*'], '', $current_page);
        $next = !empty($dtNotification->hasMorePages()) ? 1 : 0;
        return view('admin.notification.list_noti', [
            'dtNotification' => $dtNotification,
            'next' => $next,
        ]);
    }

    public function readSingleNoti(){
        $notification_id = $this->request->input('notification_id');
        $success = DB::table('tbl_notification_staff')
            ->where(function ($query) use ($notification_id){
                $query->where('is_read',0);
                $query->where('notification_id',$notification_id);
            })
            ->update(['is_read' => 1]);
        if ($success){
            $data['result'] = true;
            $data['message'] = lang('dt_success');
        } else {
            $data['result'] = false;
            $data['message'] = lang('dt_error');
        }
        return response()->json($data);
    }

    public function readAllNoti(){
        $staff_id = get_staff_user_id();
        $type = !empty($this->request->input('type')) ? $this->request->input('type') : null;

        $success = DB::table('tbl_notification_staff')
            ->where(function ($query) use ($staff_id,$type){
                $query->where('is_read',0);
                $query->where('object_id',$staff_id);
                $query->where('object_type',$type);
            })
            ->update(['is_read' => 1]);
        if ($success) {
            $data['result'] = true;
            $data['message'] = lang('dt_success');
        } else {
            $data['result'] = false;
            $data['message'] = lang('dt_error');
        }
        return response()->json($data);
    }
}
