<?php

namespace App\Models;

use App\Traits\NotificationTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Mime\Email;

class Notification extends Model
{
    use HasFactory, NotificationTrait;

    protected $table = 'tbl_notification';

    function notification_staff()
    {
        return $this->hasMany('App\Models\NotificationStaff', 'notification_id', 'id');
    }

    static function notiBookCarTransaction($id, $type, $created_by,$check = 1){
        $transaction = Transaction::find($id);
        if (!empty($transaction)) {
            $arr_object_id = [];

            $transactionStaff = User::select(
                'tbl_users.name',
                'tbl_users.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'staff' as 'object_type'")
            )
                ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                })
                ->where('transaction_id', $transaction->id)
                ->get()->toArray();
            if (!empty($transactionStaff)) {
                $arr_object_id = array_merge($arr_object_id, $transactionStaff);
            }

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

            //chu xe
            $dtOwen = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'owen' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $transaction->car->customer->id)
                ->get()->toArray();
            if (!empty($dtOwen)) {
                $arr_object_id = array_merge($arr_object_id, $dtOwen);
            }

            if ($arr_object_id) {
                foreach ($arr_object_id as $key => $value) {
                    if ($check == 1) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 2){
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'customer') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 3) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'owen') {
                            unset($arr_object_id[$key]);
                        }
                    }
                }
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction'], JSON_UNESCAPED_UNICODE);
                $content = "Đặt xe thành công - Khách thuê " . $transaction->customer->fullname . ', xe ' . $transaction->car->number_car . ', ' . _dt_new($transaction->date_start) . ' - ' . _dt_new($transaction->date_end) . '';
                $title = 'Đặt xe thành công';
                $title_owen = 'Đặt xe thành công';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notiApproveTransaction($id, $type, $created_by,$check = 1,$arr_object_id = [])
    {
        $transaction = Transaction::find($id);
        if (!empty($transaction)) {
            if (!empty($arr_object_id)) {
                $arr_object_id = array_values($arr_object_id);
                $playerId = [];
                if (!empty($arr_object_id)) {
                    $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                    $content = '';
                    $json_data = json_encode([
                        'transaction_id' => $id,
                        'object' => 'transaction'
                    ], JSON_UNESCAPED_UNICODE);
                    $title = '';
                    $title_owen = '';
                    $content = "The transaction ".$transaction->reference_no." for purchasing a ".$transaction->category_card->name." membership package of member " . $transaction->customer->fullname . ' has been approved, At '. _dt($transaction->date) . '';
                    $title = 'Approve transaction status';
                    $title_owen = 'Approve transaction status';
                    $data = [
                        'arr_object_id' => $arr_object_id,
                        'player_id' => $playerId,
                        'json_data' => $json_data,
                        'content' => $content,
                        'created_by' => $created_by,
                        'title' => $title,
                        'title_owen' => $title_owen,
                        'type' => null,
                    ];
                    static::addNotification($id, $type, $data);
                }
            }
        }
    }

    static function notiCancelTransaction($id, $type, $created_by, $check = 1,$arr_object_id = [])
    {
        $transaction = Transaction::find($id);
        if (!empty($transaction)) {
            if (empty($arr_object_id)){
                if ($type == Config::get('constant')['noti_cancel_owen']) {
                    $transactionStaff = User::select(
                        'tbl_users.name',
                        'tbl_users.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'staff' as 'object_type'")
                    )
                        ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                        })
                        ->where('transaction_id', $transaction->id)
                        ->get()->toArray();
                    if (!empty($transactionStaff)) {
                        $arr_object_id = array_merge($arr_object_id, $transactionStaff);
                    }
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
                    $dtCustomer = Clients::select(
                        'tbl_clients.fullname as name',
                        'tbl_clients.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'customer' as 'object_type'")
                    )
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                        })
                        ->where('tbl_clients.id', $transaction->customer_id)
                        ->get()->toArray();
                    if (!empty($dtCustomer)) {
                        $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                    }
                } elseif ($type == Config::get('constant')['noti_cancel_guest']) {
                    $transactionStaff = User::select(
                        'tbl_users.name',
                        'tbl_users.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'staff' as 'object_type'")
                    )
                        ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                        })
                        ->where('transaction_id', $transaction->id)
                        ->get()->toArray();
                    if (!empty($transactionStaff)) {
                        $arr_object_id = array_merge($arr_object_id, $transactionStaff);
                    }
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

                    //chu xe
                    $dtCustomer = Clients::select(
                        'tbl_clients.fullname as name',
                        'tbl_clients.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'owen' as 'object_type'")
                    )
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                        })
                        ->where('tbl_clients.id', $transaction->car->customer->id)
                        ->get()->toArray();
                    if (!empty($dtCustomer)) {
                        $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                    }
                } elseif($type == Config::get('constant')['noti_cancel_system']){
                    $transactionStaff = User::select(
                        'tbl_users.name',
                        'tbl_users.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'staff' as 'object_type'")
                    )
                        ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                        })
                        ->where('transaction_id', $transaction->id)
                        ->get()->toArray();
                    if (!empty($transactionStaff)) {
                        $arr_object_id = array_merge($arr_object_id, $transactionStaff);
                    }
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

                    $dtCustomer = Clients::select(
                        'tbl_clients.fullname as name',
                        'tbl_clients.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'customer' as 'object_type'")
                    )
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                        })
                        ->where('tbl_clients.id', $transaction->customer_id)
                        ->get()->toArray();
                    if (!empty($dtCustomer)) {
                        $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                    }

                    //chu xe
                    $dtOwner = Clients::select(
                        'tbl_clients.fullname as name',
                        'tbl_clients.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'owen' as 'object_type'")
                    )
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                        })
                        ->where('tbl_clients.id', $transaction->car->customer->id)
                        ->get()->toArray();
                    if (!empty($dtOwner)) {
                        $arr_object_id = array_merge($arr_object_id, $dtOwner);
                    }
                }
                if ($arr_object_id) {
                    foreach ($arr_object_id as $key => $value) {
                        if ($check == 1) {
                            if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                                unset($arr_object_id[$key]);
                            }
                        } elseif($check == 2){
                            if ($created_by == $value['object_id'] && $value['object_type'] == 'customer') {
                                unset($arr_object_id[$key]);
                            }
                        } elseif($check == 3) {
                            if ($created_by == $value['object_id'] && $value['object_type'] == 'owen') {
                                unset($arr_object_id[$key]);
                            }
                        }
                    }
                }
                $arr_object_id = array_values($arr_object_id);
            }
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                if ($type == Config::get('constant')['noti_cancel_owen']) {
                    $content = "Chủ xe " . $transaction->car->customer->fullname . ' đã hủy chuyến đi , xe ' . $transaction->car->number_car . ', ' . _dt_new($transaction->date_start) . ' - ' . _dt_new($transaction->date_end) . '';
                    $title = 'Chủ xe hủy chuyến';
                    $title_owen = 'Chủ xe hủy chuyến';
                } elseif ($type == Config::get('constant')['noti_cancel_guest']) {
                    $content = "Khách thuê  " . $transaction->customer->fullname . ' đã hủy chuyến đi , xe ' . $transaction->car->number_car . ', ' . _dt_new($transaction->date_start) . ' - ' . _dt_new($transaction->date_end) . '';
                    $title = 'Khách thuê hủy chuyến';
                    $title_owen = 'Khách thuê hủy chuyến';
                } elseif($type == Config::get('constant')['noti_cancel_system']){
                    if ($transaction->cancel_despoit == 1){
                        $content = "Chuyến đi " . $transaction->reference_no . ', Khách thuê '.$transaction->customer->fullname.', xe ' . $transaction->car->number_car . ', ' . _dt_new($transaction->date_start) . ' - ' . _dt_new($transaction->date_end) . ' đã được hệ thống hủy do quá thời gian đặt cọc xe.';
                    } elseif ($transaction->cancel_note_approve == 1){
                        $content = "Chuyến đi " . $transaction->reference_no . ', Khách thuê '.$transaction->customer->fullname.', xe ' . $transaction->car->number_car . ', ' . _dt_new($transaction->date_start) . ' - ' . _dt_new($transaction->date_end) . ' đã được hệ thống hủy do quá thời gian duyệt yêu cầu thuê xe.';
                    } else {
                        $content = "Chuyến đi " . $transaction->reference_no . ', Khách thuê '.$transaction->customer->fullname.', xe ' . $transaction->car->number_car . ', ' . _dt_new($transaction->date_start) . ' - ' . _dt_new($transaction->date_end) . ' đã được hệ thống hủy do khách thuê đã đặt cọc chuyến đi khác.';
                    }
                    $title = 'Hệ thống hủy chuyến';
                    $title_owen = 'Hệ thống hủy chuyến';
                }
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notiRemindFinishTransaction($id, $type, $created_by, $check = 1,$arr_object_id = [])
    {
        $transaction = Transaction::find($id);
        if (!empty($transaction)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';
                $content = "Chuyến đi " . $transaction->reference_no . ', Khách thuê '.$transaction->customer->fullname.', xe ' . $transaction->car->number_car . ', ' . _dt_new($transaction->date_start) . ' - ' . _dt_new($transaction->date_end) . ' đã hoàn thành. Vui lòng bấm nhận xe.';
                $title = 'Nhắc nhở nhận xe';
                $title_owen = 'Nhắc nhở nhận xe';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notiStartTransaction($id, $type, $created_by, $check = 1)
    {
        $transaction = Transaction::find($id);
        if (!empty($transaction)) {
            $arr_object_id = [];

            $transactionStaff = User::select(
                'tbl_users.name',
                'tbl_users.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'staff' as 'object_type'")
            )
                ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                })
                ->where('transaction_id', $transaction->id)
                ->get()->toArray();
            if (!empty($transactionStaff)) {
                $arr_object_id = array_merge($arr_object_id, $transactionStaff);
            }
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
            //chu xe
            $dtCustomer = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'owen' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $transaction->car->customer->id)
                ->get()->toArray();
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
            // nguoi thue xe
            $dtGuest = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $transaction->customer_id)
                ->get()->toArray();
            if (!empty($dtGuest)) {
                $arr_object_id = array_merge($arr_object_id, $dtGuest);
            }

            if ($arr_object_id) {
                foreach ($arr_object_id as $key => $value) {
                    if ($check == 1) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 2){
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'customer') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 3) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'owen') {
                            unset($arr_object_id[$key]);
                        }
                    }
                }
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction'], JSON_UNESCAPED_UNICODE);
                $content = "Chuyến đi " . $transaction->reference_no . ', Khách thuê '.$transaction->customer->fullname.', xe ' . $transaction->car->number_car . ', ' . _dt_new($transaction->date_start) . ' - ' . _dt_new($transaction->date_end) . ' đang khởi hành';
                $title = 'Khởi hành chuyến';
                $title_owen = 'Khởi hành chuyến';

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notiFinishTransaction($id, $type, $created_by, $check = 1)
    {
        $transaction = Transaction::find($id);
        if (!empty($transaction)) {
            $arr_object_id = [];

            $transactionStaff = User::select(
                'tbl_users.name',
                'tbl_users.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'staff' as 'object_type'")
            )
                ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                })
                ->where('transaction_id', $transaction->id)
                ->get()->toArray();
            if (!empty($transactionStaff)) {
                $arr_object_id = array_merge($arr_object_id, $transactionStaff);
            }
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
            //chu xe
            $dtCustomer = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'owen' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $transaction->car->customer->id)
                ->get()->toArray();
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
            // nguoi thue xe
            $dtGuest = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $transaction->customer_id)
                ->get()->toArray();
            if (!empty($dtGuest)) {
                $arr_object_id = array_merge($arr_object_id, $dtGuest);
            }

            if ($arr_object_id) {
                foreach ($arr_object_id as $key => $value) {
                    if ($check == 1) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 2){
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'customer') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 3) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'owen') {
                            unset($arr_object_id[$key]);
                        }
                    }
                }
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction'], JSON_UNESCAPED_UNICODE);
                $content = "Chuyến đi " . $transaction->reference_no . ', Khách thuê '.$transaction->customer->fullname.', xe ' . $transaction->car->number_car . ', ' . _dt_new($transaction->date_start) . ' - ' . _dt_new($transaction->date_end) . ' đã hoàn thành';
                $title = 'Hoàn thành chuyến';
                $title_owen = 'Hoàn thành chuyến';

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notiDespoitTransaction($id, $type, $created_by,$check = 1){
        $payment = Payment::find($id);
        if (!empty($payment)) {
            $arr_object_id = [];
            $transaction_id = 0;
            $customer_id = 0;
            if (!empty($payment->transaction)){
                foreach ($payment->transaction as $value){
                    $transaction_id = $value->pivot->transaction_id;
                    $customer_id = $value->car->customer->id;
                }
            }
            $transaction = Transaction::find($transaction_id);
            $transactionStaff = User::select(
                'tbl_users.name',
                'tbl_users.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'staff' as 'object_type'")
            )
                ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                })
                ->where('transaction_id', $transaction_id)
                ->get()->toArray();
            if (!empty($transactionStaff)) {
                $arr_object_id = array_merge($arr_object_id, $transactionStaff);
            }

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

            //chu xe
            $dtOwen = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'owen' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $customer_id)
                ->get()->toArray();
            if (!empty($dtOwen)) {
                $arr_object_id = array_merge($arr_object_id, $dtOwen);
            }

            if ($arr_object_id) {
                foreach ($arr_object_id as $key => $value) {
                    if ($check == 1) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 2){
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'customer') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 3) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'owen') {
                            unset($arr_object_id[$key]);
                        }
                    }
                }
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['payment_id'=>$id,'transaction_id' => $transaction_id,'type' => $transaction->type,'object'=>'transaction'], JSON_UNESCAPED_UNICODE);
                $content = "Chuyến đi " . $transaction->reference_no . ', Khách thuê '.$transaction->customer->fullname.', xe ' . $transaction->car->number_car . ', ' . _dt_new($transaction->date_start) . ' - ' . _dt_new($transaction->date_end) . ' đã đặt cọc thành công. Số tiền '.formatMoney($payment->payment).'';
                $title = 'Đặt cọc xe';
                $title_owen = 'Đặt cọc xe';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($transaction_id, $type, $data);
            }
        }
    }

    static function notiAccuracyLicense($id, $type, $created_by){
        $drivingLiscense = DrivingLiscense::find($id);
        if (!empty($drivingLiscense)) {
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

            $dtCustomer = DrivingLiscense::select(
                    'tbl_clients.fullname as name',
                    'tbl_clients.id as object_id',
                    'tbl_player_id.player_id as player_id',
                    DB::raw("'customer' as 'object_type'")
                )
                ->join('tbl_clients','tbl_clients.id','=','tbl_driving_liscense_client.customer_id')
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $drivingLiscense->customer_id)
                ->get()->toArray();
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
            if ($arr_object_id) {
                foreach ($arr_object_id as $key => $value) {
                    if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                        unset($arr_object_id[$key]);
                    }
                }
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['driving_liscense_client_id' => $id,'object'=>'driving_liscense_client','status'=>$drivingLiscense->status], JSON_UNESCAPED_UNICODE);
                if ($drivingLiscense->status == 1) {
                    $content = "Giấy phép lái xe " . $drivingLiscense->customer->fullname . ". Đã được xác nhận!";
                    $title = 'Xác nhận giấy phép lái xe';
                    $title_owen = 'Xác nhận giấy phép lái xe';
                } elseif ($drivingLiscense->status == 2){
                    $content = "Giấy phép lái xe " . $drivingLiscense->customer->fullname . ". Không được xác nhận!";
                    $title = 'Không xác nhận giấy phép lái xe';
                    $title_owen = 'Không xác nhận giấy phép lái xe';
                } elseif ($drivingLiscense->status == 0){
                    $content = "Giấy phép lái xe " . $drivingLiscense->customer->fullname . ". Chưa được xác nhận!";
                    $title = 'Chưa xác nhận giấy phép lái xe';
                    $title_owen = 'Chưa xác nhận giấy phép lái xe';
                }
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notiAccuracyBusiness($id, $type, $created_by){
        $clientBusiness = ClientBusiness::find($id);
        if (!empty($clientBusiness)) {
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

            $dtCustomer = ClientBusiness::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )
                ->join('tbl_clients','tbl_clients.id','=','tbl_client_business.customer_id')
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $clientBusiness->customer_id)
                ->get()->toArray();
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
            if ($arr_object_id) {
                foreach ($arr_object_id as $key => $value) {
                    if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                        unset($arr_object_id[$key]);
                    }
                }
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['business_client_id' => $id,'object'=>'client_business','status'=>$clientBusiness->status], JSON_UNESCAPED_UNICODE);
                if ($clientBusiness->status == 1) {
                    $content = "Tài khoản doanh nghiệp " . $clientBusiness->customer->fullname . ". Đã được xác nhận!";
                    $title = 'Xác nhận tài khoản doanh nghiệp';
                    $title_owen = 'Xác nhận tài khoản doanh nghiệp';
                } elseif ($clientBusiness->status == 2){
                    $content = "Tài khoản doanh nghiệp " . $clientBusiness->customer->fullname . ". Không được xác nhận!";
                    $title = 'Không xác nhận tài khoản doanh nghiệp';
                    $title_owen = 'Không xác nhận tài khoản doanh nghiệp';
                } elseif ($clientBusiness->status == 0){
                    $content = "Tài khoản doanh nghiệp " . $clientBusiness->customer->fullname . ". Chưa được xác nhận!";
                    $title = 'Chưa xác nhận tài khoản doanh nghiệp';
                    $title_owen = 'Chưa xác nhận tài khoản doanh nghiệp';
                }
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notiSystemCancelTransaction($transaction = []){
        $notification = new Notification();
        $dataNoti = [];
        if (!empty($transaction)){
            foreach ($transaction as $key => $value){
                $arr_object_id = [];
                $transactionStaff = User::select(
                    'tbl_users.name',
                    'tbl_users.id as object_id',
                    'tbl_player_id.player_id as player_id',
                    DB::raw("'staff' as 'object_type'")
                )
                    ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                    ->leftJoin('tbl_player_id', function ($join) {
                        $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                        $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                    })
                    ->where('transaction_id', $value->id)
                    ->get()->toArray();
                if (!empty($transactionStaff)) {
                    $arr_object_id = array_merge($arr_object_id, $transactionStaff);
                }

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

                $dtCustomer = Clients::select(
                    'tbl_clients.fullname as name',
                    'tbl_clients.id as object_id',
                    'tbl_player_id.player_id as player_id',
                    DB::raw("'customer' as 'object_type'")
                )
                    ->leftJoin('tbl_player_id', function ($join) {
                        $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                        $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                    })
                    ->where('tbl_clients.id', $value->customer_id)
                    ->get()->toArray();
                if (!empty($dtCustomer)) {
                    $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                }

                //chu xe
                $dtOwen = Clients::select(
                    'tbl_clients.fullname as name',
                    'tbl_clients.id as object_id',
                    'tbl_player_id.player_id as player_id',
                    DB::raw("'owen' as 'object_type'")
                )
                    ->leftJoin('tbl_player_id', function ($join) {
                        $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                        $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                    })
                    ->where('tbl_clients.id', $value->car->customer->id)
                    ->get()->toArray();
                if (!empty($dtOwen)) {
                    $arr_object_id = array_merge($arr_object_id, $dtOwen);
                }

                $arr_object_id = array_values($arr_object_id);
                $playerId = [];
                if (!empty($arr_object_id)) {
                    $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                    $content = '';
                    $json_data = json_encode(['transaction_id' => $value->id,'type' => $value->type,'object'=>'transaction'], JSON_UNESCAPED_UNICODE);
                    $title = '';
                    $title_owen = '';
                    $content = "Chuyến đi " . $value->reference_no . ', Khách thuê '.$value->customer->fullname.', xe ' . $value->car->number_car . ', ' . _dt_new($value->date_start) . ' - ' . _dt_new($value->date_end) . ' đã được hệ thống hủy do khách thuê đã đặt cọc chuyến đi khác.';
                    $title = 'Hệ thống hủy chuyến';
                    $title_owen = 'Hệ thống hủy chuyến';
                    $data = [
                        'arr_object_id' => $arr_object_id,
                        'player_id' => $playerId,
                        'json_data' => $json_data,
                        'object_id' => $value['id'],
                        'content' => $content,
                        'created_by' => 0,
                        'title' => $title,
                        'title_owen' => $title_owen,
                        'type' => $value->type,
                    ];
                    $dataNoti[] = $data;
                }
            }
        }
        $notification->sendNotiOnesignalMutile($dataNoti,Config::get('constant')['noti_cancel_system']);
    }

    static function notiApproveCar($id, $type, $created_by){
        $dtCar = Car::find($id);
        if (!empty($dtCar)) {
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

            $dtCustomer = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'owen' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $dtCar->customer_id)
                ->get()->toArray();
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
            if ($arr_object_id) {
                foreach ($arr_object_id as $key => $value) {
                    if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                        unset($arr_object_id[$key]);
                    }
                }
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['car_id' => $id,'object'=>'car','status'=>$dtCar->status], JSON_UNESCAPED_UNICODE);
                if ($dtCar->status == 1) {
                    $content = 'Xe ' . $dtCar->name . ', biển số ' . $dtCar->number_car  . '. Đã được duyệt!';
                    $title = 'Đồng ý duyệt xe';
                    $title_owen = 'Đồng ý duyệt xe';
                } elseif ($dtCar->status == 2){
                    $content = 'Xe ' . $dtCar->name . ', biển số ' . $dtCar->number_car  . '. Đã được bị từ chối!';
                    $title = 'Từ chối xe';
                    $title_owen = 'Từ chối xe';
                } elseif ($dtCar->status == 3){
                    $content = 'Xe ' . $dtCar->name . ', biển số ' . $dtCar->number_car  . '. Đang tạm ngưng!';
                    $title = 'Tạm ngưng xe';
                    $title_owen = 'Tạm ngưng xe';
                }
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notiChangeBalanceOwner($customer = 0,$revenue = 0,$account_balance_client = 0,$title_balance = '',$type_driver = 1){
        if (!empty($customer)){
            $arr_object_id = [];
            $dtOwen = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $customer)
                ->get()->toArray();
            if (!empty($dtOwen)) {
                $arr_object_id = array_merge($arr_object_id, $dtOwen);
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['customer' => $customer,'object'=>'clients','status'=> 'change_balance'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';
                if ($revenue < 0){
                    $prefix = '-';
                } else {
                    $prefix = '+';
                }
                $content = $title_balance . '. Member\'s account at SMB ' . $prefix . formatMoney($revenue) . ' '.get_option('money_unit').'. Available balance: ' . formatMoney($account_balance_client).' '.get_option('money_unit').'';
                $title = 'Member wallet - Change balance';
                $title_owen = 'Member wallet - Change balance';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'object_id' => $customer,
                    'content' => $content,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_owen,
                ];
                static::addNotification($customer, Config::get('constant')['noti_change_balance'], $data);
            }
        }
    }

    static function notiSetupHoliday($type, $created_by,$arr_object_id = [])
    {
        $playerId = [];
        if (!empty($arr_object_id)) {
            $playerId = array_unique(array_column($arr_object_id, 'player_id'));
            $content = '';
            $json_data = json_encode(['type' => 'setup_holiday_day','object'=>'tbl_setup_holiday_day'], JSON_UNESCAPED_UNICODE);
            $title = '';
            $title_owen = '';
            $content = "Hệ thống vừa cập nhập thời gian thuê xe tối thiểu lễ, tết. Vui lòng vào cập nhập lại!";
            $title = 'Lời nhắn';
            $title_owen = 'Lời nhắn';
            $data = [
                'arr_object_id' => $arr_object_id,
                'player_id' => $playerId,
                'json_data' => $json_data,
                'content' => $content,
                'created_by' => $created_by,
                'title' => $title,
                'title_owen' => $title_owen,
            ];
            static::addNotification(0, $type, $data);
        }
    }
    // driver

    static function notiChangeBalanceDriver($driver_id = 0,$revenue = 0,$account_balance_driver = 0,$title_balance = ''){
        if (!empty($driver_id)){
            $arr_object_id = [];
            //tài xế
            $dtDriver = Driver::select(
                'tbl_driver.fullname as name',
                'tbl_driver.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'driver' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_driver.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'driver'"));
                })
                ->where('tbl_driver.id', $driver_id)
                ->get()->toArray();
            if (!empty($dtDriver)) {
                $arr_object_id = array_merge($arr_object_id, $dtDriver);
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['driver_id' => $driver_id,'object'=>'driver','status'=> 'change_balance'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_driver = '';
                if ($revenue < 0){
                    $prefix = '';
                } else {
                    $prefix = '+';
                }
                $content = $title_balance.'. TK tài xế tại kanow '.$prefix.formatMoney($revenue).'. Số dư khả dụng: '.formatMoney($account_balance_driver);
                $title = 'Ví tài xế - Thay đổi số dư';
                $title_driver = 'Ví tài xế - Thay đổi số dư';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'object_id' => $driver_id,
                    'content' => $content,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_driver,
                ];
                static::addNotificationDriver($driver_id, Config::get('constant')['noti_change_balance_driver'], $data);
            }
        }
    }

    static function notiFinishTransactionDriver($id, $type, $created_by, $check = 1)
    {
        $transaction = TransactionDriver::find($id);
        if (!empty($transaction)) {
            $arr_object_id = [];
            $arr_object_id_driver = [];

            $transactionStaff = User::select(
                'tbl_users.name',
                'tbl_users.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'staff' as 'object_type'")
            )
                ->join('tbl_transaction_driver_staff', 'tbl_transaction_driver_staff.user_id', '=', 'tbl_users.id')
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_driver_staff.user_id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                })
                ->where('transaction_id', $transaction->id)
                ->get()->toArray();
            if (!empty($transactionStaff)) {
                $arr_object_id = array_merge($arr_object_id, $transactionStaff);
            }
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
            //tài xế
            $dtDriver = Driver::select(
                'tbl_driver.fullname as name',
                'tbl_driver.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'driver' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_driver.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'driver'"));
                })
                ->where('tbl_driver.id', $transaction->driver_id)
                ->get()->toArray();
            if (!empty($dtDriver)) {
                $arr_object_id = array_merge($arr_object_id, $dtDriver);
                $arr_object_id_driver = array_merge($arr_object_id_driver, $dtDriver);
            }
            // người đặt xe
            $dtGuest = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $transaction->customer_id)
                ->get()->toArray();
            if (!empty($dtGuest)) {
                $arr_object_id = array_merge($arr_object_id, $dtGuest);
            }

            if ($arr_object_id) {
                foreach ($arr_object_id as $key => $value) {
                    if ($check == 1) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 2){
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'customer') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 3) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'driver') {
                            unset($arr_object_id[$key]);
                        }
                    }
                }
            }
            //driver
            if ($arr_object_id_driver) {
                foreach ($arr_object_id_driver as $key => $value) {
                    if ($check == 1) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                            unset($arr_object_id_driver[$key]);
                        }
                    } elseif($check == 2){
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'customer') {
                            unset($arr_object_id_driver[$key]);
                        }
                    } elseif($check == 3) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'driver') {
                            unset($arr_object_id_driver[$key]);
                        }
                    }
                }
            }
            $arr_object_id = array_values($arr_object_id);
            $arr_object_id_driver = array_values($arr_object_id_driver);
            $playerId = [];
            $playerIdDriver = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction_driver'], JSON_UNESCAPED_UNICODE);
                $content = "Chuyến đi " . $transaction->reference_no . ', Người đặt xe '.$transaction->customer->fullname.', Tài xế ' . $transaction->driver->fullname . ', ' . _dt_new($transaction->date)  . ' đã hoàn thành';
                $title = 'Hoàn thành chuyến';
                $title_owen = 'Hoàn thành chuyến';

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($id, $type, $data);
            }
            if (!empty($arr_object_id_driver)){
                $playerIdDriver = array_unique(array_column($arr_object_id_driver, 'player_id'));
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction_driver'], JSON_UNESCAPED_UNICODE);
                $content = "Chuyến đi " . $transaction->reference_no . ', Người đặt xe '.$transaction->customer->fullname.', Tài xế ' . $transaction->driver->fullname . ', ' . _dt_new($transaction->date)  . ' đã hoàn thành';
                $title = 'Hoàn thành chuyến';
                $title_owen = 'Hoàn thành chuyến';

                $data = [
                    'arr_object_id' => $arr_object_id_driver,
                    'player_id' => $playerIdDriver,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotificationDriver($id, $type, $data);
            }
        }
    }

    static function notiCancelTransactionDriver($id, $type, $created_by, $check = 1,$arr_object_id = [],$arr_object_id_driver = [],$arr_driver_pusher = [])
    {
        $transaction = TransactionDriver::find($id);
        if (!empty($transaction)) {
            if (empty($arr_object_id)){
                if ($type == Config::get('constant')['noti_driver_cancel_driver']) {
                    $transactionStaff = User::select(
                        'tbl_users.name',
                        'tbl_users.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'staff' as 'object_type'")
                    )
                        ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                        })
                        ->where('transaction_id', $transaction->id)
                        ->get()->toArray();
                    if (!empty($transactionStaff)) {
                        $arr_object_id = array_merge($arr_object_id, $transactionStaff);
                    }
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
                    $dtCustomer = Clients::select(
                        'tbl_clients.fullname as name',
                        'tbl_clients.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'customer' as 'object_type'")
                    )
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                        })
                        ->where('tbl_clients.id', $transaction->customer_id)
                        ->get()->toArray();
                    if (!empty($dtCustomer)) {
                        $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                    }
                } elseif ($type == Config::get('constant')['noti_guest_cancel_driver']) {
                    $transactionStaff = User::select(
                        'tbl_users.name',
                        'tbl_users.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'staff' as 'object_type'")
                    )
                        ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                        })
                        ->where('transaction_id', $transaction->id)
                        ->get()->toArray();
                    if (!empty($transactionStaff)) {
                        $arr_object_id = array_merge($arr_object_id, $transactionStaff);
                    }
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

                } elseif($type == Config::get('constant')['status_system_cancel_driver']){
                    $transactionStaff = User::select(
                        'tbl_users.name',
                        'tbl_users.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'staff' as 'object_type'")
                    )
                        ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                        })
                        ->where('transaction_id', $transaction->id)
                        ->get()->toArray();
                    if (!empty($transactionStaff)) {
                        $arr_object_id = array_merge($arr_object_id, $transactionStaff);
                    }
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

                    $dtCustomer = Clients::select(
                        'tbl_clients.fullname as name',
                        'tbl_clients.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'customer' as 'object_type'")
                    )
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                        })
                        ->where('tbl_clients.id', $transaction->customer_id)
                        ->get()->toArray();
                    if (!empty($dtCustomer)) {
                        $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                    }

                }
                if ($arr_object_id) {
                    foreach ($arr_object_id as $key => $value) {
                        if ($check == 1) {
                            if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                                unset($arr_object_id[$key]);
                            }
                        } elseif($check == 2){
                            if ($created_by == $value['object_id'] && $value['object_type'] == 'customer') {
                                unset($arr_object_id[$key]);
                            }
                        } elseif($check == 3) {
                            if ($created_by == $value['object_id'] && $value['object_type'] == 'driver') {
                                unset($arr_object_id[$key]);
                            }
                        }
                    }
                }
                $arr_object_id = array_values($arr_object_id);
            }
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction_driver'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                if ($type == Config::get('constant')['noti_driver_cancel_driver']) {
                    $content = "Tài xế " . $transaction->driver->fullname . ' đã hủy chuyến đi '.$transaction->reference_no.', Người đặt xe ' . $transaction->customer->fullname . ', ' . _dt_new($transaction->date);
                    $title = 'Tài xế hủy chuyến';
                    $title_owen = 'Tài xế hủy chuyến';
                } elseif ($type == Config::get('constant')['noti_guest_cancel_driver']) {
                    $content = "Người đặt xe  " . $transaction->customer->fullname . ' đã hủy chuyến đi '.$transaction->reference_no.' , Tài xế '.(!empty($transaction->driver->fullname) ? $transaction->driver->fullname : '').', ' . _dt_new($transaction->date) . '';
                    $title = 'Người đặt xe hủy chuyến';
                    $title_owen = 'Người đặt xe hủy chuyến';
                } elseif($type == Config::get('constant')['status_system_cancel_driver']){
                    $content = "Chuyến đi " . $transaction->reference_no . ', Người đặt xe '.$transaction->customer->fullname.', ' . _dt_new($transaction->date) . ' đã được hệ thống hủy do không tìm thấy tài xế.';
                    $title = 'Hệ thống hủy chuyến';
                    $title_owen = 'Hệ thống hủy chuyến';
                }
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($id, $type, $data);
            }

            if (empty($arr_object_id_driver)){
                //tài xế
                $dtDriver = Driver::select(
                    'tbl_driver.fullname as name',
                    'tbl_driver.id as object_id',
                    'tbl_player_id.player_id as player_id',
                    DB::raw("'driver' as 'object_type'")
                )
                    ->leftJoin('tbl_player_id', function ($join) {
                        $join->on('tbl_player_id.object_id', '=', 'tbl_driver.id');
                        $join->on('tbl_player_id.object_type', '=', DB::raw("'driver'"));
                    })
                    ->where('tbl_driver.id', $transaction->driver_id)
                    ->get()->toArray();
                if (!empty($dtDriver)) {
                    $arr_object_id_driver = array_merge($arr_object_id_driver, $dtDriver);
                }
                if (count($arr_driver_pusher) > 0) {
                    $dtDriverPusher = Driver::select(
                        'tbl_driver.fullname as name',
                        'tbl_driver.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'driver' as 'object_type'")
                    )
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_driver.id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'driver'"));
                        })
                        ->where('tbl_driver.id', $arr_driver_pusher[0]->driver_id)
                        ->get()->toArray();
                    if (!empty($dtDriverPusher)) {
                        $arr_object_id_driver = array_merge($arr_object_id_driver, $dtDriverPusher);
                    }
                }
                if ($arr_object_id_driver) {
                    foreach ($arr_object_id_driver as $key => $value) {
                        if ($check == 1) {
                            if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                                unset($arr_object_id_driver[$key]);
                            }
                        } elseif($check == 2){
                            if ($created_by == $value['object_id'] && $value['object_type'] == 'customer') {
                                unset($arr_object_id_driver[$key]);
                            }
                        } elseif($check == 3) {
                            if ($created_by == $value['object_id'] && $value['object_type'] == 'driver') {
                                unset($arr_object_id_driver[$key]);
                            }
                        }
                    }
                }
                $arr_object_id_driver = array_values($arr_object_id_driver);
            }
            $playerId = [];
            if (!empty($arr_object_id_driver)) {
                $playerId = array_unique(array_column($arr_object_id_driver, 'player_id'));
                $content = '';
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction_driver'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                if ($type == Config::get('constant')['noti_driver_cancel_driver']) {
                    $content = "Tài xế " . $transaction->driver->fullname . ' đã hủy chuyến đi '.$transaction->reference_no.', Người đặt xe ' . $transaction->customer->fullname . ', ' . _dt_new($transaction->date);
                    $title = 'Tài xế hủy chuyến';
                    $title_owen = 'Tài xế hủy chuyến';
                } elseif ($type == Config::get('constant')['noti_guest_cancel_driver']) {
                    $content = "Người đặt xe  " . $transaction->customer->fullname . ' đã hủy chuyến đi '.$transaction->reference_no.' , Tài xế '.(!empty($transaction->driver->fullname) ? $transaction->driver->fullname : '').', ' . _dt_new($transaction->date) . '';
                    $title = 'Người đặt xe hủy chuyến';
                    $title_owen = 'Người đặt xe hủy chuyến';
                } elseif($type == Config::get('constant')['status_system_cancel_driver']){
                    $content = "Chuyến đi " . $transaction->reference_no . ', Người đặt xe '.$transaction->customer->fullname.', ' . _dt_new($transaction->date) . ' đã được hệ thống hủy do không tìm thấy tài xế.';
                    $title = 'Hệ thống hủy chuyến';
                    $title_owen = 'Hệ thống hủy chuyến';
                }
                $data = [
                    'arr_object_id' => $arr_object_id_driver,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotificationDriver($id, $type, $data);
            }
        }
    }

    static function notiBookDriverTransaction($id, $type, $created_by, $check = 1,$arr_object_id = [],$arr_object_id_driver = [])
    {
        $transaction = TransactionDriver::find($id);
        if (!empty($transaction)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $content_driver = '';
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction_driver'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                $content = "Chuyến đi " . $transaction->reference_no . ', Người đặt xe '.$transaction->customer->fullname.', ' . _dt_new($transaction->date) . ' đã tìm được tài xế!.';
                $title = 'Đã tìm thấy tài xế';
                $title_owen = 'Đã xác nhận chuyến đi';

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'content_driver' => $content_driver,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($id, $type, $data);
            }
            $playerId = [];
            if (!empty($arr_object_id_driver)) {
                $playerId = array_unique(array_column($arr_object_id_driver, 'player_id'));
                $content = '';
                $content_driver = '';
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction_driver'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                $content = "Bạn đã xác nhận chuyến đi " . $transaction->reference_no . ', Người đặt xe '.$transaction->customer->fullname.', ' . _dt_new($transaction->date);
                $title = 'Đã xác nhận chuyến đi';
                $title_owen = 'Đã xác nhận chuyến đi';

                $data = [
                    'arr_object_id' => $arr_object_id_driver,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'content_driver' => $content_driver,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotificationDriver($id, $type, $data);
            }
        }
    }

    static function notiNotDriverTransaction($id, $type, $created_by, $check = 1,$arr_object_id = [])
    {
        $transaction = TransactionDriver::find($id);
        if (!empty($transaction)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $content_driver = '';
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction_driver'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                $content = "Chuyến đi " . $transaction->reference_no . ', Người đặt xe '.$transaction->customer->fullname.', ' . _dt_new($transaction->date) . ' không tìm thấy tài xế phù hợp!.';
                $title = 'Không tìm thấy tài xế';
                $title_owen = 'Không tìm thấy tài xế';

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'content_driver' => $content_driver,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notiStartTransactionDriver($id, $type, $created_by, $check = 1,$arr_object_id = [])
    {
        $transaction = TransactionDriver::find($id);
        if (!empty($transaction)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                foreach ($arr_object_id as $key => $value) {
                    if ($check == 1) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 2){
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'customer') {
                            unset($arr_object_id[$key]);
                        }
                    } elseif($check == 3) {
                        if ($created_by == $value['object_id'] && $value['object_type'] == 'driver') {
                            unset($arr_object_id[$key]);
                        }
                    }
                }
                $arr_object_id = array_values($arr_object_id);
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $content_driver = '';
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction_driver'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                $content = "Chuyến đi " . $transaction->reference_no . ', Người đặt xe '.$transaction->customer->fullname.', ' . _dt_new($transaction->date) . ' đang khởi hành!.';
                $title = 'Khởi hành chuyến';
                $title_owen = 'Khởi hành chuyến';

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'content_driver' => $content_driver,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notiFindDriverTransaction($id, $type, $created_by, $check = 1,$arr_object_id = [])
    {
        $transaction = TransactionDriver::find($id);
        if (!empty($transaction)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $content_driver = '';
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction_driver'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                $content = "Có chuyến đi mới, Chuyến đi " . $transaction->reference_no . ', Người đặt xe '.$transaction->customer->fullname.', ' . _dt_new($transaction->date);
                $title = 'Chuyến đi mới';
                $title_owen = 'Chuyến đi mới';

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'content_driver' => $content_driver,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotificationDriver($id, $type, $data);
            }
        }
    }

    static function notiAccuracyLicenseDriver($id, $type, $created_by){
        $drivingLiscense = DrivingLiscenseDriver::find($id);
        if (!empty($drivingLiscense)) {
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

            $dtDriver = Driver::select(
                'tbl_driver.fullname as name',
                'tbl_driver.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'driver' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_driver.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'driver'"));
                })
                ->where('tbl_driver.id', $drivingLiscense->driver_id)
                ->get()->toArray();
            if (!empty($dtDriver)) {
                $arr_object_id = array_merge($arr_object_id, $dtDriver);
            }
            if ($arr_object_id) {
                foreach ($arr_object_id as $key => $value) {
                    if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                        unset($arr_object_id[$key]);
                    }
                }
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['driving_liscense_driver_id' => $id,'object'=>'driving_liscense_driver','status'=>$drivingLiscense->status], JSON_UNESCAPED_UNICODE);
                if ($drivingLiscense->status == 1) {
                    $content = "Giấy phép lái xe " . $drivingLiscense->driver->fullname . ". Đã được xác nhận!";
                    if ($drivingLiscense->type == 1){
                        $title = 'Xác nhận giấy phép lái xe otô';
                        $title_owen = 'Xác nhận giấy phép lái xe otô';
                    } else {
                        $title = 'Xác nhận giấy phép lái xe máy';
                        $title_owen = 'Xác nhận giấy phép lái xe máy';
                    }
                } elseif ($drivingLiscense->status == 2){
                    $content = "Giấy phép lái xe " . $drivingLiscense->driver->fullname . ". Không được xác nhận!";
                    if ($drivingLiscense->type == 1){
                        $title = 'Không xác nhận giấy phép lái xe otô';
                        $title_owen = 'Không xác nhận giấy phép lái xe otô';
                    } else {
                        $title = 'Không xác nhận giấy phép lái xe máy';
                        $title_owen = 'Không xác nhận giấy phép lái xe máy';
                    }
                } elseif ($drivingLiscense->status == 0){
                    $content = "Giấy phép lái xe " . $drivingLiscense->driver->fullname . ". Chưa được xác nhận!";
                    if ($drivingLiscense->type == 1){
                        $title = 'Chưa xác nhận giấy phép lái xe otô';
                        $title_owen = 'Chưa xác nhận giấy phép lái xe otô';
                    } else {
                        $title = 'Chưa xác nhận giấy phép lái xe máy';
                        $title_owen = 'Chưa xác nhận giấy phép lái xe máy';
                    }

                }
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                ];
                static::addNotificationDriver($id, $type, $data);
            }
        }
    }

    static function notiAgreePaperDriver($id, $type, $created_by,$type_check){
        $driver = Driver::find($id);
        if (!empty($driver)) {
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

            $dtDriver = Driver::select(
                'tbl_driver.fullname as name',
                'tbl_driver.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'driver' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_driver.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'driver'"));
                })
                ->where('tbl_driver.id', $driver->id)
                ->get()->toArray();
            if (!empty($dtDriver)) {
                $arr_object_id = array_merge($arr_object_id, $dtDriver);
            }
            if ($arr_object_id) {
                foreach ($arr_object_id as $key => $value) {
                    if ($created_by == $value['object_id'] && $value['object_type'] == 'staff') {
                        unset($arr_object_id[$key]);
                    }
                }
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                if ($type_check == 'cccd'){
                    $json_data = json_encode(['driver_id' => $id,'object'=>'driver','status_cccd'=>$driver->status_cccd], JSON_UNESCAPED_UNICODE);
                    if ($driver->status_cccd == 1) {
                        $content = "Giấy căn cước công dân " . $driver->fullname . ". Đã được xác nhận!";
                        $title = 'Xác nhận giấy căn cước công dân';
                        $title_owen = 'Xác nhận giấy căn cước công dân';
                    } elseif ($driver->status_cccd == 2){
                        $content = "Giấy căn cước công dân " . $driver->fullname . ". Không được xác nhận!";
                        $title = 'Xác nhận giấy căn cước công dân';
                        $title_owen = 'Xác nhận giấy căn cước công dân';
                    } elseif ($driver->status_cccd == 0){
                        $content = "Giấy căn cước công dân " . $driver->fullname . ". Chưa được xác nhận!";
                        $title = 'Xác nhận giấy căn cước công dân';
                        $title_owen = 'Xác nhận giấy căn cước công dân';
                    }
                } elseif ($type_check == 'judicial_record'){
                    $json_data = json_encode(['driver_id' => $id,'object'=>'driver','status_judicial_record'=>$driver->status_judicial_record], JSON_UNESCAPED_UNICODE);
                    if ($driver->status_judicial_record == 1) {
                        $content = "Giấy lý lịch tư pháp " . $driver->fullname . ". Đã được xác nhận!";
                        $title = 'Xác nhận giấy lý lịch tư pháp';
                        $title_owen = 'Xác nhận giấy lý lịch tư pháp';
                    } elseif ($driver->status_judicial_record == 2){
                        $content = "Giấy căn cước công dân " . $driver->fullname . ". Không được xác nhận!";
                        $title = 'Xác nhận giấy lý lịch tư pháp';
                        $title_owen = 'Xác nhận giấy lý lịch tư pháp';
                    } elseif ($driver->status_judicial_record == 0){
                        $content = "Giấy căn cước công dân " . $driver->fullname . ". Chưa được xác nhận!";
                        $title = 'Xác nhận giấy lý lịch tư pháp';
                        $title_owen = 'Xác nhận giấy lý lịch tư pháp';
                    }
                } elseif ($type_check == 'confirm_conduct'){
                    $json_data = json_encode(['driver_id' => $id,'object'=>'driver','status_confirm_conduct'=>$driver->status_confirm_conduct], JSON_UNESCAPED_UNICODE);
                    if ($driver->status_confirm_conduct == 1) {
                        $content = "Giấy xác nhận hạnh kiểm " . $driver->fullname . ". Đã được xác nhận!";
                        $title = 'Xác nhận giấy xác nhận hạnh kiểm';
                        $title_owen = 'Xác nhận giấy xác nhận hạnh kiểm';
                    } elseif ($driver->status_confirm_conduct == 2){
                        $content = "Giấy căn cước công dân " . $driver->fullname . ". Không được xác nhận!";
                        $title = 'Xác nhận giấy xác nhận hạnh kiểm';
                        $title_owen = 'Xác nhận giấy xác nhận hạnh kiểm';
                    } elseif ($driver->status_confirm_conduct == 0){
                        $content = "Giấy căn cước công dân " . $driver->fullname . ". Chưa được xác nhận!";
                        $title = 'Xác nhận giấy xác nhận hạnh kiểm';
                        $title_owen = 'Xác nhận giấy xác nhận hạnh kiểm';
                    }
                } elseif ($type_check == 'health_certificate'){
                    $json_data = json_encode(['driver_id' => $id,'object'=>'driver','status_health_certificate'=>$driver->status_health_certificate], JSON_UNESCAPED_UNICODE);
                    if ($driver->status_health_certificate == 1) {
                        $content = "Giấy khám sức khỏe " . $driver->fullname . ". Đã được xác nhận!";
                        $title = 'Xác nhận giấy khám sức khỏe';
                        $title_owen = 'Xác nhận giấy khám sức khỏe';
                    } elseif ($driver->status_health_certificate == 2){
                        $content = "Giấy khám sức khỏe " . $driver->fullname . ". Không được xác nhận!";
                        $title = 'Xác nhận giấy khám sức khỏe';
                        $title_owen = 'Xác nhận giấy khám sức khỏe';
                    } elseif ($driver->status_health_certificate == 0){
                        $content = "Giấy khám sức khỏe " . $driver->fullname . ". Chưa được xác nhận!";
                        $title = 'Xác nhận giấy khám sức khỏe';
                        $title_owen = 'Xác nhận giấy khám sức khỏe';
                    }
                } elseif ($type_check == 'certificate_hiv'){
                    $json_data = json_encode(['driver_id' => $id,'object'=>'driver','status_certificate_hiv'=>$driver->status_certificate_hiv], JSON_UNESCAPED_UNICODE);
                    if ($driver->status_certificate_hiv == 1) {
                        $content = "Giấy xác nhận giấy hiv " . $driver->fullname . ". Đã được xác nhận!";
                        $title = 'Xác nhận giấy xác nhận giấy hiv';
                        $title_owen = 'Xác nhận giấy xác nhận giấy hiv';
                    } elseif ($driver->status_certificate_hiv == 2){
                        $content = "Giấy xác nhận giấy hiv " . $driver->fullname . ". Không được xác nhận!";
                        $title = 'Xác nhận giấy xác nhận giấy hiv';
                        $title_owen = 'Xác nhận giấy xác nhận giấy hiv';
                    } elseif ($driver->status_certificate_hiv == 0){
                        $content = "Giấy xác nhận giấy hiv " . $driver->fullname . ". Chưa được xác nhận!";
                        $title = 'Xác nhận giấy xác nhận giấy hiv';
                        $title_owen = 'Xác nhận giấy xác nhận giấy hiv';
                    }
                }

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                ];
                static::addNotificationDriver($id, $type, $data);
            }
        }
    }

    static function notiPaymentTransactionDriver($id, $type, $created_by,$check = 1){
        $payment = PaymentDriver::find($id);
        if (!empty($payment)) {
            $arr_object_id = [];
            $transaction_id = $payment->transaction_id;
            $customer_id = $payment->customer_id;
            $transaction = TransactionDriver::find($transaction_id);
            $transactionStaff = User::select(
                'tbl_users.name',
                'tbl_users.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'staff' as 'object_type'")
            )
                ->join('tbl_transaction_staff', 'tbl_transaction_staff.user_id', '=', 'tbl_users.id')
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_transaction_staff.user_id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'staff'"));
                })
                ->where('transaction_id', $transaction_id)
                ->get()->toArray();
            if (!empty($transactionStaff)) {
                $arr_object_id = array_merge($arr_object_id, $transactionStaff);
            }

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

            $dtCustomer = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $customer_id)
                ->get()->toArray();
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }

            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['payment_id'=>$id,'transaction_id' => $transaction_id,'type' => $transaction->type,'object'=>'transaction_driver'], JSON_UNESCAPED_UNICODE);
                $content = "Chuyến đi " . $transaction->reference_no . ', Người đặt xe '.$transaction->customer->fullname . _dt_new($transaction->date) . ' đã thanh toán thành công. Số tiền '.formatMoney($payment->payment).'';
                $title = 'Thanh toán chuyến';
                $title_owen = 'Thanh toán chuyến';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($transaction_id, $type, $data);
            }
        }
    }

    static function notiSignContract($id, $type, $created_by, $check = 1,$arr_object_id = [],$type_signature = 1)
    {
        $handoverRecord = HandoverRecord::find($id);
        if (!empty($handoverRecord)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                $arr_object_id = array_values($arr_object_id);
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $content_driver = '';
                $json_data = json_encode(['transaction_id' => $handoverRecord->transaction_id,'type'=>$handoverRecord->transaction->type,'object'=>'handover_record','handover_record_id' => $handoverRecord->id], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                if ($type_signature == 1) {
                    $content = "Hợp đồng chuyến đi " . $handoverRecord->transaction->reference_no . ', Người đặt xe ' . $handoverRecord->transaction->customer->fullname . ', ' . _dt_new($handoverRecord->transaction->date) . ' đã được chủ xe ký!.';
                } else {
                    $content = "Hợp đồng chuyến đi " . $handoverRecord->transaction->reference_no . ', Người đặt xe ' . $handoverRecord->transaction->customer->fullname . ', ' . _dt_new($handoverRecord->transaction->date) . ' đã được người thuê xe ký!.';
                }
                if ($type_signature == 1) {
                    $title = 'Chủ xe ký hợp đồng';
                    $title_owen = 'Chủ xe ký hợp đồng';
                } else {
                    $title = 'Khách thuê ký hợp đồng';
                    $title_owen = 'Khách thuê ký hợp đồng';
                }

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'content_driver' => $content_driver,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => 0,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notiCancelTransactionDriverProvince($id, $type, $created_by, $check = 1,$arr_object_id = [],$type_payment = 0)
    {
        $transaction = TransactionDriver::find($id);
        if (!empty($transaction)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $json_data = json_encode(['transaction_id' => $id,'type' => $transaction->type,'object'=>'transaction_driver'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                if($type_payment == 1){
                    $content = "Chuyến đi " . $transaction->reference_no . ', Người đặt xe ' . $transaction->customer->fullname . _dt_new($transaction->date) . '.Hệ thống hủy, do quá thời gian thanh toán.';
                } else {
                    $content = "Chuyến đi " . $transaction->reference_no . ', Người đặt xe ' . $transaction->customer->fullname . _dt_new($transaction->date) . '.Hệ thống hủy, do quá thời gian tìm tài xế.';
                }
                $title = 'Hệ thống hủy chuyến';
                $title_owen = 'Hệ thống hủy chuyến';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $transaction->type,
                ];
                static::addNotification($id, $type, $data);
            }
        }
    }

    static function notficationModule($id,$type,$arr_object_id = [],$arr_object_id_driver = [])
    {
        $moduleNoti = ModuleNoti::find($id);
        if (!empty($moduleNoti)) {
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $json_data = json_encode(['module_noti_id' => $id, 'object' => 'module_noti'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                $content = $moduleNoti->name;

                $title = 'Thông báo hệ thống';
                $title_owen = 'Thông báo hệ thống';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'content_html' => !empty($moduleNoti) ? $moduleNoti->content : null,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => 0,
                ];
                static::addNotification($id, $type, $data);
            }

            if (!empty($arr_object_id_driver)) {
                $playerId = array_unique(array_column($arr_object_id_driver, 'player_id'));
                $content = '';
                $json_data = json_encode(['module_noti_id' => $id, 'object' => 'module_noti'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';

                $content = $moduleNoti->name;

                $title = 'Thông báo hệ thống';
                $title_owen = 'Thông báo hệ thống';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'content_html' => !empty($moduleNoti) ? $moduleNoti->content : null,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => 0,
                ];
                static::addNotificationDriver($id, $type, $data);
            }
        }
    }

    static function notiChangePointClient($customer = 0,$point = 0,$point_client = 0,$title_point = '',$type = 1){
        if (!empty($customer)){
            $arr_object_id = [];
            $dtCustomer = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $customer)
                ->get()->toArray();
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['customer' => $customer,'object'=>'clients','status'=> 'change_point'], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';
                if ($point < 0){
                    $prefix = '-';
                } else {
                    $prefix = '+';
                }
                $content = $title_point . '. TK của bạn tại kanow ' . $prefix . formatMoney($point) . ' điểm. Số dư điểm khả dụng: ' . formatMoney($point_client) .' điểm';
                $title = 'Thay đổi số dư điểm';
                $title_owen = 'Thay đổi số dư điểm';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'object_id' => $customer,
                    'content' => $content,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_owen,
                ];
                static::addNotification($customer, Config::get('constant')['noti_change_point'], $data);
            }
        }
    }

    static function notiRequestWithdrawMoney($requestMoney, $type, $created_by, $type_request = 1)
    {
        if (!empty($requestMoney)) {
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

            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            $content = "";
            $content_telegram = "";
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['request_withdraw_money_id' => $requestMoney->id,'type' => $requestMoney->type,'object'=>'request_withdraw_money'], JSON_UNESCAPED_UNICODE);
                $content = "The member " . $requestMoney->customer->referral_code . ', has just submitted a withdrawal request ' . $requestMoney->reference_no . ', ' . _dt($requestMoney->date) . '. The '.get_option('money_unit').' transfer address is : '.$requestMoney->transfer_address.'.  Please check, proceed, and handle the request!';
                $title = 'Submit a withdrawal request';
                $title_owen = 'Submit a withdrawal request';
                $content_telegram = "Name ".$requestMoney->customer->referral_code."; Amount: ".formatMoney($requestMoney->total)."; Out: ".$requestMoney->transfer_address.", ".$requestMoney->network.", ".$requestMoney->title_address."";
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $requestMoney->type,
                ];
                static::addNotification($requestMoney->id, $type, $data);
            }
            if (!empty($content_telegram)) {
                send_lark($content_telegram,2);
                send_telegram($content_telegram,2);
            }
        }
    }

    static function notiApproveRequestWithdrawMoney($request_money_id, $type, $created_by, $type_request = 1)
    {
        $requestMoney = RequestWithdrawMoney::find($request_money_id);
        if (!empty($requestMoney)) {
            $arr_object_id = [];

            $dtOwen = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $requestMoney->customer_id)
                ->get()->toArray();
            if (!empty($dtOwen)) {
                $arr_object_id = array_merge($arr_object_id, $dtOwen);
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['request_withdraw_money_id' => $requestMoney->id,'type' => $requestMoney->type,'object'=>'request_withdraw_money'], JSON_UNESCAPED_UNICODE);
                $content = "The withdrawal request voucher ".$requestMoney->reference_no." from member " . $requestMoney->customer->referral_code . ' has been approved and processed!';
                $title = 'Approve the withdrawal request';
                $title_owen = 'Approve the withdrawal request';

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $requestMoney->type,
                ];
                static::addNotification($requestMoney->id, $type, $data);
            }
        }
    }

    static function notiTransferPackage($dtData, $type, $created_by, $type_request = 1)
    {
        if (!empty($dtData)) {
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

            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            $content = "";
            $content_telegram = "";
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['transfer_package_id' => $dtData->id,'object'=>'transfer_package'], JSON_UNESCAPED_UNICODE);
                $content = "Member " . $dtData->customer->referral_code . ', has just submitted a request to transfer package ' . $dtData->reference_no . ', ' . _dt($dtData->date) . '. Please check, proceed, and handle the request!';
                $title = 'Submit a package transfer request';
                $title_owen = 'Submit a package transfer request';
                $content_telegram = "Member ".$dtData->customer->referral_code." transfer to ".$dtData->username." amount: ".$dtData->total." ".get_option('money_unit') ." "._dt($dtData->date)."";

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => null,
                ];
                static::addNotification($dtData->id, $type, $data);
            }
            if (!empty($content_telegram)) {
                send_lark($content_telegram,3);
                send_telegram($content_telegram,1);
            }
        }
    }

    static function notiApproveTransferPackage($transfer_package_id, $type, $created_by, $type_request = 1)
    {
        $dtData = TransferPackage::find($transfer_package_id);
        if (!empty($dtData)) {
            $arr_object_id = [];

            $dtOwen = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'customer' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $dtData->customer_id)
                ->get()->toArray();
            if (!empty($dtOwen)) {
                $arr_object_id = array_merge($arr_object_id, $dtOwen);
            }
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['transfer_package_id' => $dtData->id,'object'=>'transfer_package'], JSON_UNESCAPED_UNICODE);
                $content = "The package transfer request voucher ".$dtData->reference_no." from member " . $dtData->customer->referral_code . ' has been approved and processed!';
                $title = 'Approve the package transfer request';
                $title_owen = 'Approve the package transfer request';

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => $dtData->type,
                ];
                static::addNotification($dtData->id, $type, $data);
            }
        }
    }

    static function notiParentTransaction($transaction_id, $type, $created_by, $type_request = 1)
    {
        $dtData = Transaction::find($transaction_id);
        if (!empty($dtData)) {
            $parent_id = get_parent_customer($dtData->customer_id);
            if (!empty($parent_id)) {
                $arr_object_id = [];
                $dtOwen = Clients::select(
                    'tbl_clients.fullname as name',
                    'tbl_clients.id as object_id',
                    'tbl_player_id.player_id as player_id',
                    DB::raw("'customer' as 'object_type'")
                )
                    ->leftJoin('tbl_player_id', function ($join) {
                        $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                        $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                    })
                    ->whereIn('tbl_clients.id', $parent_id)
                    ->get()->toArray();
                if (!empty($dtOwen)) {
                    $arr_object_id = array_merge($arr_object_id, $dtOwen);
                }
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
                $arr_object_id = array_values($arr_object_id);
                $playerId = [];
                if (!empty($arr_object_id)) {
                    $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                    $json_data = json_encode([
                        'transaction_id' => $dtData->id,
                        'object' => 'transaction',
                        'type_child' => 'add_transaction'
                    ], JSON_UNESCAPED_UNICODE);
                    $content = "Member " . $dtData->customer->referral_code . ". has just made a transaction " . $dtData->reference_no . "to purchase a " . $dtData->category_card->name . " membership package. At " . _dt_new($dtData->date) . ". Hash/IXID : ".$dtData->lock_hash."";
                    $title = 'The subordinate member has purchased a package';
                    $title_owen = 'The subordinate member has purchased a package';

                    $data = [
                        'arr_object_id' => $arr_object_id,
                        'player_id' => $playerId,
                        'json_data' => $json_data,
                        'content' => $content,
                        'created_by' => $created_by,
                        'title' => $title,
                        'title_owen' => $title_owen,
                        'type' => null,
                    ];
                    static::addNotification($dtData->id, $type, $data);
                }
            }
        }
    }

    static function notiRegisterByReferralCode($customer_id, $type, $created_by, $type_request = 1)
    {
        return true;
        $dtData = Clients::find($customer_id);
        if (!empty($dtData)) {
            $referralCode = $dtData->referral_level->referral_code;
            $dtCustomer = Clients::where('referral_code',$referralCode)->first();
            if (!empty($dtCustomer)) {
                $arr_object_id = [];
                $dtOwen = Clients::select(
                    'tbl_clients.fullname as name',
                    'tbl_clients.id as object_id',
                    'tbl_player_id.player_id as player_id',
                    DB::raw("'customer' as 'object_type'")
                )
                    ->leftJoin('tbl_player_id', function ($join) {
                        $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                        $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                    })
                    ->where('tbl_clients.id', $dtCustomer->id)
                    ->get()->toArray();
                if (!empty($dtOwen)) {
                    $arr_object_id = array_merge($arr_object_id, $dtOwen);
                }
                $arr_object_id = array_values($arr_object_id);
                $playerId = [];
                if (!empty($arr_object_id)) {
                    $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                    $json_data = json_encode([
                        'client_id' => $dtData->id,
                        'object' => 'client',
                        'status' => 'register_by_referral'
                    ], JSON_UNESCAPED_UNICODE);
                    $content = "Member " . $dtData->fullname . ". Has just successfully registered through your referral code " . $referralCode. ". At " . _dt_new($dtData->created_at) . "";
                    $title = 'Member registered through referral code';
                    $title_owen = 'Member registered through referral code';

                    $data = [
                        'arr_object_id' => $arr_object_id,
                        'player_id' => $playerId,
                        'json_data' => $json_data,
                        'content' => $content,
                        'created_by' => $created_by,
                        'title' => $title,
                        'title_owen' => $title_owen,
                        'type' => null,
                    ];
                    static::addNotification($dtData->id, $type, $data);
                }
            }
        }
    }
    static function notiAddTransaction($dtData, $type, $created_by, $type_request = 1)
    {
        if (!empty($dtData)) {
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

            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            $content = "";
            $content_telegram = "";
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['transaction_id' => $dtData->id,'object'=>'transaction','status'=>'add_transaction'], JSON_UNESCAPED_UNICODE);
                $content = "Member " . $dtData->customer->referral_code . ', has just made a transaction to purchase a '.$dtData->category_card->name.' package ' . $dtData->reference_no . ', ' . _dt($dtData->date) . '. Hash/IXID : '.$dtData->lock_hash.'. Please check, proceed, and handle the request!';
                $title = 'Purchase a package';
                $title_owen = 'Purchase a package';
                if ($dtData->wallet == 1){
                    $content_telegram = "Name: " . $dtData->customer->referral_code . "; " . $dtData->category_card->name . "; In: " . $dtData->lock_hash . ", Wallet";
                } else {
                    $content_telegram = "Name: " . $dtData->customer->referral_code . "; " . $dtData->category_card->name . "; In: " . $dtData->lock_hash . ", " . $dtData->title_address . "";
                }

                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => $title_owen,
                    'type' => null,
                ];
                static::addNotification($dtData->id, $type, $data);
            }
            if (!empty($content_telegram)) {
                send_lark($content_telegram,1);
                send_telegram($content_telegram,1);
            }
        }
    }
}
