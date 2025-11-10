<?php

namespace App\Traits;

use App\Models\Clients;
use App\Models\Transaction;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Services\AccountService;

trait NotificationTrait
{
    use SocketTrait;
    static function addNotification($id, $type, $data = [])
    {
        $notification_id = (string)Str::uuid();
        $arrStaffNoti = [];
        foreach ($data['arr_object_id'] as $kU => $vU) {
            $object_id = $vU['object_id'].'__'.$vU['object_type'];
            if (!empty($arrStaffNoti[$object_id])) {
                continue;
            }
            $arrStaffNoti[$object_id] = [
                'object_id' => $vU['object_id'],
                'object_type' => $vU['object_type'],
                'is_read' => 0,
            ];
        }
        $notification = new Notification();
        $notification->object_id = $id;
        $notification->object_type = $type;
        $notification->title = $data['title'];
        $notification->title_owen = $data['title_owen'];
        $notification->content = $data['content'];
        $notification->content_driver = !empty($data['content_driver']) ? $data['content_driver'] : null;
        $notification->created_by = $data['created_by'];
        $notification->json_data = $data['json_data'];
        $notification->save();
        if (!empty($notification)) {
            $notification_id = $notification->id;
            if (!empty($arrStaffNoti)) {
                foreach ($arrStaffNoti as $key => $value) {
                    $arrStaffNoti[$key]['notification_id'] = $notification->id;
                }
                $arrStaffNoti = array_values($arrStaffNoti);
                DB::table('tbl_notification_staff')->insert($arrStaffNoti);
            }
            $notification->created_at_new = _dt_new($notification->created_at);
            $instance = new static();
            $instance->sendNotificationSocket([
                'channels' => $data['arr_object_id'],
                'event' => 'notification',
                'data' => $notification,
                'db_name' => config('database.connections.mysql.database')
            ]);
            if (!empty($data['player_id'])) {
                static::sendNotiOnesignal($notification_id, $data, $type);
            }
        }
    }

