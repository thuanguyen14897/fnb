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

    static function notiEndTransaction($dtData, $type, $created_by, $check = 1, $arr_object_id = [])
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

                $content = "Chuyến đi " . $transaction['name'] . ', Khách hàng ' . $transaction['customer']['fullname'] . ', ' . _dt_new($transaction['date_start'],
                        false) . ' - ' . _dt_new($transaction['date_end'], false) . ' đã được hoàn thành ! Cám ơn quý khách đã tin tưởng đồng hành cùng FnBVN.';
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

                $content = "Khách hàng  " . $transaction['customer']['fullname'] . ' đã hủy chuyến đi ' . $transaction['reference_no'] . ', ' . _dt_new($transaction['date_start'],false) . ' - ' . _dt_new($transaction['date_end'],false) . '';
                $title = 'Khách hàng hủy chuyến';
                $title_owen = 'Khách hàng hủy chuyến';
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

    static function notiPaymentTransactionPackage($dtData, $type, $created_by, $check = 1, $arr_object_id = [])
    {
        if (!empty($dtData)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode([
                    'transaction_package_id' => $dtData['id'],
                    'object' => 'transaction_package'
                ], JSON_UNESCAPED_UNICODE);
                $content = "Giao dịch mua gói thành viên " . $dtData['reference_no'] . ', Thành viên ' . $dtData['data_customer']['fullname'] .' ' . _dt($dtData['date']) . ' đã thanh toán thành công. Số tiền ' . formatMoney($dtData['grand_total']) . '';
                $title = 'Thanh toán mua gói thành viên';
                $title_owen = 'Thanh toán mua gói thành viên';
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
                static::addNotification($dtData['id'], $type, $data);
            }
        }
    }

    static function notiPaymentTransactionBill($dtData, $type, $created_by, $check = 1, $arr_object_id = [])
    {
        if (!empty($dtData)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode([
                    'transaction_bill_id' => $dtData['transaction_bill_id'],
                    'payment_id' => $dtData['id'],
                    'object' => 'payment'
                ], JSON_UNESCAPED_UNICODE);
                $content = "Hóa đơn " . $dtData['data_transaction_bill']['reference_no'] . ', Khách thuê ' . $dtData['data_customer']['fullname'] .' ' . _dt($dtData['date']) . ' đã thanh toán thành công. Số tiền ' . formatMoney($dtData['payment']) . '';
                $title = 'Thanh toán hóa đơn';
                $title_owen = 'Khách hàng thanh toán hóa đơn';
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
                static::addNotification($dtData['id'], $type, $data);
            }
        }
    }

    static function notiRemindPaymentTransaction($dtData, $type, $created_by, $check = 1,$arr_object_id = [])
    {
        if (!empty($dtData)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['transaction_bill_id' => $dtData['id'],'object'=>'transaction_bill'], JSON_UNESCAPED_UNICODE);
                $content = "Hóa đơn " . $dtData['reference_no'] . ', Khách hàng '.$dtData['data_customer']['fullname'].' '._dt($dtData['date']) . '. Có 1 phiếu cần thanh toán!';
                $title = 'Nhắc thanh toán';
                $title_owen = 'Nhắc thanh toán';

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
                static::addNotification($dtData['id'], $type, $data);
            }
        }
    }

    static function notiApproveService($dtData, $type, $created_by,$arr_object_id = [])
    {
        if (!empty($dtData)) {
            $playerId = [];
            if (!empty($arr_object_id)) {
                $active = $dtData['active'];
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['service_id' => $dtData['id'], 'object' => 'service', 'status' => $dtData['active']],
                    JSON_UNESCAPED_UNICODE);
                if ($active == 1) {
                    $content = 'Gian hàng ' . $dtData['name'] . '. Đã được duyệt!';
                    $title = 'Đồng ý duyệt gian hàng';
                    $title_owen = 'Đồng ý duyệt gian hàng';
                } elseif ($active == 2) {
                    $content = 'Gian hàng ' . $dtData['name'] . '. Đã được bị từ chối!';
                    $title = 'Từ chối gian hàng';
                    $title_owen = 'Từ chối gian hàng';
                } elseif ($active == 3) {
                    $content = 'Gian hàng ' . $dtData['name'] . '. Đang tạm ngưng!';
                    $title = 'Tạm ngưng gian hàng';
                    $title_owen = 'Tạm ngưng gian hàng';
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
                static::addNotification($dtData['id'], $type, $data);
            }
        }
    }

    static function notiChangePointClient($customer_id = 0,$dtData = [], $point = 0, $point_client = 0, $title_point = '', $type = 1)
    {
        if (!empty($dtData)) {
            $arr_object_id = array_values($dtData);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode(['customer' => $customer_id, 'object' => 'clients', 'status' => 'change_point'],
                    JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';
                if ($point < 0) {
                    $prefix = '';
                } else {
                    $prefix = '+';
                }
                $content = $title_point . '. Số điểm của bạn tại FNBVN ' . $prefix . formatMoney($point) . ' điểm. Số dư điểm khả dụng: ' . formatMoney($point_client) . ' điểm';
                $title = 'Thay đổi số dư điểm';
                $title_owen = 'Thay đổi số dư điểm';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'object_id' => $customer_id,
                    'content' => $content,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_owen,
                ];
                static::addNotification($customer_id, Config::get('constant')['noti_change_point'], $data);
            }
        }
    }

    static function notiChangeBalance(
        $customer_id = 0,
        $dtData = [],
        $revenue = 0,
        $account_balance_client = 0,
        $title_balance = '',
    ) {
        if (!empty($dtData)) {
            $arr_object_id = array_values($dtData);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode([
                    'customer' => $customer_id,
                    'object' => 'clients',
                    'status' => 'change_balance'
                ], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';
                if ($revenue < 0) {
                    $prefix = '-';
                } else {
                    $prefix = '+';
                }
                $content = $title_balance . '. TK của bạn tại FNBVN ' . $prefix . formatMoney($revenue) . '. Số dư khả dụng: ' . formatMoney($account_balance_client);
                $title = 'Thay đổi số dư';
                $title_owen = 'Thay đổi số dư';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'object_id' => $customer_id,
                    'content' => $content,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_owen,
                ];
                static::addNotification($customer_id, Config::get('constant')['noti_change_balance'], $data);
            }
        }
    }

    static function notiUpgradeMembership(
        $customer_id = 0,
        $dtData = [],
        $type_check = 1,
        $arr_object_id = [],
        $type_run = 0
    ) {
        if (!empty($dtData)) {
            $arr_object_id = array_values($arr_object_id);
            $playerId = [];
            if (!empty($arr_object_id)) {
                $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                $json_data = json_encode([
                    'customer' => $customer_id,
                    'object' => 'clients',
                    'membership_id' => $dtData['id'] ?? 0,
                    'membership_name' => $dtData['name'] ?? null,
                    'type_check' => $type_check,
                    'status' => 'upgrade_membership',
                    'type_run' => $type_run,
                ], JSON_UNESCAPED_UNICODE);
                $title = '';
                $title_owen = '';
                $name_membership = $dtData['name'] ?? null;
                if ($type_check == 1){
                    if ($type_run == 1){
                        $content = get_option('content_send_noti_upgrade_fall');
                    } else {
                        $content = 'Rất tiếc, do không đạt yêu cầu cần thiết, bạn đã bị rớt xuống hạng '.$name_membership.'. Hãy cố gắng cải thiện và lấy lại phong độ trong các lần sau!';
                    }
                } elseif ($type_check == 2){
                    $content = 'Chúc mừng! Bạn đã hoàn thành các điều kiện và chính thức nâng hạng '.$name_membership.' thành công. Hãy tiếp tục duy trì vị trí để giữ hàng và đạt được nhiều thành tựu hơn nữa!';
                } elseif ($type_check == 3){
                    if ($type_run == 1){
                        $content = get_option('content_send_noti_upgrade');
                    } else {
                        $content = 'Xin chúc mừng bạn đã duy trì thành tích xuất sắc và giữ vững hạng hiện tại ' . $name_membership . '. Hãy tiếp tục nỗ lực để giữ vững phong độ và hướng tới những mục tiêu mới';
                    }
                }
                $title = 'Thay đổi hạng thành viên';
                $title = 'Thay đổi hạng thành viên';
                $data = [
                    'arr_object_id' => $arr_object_id,
                    'player_id' => $playerId,
                    'json_data' => $json_data,
                    'object_id' => $customer_id,
                    'content' => $content,
                    'created_by' => 0,
                    'title' => $title,
                    'title_owen' => $title_owen,
                ];
                static::addNotification($customer_id, Config::get('constant')['noti_upgrade_membership'], $data);
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
