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

    static function notiCancelTransaction($dtData, $type, $created_by, $check = 1, $arr_object_id = [])
    {
        $transaction = $dtData;
        if (!empty($transaction)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $content = '';
                $json_data = json_encode(['transaction_id' => $transaction['id'], 'object' => 'transaction'],
                    JSON_UNESCAPED_UNICODE);
                $title = '';

                $content = "Chuyến đi " . $transaction['reference_no'] . ', Khách hàng ' . $transaction['customer']['fullname'] . ', ' . _dt_new($transaction['date_start'],false) . ' - ' . _dt_new($transaction['date_end'],false) . ' đã kết thúc.';
                $title = 'Kết thúc chuyến đi';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'content' => $content,
                    'created_by' => $created_by,
                    'title' => $title,
                    'title_owen' => "",
                    'type' => 1,
                ];
                static::addNotification($transaction['id'], $type, $data);
            }
        }
    }

    static function notficationModule($id, $type, $arr_object_id = [], $arr_object_id_driver = [])
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

}
