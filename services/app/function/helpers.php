<?php

use App\Libraries\App;
use App\Libraries\Alepay;
use App\Models\Clients;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Pusher\Pusher;
use App\Models\Notification;

function getTransactionStaff($service = 1)
{
    $transaction_staff_id = 0;
    $dtUser = User::whereHas('department', function ($query) {
        $query->where('check_transaction', 1);
    })
        ->whereExists(function ($query) use ($service) {
            $query->select("tbl_user_service.user_id")
                ->from('tbl_user_service')
                ->whereRaw('tbl_user_service.user_id = tbl_users.id')
                ->where('tbl_user_service.service', $service);
        })
        ->where('check_tran', 0)->where('active', 1)->orderBy('priority', 'asc')->get();
    if (count($dtUser) == 0) {
        User::whereHas('department', function ($query) {
            $query->where('check_transaction', 1);
        })->update([
            'check_tran' => 0
        ]);
        $dtUser = User::whereHas('department', function ($query) {
            $query->where('check_transaction', 1);
        })
            ->whereExists(function ($query) use ($service) {
                $query->select("tbl_user_service.user_id")
                    ->from('tbl_user_service')
                    ->whereRaw('tbl_user_service.user_id = tbl_users.id')
                    ->where('tbl_user_service.service', $service);
            })
            ->where('check_tran', 0)->where('active', 1)->orderBy('priority', 'asc')->get();
    }
    if (!empty($dtUser)) {
        foreach ($dtUser as $key => $value) {
            $user_id = $value->id;
            $dtTransactionCheckDriver = TransactionDriver::select('id', 'date', 'created_at',
                DB::raw("1 as type"))->orderBy('created_at', 'desc')->limit(1);
            $dtTransactionCheckVs1 = Transaction::select('id', 'date', 'created_at',
                DB::raw("2 as type"))->orderBy('created_at', 'desc')->limit(1)->unionall($dtTransactionCheckDriver);
            $dtTransactionCheckNew = DB::query()
                ->fromSub($dtTransactionCheckVs1, 'union_query')
                ->select('id', 'type')
                ->orderBy('created_at', 'desc')
                ->first();
            if (!empty($dtTransactionCheckNew)) {
                if ($dtTransactionCheckNew->type == 1) {
                    $dtTransactionCheck = TransactionDriver::select('id',
                        'created_at')->find($dtTransactionCheckNew->id);
                } else {
                    $dtTransactionCheck = Transaction::select('id', 'created_at')->find($dtTransactionCheckNew->id);
                }
                if (!empty($dtTransactionCheck->transaction_staff_new())) {
                    if ($dtTransactionCheck->transaction_staff_new()->id == $user_id) {
                        continue;
                    } else {
                        $transaction_staff_id = $user_id;
                        DB::table('tbl_users')->where('id', $user_id)->update([
                            'check_tran' => 1
                        ]);
                        break;
                    }
                } else {
                    $transaction_staff_id = $user_id;
                    DB::table('tbl_users')->where('id', $user_id)->update([
                        'check_tran' => 1
                    ]);
                    break;
                }
            } else {
                $transaction_staff_id = $user_id;
                DB::table('tbl_users')->where('id', $user_id)->update([
                    'check_tran' => 1
                ]);
                break;
            }
        }
    }
    if (empty($transaction_staff_id)) {
        $dtUser = User::whereHas('department', function ($query) {
            $query->where('check_transaction', 1);
        })
            ->whereExists(function ($query) use ($service) {
                $query->select("tbl_user_service.user_id")
                    ->from('tbl_user_service')
                    ->whereRaw('tbl_user_service.user_id = tbl_users.id')
                    ->where('tbl_user_service.service', $service);
            })
            ->where('check_tran', 0)->where('active', 1)->orderBy('priority', 'asc')->first();
        if (!empty($dtUser)) {
            $user_id = $dtUser->id;
            $transaction_staff_id = $user_id;
            DB::table('tbl_users')->where('id', $user_id)->update([
                'check_tran' => 1
            ]);
        }
    }
    return $transaction_staff_id;
}
function getMonthYear($month = '', $year = '')
{
    if ($month == 01) {
        $month = 12;
        $year = $year - 1;
    } else {
        $month = $month - 1;
        $year = $year;
    }
    if ($month < 10) {
        $month = '0' . $month;
    }
    return [
        'month' => $month,
        'year' => $year,
    ];
}

function getAllDateInMonth($month, $year, $format = "d/m/Y")
{
    $list = [];

    for ($d = 1; $d <= 31; $d++) {
        $time = mktime(12, 0, 0, $month, $d, $year);
        if (date('m', $time) == $month) {
            $ymd = date('Y-m-d', $time);
            $list[$ymd] = date($format, $time);
        }
    }

    return $list;
}

function convert_vi_to_en($str)
{
    $str = preg_replace("(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)", "a", $str);
    $str = preg_replace("(à|á|ạ|ả|ã|â|ầ|ấ|ạ|ẩ|ẫ|ă|ẳ|ẵ|ặ|ắ|ằ)", "a", $str);
    $str = preg_replace("(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)", "e", $str);
    $str = preg_replace("(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)", "e", $str);
    $str = preg_replace("(ì|í|ị|ỉ|ĩ)", "i", $str);
    $str = preg_replace("(ì|í|ị|ỉ|ĩ)", "i", $str);
    $str = preg_replace("(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)", "o", $str);
    $str = preg_replace("(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)", "o", $str);
    $str = preg_replace("(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)", "u", $str);
    $str = preg_replace("(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)", "u", $str);
    $str = preg_replace("(ỳ|ý|ỵ|ỷ|ỹ)", "y", $str);
    $str = preg_replace("(ỳ|ý|ỵ|ỹ)", "y", $str);
    $str = preg_replace("(đ)", "d", $str);
    $str = preg_replace("(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)", "A", $str);
    $str = preg_replace("(À|Á|Ạ|Á|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ẵ|Ẳ|Ặ|Ắ|Ằ)", "A", $str);
    $str = preg_replace("(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)", "E", $str);
    $str = preg_replace("(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)", "E", $str);
    $str = preg_replace("(Ì|Í|Ị|Ỉ|Ĩ)", "I", $str);
    $str = preg_replace("(Ì|Í|Ị|Í|Ĩ)", "I", $str);
    $str = preg_replace("(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)", "O", $str);
    $str = preg_replace("(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)", "O", $str);
    $str = preg_replace("(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)", "U", $str);
    $str = preg_replace("(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)", "U", $str);
    $str = preg_replace("(Ỳ|Ý|Ỵ|Ỷ|Ỹ)", "Y", $str);
    $str = preg_replace("(Ỳ|Ý|Ỵ|Ý|Ỹ)", "Y", $str);
    $str = preg_replace("(Đ)", "D", $str);
    $str = preg_replace("(Đ)", "D", $str);
    return $str;
}