    static function sendNotiOnesignal($id, $data = [], $type = 0)
    {
        if (!empty($data)) {
            $message = $data['content'];
            $url = (!empty($data['url']) ? $data['url'] : '');
            $title = $data['title'];
            $icon = (!empty($data['icon']) ? $data['icon'] : '');
            $ios_badgeType = "Increase";
            $ios_badgeCount = "1";
            $__data['notification_id'] = $id;
            $__data['title'] = $data['title'];
            $__data['type'] = $type;
            $__data['user_name'] = '';
            $__data['json_data'] = $data['json_data'];
            $user_id = array_values(array_filter($data['player_id']));

            $app_id = get_option('onesignal_id');
            $keyapp = get_option('onesignal_key');
            $curl_onesignal = 'https://onesignal.com/api/v1/notifications';
            if (!empty($user_id) && !empty($app_id) && !empty($keyapp) && !empty($curl_onesignal)) {
                if (!empty($message)) {
                    $content = ["en" => "$message"];
                }
                if (!empty($title)) {
                    $headings = ["en" => "$title"];
                }

                $fields = array(
                    'app_id' => $app_id,
                    'include_player_ids' => is_array($user_id) ? $user_id : [$user_id],
                    'chrome_web_icon' => $icon,
                    'ios_badgeType' => $ios_badgeType,
                    'ios_badgeCount' => $ios_badgeCount,
                    'mutable_content' => true
                );
                if (!empty($url)) {
                    $fields['url'] = $url;
                }
                if (!empty($content)) {
                    $fields['contents'] = $content;
                }
                if (!empty($headings)) {
                    $fields['headings'] = $headings;
                }
                $fields['data'] = $__data;
                $fields = json_encode($fields);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $curl_onesignal);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json; charset=utf-8',
                    'Authorization: Basic ' . $keyapp
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                curl_close($ch);
                $_response = json_decode($response);

                if (!empty($_response->id)) {
                    $notification = Notification::find($id);
                    $notification->noti_onesignal_id = $_response->id;
                    $notification->save();
                    return true;
                } else {
                    DB::table('tbl_log_notification')->insert([
                        'notification_id' => $id,
                        'response' => $response,
                    ]);
                }
            }
        }
        return false;
    }

    public function sendNotiOnesignalMutile($_data = [],$type)
    {
        if (empty($_data)) {
            return false;
        }
        $app_id = get_option('onesignal_id');
        $keyapp = get_option('onesignal_key');
        $curl_onesignal = 'https://onesignal.com/api/v1/notifications';

        $list_send = array();
        $listTime = [];
        $mh = curl_multi_init();
        $listData = [];
        $listPlayer = [];

        $list_user_id = [];
        foreach ($_data as $j => $data) {
            if (!empty($data)) {
                $user_id = array_values(array_filter($data['player_id']));
                $message = !empty($data['content']) ? $data['content'] : ' ';
                $url = !empty($data['url']) ? $data['url'] : '';
                $title = !empty($data['title']) ? $data['title'] : '';
                $icon = !empty($data['icon']) ? ((count(explode('http://',
                        $data['icon'])) == 2 || count(explode('https://',
                        $data['icon'])) == 2) ? $data['icon'] : base_url($data['icon'])) : '';
                $images = !empty($data['images']) ? ((count(explode('http://',
                        $data['images'])) == 2 || count(explode('https://',
                        $data['images'])) == 2) ? $data['images'] : base_url($data['images'])) : '';
                $ios_badgeType = !empty($data['ios_badgeType']) ? $data['ios_badgeType'] : 'Increase';
                $ios_badgeCount = !empty($data['ios_badgeCount']) ? $data['ios_badgeCount'] : '1';
            }
            $__data['title'] = $title;
            $__data['type'] = $type;
            $__data['user_name'] = '';
            $__data['json_data'] = $data['json_data'];
            if (!empty($message)) {
                $content = ["en" => "$message"];
            }
            if (!empty($title)) {
                $headings = ["en" => "$title"];
            }
            $icon = (!empty($data['icon']) ? $data['icon'] : '');
            $user_id = is_array($user_id) ? $user_id : [$user_id];
            $user_id = array_values($user_id);
            $fields = array(
                'app_id' => $app_id,
                'include_player_ids' => $user_id,
                'chrome_web_icon' => $icon,
                'ios_badgeType' => $ios_badgeType,
                'ios_badgeCount' => $ios_badgeCount,
            );

            if (!empty($url)) {
                $fields['url'] = $url;
            }
            if (!empty($content)) {
                $fields['contents'] = $content;
            }
            if (!empty($headings)) {
                $fields['headings'] = $headings;
            }
            $fields['data'] = $__data;
            $fields['isAnyWeb'] = true;
            $fields = json_encode($fields);
            $data['user_id'] = $user_id;
            $data['title'] = $title;
            $data['message'] = $message;
            $data['icon'] = $icon;
            $data['images'] = $images;
            $data['url'] = !empty($url) ? $url : null;

            $list_send[$j] = curl_init();
            curl_setopt($list_send[$j], CURLOPT_URL, $curl_onesignal);
            curl_setopt($list_send[$j], CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic ' . $keyapp
            ]);
            curl_setopt($list_send[$j], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($list_send[$j], CURLOPT_HEADER, false);
            curl_setopt($list_send[$j], CURLOPT_POST, true);
            curl_setopt($list_send[$j], CURLOPT_POSTFIELDS, $fields);
            curl_setopt($list_send[$j], CURLOPT_SSL_VERIFYPEER, false);
            curl_multi_add_handle($mh, $list_send[$j]);

            //save_noti
            $arrStaffNoti = [];
            foreach ($data['arr_object_id'] as $kU => $vU) {
                $object_id = $vU['object_id'];
                if (!empty($arrStaffNoti[$object_id])) {
                    continue;
                }
                $arrStaffNoti[$object_id] = [
                    'object_id' => $vU['object_id'],
                    'object_type' => $vU['object_type'],
                    'is_read' => 0,
                ];
            }
            $notification = new Notification();
            $notification->object_id = $data['object_id'];
            $notification->object_type = $type;
            $notification->title = $data['title'];
            $notification->title_owen = $data['title_owen'];
            $notification->content = $data['content'];
            $notification->content_html = !empty($data['content_html']) ? $data['content_html'] : null;
            $notification->created_by = $data['created_by'];
            $notification->json_data = $data['json_data'];
            $notification->save();
            if (!empty($notification)) {
                $notification_id = $notification->id;
                if (!empty($arrStaffNoti)) {
                    foreach ($arrStaffNoti as $key => $value) {
                        $arrStaffNoti[$key]['notification_id'] = $notification->id;
                    }
                    $arrStaffNoti = array_values($arrStaffNoti);
                    DB::table('tbl_notification_staff')->insert($arrStaffNoti);
                }

                if ($type == Config::get('constant')['noti_remind_log_upgrade']){
                    $accountService = new AccountService();
                    $request = new Request();
                    $request->merge(['id' => $data['object_id']]);
                    $accountService->updateLogUpgradeClient($request);
                }

                $notification->created_at_new = _dt_new($notification->created_at);
                $data['notification_id'] = $notification_id;
            }
            $listData[$j] = $data;
        }
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);
        $success = 0;
        foreach (array_keys($list_send) as $j) {
            $data = $listData[$j];
            $response = curl_multi_getcontent($list_send[$j]);
            $_response = json_decode($response);
            if (!empty($_response->id)) {
                $notification = Notification::find($data['notification_id']);
                $notification->noti_onesignal_id = $_response->id;
                $notification->save();
            } else {
                DB::table('tbl_log_notification')->insert([
                    'notification_id' => $data['notification_id'],
                    'response' => $response,
                ]);
            }
            curl_multi_remove_handle($mh, $list_send[$j]);
        }
        curl_multi_close($mh);
        if (!empty($success)) {
            return $success;
        }
        return $listTime;
    }
}