function send_zalo($transation_id = 0,$event = null,$template_id = 0){
    if (!empty($transation_id)) {
        $dtTransaction = Transaction::find($transation_id);
        $phone = $dtTransaction->customer->phone;
        if (empty($phone)){
            return false;
        }
        $phone = substr($phone, 1, 9);
        $phone = '84'.$phone;
        $datetimeid = (time() . random_int(100, 999));

        $id_send_zalo = DB::table('tbl_send_zalo')->insertGetId([
            'template_id' => $template_id,
            'event' => $event,
            'send_zalo_id' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if(!empty($id_send_zalo)){
            DB::table('tbl_send_zalo_client')->insertGetId([
                'send_zalo_id' => $id_send_zalo,
                'phone' => $phone,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $dtTemplate = DB::table('tbl_template_zalo')->where('template_id',$template_id)->first();
        $content_api = '';
        if (!empty($dtTemplate)){
            $content_api = $dtTemplate->content_api;
        }
        $customer_name = " ";
        $reference_no = $dtTransaction->reference_no;
        $car_owner = $dtTransaction->car->customer->fullname;
        $deposit = $dtTransaction->grand_total * get_option('percent_deposit') / 100;
        $grand_total = $dtTransaction->grand_total;
        $grand_total_all = $grand_total - $deposit;
        $deposit_percent = get_option('percent_deposit');
        $car_name = $dtTransaction->car->name;
        $time =  'Từ '._dt_new($dtTransaction->date_start) . ' đến ' . _dt_new($dtTransaction->date_end);
        $type = $dtTransaction->type == 1 ? '(Tự lái)' : '(Có tài)';
        $type_fuel = getValueTypeFuel($dtTransaction->car->type_fuel);
        $address = !empty($dtTransaction->address_delivery) ? $dtTransaction->address_delivery->name_location_delivery : ($dtTransaction->car->name_location .', '.$dtTransaction->car->district->name.', '.$dtTransaction->car->province->name);
        $content_api = str_replace('{car_name}','"'.$car_name.'"',$content_api);
        $content_api = str_replace('{address}','"'.$address.'"',$content_api);
        $content_api = str_replace('{type_fuel}','"'.$type_fuel.'"',$content_api);
        $content_api = str_replace('{deposit}',($deposit),$content_api);
        $content_api = str_replace('{grand_total_all}',($grand_total_all),$content_api);
        $content_api = str_replace('{grand_total}',($grand_total),$content_api);
        $content_api = str_replace('{time}','"'.($time).'"',$content_api);
        $content_api = str_replace('{customer_name}','"'.($customer_name).'"',$content_api);
        $content_api = str_replace('{type}','"'.($type).'"',$content_api);
        $content_api = str_replace('{reference_no}','"'.$reference_no.'"',$content_api);
        $content_api = str_replace('{car_owner}','"'.$car_owner.'"',$content_api);
        $content_api = str_replace('{deposit_percent}',$deposit_percent,$content_api);

        if ($template_id == '379483') {
            $content = 'Dịch vụ ' . $type . ' Bạn vừa gửi yêu cầu thuê xe ' . $car_name . '. Bạn vui lòng chờ xác nhận, sau đó tiến hành cọc ngay để hoàn tất đặt xe. Trong thời gian chờ xác nhận, bạn có thể gửi thêm yêu cầu thuê xe đến nhiều chủ xe khác, và ưu tiên lựa chọn chủ xe xác nhận sớm để đặt cọc.' . '<br/>';
            $content .= 'Mã chuyến: ' . $reference_no . '' . '<br/>';
            $content .= 'Thuê xe: ' . $car_name . '' . '<br/>';
            $content .= 'Truyền động: ' . $type_fuel . '' . '<br/>';
            $content .= 'Thời gian: ' . $time . '' . '<br/>';
            $content .= 'Đia điểm: ' . $address . '' . '<br/>';
            $content .= 'Tổng cộng: ' . $grand_total . '' . '<br/>';
            $content .= 'Tiền cọc: ' . $deposit . '' . '<br/>';
            $content .= 'Thanh toán sau: ' . $grand_total_all . '';
        } elseif($template_id == '379472'){
            $content = 'Dịch vụ '.$type.' Bạn đã đặt xe thành công trên ứng dụng Kanow. Vui lòng liên hệ chủ xe để xác nhận lại lịch trình.' . '<br/>';
            $content .= 'Mã chuyến: ' . $reference_no . '' . '<br/>';
            $content .= 'Thuê xe: ' . $car_name . '' . '<br/>';
            $content .= 'Truyền động: ' . $type_fuel . '' . '<br/>';
            $content .= 'Thời gian: ' . $time . '' . '<br/>';
            $content .= 'Đia điểm: ' . $address . '' . '<br/>';
            $content .= 'Tổng cộng: ' . $grand_total . '' . '<br/>';
            $content .= 'Tiền cọc: ' . $deposit . '' . '<br/>';
            $content .= 'Thanh toán sau: ' . $grand_total_all . '';
        } elseif($template_id == '379492'){
            $content = 'Dịch vụ '.$type.' Chủ xe '.$car_owner.' đã đồng ý cho thuê xe '.$car_name.'. Để hoàn tất đặt xe, bạn vui lòng vào ứng dụng Kanow hoặc website, chọn đặt cọc để thanh toán trước '.$deposit_percent.'% giá trị chuyến đi.' . '<br/>';
            $content .= 'Mã chuyến: ' . $reference_no . '' . '<br/>';
            $content .= 'Thuê xe: ' . $car_name . '' . '<br/>';
            $content .= 'Truyền động: ' . $type_fuel . '' . '<br/>';
            $content .= 'Thời gian: ' . $time . '' . '<br/>';
            $content .= 'Đia điểm: ' . $address . '' . '<br/>';
            $content .= 'Tổng cộng: ' . $grand_total . '' . '<br/>';
            $content .= 'Tiền cọc: ' . $deposit . '' . '<br/>';
            $content .= 'Thanh toán sau: ' . $grand_total_all . '';
        } elseif ($template_id == '379496'){
            $content = 'Dịch vụ '.$type.' Chủ xe '.$car_owner.' đã hủy chuyến đi xe '.$car_name.'. Vui lòng liên hệ hotline hoặc fanpage Kanow để được hỗ trợ sớm nhất.' . '<br/>';
            $content .= 'Mã chuyến: ' . $reference_no . '' . '<br/>';
            $content .= 'Thuê xe: ' . $car_name . '' . '<br/>';
            $content .= 'Truyền động: ' . $type_fuel . '' . '<br/>';
            $content .= 'Thời gian: ' . $time . '' . '<br/>';
            $content .= 'Đia điểm: ' . $address . '' . '<br/>';
            $content .= 'Tổng cộng: ' . $grand_total . '' . '<br/>';
            $content .= 'Tiền cọc: ' . $deposit . '' . '<br/>';
            $content .= 'Thanh toán sau: ' . $grand_total_all . '';
        }
        //        dd($content_api);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://business.openapi.zalo.me/message/template',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_POSTFIELDS => '{
            "phone": "'.$phone.'",
            "template_id": "'.$template_id.'",
            "template_data": '.$content_api.',
                "tracking_id":"'.$datetimeid.'"
            }',
            CURLOPT_HTTPHEADER => array(
                'access_token: '.get_option('access_token_zalo').'',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $curl_response = json_decode($response);
        $status = 0;
        $log = null;
        $sendZalo = false;
        if(!empty($curl_response)){
            if($curl_response->error == 0){
                $status = $curl_response->data->msg_id;
                $sendZalo = true;
            } elseif ($curl_response->error == -124){
                $status = $curl_response->error;
                refresh_token_zalo($transation_id, $event, $template_id);
            } else {
                $status = $curl_response->error;
            }
            $log = $curl_response->message;
        }
        DB::table('tbl_send_zalo')->where('id', $id_send_zalo)->update([
            'send_zalo_id' => $status,
            'content' => $content,
            'log' => $log
        ]);
        if (!empty($sendZalo)) {
            return true;
        }
    }
    return false;
}

function refresh_token_zalo($transation_id = 0, $event = '', $template_id = 0){

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://oauth.zaloapp.com/v4/oa/access_token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_POSTFIELDS => 'refresh_token='.get_option('refresh_token_zalo').'&app_id=1369923589612502152&grant_type=refresh_token',
        CURLOPT_HTTPHEADER => array(
            'secret_key: 4oY3eYENMHU8N6qNGLVs',
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $response = json_decode($response);
    if (!empty($response->refresh_token)) {
        DB::table('tbl_options')->where('name', 'refresh_token_zalo')->update([
            'name' => 'refresh_token_zalo',
            'value' => $response->refresh_token
        ]);
        DB::table('tbl_options')->where('name', 'access_token_zalo')->update([
            'name' => 'access_token_zalo',
            'value' => $response->access_token
        ]);
        $app = new App();
        $app->flushCache();
        send_zalo($transation_id,$event,$template_id);
    }
}

function countNotiNotRead()
{
    $staff_id = get_staff_user_id();
    $dtNoti = Notification::whereHas('notification_staff', function ($query) use ($staff_id) {
        $query->where('is_read', 0);
        $query->where('object_id', $staff_id);
        $query->where('object_type', 'staff');
    })->count();
    return $dtNoti;
}

function distance($lat1, $lon1, $lat2, $lon2, $unit)
{
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    } else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } elseif ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}

function diffInHours($start_date, $end_date)
{
    $starttimestamp = strtotime($start_date);
    $endtimestamp = strtotime($end_date);
    $diff = abs($endtimestamp - $starttimestamp) / 3600;
    return $diff;
}

function formatDayHour($hour = 0)
{
    $day_new = 0;
    $day = floor($hour / 24);
    $hour_old = $hour - ($day * 24);
    if ($hour_old > 0) {
        $day_new = 1;
    }
    $day = $day + $day_new;
    return $day;
}

function formatDayHourProvince($hour = 0)
{
    if ($hour < 24) {
        return '0.' . $hour;
    }
    $day_new = 0;
    $day = floor($hour / 24);
    $hour_old = $hour - ($day * 24);
    if ($hour_old > 0) {
        $hour_old = $hour_old;
    }
    $day = $day . '.' . $hour_old;
    return $day;
}

function ConnectPusher($data = '', $users = [], $events = 'change-status')
{
    $config = Config::get('broadcasting.connections.pusher');
    $options = [
        'cluster' => $config['options']['cluster'],
        'encrypted' => $config['options']['encrypted'],
        'useTLS' => false
    ];
    if (!is_array($users) || count($users) == 0) {
        return false;
    }
    $channels = [];
    foreach ($users as $user) {
        $object_type = $user['object_type'] == 'owen' ? 'customer' : $user['object_type'];
        array_push($channels, 'notifications-channel-' . $user['object_id'] . '-' . $object_type);
    }
    $channels = array_unique($channels);
    $pusher = new Pusher($config['key'], $config['secret'], $config['app_id'], $options);
    if (count($channels) <= 100) {
        $pusher->trigger($channels, $events, $data);
    } else {
        // Chia mảng thành các phần nhỏ và gửi từng phần
        $chunks = array_chunk($channels, 100);
        foreach ($chunks as $chunk) {
            $pusher->trigger($chunk, $events, $data);
        }
    }
//    $pusher->trigger($channels, $events, $data);
    return true;
}

if (!function_exists('lang')) {
    function lang($message = null, $key = 'message')
    {
        $messageKey = (string)$message;

        $translated = trans($key . '.' . $messageKey);

        if ($translated === $key . '.' . $messageKey) {
            return $message;
        }

        return $translated;
    }
}
if (!function_exists('has_permission')) {
    function has_permission($permission_parent, $permission, $user_id = 0)
    {
        if (!empty($user_id)) {
            $user = Cache::remember('user-info', 3600, function () use ($user_id) {
                return \App\Models\User::find($user_id);
            });
        } else {
            $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        }
        if (empty($user)) {
            return false;
        }
        $subAdmin = $user->admin;
        if ($subAdmin) {
            return true;
        }
        return $user->hasPermissionUser($permission_parent, $permission);
    }
}
if (!function_exists('is_admin')) {
    function is_admin($user_id = 0)
    {
        if (!empty($user_id)) {
            $user = Cache::remember('user-info', 3600, function () use ($user_id) {
                return \App\Models\User::find($user_id);
            });
        } else {
            $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        }
        if (empty($user)) {
            return false;
        }

        $subAdmin = $user->admin;
        if ($subAdmin) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('access_denied')) {
    function access_denied($js = false, $lang = 'dt_access')
    {
        if ($js) {
            die("<script type='text/javascript'>alert_float('error','" . lang($lang) . "');setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'admin/dashboard') . "'; }, 1000);</script>");
        } else {
            return abort(401);
        }
    }
}
if (!function_exists('alert_float')) {
    function alert_float($type = 'success', $message = '')
    {
        return \Notify::$type($message, $title = null, $options = []);
    }
}

function loadImage($src = null, $size = '50px', $type = 'img-circle', $value = '', $delete = false, $height = '')
{
    return !empty($src) ? '<div style="display: flex;justify-content:center;margin-top: 5px;margin-right: 5px"
                 class="show_image">
                <a href="' . $src . '" data-lightbox="customer-profile" class="display-block mbot5">
                <img src="' . $src . '" alt="image"
                     class="img-responsive ' . $type . '"
                     ' . (!empty($size) ? 'style="width: ' . $size . ';height: ' . (!empty($height) ? $height : $size) . '"' : '') . ' >
                </a>
                <input type="hidden" name="image_old[]" id="image_old"
                class="image_old"
               data-buttonbefore="true" value="' . $value . '">
                ' . (!empty($delete) ? '<span class="delete_image" style="cursor: pointer;"><i
                class="glyphicon glyphicon-remove"></i></span>' : '') . '
            </div>' : '';
}

function loadImageNew(
    $src = null,
    $size = '50px',
    $type = 'img-circle',
    $value = '',
    $delete = false,
    $height = '',
    $name = 'image_old'
) {
    return !empty($src) ? '<div style="display: flex;justify-content:center;margin-top: 5px;margin-right: 5px"
                 class="show_image">
                <a href="' . $src . '" data-lightbox="customer-profile" class="display-block mbot5">
                <img src="' . $src . '" alt="image"
                     class="img-responsive ' . $type . '"
                     ' . (!empty($size) ? 'style="width: ' . $size . ';height: ' . (!empty($height) ? $height : $size) . '"' : '') . ' >
                </a>
                <input type="hidden" name="' . $name . '[]" id="' . $name . '"
                class="' . $name . '"
               data-buttonbefore="true" value="' . $value . '">
                ' . (!empty($delete) ? '<span class="delete_image" style="cursor: pointer;"><i
                class="glyphicon glyphicon-remove"></i></span>' : '') . '
            </div>' : '';
}

function loadImageAvatar($src = null, $size = '50px', $type = 'img-circle')
{
    return !empty($src) ? '<div style="margin-top: 5px;margin-right: 5px"
                 class="show_image">
                <a href="' . $src . '" data-lightbox="customer-profile" class="display-block mbot5">
                <img src="' . $src . '" alt="image"
                     class="img-responsive ' . $type . '"
                     style="width: ' . $size . ';height: ' . $size . '">
                </a>
            </div>' : '';
}

function loadHtmlReviewStar($star = 1)
{
    $html = '';
    $star = number_unformat($star);
    $maxStar = 5 - $star;
    for ($i = 1; $i <= $star; $i++) {
        $html .= '<li><a class="fa fa-star" href=""></a></li>';
    }
    for ($i = 1; $i <= $maxStar; $i++) {
        $html .= '<li><a class="fa fa-star-o" href=""></a></li>';
    }
    return '<div class="rating"><ul class="list-inline">' . $html . '</ul></div>';
}

function loadHtmlReviewStarNew($star = 1)
{
    $html = '';
    $star = number_unformat($star);
    $maxStar = 5 - $star;
    for ($i = 1; $i <= $star; $i++) {
        $html .= '<li><a class="fa fa-star" href=""></a></li>';
    }
    return '<div class="rating"><ul class="list-inline">' . $html . '</ul></div>';
}

function getFileType()
{
    $arFileType = ['png', 'jpeg', 'jpg', 'gif'];
    return $arFileType;
}

function getListGender($id = 0)
{
    $data = [
        [
            'id' => 1,
            'name' => 'Nam',
        ],
        [
            'id' => 2,
            'name' => 'Nữ',
        ],
        [
            'id' => 2,
            'name' => 'Khác',
        ],
    ];
    if (!empty($id)) {
        $data = array_filter($data, function ($item) use ($id) {
            return $item['id'] == $id;
        });
        if (!empty($data)) {
            $data = array_values($data);
            return $data[0]['name'];
        } else {
            return null;
        }
    } else {
        return $data;
    }
}

function getListPriceMonth()
{
    return [
        [
            'id' => 1,
            'name' => '1 tháng tới',
            'selected' => false,
        ],
        [
            'id' => 2,
            'name' => '2 tháng tới',
            'selected' => false,
        ],
        [
            'id' => 3,
            'name' => '3 tháng tới',
            'selected' => true,
        ],
        [
            'id' => 5,
            'name' => '5 tháng tới',
            'selected' => false,
        ],
    ];
}

function getListStatusTransaction()
{
    return [
        [
            'id' => 0,
            'name' => 'Chưa duyệt',
            'color' => '#989898',
            'index' => 0,
        ],
        [
            'id' => 1,
            'name' => 'Đã thanh toán',
            'color' => '#ffbd4a',
            'index' => 1,
        ],
        [
            'id' => 2,
            'name' => 'Hủy giao dịch',
            'color' => '#ef190a',
            'index' => 2,
        ],
    ];
}

function getValueStatusTransaction($id, $type = 'name')
{
    $option[0]['name'] = lang('chua_duyet');
    $option[1]['name'] = lang('da_thanh_toan');
    $option[2]['name'] = lang('huy_giao_dich');

    $option[0]['color'] = '#989898';
    $option[1]['color'] = '#ffbd4a';
    $option[2]['color'] = '#ef190a';

    $option[0]['index'] = 0;
    $option[1]['index'] = 1;
    $option[2]['index'] = 2;

    return $option[$id][$type];
}

//Hình ảnh mặc định
function imgDefault()
{
    return 'admin/assets/images/users/avatar-1.jpg';
}

function imgCameraDefault()
{
    return 'admin/assets/images/not_available.jpg';
}

function get_option($field = '')
{
    $app = new App();
    return $app->get_option($field);
    // $data = DB::table('tbl_options')->where('name', $field)->get()->first();
    // return !empty($data) ? $data->value : null;
}

function to_sql_date($date, $datetime = false)
{
    if (strpos($date, ' ') === false) {
        $date .= ' 00:00:00';
    }
    $from_format = get_current_date_format(true);
    $date = _simplify_date_fix($date, $from_format);
    $timestamp = strtotime($date);
    if (!empty($datetime)) {
        $mydate = strftime('%Y-%m-%d %H:%M:%S', $timestamp);
    } else {
        $mydate = strftime('%Y-%m-%d', $timestamp);
    }

    return $mydate;
}

function _simplify_date_fix($date, $from_format)
{
    if ($from_format == 'd/m/Y') {
        $date = preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$2-$1 $4', $date);
    } elseif ($from_format == 'm/d/Y') {
        $date = preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    } elseif ($from_format == 'm.d.Y') {
        $date = preg_replace('#(\d{2}).(\d{2}).(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    } elseif ($from_format == 'm-d-Y') {
        $date = preg_replace('#(\d{2})-(\d{2})-(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    }

    return $date;
}


function number_unformat($number, $force_number = true)
{
    $decimal_separator = get_option('decimal_separator');
    $thousand_separator = get_option('thousand_separator');
    if ($force_number) {
        $number = preg_replace('/^[^\d]+/', '', $number);
    } elseif (preg_match('/^[^\d]+/', $number)) {
        return false;
    }
    $dec_point = $decimal_separator;
    $thousands_sep = $thousand_separator;
    $type = (strpos($number, $dec_point) === false) ? 'int' : 'float';
    $number = str_replace([
        $dec_point,
        $thousands_sep,
    ], [
        '.',
        '',
    ], $number);
    settype($number, $type);

    return $number;
}

function get_staff_user_id($staff_id = 0)
{
    if (!empty($staff_id)) {
        $user = Cache::remember('user-info', 3600, function () use ($staff_id) {
            return \App\Models\User::find($staff_id);
        });
    } else {
        $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
    }
    if (empty($user)) {
        return 0;
    } else {
        return $user->id;
    }
}

function getDiffForHumans($date = '')
{
    if (empty($date)) {
        return null;
    }
    Carbon::setLocale('vi');
    $dt = new Carbon($date);
    $now = Carbon::now();
    return $dt->diffForHumans($now);
}

if (!function_exists('convertDate')) {
    function convertDate($date_work)
    {
        $date = '';
        switch (true) {
            case $date_work == 'Mon':
                $date = 'Monday';
                break;
            case $date_work == 'Tue':
                $date = 'Tuesday';
                break;
            case $date_work == 'Wed':
                $date = 'Wednesday';
                break;
            case $date_work == 'Thu':
                $date = 'Thursday';
                break;
            case $date_work == 'Fri':
                $date = 'Friday';
                break;
            case $date_work == 'Sat':
                $date = 'Saturday';
                break;
            case $date_work == 'Sun':
                $date = 'Sunday';
                break;
            default:
                $date = $date_work;
                break;
        }

        return $date;
    }
}

function get_current_date_format($php = false)
{
    $format = "d/m/Y|%d/%m/%Y";
    $format = explode('|', $format);

    if ($php == false) {
        return $format[1];
    }

    return $format[0];
}

function _dt($date, $is_timesheet = false)
{
    $original = $date;
    $time_format = 24;

    if ($date == '' || is_null($date) || $date == '0000-00-00 00:00:00') {
        return '';
    }

    $format = get_current_date_format();
    $hour12 = ($time_format == 24 ? false : true);

    if ($is_timesheet == false) {
        $date = strtotime($date);
    }

    if ($hour12 == false) {
        $tf = '%H:%M:%S';
        if ($is_timesheet == true) {
            $tf = '%H:%M';
        }
        $date = strftime($format . ' ' . $tf, $date);
    } else {
        $date = date(get_current_date_format(true) . ' g:i A', $date);
    }

    return $date;
}

function _dt_new($date)
{
    $original = $date;
    $time_format = 24;

    if ($date == '' || is_null($date) || $date == '0000-00-00 00:00:00') {
        return '';
    }

    $format = get_current_date_format();
    $hour12 = ($time_format == 24 ? false : true);

    $date = strtotime($date);

    if ($hour12 == false) {
        $tf = '%H:%M';
        $dayname = convertDate(date('D', $date));

        $date = strftime($tf . ' - ' . $dayname . ', ' . $format, $date);
    } else {
        $date = date(get_current_date_format(true) . ' g:i A', $date);
    }

    return $date;
}

function _dthuan($date, $is_timesheet = false)
{
    $original = $date;
    $time_format = 24;

    if ($date == '' || is_null($date) || $date == '0000-00-00') {
        return '';
    }

    $format = get_current_date_format();
    $hour12 = ($time_format == 24 ? false : true);

    if ($is_timesheet == false) {
        $date = strtotime($date);
    }

    if ($hour12 == false) {
        $tf = '';
        if ($is_timesheet == true) {
            $tf = '';
        }
        $date = strftime($format . ' ' . $tf, $date);
    } else {
        $date = date(get_current_date_format(true) . ' g:i A', $date);
    }
    $date = trim($date);
    return $date;
}

function formatDecimalMoney($number, $decimals = null)
{
    $decimals_money = get_option('decimals_money');
    if (!is_numeric($number)) {
        return null;
    }
    if (!$decimals) {
        $decimals = $decimals_money;
    }

    return number_format($number, $decimals, '.', '');
}

function formatSAC($num)
{
    $pos = strpos((string)$num, ".");
    if ($pos === false) {
        $decimalpart = "00";
    } else {
        $decimalpart = substr($num, $pos + 1, 2);
        $num = substr($num, 0, $pos);
    }

    if (strlen($num) > 3 & strlen($num) <= 12) {
        $last3digits = substr($num, -3);
        $numexceptlastdigits = substr($num, 0, -3);
        $formatted = $numexceptlastdigits;
        $stringtoreturn = $formatted . "," . $last3digits . "." . $decimalpart;
    } elseif (strlen($num) <= 3) {
        $stringtoreturn = $num . "." . $decimalpart;
    } elseif (strlen($num) > 12) {
        $stringtoreturn = number_format($num, 2);
    }

    if (substr($stringtoreturn, 0, 2) == "-,") {
        $stringtoreturn = "-" . substr($stringtoreturn, 2);
    }

    return $stringtoreturn;
}

function is_decimal($val)
{
    return is_numeric($val) && floor($val) != $val;
}

function formatMoney($number, $decimals = null)
{
    $decimals_money = get_option('decimals_money');
    $sac = 0;
    $thousands_sep = get_option('thousands_sep');
    $decimals_sep = get_option('decimals_sep');
    if ($sac) {
        return formatSAC(formatDecimalMoney($number));
    }
    if (!$decimals) {
        $decimals = $decimals_money;
    }

    if (!is_decimal($number)) {
        $decimals = 0;
    }

    $ts = $thousands_sep == '0' ? ' ' : $thousands_sep;
    $ds = $decimals_sep;

    return number_format($number, $decimals, $ds, $ts);
}

function formatNumber($number, $decimals = null)
{
    $decimals_number = get_option('decimals_number');
    $thousands_sep = get_option('thousands_sep');
    $decimals_sep = get_option('decimals_sep');
    if (!$decimals) {
        $decimals = $decimals_number;
    }
    $ts = $thousands_sep == '0' ? ' ' : $thousands_sep;
    $ds = $decimals_sep;

    return number_format($number, $decimals, $ds, $ts);
}

function formatNumberStar($number, $decimals = null)
{
    $decimals_number = 1;
    $thousands_sep = get_option('thousands_sep');
    $decimals_sep = get_option('decimals_sep');
    if (!$decimals) {
        $decimals = $decimals_number;
    }
    $ts = $thousands_sep == '0' ? ' ' : $thousands_sep;
    $ds = $decimals_sep;

    return number_format($number, $decimals, $ds, $ts);
}

function date_range($first, $last, $step = '+1 day', $output_format = 'd/m/Y')
{

    $dates = array();
    $first = explode('/', $first);
    $last = explode('/', $last);
    if (empty($first) || empty($last)) {
        return null;
    }
    $current = mktime(0, 0, 0, $first[1], $first[0], $first[2]);
    $last = mktime(0, 0, 0, $last[1], $last[0], $last[2]);
    while ($current <= $last) {

        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

function createDateRangeArray($month, $year)
{
    $date_array = array();
    $start_date = mktime(0, 0, 0, $month, 1, $year);
    $end_date = mktime(0, 0, 0, $month + 1, 0, $year);

    while ($start_date <= $end_date) {
        $date_array[] = date('Y-m-d', $start_date);
        $start_date = strtotime('+1 day', $start_date);
    }

    return $date_array;
}

if (!function_exists('convertDateNew')) {
    function convertDateNew($date_work)
    {
        $date = '';
        switch (true) {
            case $date_work == 'Mon':
                $date = 'T2';
                break;
            case $date_work == 'Tue':
                $date = 'T3';
                break;
            case $date_work == 'Wed':
                $date = 'T4';
                break;
            case $date_work == 'Thu':
                $date = 'T5';
                break;
            case $date_work == 'Fri':
                $date = 'T6';
                break;
            case $date_work == 'Sat':
                $date = 'T7';
                break;
            case $date_work == 'Sun':
                $date = 'CN';
                break;
            default:
                $date = $date_work;
                break;
        }

        return $date;
    }
}

function year2array($year)
{
    $res = $year >= 1970;
    if ($res) {
        date_default_timezone_set(date_default_timezone_get());
        $dt = strtotime("-1 day", strtotime("$year-01-01 00:00:00"));
        $res = array();
        $week = array_fill(1, 7, false);
        $last_month = 1;
        $w = 1;
        do {
            $dt = strtotime('+1 day', $dt);
            $dta = getdate($dt);
            $wday = $dta['wday'] == 0 ? 7 : $dta['wday'];
            if (($dta['mon'] != $last_month) || ($wday == 1)) {
                if ($week[1] || $week[7]) {
                    $res[$last_month][] = $week;
                }
                $week = array_fill(1, 7, false);
                $last_month = $dta['mon'];
            }
            $week[$wday] = $dta['mday'];
        } while ($dta['year'] == $year);
    }
    return $res;
}

function month2table($month, $calendar_array, $year, $dtDate = array(), $type = 0, $type_car = 1)
{
    $month_new = $month;
    if ($month < 10) {
        $month = (int)($month);
    }
    $dateNow = date('Y-m-d');
    if ($type_car == 1) {
        $classTable = 'tb-price';
        $dayName = 'day';
        $priceName = 'price';
        $statusName = 'status';
    } else {
        $classTable = 'tb-price-talent';
        $dayName = 'day_talent';
        $priceName = 'price_talent';
        $statusName = 'status_talent';
    }
    $res = '<table class="tb-price-month ' . $classTable . '">
            <thead>
                 <tr>
                    <td colspan="7">Tháng ' . $month . ' ' . $year . '</td>
                 </tr>
            </thead>
            <tr class="days">
                <td>T2</td>
                <td>T3</td>
                <td>T4</td>
                <td>T5</td>
                <td>T6</td>
                <td>T7</td>
                <td>CN</td>
                </tr>';
    foreach ($calendar_array[$month] as $week) {
        $res .= '<tr>';
        foreach ($week as $day) {
            $price = -1;
            $date = '';
            $dateCheck = '';
            $status = 0;
            $id = 0;
            if (!empty($dtDate)) {
                foreach ($dtDate as $k => $v) {
                    if ($v->day == $day) {
                        $price = $v->price;
                        $date = _dthuan($v->date);
                        $dateCheck = ($v->date);
                        $status = $v->status;
                        $id = $v->id;
                    }
                }
            }
            if (empty($day)) {
                $res .= '<td></td>';
            } else {
                $res .= '<td class="normal status_' . $status . ' ' . (strtotime($dateCheck) < strtotime($dateNow) ? 'day_old' : '') . ' month_' . $month_new . '">
                    ' . ($type == 0 ? '<a class="dt-modal2 update" data-id="' . $id . '" href="admin/car/updatePrice/' . $id . '/' . $type_car . '">' : '<a class="update" data-id="' . $id . '" onclick="updateStatus(' . $id . ',' . $type_car . ')" >') . '
                    <input type="hidden" name="' . $dayName . '[' . $month_new . '][]" value="' . $date . '">
                    <input type="hidden" name="' . $priceName . '[' . $month_new . '][' . $date . ']" value="' . $price . '">
                    <input type="hidden" name="' . $statusName . '[' . $month_new . '][' . $date . ']" value="' . $status . '">
                    <span class="day">' . $day . '</span><br><span class="price_db status_' . $status . '">' . ($price != -1 ? formatMoney($price) : '') . '</span>
                    </a>
                </td>';
            }
        }
        $res .= '</tr>';
    }
    $res .= '</table>';
    return $res;
}

//function that checks if a holiday lands on saturday/sunday and so we can move them to a friday/monday respectively
function getObservedDate($holidayDate)
{

    $dayofweek = date("w", strtotime($holidayDate));

    if ($dayofweek == 6) {
        $holidayDate = date('m/d/Y', strtotime("$holidayDate - 1 days"));
    } //saturday moves to friday
    elseif ($dayofweek == 0) {
        $holidayDate = date('m/d/Y', strtotime("$holidayDate + 1 days"));
    }  //sunday moves monday

    return $holidayDate;
}


//function that calculates the holidays for any given year
function getFederalHolidaysForYear($year)
{

    $NY = getObservedDate(date('m/d/Y', strtotime("1/1/$year"))); //new years day

    $MLK = getObservedDate(date('m/d/Y', strtotime("third monday of january $year")));  //martin luther king day

    $PD = getObservedDate(date('m/d/Y', strtotime("third monday of february $year")));; //presidents day

    $MDay = getObservedDate(date('m/d/Y', strtotime("last monday of May $year"))); //memorial day

    $IDay = getObservedDate(date('m/d/Y', strtotime("7/4/$year")));  // independence day

    $LD = getObservedDate(date('m/d/Y', strtotime("first monday of september $year"))); //labor day

    $VD = getObservedDate(date('m/d/Y', strtotime("11/11/$year"))); //veterans day

    $ColD = getObservedDate(date('m/d/Y', strtotime("second monday of october $year"))); //columbus day

    $TG = getObservedDate(date('m/d/Y', strtotime("last thursday of november $year"))); // thanksgiving

    $CD = getObservedDate(date('m/d/Y', strtotime("12/25/$year")));  //christmas day

    $nonWorkingDays = array();

    array_push($nonWorkingDays, $NY, $MLK, $PD, $MDay, $IDay, $LD, $ColD, $VD, $TG, $CD);

    return $nonWorkingDays;
}

function dateWord()
{
    return [
        [
            'id' => 'CN',
            'name' => 'CN'
        ],
        [
            'id' => 'T2',
            'name' => 'T2'
        ],
        [
            'id' => 'T3',
            'name' => 'T3'
        ],
        [
            'id' => 'T4',
            'name' => 'T4'
        ],
        [
            'id' => 'T5',
            'name' => 'T5'
        ],
        [
            'id' => 'T6',
            'name' => 'T6'
        ],
        [
            'id' => 'T7',
            'name' => 'T7'
        ],
    ];
}

if (!function_exists('getMonth')) {
    function getMonth()
    {
        $option[''] = '';
        $option['01'] = 'Tháng 1';
        $option['02'] = 'Tháng 2';
        $option['03'] = 'Tháng 3';
        $option['04'] = 'Tháng 4';
        $option['05'] = 'Tháng 5';
        $option['06'] = 'Tháng 6';
        $option['07'] = 'Tháng 7';
        $option['08'] = 'Tháng 8';
        $option['09'] = 'Tháng 9';
        $option['10'] = 'Tháng 10';
        $option['11'] = 'Tháng 11';
        $option['12'] = 'Tháng 12';

        return $option;
    }
}

if (!function_exists('getYear')) {
    function getYear()
    {
        $year = [];
        $year[''] = '';
        for ($i = -1; $i < 5; $i++) {
            $date = date('Y', strtotime(date('Y') . ' -' . $i . ' year'));
            $year[$date] = $date;
        }

        return $year;
    }
}

function loadWeekToMonthYear($month, $year)
{
    $startDate = new DateTime("$year-$month-01");
    $endDate = clone $startDate;
    $endDate->modify('last day of this month');

    $dates = [];
    while ($startDate <= $endDate) {
        $dates[] = $startDate->format('Y-m-d');
        $startDate->modify('+1 day');
    }
    return $dates;
}

if (!function_exists('getReference')) {
    function getReference($field)
    {
        $q = DB::table('tbl_order_ref')->where('ref_id', 1)->first();
        if (!empty($q)) {
            $ref = $q;
            switch ($field) {
                case 'transaction':
                    $prefix = 'GD';
                    break;
                case 'payment':
                    $prefix = 'PT';
                    break;
                case 'driver_ticket':
                    $prefix = 'FD';
                    break;
                case 'request_withdraw_money':
                    $prefix = 'YCRT';
                    break;
                case 'transfer_money':
                    $prefix = 'CT';
                    break;
                case 'transfer_package':
                    $prefix = 'CG';
                    break;
                case 'transaction_driver':
                    $prefix = 'GDTX';
                    break;
                case 'payment_driver':
                    $prefix = 'PTTX';
                    break;
                case 'pay_slip':
                    $prefix = 'PC';
                    break;
                case 'client':
                    $prefix = 'KH';
                    break;
                case 'driver':
                    $prefix = 'TX';
                    break;
                case 'contract_transaction':
                    $prefix = 'HĐTX';
                    break;
                case 'handover_record':
                    $prefix = 'BBBG';
                    break;
                default:
                    $prefix = '';
            }

            $separator = get_option('separator');
            $format_date_prefix = get_option('format_date_prefix');
            $ref_no = (!empty($prefix)) ? $prefix . "$separator" : '';
            $ref_no .= date("$format_date_prefix") . sprintf("%02s", $ref->{$field});

            return $ref_no;
        }
        return false;
    }
}

if (!function_exists('updateReference')) {
    function updateReference($field)
    {
        $q = DB::table('tbl_order_ref')->where('ref_id', 1)->first();
        if (!empty($q)) {
            $ref = $q;
            DB::table('tbl_order_ref')->where('ref_id', 1)->update([$field => $ref->{$field} + 1]);
            return true;
        }
        return false;
    }
}

function optionHour($hour_start = '00:00', $hour_end = '23:30', $defaultValue = -1)
{
    $hour_start = strtotime(date('Y-m-d') . ' ' . $hour_start);
    $hour_end = strtotime(date('Y-m-d') . ' ' . $hour_end);
    $arrHour = [];
    $value = 0;
    for ($i = ($hour_start); $i <= $hour_end; $i = $i_new) {
        $date_new = strftime("%Y-%m-%d %H:%M:%S", $i);
        if ($defaultValue == $value) {
            return strftime("%H:%M", strtotime($date_new));
        }
        $arrHour[] = [
            'value' => $value,
            'hour' => date("h:i a", strtotime($date_new)),
            'hour_new' => strftime("%H:%M", strtotime($date_new)),
        ];
        $i = strftime("%Y-%m-%d %H:%M:%S", $i);
        $i_new = strtotime($i . " +30 minutes");
        $value += 1800;
    }
    return $arrHour;
}

function GetCurlData($service_url = "", $data_string = [])
{
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_URL, $service_url);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_HTTPGET, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('application/json'));
    if (!empty($data_string)) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    }
    $curl_response = curl_exec($curl);
    curl_close($curl);
    return $curl_response;
}

if (!function_exists('menuHelper')) {
    function menuHelper()
    {
        $menu = [
            [
                'id' => 'Dashboard',
                'name' => 'Dashboard',
                'link' => 'admin/dashboard',
                'class' => 'dashboad',
                'image' => 'admin/assets/images/icon_menu/dashboard.png',
                'child' => []
            ],
            [
                'id' => 'category',
                'name' => lang('dt_category'),
                'link' => '',
                'class' => 'danh_muc',
                'image' => 'admin/assets/images/icon_menu/danh_muc.png',
                'child' => [
                    [
                        'id' => 'department',
                        'name' => lang('dt_department'),
                        'link' => 'admin/department/list',
                        'image' => '',
                    ],
                    [
                        'id' => 'role',
                        'name' => lang('dt_role'),
                        'link' => 'admin/role/list',
                        'image' => '',
                    ],
                    [
                        'id' => 'department',
                        'name' => lang('dt_permission'),
                        'link' => 'admin/permission/list',
                        'image' => '',
                    ]
                ]
            ],
            [
                'id' => 'user',
                'name' => lang('dt_user'),
                'link' => 'admin/user/list',
                'class' => 'nhan_vien',
                'image' => 'admin/assets/images/icon_menu/nhan_vien.png',
                'child' => []
            ],
            [
                'id' => 'manager_clients',
                'name' => 'Thành viên',
                'link' => 'admin/clients/list',
                'class' => 'nguoi_dung_app',
                'image' => 'admin/assets/images/icon_menu/nguoi_dung_app.png',
                'child' => [
                ],
            ],
            [
                'id' => 'service',
                'name' => 'Dịch vụ sản phẩm',
                'link' => '',
                'class' => 'danh_muc',
                'image' => 'admin/assets/images/icon_menu/danh_muc.png',
                'child' => [
                ]
            ],
            [
                'id' => 'module_noti',
                'name' => 'Thông báo',
                'link' => 'admin/module_noti/list',
                'class' => 'danh_muc',
                'image' => 'admin/assets/images/icon_menu/danh_muc.png',
                'child' => [
                ]
            ],
            [
                'id' => 'settings',
                'name' => 'Cài Đặt',
                'link' => 'admin/settings',
                'class' => 'cai_dat',
                'image' => 'admin/assets/images/icon_menu/cai_dat.png',
                'child' => []
            ],
        ];
        return $menu;
    }
}

function getListDay($id = "")
{
    $data = [
        [
            'id' => 'Mon',
            'name' => 'Thứ 2'
        ],
        [
            'id' => 'Tue',
            'name' => 'Thứ 3'
        ],
        [
            'id' => 'Wed',
            'name' => 'Thứ 4'
        ],
        [
            'id' => 'Thu',
            'name' => 'Thứ 5'
        ],
        [
            'id' => 'Fri',
            'name' => 'Thứ 6'
        ],
        [
            'id' => 'Sat',
            'name' => 'Thứ 7'
        ],
        [
            'id' => 'Sun',
            'name' => 'Chủ nhật'
        ],
    ];
    if (!empty($id)) {
        $data = array_filter($data, function ($item) use ($id) {
            return $item['id'] == $id;
        });
        if (!empty($data)) {
            $data = array_values($data);
            return $data[0]['name'];
        } else {
            return null;
        }
    } else {
        return $data;
    }
}

function getDistancesToMultipleDestinations($originLat, $originLng, $destinations, $apiKey,$objectKey = 'service_id')
{
    $maxPerRequest = 40; // Giới hạn của Google API
    $origin = "$originLat,$originLng";
    $results = [];
    $destinationChunks = array_chunk($destinations, $maxPerRequest);
    foreach ($destinationChunks as $chunk) {
        $destStr = implode('|', array_map(function ($item) {
            return "{$item['lat']},{$item['lng']}";
        }, $chunk));
        // Gọi API
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$origin&destinations=$destStr&language=vi&key=$apiKey";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        if ($data['status'] === 'OK') {
            $elements = $data['rows'][0]['elements'];
            foreach ($elements as $index => $element) {
                $objectId = $chunk[$index][$objectKey];
                if ($element['status'] === 'OK') {
                    $results[$objectId] = [
                        'lat' => $chunk[$index]['lat'],
                        'lng' => $chunk[$index]['lng'],
                        'distance_km' => $element['distance']['value'] / 1000,
                        'duration_text' => $element['duration']['text'],
                        'duration_seconds' => $element['duration']['value']
                    ];
                } else {
                    $results[$objectId] = [
                        'lat' => $chunk[$index]['lat'],
                        'lng' => $chunk[$index]['lng'],
                        'error' => $element['status']
                    ];
                }
            }
        } else {
            return [
                'error' => 'API Error: ' . $data['status']
            ];
        }
    }
    return $results;
}


function getLatLngFromAddress($address, $apiKey)
{
    $address = urlencode($address);

    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";

    // Gửi request
    $response = file_get_contents($url);
    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    // Kiểm tra kết quả
    if (isset($data['status']) && $data['status'] === 'OK') {
        $location = $data['results'][0]['geometry']['location'];
        return [
            'lat' => $location['lat'],
            'lng' => $location['lng']
        ];
    }

    return null;
}

function getListStatusService($id = -1,$type = 'name')
{
    $data = [
        [
            'id' => 0,
            'name' => lang('Đang chờ duyệt'),
            'color' => '#FF5A1F',
            'background' => '#FEECDC'
        ],
        [
            'id' => 1,
            'name' => lang('Đang hoạt động'),
            'color' => '#079449',
            'background' => '#D7FAE0'
        ],
        [
            'id' => 2,
            'name' => lang('Đã bị từ chối'),
            'color' => '#D93843',
            'background' => '#FFDBDE'
        ],
        [
            'id' => 3,
            'name' => lang('Đang tạm ngưng'),
            'color' => '#64646D',
            'background' => '#EBEBF0'
        ],
        [
            'id' => 4,
            'name' => lang('Đang khởi tạo'),
            'color' => '#0B74E5',
            'background' => '#DBEEFF'
        ],
    ];
    if ($id != -1) {
        $data = array_filter($data, function ($item) use ($id) {
            return $item['id'] == $id;
        });
        if (!empty($data)) {
            $data = array_values($data);
            return $data[0][$type];
        } else {
            return null;
        }
    } else {
        return $data;
    }
}
