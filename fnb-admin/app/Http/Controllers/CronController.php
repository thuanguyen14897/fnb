<?php

namespace App\Http\Controllers;

use App\Libraries\Alepay;
use App\Models\Car;
use App\Models\ClassCustomer;
use App\Models\Clients;
use App\Models\CustomerClass;
use App\Models\CustomerRewardDay;
use App\Models\Driver;
use App\Models\HistoryCustomerWarning;
use App\Models\HistoryCustomerWarningMail;
use App\Models\ImageCar;
use App\Models\LeaderShipCustomer;
use App\Models\ListBank;
use App\Models\ModuleNoti;
use App\Models\Notification;
use App\Models\PaymentMode;
use App\Models\Permission;
use App\Models\ReferralLevel;
use App\Models\RequestWithdrawMoney;
use App\Models\ReviewCar;
use App\Models\ReviewCustomer;
use App\Models\SettingCustomerClass;
use App\Models\SettingCustomerLeaderShip;
use App\Models\Transaction;
use App\Models\TransactionCertificate;
use App\Models\TransactionDriver;
use App\Models\User;
use App\Traits\NotificationTrait;
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
    use NotificationTrait;

    public function noti_remind_transaction()
    {
        $hour_remind = get_option('hour_remind');
        $date_now = date('Y-m-d H:i');
        $new_date = strtotime('+' . $hour_remind . ' hour', strtotime($date_now));
        $new_date = date('Y-m-d H:i', $new_date);
        $transaction = Transaction::where(function ($query) use ($new_date) {
            $query->where('status', Config::get('constant')['status_despoit']);
            $query->where('noti', 0);
            $query->where('date_start', '<=', DB::raw('"' . $new_date . '"'));
            $query->where('date_start', '>', DB::raw('"' . date('Y-m-d H:i') . '"'));
        })
            ->get();
        $dataNoti = [];
        if (!empty($transaction)) {
            foreach ($transaction as $key => $value) {
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
                        $join->on('tbl_player_id.object_type', '=', DB::raw("'owen'"));
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
                    $json_data = json_encode([
                        'transaction_id' => $value->id,
                        'type' => $value->type,
                        'object' => 'transaction'
                    ], JSON_UNESCAPED_UNICODE);
                    $title = '';
                    $title_owen = '';
                    $content = "Chuyến đi " . $value->reference_no . ', Khách thuê ' . $value->customer->fullname . ', xe ' . $value->car->number_car . ', ' . _dt_new($value->date_start) . ' - ' . _dt_new($value->date_end) . ' sắp bắt đầu';
                    $title = 'Chuyến đi sắp tới';
                    $title_owen = 'Chuyến đi sắp tới';
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
        $this->sendNotiOnesignalMutile($dataNoti, Config::get('constant')['noti_remind']);
        dd($dataNoti);
    }

    public function noti_one_hour_remind_transaction()
    {
        $hour_remind = get_option('hour_remind_one');
        $date_now = date('Y-m-d H:i');
        $new_date = strtotime('+' . $hour_remind . ' hour', strtotime($date_now));
        $new_date = date('Y-m-d H:i', $new_date);
        $transaction = Transaction::where(function ($query) use ($new_date) {
            $query->where('status', Config::get('constant')['status_despoit']);
            $query->where('noti_one', 0);
            $query->where('date_start', '<=', DB::raw('"' . $new_date . '"'));
            $query->where('date_start', '>', DB::raw('"' . date('Y-m-d H:i') . '"'));
        })
            ->get();
        $dataNoti = [];
        if (!empty($transaction)) {
            foreach ($transaction as $key => $value) {
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
                        $join->on('tbl_player_id.object_type', '=', DB::raw("'owen'"));
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
                    $json_data = json_encode([
                        'transaction_id' => $value->id,
                        'type' => $value->type,
                        'object' => 'transaction'
                    ], JSON_UNESCAPED_UNICODE);
                    $title = '';
                    $title_owen = '';
                    $content = "Chuyến đi " . $value->reference_no . ', Khch thuê ' . $value->customer->fullname . ', xe ' . $value->car->number_car . ', ' . _dt_new($value->date_start) . ' - ' . _dt_new($value->date_end) . ' sắp bt đầu';
                    $title = 'Chuyến đi sắp ti';
                    $title_owen = 'Chuyến đi sắp ti';
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
        $this->sendNotiOnesignalMutile($dataNoti, Config::get('constant')['noti_remind_one']);
        dd($dataNoti);
    }

    public function cronCloseBalance()
    {
        $dtClient = Clients::where('active', 1)->where('id', '!=', Config::get('constant')['customer_kanow'])->get();
        $dtClient = Clients::where('active', 1)->where('id', 25)->get();
        $count = 0;
        if (!empty($dtClient)) {
            foreach ($dtClient as $row) {
                $account_balance = 0;
                $arrUpdate = [];
                $arrUpdateRequest = [];
                $arrMonthYear = [];

                $dtRequestMoney = RequestWithdrawMoney::select(
                    'tbl_request_withdraw_money.id',
                    'tbl_request_withdraw_money.status',
                    DB::raw("'request_money' as 'type'"),
                    'tbl_request_withdraw_money.date as date',
                    'tbl_request_withdraw_money.total as total',
                    DB::raw("0 as 'revenue_customer'"),
                )
                    ->where(function ($query) use ($row) {
                        $query->where('check_balance', 0);
                        $query->where('customer_id', $row->id);
                    });

                $dtTransactionCancelGuest = Transaction::select(
                    'tbl_transaction.id',
                    'tbl_transaction.status',
                    DB::raw("'cancel_guest' as 'type'"),
                    'tbl_transaction.date_end as date',
                    'tbl_transaction.refund_money as total',
                    DB::raw("0 as 'revenue_customer'"),
                )
                    ->where(function ($query) use ($row) {
                        $query->where('check_balance', 0);
                        $query->where('status', Config::get('constant')['cancel_guest']);
                        $query->whereHas('car', function ($q) use ($row) {
                            $q->where('customer_id', $row->id);
                        });
                        $query->whereHas('paymentDeposit');
                    });

                $dtTransactionCancelOwen = Transaction::select(
                    'tbl_transaction.id',
                    'tbl_transaction.status',
                    DB::raw("'cancel_owen' as 'type'"),
                    'tbl_transaction.date_end as date',
                    'tbl_transaction.owner_refund_money as total',
                    DB::raw("0 as 'revenue_customer'"),
                )
                    ->where(function ($query) use ($row) {
                        $query->where('check_balance', 0);
                        $query->where('status', Config::get('constant')['cancel_owen']);
                        $query->whereHas('car', function ($q) use ($row) {
                            $q->where('customer_id', $row->id);
                        });
                        $query->whereHas('paymentDeposit');
                    });

                $dtTransaction = Transaction::select(
                    'tbl_transaction.id',
                    'tbl_transaction.status',
                    DB::raw("'finish' as 'type'"),
                    'tbl_transaction.date_end as date',
                    'tbl_transaction.grand_total as total',
                    'tbl_transaction.revenue_customer as revenue_customer',
                )
                    ->where(function ($query) use ($row) {
                        $query->where('check_balance', 0);
                        $query->where('status', Config::get('constant')['status_transaction_finish']);
                        $query->whereHas('car', function ($q) use ($row) {
                            $q->where('customer_id', $row->id);
                        });
                        $query->whereHas('paymentDeposit');
                    })
                    ->unionall($dtRequestMoney)
                    ->unionall($dtTransactionCancelGuest)
                    ->unionall($dtTransactionCancelOwen)
                    ->orderByRaw('date asc')
                    ->get();
                if (!empty($dtTransaction)) {
                    foreach ($dtTransaction as $key => $value) {
                        $revenue = 0;
                        $date = _dthuan($value->date);
                        $date = explode('/', $date);
                        $month = $date[1];
                        $year = $date[2];
                        if ($value->type == 'request_money') {
                            $deposit = 0;
                        } else {
                            $deposit = $value->paymentDeposit->sum('payment');
                        }
                        $revenue_customer = $value->revenue_customer;
                        $total = $value->total;
                        if ($value->type == 'finish') {
                            $revenue = $revenue_customer - ($total - $deposit);
                            $account_balance += $revenue;
                        } elseif ($value->type == 'cancel_guest') {
                            $revenue = $deposit - $total;
                            $account_balance += $deposit - $total;
                        } elseif ($value->type == 'cancel_owen') {
                            $revenue = -$total;
                            $account_balance -= $total;
                        } elseif ($value->type == 'request_money') {
                            $revenue = -$total;
                            $account_balance -= $total;
                        }
                        $keyMonth = $month . '__' . $year;
                        if (!empty($arrMonthYear[$keyMonth])) {
                            $arrMonthYear[$keyMonth]['balance'] += $revenue;
                        } else {
                            $arrMonthYear[$keyMonth]['month'] = $month;
                            $arrMonthYear[$keyMonth]['year'] = $year;
                            $arrMonthYear[$keyMonth]['customer_id'] = $row->id;
                            $arrMonthYear[$keyMonth]['balance'] = $revenue;
                            $arrMonthYear[$keyMonth]['created_at'] = date('Y-m-d h:i:s');
                        }
                        if ($value->type == 'request_money') {
                            $arrUpdateRequest[] = [
                                'id' => $value->id,
                                'check_balance' => 1
                            ];

                        } else {
                            $arrUpdate[] = [
                                'id' => $value->id,
                                'check_balance' => 1
                            ];
                        }
                    }
                }
                DB::beginTransaction();
                try {
                    if (!empty($arrMonthYear)) {
                        $arrMonthYear = array_values($arrMonthYear);
                        $account_balance_client = $row->account_balance + $account_balance;
                        $success = Clients::where('id', $row->id)->update([
                            'account_balance' => $account_balance_client
                        ]);
                        if ($success) {
                            $count++;
                        }
                        if (!empty($arrUpdate)) {
                            Transaction::batchUpdate($arrUpdate, 'id');
                        }
                        if (!empty($arrUpdateRequest)) {
                            RequestWithdrawMoney::batchUpdate($arrUpdateRequest, 'id');
                        }
                        foreach ($arrMonthYear as $key => $value) {
                            $clientBalance = DB::table('tbl_client_balance_month')
                                ->where('customer_id', $value['customer_id'])
                                ->where('month', $value['month'])
                                ->where('year', $value['year'])
                                ->first();
                            if (!empty($clientBalance)) {
                                $balance = $clientBalance->balance + $value['balance'];
                                DB::table('tbl_client_balance_month')->where('id', $clientBalance->id)
                                    ->update([
                                        'balance' => $value['balance']
                                    ]);
                            } else {
                                DB::table('tbl_client_balance_month')->insert($value);
                            }
                        }

                    }
                    DB::commit();
                } catch (\Exception $exception) {
                    $data['result'] = false;
                    $data['message'] = $exception;
                    DB::rollBack();
                    return response()->json($data);
                }
            }
        }
        echo $count;
    }

    public function cronCloseBalanceMonth()
    {
        $dtClient = Clients::where('active', 1)->where('id', '!=',
            Config::get('constant')['customer_kanow'])->where('type_client', 2)->get();
        $count = 0;
        if (!empty($dtClient)) {
            foreach ($dtClient as $row) {
                $getMonthYear = getMonthYear(date('m'), date('Y'));
                if (!empty($getMonthYear)) {
                    $clientBalanceOld = DB::table('tbl_client_balance_month')
                        ->where('customer_id', $row->id)
                        ->where('month', $getMonthYear['month'])
                        ->where('year', $getMonthYear['year'])
                        ->first();

                    $clientBalance = DB::table('tbl_client_balance_month')
                        ->where('customer_id', $row->id)
                        ->where('month', date('m'))
                        ->where('year', date('Y'))
                        ->first();
                    if (empty($clientBalance)) {
                        $balance = (!empty($clientBalanceOld) ? $clientBalanceOld->balance : 0);
                        DB::table('tbl_client_balance_month')->insert([
                            'month' => date('m'),
                            'year' => date('Y'),
                            'customer_id' => $row->id,
                            'balance' => $balance,
                            'created_at' => date('Y-m-d h:i:s')
                        ]);
                    }
                }
            }
        }
        echo $count;
    }


    public function getListBanks()
    {
        $opts = [
            'apiKey' => get_option('token_key'),
            'encryptKey' => get_option('encrypt_key'),
            'checksumKey' => get_option('checksum_key'),
            'callbackUrl' => asset('/api/alepay/resultAlepay')
        ];
        $this->alepay = new Alepay($opts);
        $dataPost['tokenKey'] = get_option('token_key');
        $result = $this->alepay->getListBanks($dataPost);
        $arrData = [];
        if ($result->code === "000") {
            if (!empty($result->data)) {
                $data = $result->data;
                foreach ($data as $key => $value) {
                    $methodCode = $value->methodCode;
                    $payment_mode_name = 'Chưa xác đnh';
                    if ($methodCode === 'ATM_ON') {
                        $payment_mode_name = 'Thẻ Nội Địa';
                    } elseif ($methodCode === 'VIETQR') {
                        $payment_mode_name = 'Ví Điện Tử';
                    } elseif ($methodCode === 'IB_ON') {
                        $payment_mode_name = 'Thanh ton bằng tài khoản IB';
                    } elseif ($methodCode == 'VA') {
                        $payment_mode_name = 'Qut Mã VietQR | Chuyn Khoản 24/7';
                    }
                    $bankFullName = $value->bankFullName;
                    $bankCode = $value->bankCode;
                    $tradeName = $value->tradeName;
                    $urlBankLogo = $value->urlBankLogo;
                    $arrBank = [
                        'name' => $bankFullName,
                        'code' => $bankCode,
                        'trand_name' => $tradeName,
                        'image' => $urlBankLogo,
                    ];
                    if (!empty($arrData[$methodCode])) {
                        $arrData[$methodCode]['child'][] = $arrBank;
                    } else {
                        $arrData[$methodCode]['code'] = $methodCode;
                        $arrData[$methodCode]['name'] = $payment_mode_name;
                        $arrData[$methodCode]['type'] = 2;
                        $arrData[$methodCode]['active'] = 1;
                        $arrData[$methodCode]['child'][] = $arrBank;
                    }
                }
            }
        }
        $arrData = array_values($arrData);
        if (!empty($arrData)) {
            foreach ($arrData as $key => $value) {
                $payment_mode = PaymentMode::where('code', $value['code'])->first();
                if (!empty($payment_mode)) {
                    $payment_mode = PaymentMode::find($payment_mode->id);
                } else {
                    $payment_mode = new PaymentMode();
                    $payment_mode->active = $value['active'];
                }
                $payment_mode->code = $value['code'];
                $payment_mode->name = $value['name'];
                $payment_mode->type = $value['type'];
                $payment_mode->save();
                if ($payment_mode) {
                    $banks = $value['child'];
                    $payment_mode_id = $payment_mode->id;
                    if (!empty($banks)) {
                        foreach ($banks as $kk => $vv) {
                            $checkBank = DB::table('tbl_payment_mode_bank')->where('code', $vv['code'])->first();
                            if (!empty($checkBank)) {
                                DB::table('tbl_payment_mode_bank')
                                    ->where('id', $checkBank->id)
                                    ->update([
                                        'name' => $vv['name'],
                                        'trand_name' => $vv['trand_name'],
                                        'image' => 'https://image-alepay.nganluong.vn' . $vv['image'],
                                        'payment_mode_id' => $payment_mode_id
                                    ]);
                            } else {
                                DB::table('tbl_payment_mode_bank')->insert([
                                    'name' => $vv['name'],
                                    'code' => $vv['code'],
                                    'trand_name' => $vv['trand_name'],
                                    'image' => 'https://image-alepay.nganluong.vn' . $vv['image'],
                                    'payment_mode_id' => $payment_mode_id
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }

    public function cancelTransactionNotDepoist()
    {
        $hour_wait_status = get_option('hour_wait_status');
        $hour_wait_status = $hour_wait_status * 60;
        $dtTransaction = Transaction::with('car')->where(function ($query) use ($hour_wait_status) {
            $query->where('status', Config::get('constant')['status_approve_request']);
            $query->where(DB::raw("DATE_ADD(date_status,  INTERVAL $hour_wait_status MINUTE)"), '<',
                DB::raw('"' . date('Y-m-d H:i') . '"'));
        })
            ->get();
        $refund_money = 0;
        $owner_refund_money = 0;
        $number_day_before_cancel = 0;
        $hourCancel = 0;
        $cancel_despoit = 1;
        $count = 0;
        if (!empty($dtTransaction)) {
            foreach ($dtTransaction as $key => $transaction) {
                $transaction->note_status = 'Quá thời gian đt cọc xe';
                $transaction->refund_money = $refund_money;
                $transaction->owner_refund_money = $owner_refund_money;
                $transaction->hour_cancel = $hourCancel;
                $transaction->number_day_before_cancel = $number_day_before_cancel;
                $transaction->status = Config::get('constant')['cancel_system'];
                $transaction->cancel_despoit = $cancel_despoit;
                $transaction->date_status = date('Y-m-d H:i:s');
                $transaction->staff_status = Config::get('constant')['user_admin'];
                $transaction->customer_status = 0;
                $transaction->save();
                if ($transaction) {
                    $count++;
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
                    $dtCustomerOwner = Clients::select(
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
                    if (!empty($dtCustomerOwner)) {
                        $arr_object_id = array_merge($arr_object_id, $dtCustomerOwner);
                    }
                    $arr_object_id = array_values($arr_object_id);
                    ConnectPusher($transaction, $arr_object_id);
                    Notification::notiCancelTransaction($transaction->id, Config::get('constant')['noti_cancel_system'],
                        0, 2, $arr_object_id);
                }
            }
        }
        echo $count;
    }

    public function cancelTransactionNotApprove()
    {
        $hour_wait_status = get_option('hour_wait_status');
        $hour_wait_status = $hour_wait_status * 60;
        $dtTransaction = Transaction::with('car')->where(function ($query) use ($hour_wait_status) {
            $query->where('status', Config::get('constant')['status_request']);
            $query->where(DB::raw("DATE_ADD(created_at,  INTERVAL $hour_wait_status MINUTE)"), '<',
                DB::raw('"' . date('Y-m-d H:i') . '"'));
        })
            ->get();
        $refund_money = 0;
        $owner_refund_money = 0;
        $number_day_before_cancel = 0;
        $hourCancel = 0;
        $cancel_despoit = 0;
        $cancel_note_approve = 1;
        $count = 0;
        if (!empty($dtTransaction)) {
            foreach ($dtTransaction as $key => $transaction) {
                $transaction->note_status = 'Quá thời gian duyệt xe';
                $transaction->refund_money = $refund_money;
                $transaction->owner_refund_money = $owner_refund_money;
                $transaction->hour_cancel = $hourCancel;
                $transaction->number_day_before_cancel = $number_day_before_cancel;
                $transaction->status = Config::get('constant')['cancel_system'];
                $transaction->cancel_despoit = $cancel_despoit;
                $transaction->cancel_note_approve = $cancel_note_approve;
                $transaction->date_status = date('Y-m-d H:i:s');
                $transaction->staff_status = Config::get('constant')['user_admin'];
                $transaction->customer_status = 0;
                $transaction->save();
                if ($transaction) {
                    $count++;
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
                    $dtCustomerOwner = Clients::select(
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
                    if (!empty($dtCustomerOwner)) {
                        $arr_object_id = array_merge($arr_object_id, $dtCustomerOwner);
                    }
                    $arr_object_id = array_values($arr_object_id);
                    ConnectPusher($transaction, $arr_object_id);
                    Notification::notiCancelTransaction($transaction->id, Config::get('constant')['noti_cancel_system'],
                        0, 2, $arr_object_id);
                }
            }
        }
        echo $count;
    }

    public function cancelTransactionDriverNotDriver()
    {
        $hour_wait_status = get_option('hour_wait_status');
        $hour_wait_status = $hour_wait_status * 60;
        $dtTransactionDriver = TransactionDriver::where(function ($query) use ($hour_wait_status) {
            $query->where('type',2);
            $query->where('status', Config::get('constant')['status_request_driver']);
            $query->where(DB::raw("(date_start - INTERVAL $hour_wait_status MINUTE)"), '<',
                DB::raw('"' . date('Y-m-d H:i') . '"'));
        })->get();
        $count = 0;
        if (!empty($dtTransactionDriver)) {
            foreach ($dtTransactionDriver as $key => $transaction) {
                $refund_money = 0;
                $owner_refund_money = 0;
                $cancel_trip_id = 2;
                $dtCancelTrip = DB::table('tbl_cancel_trip_driver')->where('id', $cancel_trip_id)->first();
                if (!empty($dtCancelTrip)) {
                    $percent_owen_cancel = $dtCancelTrip->percent_owen_cancel;
                    $owner_refund_money = $transaction->payment->payment * $percent_owen_cancel / 100;

                    $percent_guest_cancel = $dtCancelTrip->percent_guest_cancel;
                    $refund_money = $transaction->payment->payment * ($percent_guest_cancel + $percent_owen_cancel) / 100;
                }
                $transaction->refund_money = $refund_money;
                $transaction->owner_refund_money = $owner_refund_money;
                $transaction->note_status = 'Quá thi gian tìm tài xế';
                $transaction->status = Config::get('constant')['status_system_cancel_driver'];
                $transaction->date_status = date('Y-m-d H:i:s');
                $transaction->staff_status = Config::get('constant')['user_admin'];
                $transaction->customer_status = 0;
                $transaction->save();
                if ($transaction) {
                    $count++;
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
                    $arr_object_id = array_values($arr_object_id);
                    ConnectPusher($transaction, $arr_object_id);
                    Notification::notiCancelTransactionDriverProvince($transaction->id, Config::get('constant')['noti_system_cancel_driver'],
                        0, 2, $arr_object_id);
                    $dtPayment = !empty($transaction->payment) ? $transaction->payment : [];
                    if (!empty($dtPayment)){
                        if ($dtPayment->payment_mode->type == 1){
                            addPaySlip($transaction->id);
                        } else {
                            $dataRefund = [];
                            $dataRefund['tokenKey'] = get_option('token_key');
                            $dataRefund['transactionCode'] = $dtPayment->note;
                            $dataRefund['merchantRefundCode'] = $transaction->reference_no;
                            $dataRefund['refundAmount'] = $dtPayment->payment;
                            $dataRefund['reason'] = 'Hoàn tin giao dịch '.$transaction->reference_no;
                            $dataRefund['transaction_id'] = $transaction->id;
                            getRefundTransaction($dataRefund);
                        }
                    }
                }
            }
        }
        echo $count;
    }

    public function addGroupPermistionByPermission()
    {
        $dtGroupPermission = DB::table('tbl_group_permissions')->whereNotIn('id', [1, 2, 4, 5, 8])->get();
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

    public function sendSmsTransaction()
    {
        $transaction = Transaction::where(function ($query) {
            $query->whereIn('status',
                [Config::get('constant')['status_approve_request'], Config::get('constant')['status_request']]);
            $query->where('check_sms', 0);
        })->get();
        if (!empty($transaction)) {
            foreach ($transaction as $key => $value) {
                $type = $value->type;
                $dtCar = Car::find($value->car_id);
                $dtCustomer = Clients::find($value->customer_id);
                $date_start = $value->date_start;
                $date_end = $value->date_end;
                $grand_total = $value->grand_total;
                $book_car_flash = $dtCar->book_car_flash;
                $phone_customer = $dtCustomer->phone;
                $phone_owner = $dtCar->customer->phone;
                if ($type == 2) {
                    $car_html = $dtCar->name . ', ' . _dt_new($date_start) . ' - ' . _dt_new($date_end);

                    $content_book_car = get_option('sms_book_car_co_tai');
                    $content_book_car = str_replace('{car}', $car_html, $content_book_car);
                    $content_book_car = str_replace('{price}', formatMoney($grand_total) . 'd', $content_book_car);
                    $content_book_car = str_replace('{customer}', $dtCustomer->fullname, $content_book_car);
                    $content_book_car = str_replace('{price_deposit}', '', $content_book_car);
                    send_zalo($value->id,'send_request','379483');
                    send_sms($phone_customer, $content_book_car, 'book_car_co_tai');
                    send_sms($phone_owner, $content_book_car, 'book_car_co_tai');

                    if ($book_car_flash == 1) {
                        $content_approve_car = get_option('sms_approve_car_co_tai');
                        $content_approve_car = str_replace('{car}', $car_html, $content_approve_car);
                        $content_approve_car = str_replace('{price}', formatMoney($grand_total) . 'd',
                            $content_approve_car);
                        $content_approve_car = str_replace('{customer}', $dtCustomer->fullname, $content_approve_car);
                        send_zalo($value->id,'approve_transaction','379472');
                        send_zalo($value->id,'deposit_transaction','379492');
                        send_sms($phone_customer, $content_approve_car, 'approve_car_co_tai');
                        send_sms($phone_owner, $content_approve_car, 'approve_car_co_tai');
                    }
                } else {
                    $car_html = $dtCar->name . ', ' . _dt_new($date_start) . ' - ' . _dt_new($date_end);

                    $content_book_car = get_option('sms_book_car_tu_lai');
                    $content_book_car = str_replace('{car}', $car_html, $content_book_car);
                    $content_book_car = str_replace('{price}', formatMoney($grand_total) . 'd', $content_book_car);
                    $content_book_car = str_replace('{customer}', $dtCustomer->fullname, $content_book_car);
                    $content_book_car = str_replace('{price_deposit}', '', $content_book_car);
                    send_zalo($value->id,'send_request','379483');
                    send_sms($phone_customer, $content_book_car, 'book_car_tu_lai');
                    send_sms($phone_owner, $content_book_car, 'book_car_tu_lai');

                    if ($book_car_flash == 1) {
                        $content_approve_car = get_option('sms_approve_car_tu_lai');
                        $content_approve_car = str_replace('{car}', $car_html, $content_approve_car);
                        $content_approve_car = str_replace('{price}', formatMoney($grand_total) . 'd',
                            $content_approve_car);
                        $content_approve_car = str_replace('{customer}', $dtCustomer->fullname, $content_approve_car);
                        send_zalo($value->id,'approve_transaction','379472');
                        send_zalo($value->id,'deposit_transaction','379492');
                        send_sms($phone_customer, $content_approve_car, 'approve_car_tu_lai');
                        send_sms($phone_owner, $content_approve_car, 'approve_car_tu_lai');
                    }
                }
                $value->check_sms = 1;
                $value->save();
            }
        }
    }

    public function addAutoReview()
    {
        $day_review = get_option('day_review');
        $transaction = Transaction::select('id', 'date_status', 'status', 'reference_no', 'car_id',
            'customer_id')->with('car')->where(function ($query) use ($day_review) {
            $query->where('status', Config::get('constant')['status_transaction_finish']);
            $query->where(DB::raw('CURDATE()'), '>',
                DB::raw('DATE_ADD(DATE_FORMAT(tbl_transaction.date_status,"%Y-%m-%d"), INTERVAL ' . $day_review . ' DAY)'));
            $query->doesntHave('review_customer');
        })->orderByRaw('id desc')->get();
        if (!empty($transaction)) {
            foreach ($transaction as $key => $value) {
                $review = new ReviewCustomer();
                $review->content = null;
                $review->star = 5;
                $review->customer_owner_id = $value->car->customer_id;
                $review->transaction_id = $value->id;
                $review->customer_id = $value->customer_id;
                $review->save();
            }
        }

        $transactionReview = Transaction::select('id', 'date_status', 'status', 'reference_no', 'car_id', 'type',
            'customer_id')->where(function ($query) use ($day_review) {
            $query->where('status', Config::get('constant')['status_transaction_finish']);
            $query->where(DB::raw('CURDATE()'), '>',
                DB::raw('DATE_ADD(DATE_FORMAT(tbl_transaction.date_status,"%Y-%m-%d"), INTERVAL ' . $day_review . ' DAY)'));
            $query->doesntHave('review');
        })->orderByRaw('id desc')->get();
        if (!empty($transactionReview)) {
            foreach ($transactionReview as $key => $value) {
                $review = new ReviewCar();
                $review->content = null;
                $review->star = 5;
                $review->customer_id = $value->customer_id;
                $review->transaction_id = $value->id;
                $review->car_id = $value->car_id;
                $review->type = $value->type;
                $review->save();
            }
        }
    }

    public function remindFinishOwner()
    {
        $date_now = date('Y-m-d H:i:s');
        $transaction = Transaction::select('id', 'date_status', 'status', 'reference_no', 'car_id',
            'customer_id')->with('car')->where(function ($query) use ($date_now) {
            $query->where('status', Config::get('constant')['status_start']);
            $query->where('date_end', '<', $date_now);
            $query->where('noti_remind_finish', 0);
        })->orderByRaw('id desc')->get();
        if (!empty($transaction)) {
            foreach ($transaction as $key => $value) {
                $arr_object_id = [];
                //chu xe
                $dtCustomerOwner = Clients::select(
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
                if (!empty($dtCustomerOwner)) {
                    $arr_object_id = array_merge($arr_object_id, $dtCustomerOwner);
                }
                $arr_object_id = array_values($arr_object_id);
                Notification::notiRemindFinishTransaction($value->id, Config::get('constant')['noti_remind_finish'],
                    0, 2, $arr_object_id);
                $value->noti_remind_finish = 1;
                $value->save();
            }
        }
    }

    public function autoFinishTransaction()
    {
        $date_now = date('Y-m-d H:i:s');
        $hour_finish = get_option('hour_finish');
        $transaction = Transaction::select('id', 'date_status', 'status', 'reference_no', 'car_id',
            'customer_id')->with('car')->where(function ($query) use ($date_now, $hour_finish) {
            $query->where('status', Config::get('constant')['status_start']);
            $query->where('date_end', '<', $date_now);
            $query->where(DB::raw('NOW()'), '>',
                DB::raw('DATE_ADD(tbl_transaction.date_end, INTERVAL ' . $hour_finish . ' HOUR)'));
        })->orderByRaw('id desc')->get();
        if (!empty($transaction)) {
            foreach ($transaction as $key => $value) {
                $value->status = Config::get('constant')['status_transaction_finish'];
                $value->date_status = date('Y-m-d H:i:s');
                $value->staff_status = Config::get('constant')['user_admin'];
                $value->customer_status = 0;
                $value->save();
                changeBalance($value->id, 'finish');
                Notification::notiFinishTransaction($value->id, Config::get('constant')['noti_finish'], 0, 3);
            }
        }
    }

    public function getListBankNew()
    {
        $dataList = GetCurlData('https://api.vietqr.io/v2/banks');
        $dataList = json_decode($dataList);
        $count = 0;
        if (!empty($dataList)) {
            if (!empty($dataList->data)) {
                foreach ($dataList->data as $key => $value) {
                    $value = (array)$value;
                    $listBank = ListBank::where('code', $value['code'])->first();
                    if (!empty($listBank)) {
                        $list_bank = ListBank::find($listBank->id);
                    } else {
                        $list_bank = new ListBank();
                    }
                    $list_bank->code = $value['code'];
                    $list_bank->name = $value['name'];
                    $list_bank->shortName = $value['shortName'];
                    $list_bank->save();
                    if ($list_bank) {

                        $folder = storage_path('app/public/list_banks');
                        $subFolder = 'list_banks/' . $value['code'] . '.png';
                        if (!File::exists($folder)) {
                            File::makeDirectory($folder);
                        }
                        $url = $value['logo'];
                        $imgpath = $folder . '/' . $value['code'] . '.png';
                        file_put_contents($imgpath, file_get_contents($url));
                        $list_bank->logo = $subFolder;
                        $list_bank->save();
                        $count++;
                    }
                }
            }
        }
        echo $count;
    }

    public function getCancelSystemTransactionDriver(){
        $hour_wait_status = get_option('time_cancel_driver');
        $hour_wait_status_province = get_option('time_cancel_driver_province');
        $dtTransactionDriver = TransactionDriver::where(function ($query) use ($hour_wait_status,$hour_wait_status_province) {
            $query->where('status', Config::get('constant')['status_request_driver']);
            $query->doesntHave('payment');
            $query->where(DB::raw("DATE_ADD(date,  INTERVAL IF(type = 1,$hour_wait_status,$hour_wait_status_province) MINUTE)"), '<',
                DB::raw('"' . date('Y-m-d H:i') . '"'));
        })->get();
        $count = 0;
        if (!empty($dtTransactionDriver)) {
            foreach ($dtTransactionDriver as $key => $transaction) {
                $transaction->note_status = 'Quá thời gian thanh ton, hệ thống hủy!';
                $transaction->status = Config::get('constant')['status_system_cancel_driver'];
                $transaction->date_status = date('Y-m-d H:i:s');
                $transaction->staff_status = Config::get('constant')['user_admin'];
                $transaction->customer_status = 0;
                $transaction->payment_expires = 1;
                $transaction->save();
                if ($transaction) {
                    $count++;
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
                    $arr_object_id = array_values($arr_object_id);
                    ConnectPusher($transaction, $arr_object_id);
                    Notification::notiCancelTransactionDriverProvince($transaction->id, Config::get('constant')['noti_system_cancel_driver'],
                        0, 2, $arr_object_id,1);
                }
            }
        }
        echo $count;
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

    public function noti_remind_transaction_driver_province()
    {
        $hour_remind = get_option('hour_remind_driver_province');
        $date_now = date('Y-m-d H:i');
        $new_date = strtotime('+' . $hour_remind . ' hour', strtotime($date_now));
        $new_date = date('Y-m-d H:i', $new_date);
        $transaction = TransactionDriver::where(function ($query) use ($new_date) {
            $query->where('status', Config::get('constant')['status_approve_driver']);
            $query->where('noti', 0);
            $query->where('date_start', '<=', DB::raw('"' . $new_date . '"'));
            $query->where('date_start', '>', DB::raw('"' . date('Y-m-d H:i') . '"'));
        })
            ->get();
        $dataNoti = [];
        $dataNotiDriver = [];
        if (!empty($transaction)) {
            foreach ($transaction as $key => $value) {
                $arr_object_id = [];
                $arr_object_id_driver = [];
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
                    ->where('tbl_driver.id', $value->driver->id)
                    ->get()->toArray();
                if (!empty($dtDriver)) {
                    $arr_object_id_driver = array_merge($arr_object_id_driver, $dtDriver);
                }

                $arr_object_id = array_values($arr_object_id);
                $arr_object_id_driver = array_values($arr_object_id_driver);
                $playerId = [];
                if (!empty($arr_object_id)) {
                    $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                    $content = '';
                    $json_data = json_encode([
                        'transaction_id' => $value->id,
                        'type' => $value->type,
                        'object' => 'transaction_driver',
                        'driver' => false
                    ], JSON_UNESCAPED_UNICODE);
                    $title = '';
                    $title_owen = '';
                    $content = "Chuyn đi tỉnh " . $value->reference_no . ', ngời đt tài x ' . $value->customer->fullname . '. Tài xế '.$value->driver->fullname.', ' . _dt_new($value->date_start) . ' - ' . _dt_new($value->date_end) . ' sp bắt đầu';
                    $title = 'Chuyến đi tnh sắp ti';
                    $title_owen = 'Chuyn đi tnh sắp tới';
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
                if (!empty($arr_object_id_driver)){
                    $playerId = array_unique(array_column($arr_object_id_driver, 'player_id'));
                    $content = '';
                    $json_data = json_encode([
                        'transaction_id' => $value->id,
                        'type' => $value->type,
                        'object' => 'transaction_driver',
                        'driver' => true
                    ], JSON_UNESCAPED_UNICODE);
                    $title = '';
                    $title_owen = '';
                    $content = "Chuyến đi tỉnh " . $value->reference_no . ', người đặt tài xế ' . $value->customer->fullname . '. Tài xế '.$value->driver->fullname.', ' . _dt_new($value->date_start) . ' - ' . _dt_new($value->date_end) . ' sắp bắt ầu';
                    $title = 'Chuyn đi tnh sắp tới';
                    $title_owen = 'Chuyến đi tỉnh sp tới';
                    $data = [
                        'arr_object_id' => $arr_object_id_driver,
                        'player_id' => $playerId,
                        'json_data' => $json_data,
                        'object_id' => $value['id'],
                        'content' => $content,
                        'created_by' => 0,
                        'title' => $title,
                        'title_owen' => $title_owen,
                        'type' => $value->type,
                    ];
                    $dataNotiDriver[] = $data;
                }
            }
        }
        $this->sendNotiOnesignalMutile($dataNoti, Config::get('constant')['noti_remind_province']);
        $this->sendNotiOnesignalMutileDriver($dataNotiDriver, Config::get('constant')['noti_remind_province']);
        dd($dataNoti);
    }

    public function noti_remind_use_point_client()
    {
        $month_reset_point = get_option('month_reset_point');
        $day_remind_point = get_option('day_remind_point');
        $date = date('Y-m-d');
        $hour_run = "06:00";
        $hour_run_end = date('H:i',strtotime("+1 hour",strtotime($hour_run)));
        $dtClient = [];
        if (strtotime(date('H:i')) >= strtotime($hour_run) && strtotime(date('H:i')) <= strtotime($hour_run_end)) {
            $dtClient = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_clients.point as point',
                'tbl_clients.date_point as date_point',
                DB::raw('IF(type_client = 1,"customer","owen") as object_type')
            )->selectRaw("DATEDIFF(DATE_ADD(date_point, INTERVAL ? MONTH), ?) AS days_difference",
                [$month_reset_point, date('Y-m-d')])->where(function ($query) use (
                $month_reset_point,
                $day_remind_point,
                $date
            ) {
                $query->whereNotNull('date_point');
                $query->where(function ($q) use ($month_reset_point, $day_remind_point) {
                    $q->whereRaw("DATEDIFF(DATE_ADD(date_point, INTERVAL ? MONTH), ?) > ?",
                        [$month_reset_point, date('Y-m-d'), 0]);
                    $q->whereRaw("DATEDIFF(DATE_ADD(date_point, INTERVAL ? MONTH), ?) <= ?",
                        [$month_reset_point, date('Y-m-d'), $day_remind_point]);
                });
                $query->whereNotExists(function ($query) use ($date) {
                    $query->select(DB::raw(1))
                        ->from('tbl_notification_staff')
                        ->join('tbl_notification', 'tbl_notification.id', '=', 'tbl_notification_staff.notification_id')
                        ->whereColumn('tbl_notification_staff.object_id', 'tbl_clients.id')
                        ->where('tbl_notification.object_type', '402')
                        ->where(DB::raw('DATE_FORMAT(tbl_notification.created_at, "%Y-%m-%d")'), '=', $date);
                });
            })->limit(100)->get()->toArray();
        }
        $dataNoti = [];
        if (!empty($dtClient)) {
            foreach ($dtClient as $key => $value) {
                $arr_object_id = [];
                if ($value)
                $dtPlayer = DB::table('tbl_player_id')
                    ->where('tbl_player_id.object_id',$value['object_id'])
                    ->where('tbl_player_id.object_type','=',DB::raw("'customer'"))
                    ->get()->toArray();
                $arr_object_id []= $value;
                $playerId = [];
                if (!empty($arr_object_id)) {
                    $playerId = array_unique(array_column($dtPlayer, 'player_id'));
                    $content = '';
                    $json_data = json_encode([
                        'customer_id' => $value['object_id'],
                        'object' => 'client',
                    ], JSON_UNESCAPED_UNICODE);
                    $title = '';
                    $title_owen = '';
                    $content = "S điểm " . formatMoney($value['point']) . ' đim của bn sẽ ht hạn trong ' . $value['days_difference'] . ' ngày na . Vui lòng s dụng điểm trưc khi hết hn.';
                    $title = 'Nhắc nh sử dng đim';
                    $title_owen = 'Nhắc nh sử dụng điểm';
                    $data = [
                        'arr_object_id' => $arr_object_id,
                        'player_id' => $playerId,
                        'json_data' => $json_data,
                        'object_id' => $value['object_id'],
                        'content' => $content,
                        'created_by' => 0,
                        'title' => $title,
                        'title_owen' => $title_owen,
                        'type' => 0,
                    ];
                    $dataNoti[] = $data;
                }
            }
        }
        $this->sendNotiOnesignalMutile($dataNoti, Config::get('constant')['noti_remind_use_point']);
        dd($dataNoti);
    }

    public function noti_reset_use_point_client()
    {
        $month_reset_point = get_option('month_reset_point');
        $day_remind_point = get_option('day_remind_point');
        $date = date('Y-m-d');
        $hour_run = "06:00";
        $hour_run_end = date('H:i',strtotime("+1 hour",strtotime($hour_run)));
        $dtClient = [];
        if (strtotime(date('H:i')) >= strtotime($hour_run) && strtotime(date('H:i')) <= strtotime($hour_run_end)) {
            $dtClient = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_clients.point as point',
                'tbl_clients.date_point as date_point',
                DB::raw('IF(type_client = 1,"customer","owen") as object_type')
            )->selectRaw("DATEDIFF(DATE_ADD(date_point, INTERVAL ? MONTH), ?) AS days_difference",
                [$month_reset_point, date('Y-m-d')])->where(function ($query) use (
                $month_reset_point,
                $day_remind_point,
                $date
            ) {
                $query->whereNotNull('date_point');
                $query->where(function ($q) use ($month_reset_point, $day_remind_point) {
                    $q->whereRaw("DATEDIFF(DATE_ADD(date_point, INTERVAL ? MONTH), ?) <= ?",
                        [$month_reset_point, date('Y-m-d'), 0]);
                    $q->whereRaw("DATEDIFF(DATE_ADD(date_point, INTERVAL ? MONTH), ?) >= ?",
                        [$month_reset_point, date('Y-m-d'), -1]);
                });
                $query->whereNotExists(function ($query) use ($date) {
                    $query->select(DB::raw(1))
                        ->from('tbl_notification_staff')
                        ->join('tbl_notification', 'tbl_notification.id', '=', 'tbl_notification_staff.notification_id')
                        ->whereColumn('tbl_notification_staff.object_id', 'tbl_clients.id')
                        ->where('tbl_notification.object_type', '403')
                        ->where(DB::raw('DATE_FORMAT(tbl_notification.created_at, "%Y-%m-%d")'), '=', $date);
                });
            })->limit(100)->get()->toArray();
        }
        $dataNoti = [];
        if (!empty($dtClient)) {
            foreach ($dtClient as $key => $value) {
                $arr_object_id = [];
                if ($value)
                    $dtPlayer = DB::table('tbl_player_id')
                        ->where('tbl_player_id.object_id',$value['object_id'])
                        ->where('tbl_player_id.object_type','=',DB::raw("'customer'"))
                        ->get()->toArray();
                $arr_object_id []= $value;
                $playerId = [];
                if (!empty($arr_object_id)) {
                    $playerId = array_unique(array_column($dtPlayer, 'player_id'));
                    $content = '';
                    $json_data = json_encode([
                        'customer_id' => $value['object_id'],
                        'object' => 'client',
                    ], JSON_UNESCAPED_UNICODE);
                    $title = '';
                    $title_owen = '';
                    $content = "S đim " . formatMoney($value['point']) . ' điểm ca bn đã hết hạn. Số đim hiện ti 0 điểm';
                    $title = 'Hết hn sử dng điểm';
                    $title_owen = 'Ht hạn sử dụng điểm';
                    $data = [
                        'arr_object_id' => $arr_object_id,
                        'player_id' => $playerId,
                        'json_data' => $json_data,
                        'object_id' => $value['object_id'],
                        'content' => $content,
                        'created_by' => 0,
                        'title' => $title,
                        'title_owen' => $title_owen,
                        'type' => 0,
                    ];
                    $dataNoti[] = $data;
                }
            }
        }
        $this->sendNotiOnesignalMutile($dataNoti, Config::get('constant')['noti_reset_use_point']);
        dd($dataNoti);
    }

    public function moveFileToS3(){
        $date = date('Y-m-d');
        $hour_run = "15:00";
        $hour_run_end = date('H:i',strtotime("+1 hour",strtotime($hour_run)));
        $limit = 1;
        $count = 0;
        if (strtotime(date('H:i')) >= strtotime($hour_run) && strtotime(date('H:i')) <= strtotime($hour_run_end)) {
            $dtImageCar = ImageCar::where(function ($query){
                    $query->where('type_s3',0);
                })
                ->limit($limit)->get();
            if (!empty($dtImageCar)){
                foreach ($dtImageCar as $key => $value){
                    $imageUrl = asset('storage/' . $value->name);
                    if (file_exists('storage/'.$value->name)) {
                        $imageContents = Http::withoutVerifying()->get($imageUrl)->body();
                        $fileName = basename($imageUrl);
                        $filePath = parse_url($imageUrl, PHP_URL_PATH);
                        $filePath = ltrim($filePath, '/');
                        $pathNew = str_replace('storage/', '', $filePath);
                        $folder = explode('/', $pathNew);
                        $folder = implode('/', array_slice($folder, 0, 2));
                        $folder .= '/';
                        try {
                            $path = Storage::disk('s3')->put($folder . $fileName, $imageContents, 'public');
                            $url = Storage::disk('s3')->url($folder . $fileName);
                            $value->type_s3 = 1;
                            $value->image_s3 = $url;
                            $value->save();
                            $count++;
                        } catch (\Exception $exception) {
                        }

                    }
                }
            }
        }
        echo $count;

    }

    public function sendNotiTransaction()
    {
        $transaction = Transaction::where(function ($query) {
            $query->whereIn('status',
                [
                    Config::get('constant')['status_request'],
                    Config::get('constant')['status_despoit'],
                ]
            );
            $query->where('check_noti', 0);
        })->get();
        if (!empty($transaction)) {
            foreach ($transaction as $key => $value) {
                Notification::notiParentTransaction($value->id,Config::get('constant')['noti_child_add_transaction'],$value->customer_id);
                Notification::notiAddTransaction($value,Config::get('constant')['noti_add_transaction'],$value->customer_id);
                $value->check_noti = 1;
                $value->save();
            }
        }
    }

    public function cronCustomerRewardDay(){
        $timeEnd = "23:59";
        $dateNow = date('Y-m-d');
        $dateOldCheck = Carbon::parse($dateNow);
        $dateOldCheck = $dateOldCheck->subDay();
        $dateOld = $dateOldCheck->toDateString();
        $dtData = CustomerClass::with('transaction')
            ->whereHas('customer',function ($query){
                $query->where('active',1);
                $query->where('active_reward',1);
            })
            ->select('*')
            ->selectRaw('IF(DATE_FORMAT(created_at, "%Y-%m-%d") = DATE_FORMAT(NOW(), "%Y-%m-%d"),
                COALESCE((
                    SELECT SUM(tbl_customer_reward_profit_day.balance)
                    FROM tbl_customer_reward_profit_day
                    WHERE date = ? AND type = 1
                    AND customer_id = tbl_customer_class.customer_id
                    AND tbl_customer_reward_profit_day.created_at >= tbl_customer_class.created_at
                ), 0) - balance_day_use,
                COALESCE((
                    SELECT SUM(tbl_customer_reward_profit_day.balance)
                    FROM tbl_customer_reward_profit_day
                    WHERE date = ? AND type = 1
                    AND customer_id = tbl_customer_class.customer_id
                    AND tbl_customer_reward_profit_day.created_at >= tbl_customer_class.created_at
                ), 0)) AS balance_day', [$dateNow, $dateNow])
            ->selectRaw('COALESCE((SELECT SUM(tbl_customer_reward_profit_day.balance)
                            FROM tbl_customer_reward_profit_day
                            WHERE type = 1 AND customer_id = tbl_customer_class.customer_id
                            AND tbl_customer_reward_profit_day.created_at >= tbl_customer_class.created_at
                            ), 0) - balance_day_use as balance_month')
            ->where(function ($query) use ($dateNow){
                $query->whereRaw('grand_total_day >
                IF(DATE_FORMAT(created_at, "%Y-%m-%d") = DATE_FORMAT(NOW(), "%Y-%m-%d"),
                    COALESCE((
                        SELECT SUM(tbl_customer_reward_profit_day.balance)
                        FROM tbl_customer_reward_profit_day
                        WHERE date = ? AND type = 1
                        AND customer_id = tbl_customer_class.customer_id
                        AND tbl_customer_reward_profit_day.created_at >= tbl_customer_class.created_at
                    ), 0) - balance_day_use,
                    COALESCE((
                        SELECT SUM(tbl_customer_reward_profit_day.balance)
                        FROM tbl_customer_reward_profit_day
                        WHERE date = ? AND type = 1
                        AND customer_id = tbl_customer_class.customer_id
                        AND tbl_customer_reward_profit_day.created_at >= tbl_customer_class.created_at
                    ), 0))
            ', [$dateNow,$dateNow]);
                $query->whereRaw('grand_total > (COALESCE((SELECT SUM(tbl_customer_reward_profit_day.balance) FROM tbl_customer_reward_profit_day WHERE type = 1 AND customer_id = tbl_customer_class.customer_id AND tbl_customer_reward_profit_day.created_at >= tbl_customer_class.created_at), 0) - balance_day_use)');
            })->get();
        $arr_object_id = [];
        if (!empty($dtData)){
            foreach ($dtData as $key => $value){
                $customer_id = $value->customer_id;
                $rewardDayProfitDateStart = !empty($value->customer->reward_day_profit) ? $value->customer->reward_day_profit->date_start : null;
                $grand_total_day = $value->grand_total_day;
                // update balance ngày hôm trước nếu chưa cộng đủ tiền
                if (strtotime($dateOld) >= strtotime(to_sql_date(_dthuan($value->created_at)))) {
                    //trừ số tiền khi nâng cấp hạng
                    $balance_day_use = 0;
                    if ($dateOld == to_sql_date(_dthuan($value->created_at))){
                        $balance_day_use = $value->balance_day_use;
                    }
                    //end
                    $dtProfitDayNew = DB::table('tbl_customer_reward_profit_day')
                        ->where('customer_id',$customer_id)
                        ->where('type',1)
                        ->where('date',$dateNow)
                        ->first();
                    if(empty($dtProfitDayNew)){
                        DB::table('tbl_customer_reward_profit_day')->insert([
                            'customer_id' => $customer_id,
                            'date' => $dateNow,
                            'balance' => 0,
                            'type' => 1,
                            'date_start' => date('Y-m-d H:i:s'),
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                        $rewardDayProfitDateStart = date('Y-m-d H:i:s');
                    }
                    $dtCustomerRewardDay = CustomerRewardDay::where('customer_id', $customer_id)->where('date',
                        $dateOld)->where('type',1)->first();
                    if (!empty($dtCustomerRewardDay)) {
                        $balanceCustomerRewardDay = $grand_total_day + $balance_day_use - $dtCustomerRewardDay->balance;
                        if ($balanceCustomerRewardDay > 0) {
                            $balance_day_profit_old = $dtCustomerRewardDay->balance + $balanceCustomerRewardDay;
                            DB::table('tbl_customer_reward_profit_day')->where('id', $dtCustomerRewardDay->id)
                                ->update([
                                    'balance' => $balance_day_profit_old,
                                ]);
                            $dtProfitOld = DB::table('tbl_customer_reward_day')
                                ->where('customer_id', $customer_id)
                                ->where('date', $dateOld)
                                ->first();
                            if (!empty($dtProfitOld)) {
                                $balance_day_old = $dtProfitOld->balance + $balanceCustomerRewardDay;
                                DB::table('tbl_customer_reward_day')->where('id', $dtProfitOld->id)
                                    ->update([
                                        'balance' => $balance_day_old
                                    ]);
                            }
                            changeBalance($value->id, 'reward_profit', true, '', 0, $balanceCustomerRewardDay);
                        }
                    }
                }
                //end
                if (empty($rewardDayProfitDateStart)) {
                    $dateTransactionCheck = $value->date_start;
                    $dateTransaction = explode(' ', $dateTransactionCheck);
                } else {
                    $dateTransactionCheck = $rewardDayProfitDateStart;
                    $dateTransaction = explode(' ', $dateTransactionCheck);
                }
                $timeTransaction = $dateTransaction[1];
                $timeTransaction = new \DateTime($timeTransaction);
                $timeTransaction = $timeTransaction->format('H:i');
                if ($dateTransaction[0] == $dateNow){
                    $start = new \DateTime($timeTransaction);
                } else {
                    $start = new \DateTime("00:00");
                }
                $end = new \DateTime($timeEnd);
                $interval = $start->diff($end);
                $totalMinutes = ($interval->h * 60) + $interval->i;
                $balanceMinute =  ($grand_total_day - $value->balance_day) / $totalMinutes;
                $dateStart = $dateTransactionCheck;
                $dateStartNew = strtotime ( '+1 minute' , strtotime ( $dateStart ) ) ;
                $dateStartNew = date ( 'Y-m-d H:i:s' , $dateStartNew );
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

                $dtProfitDay = DB::table('tbl_customer_reward_profit_day')
                    ->where('customer_id',$customer_id)
                    ->where('type',1)
                    ->where('date',$dateNow)
                    ->first();
                if(!empty($dtProfitDay)){
                    $balance_day_profit = $dtProfitDay->balance  + $balanceMinute;
                    if ($balance_day_profit >= $grand_total_day){
                        continue;
                    }
                    if (strtotime($value->created_at) > strtotime($dtProfitDay->created_at)){
                        //thay đổi gói mới
                        DB::table('tbl_customer_reward_profit_day')->where('id', $dtProfitDay->id)
                            ->update([
                                'balance' => $balance_day_profit,
                                'date_start' => $value->created_at,
                                'created_at' => $value->created_at,
                            ]);
                    } else {
                        DB::table('tbl_customer_reward_profit_day')->where('id', $dtProfitDay->id)
                            ->update([
                                'balance' => $balance_day_profit,
                                'date_start' => $dateStartNew
                            ]);
                    }
                    DB::table('tbl_customer_class')->where('id',$value->id)->update([
                        'balance_day_remaining' => $balance_day_profit
                    ]);
                } else {
                    DB::table('tbl_customer_reward_profit_day')->insert([
                        'customer_id' => $customer_id,
                        'date' => $dateNow,
                        'balance' => $balanceMinute,
                        'type' => 1,
                        'date_start' => $dateStartNew,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                $dtProfit = DB::table('tbl_customer_reward_day')
                    ->where('customer_id',$customer_id)
                    ->where('date',$dateNow)
                    ->first();
                if(!empty($dtProfit)){
                    $balance_day = $dtProfit->balance  + $balanceMinute;
                    DB::table('tbl_customer_reward_day')->where('id', $dtProfit->id)
                        ->update([
                            'balance' => $balance_day
                        ]);
                } else {
                    DB::table('tbl_customer_reward_day')->insert([
                        'customer_id' => $customer_id,
                        'date' => $dateNow,
                        'balance' => $balanceMinute,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                changeBalance($value->id,'reward_profit',true,'',0,$balanceMinute);

            }
        }
        if (!empty($arr_object_id)){
            $arr_object_id = array_values($arr_object_id);
            ConnectPusher('',$arr_object_id,'reward_day');
        }
    }

    public function cronCustomerClassDay(){
        $date_end = date('Y-m-d '.get_option('hour_reward_day').':00');
        $date_start = date('Y-m-d H:i:s', strtotime($date_end . ' -1 day'));
        $hour_insert_cash = get_option('hour_reward_day');
//         $hour_insert_cash = '09:00';
        $dateNow = date('Y-m-d');
        $limit = 70;
        $class_percent_dong = 0;
        $class_percent_bac = 0;
        $class_percent_vang = 0;
        $dtSettingClassDong = SettingCustomerClass::find(1);
        if(!empty($dtSettingClassDong)){
            $class_percent_dong = $dtSettingClassDong->percent;
        }
        $dtSettingClassBac = SettingCustomerClass::find(2);
        if(!empty($dtSettingClassBac)){
            $class_percent_bac = $dtSettingClassBac->percent;
        }
        $dtSettingClassVang = SettingCustomerClass::find(3);
        if(!empty($dtSettingClassVang)){
            $class_percent_vang = $dtSettingClassVang->percent;
        }

        $leader_percent_dong = 0;
        $leader_percent_bac = 0;
        $leader_percent_vang = 0;
        $dtSettingLeaderDong = SettingCustomerLeaderShip::find(1);
        if(!empty($dtSettingLeaderDong)){
            $leader_percent_dong = $dtSettingLeaderDong->percent;
        }
        $dtSettingLeaderBac = SettingCustomerLeaderShip::find(2);
        if(!empty($dtSettingLeaderBac)){
            $leader_percent_bac = $dtSettingLeaderBac->percent;
        }
        $dtSettingLeaderVang = SettingCustomerLeaderShip::find(3);
        if(!empty($dtSettingLeaderVang)){
            $leader_percent_vang = $dtSettingLeaderVang->percent;
        }
        $total_seo_day_all = DB::table('tbl_customer_class')->whereBetween('created_at',[$date_start,$date_end])->sum('total');
        if (strtotime(date('H:i')) >= strtotime($hour_insert_cash) && strtotime($hour_insert_cash) <= strtotime(date('23:59'))) {
            //chạy class
            $dtClientCheckAll = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as id'
            )
                ->where('tbl_clients.active',1)
                ->where('tbl_clients.active_reward',1)
                ->doesntHave('history_cron_reward', 'and', function ($q) use ($dateNow) {
                    $q->where(function ($instance) use ($dateNow){
                        $instance->where('date_class', $dateNow);
                    });
                })
                ->count();
            $count = 1;
            while ($count <= $dtClientCheckAll){
                $dtClientCheck = Clients::select(
                    'tbl_clients.fullname as name',
                    'tbl_clients.id as id'
                )
                    ->where('tbl_clients.id',240)
                    ->where('tbl_clients.active',1)
                    ->where('tbl_clients.active_reward',1)
                    ->doesntHave('history_cron_reward', 'and', function ($q) use ($dateNow) {
                        $q->where(function ($instance) use ($dateNow){
                            $instance->where('date_class', $dateNow);
                        });
                    })
                    ->limit(50)->get();
                if (!empty($dtClientCheck)){
                    foreach ($dtClientCheck as $key => $value){
                        $count ++;
                        $customer_id = $value->id;
                        $arrId = getDataTreeReferralLevel($value->id);
                        $dataReferralLevel = ReferralLevel::select('id','customer_id','parent_id','referral_code')
                            ->with(['customer' => function ($query) {
                                $query->select('id', 'fullname', 'code','phone','email');
                                $query->with(['customer_class' => function($q){
                                    $q->with(['category_card' => function($instance){
                                        $instance->select('id','code','name','total','number_night');
                                        $instance->selectRaw('CONCAT("' . asset('storage/') . '/", image) as image');
                                        $instance->selectRaw('CONCAT("' . asset('storage/') . '/", avatar) as avatar');
                                    }]);
                                }]);
                            }])->whereIn('customer_id',$arrId)->get();
                        $newElement = new ReferralLevel();
                        $newElement->customer_id = $value->id;
                        $newElement->parent_id = 0;
                        $newElement->referral_code = null;
                        $dataReferralLevel->prepend($newElement);
                        $newElement->load(['customer' => function ($query) {
                            $query->select('id', 'fullname', 'code','phone','email');
                            $query->with(['customer_class' => function($q){
                                $q->with(['category_card' => function($instance){
                                    $instance->select('id','code','name','total','number_night');
                                    $instance->selectRaw('CONCAT("' . asset('storage/') . '/", image) as image');
                                    $instance->selectRaw('CONCAT("' . asset('storage/') . '/", avatar) as avatar');
                                }]);
                            }]);
                        }]);
                        $dtReferralLevelValue = get_parent_id_referral_level_new($dataReferralLevel);
                        $dtData = get_parent_id_referral_level($dtReferralLevelValue);

                        //class lấy gói đầu tư và total seo của nhánh nhỏ f1 lun
                        $dtReferralLevel = collect(Arr::pluck(!empty($dtData[0]->children) ? $dtData[0]->children : [],'total_seo','customer_id'));
                        $maxSeoF1 = $dtReferralLevel->max();
                        $total_seo = ($dtReferralLevel->sum()) - $maxSeoF1;
                        $dtClass = DB::table('tbl_setting_customer_class')->where(function($query) use ($total_seo){
                            $query->where('total_start', '<=',$total_seo);
                            $query->where('total_end', '>',$total_seo);
                        })->first();
                        $class_id = 0;
                        $class_name = null;
                        $class_percent = 0;
                        if (!empty($dtClass)){
                            $class_id = $dtClass->id;
                            $class_name = $dtClass->name;
                            $class_percent = $dtClass->percent;
                        }
                        $class_balance = $total_seo;

                        $checkCronReward = DB::table('tbl_history_customer_cron_reward')
                            ->where('customer_id',$value->id)
                            ->where('date_class',$dateNow)->first();
                        if (!empty($checkCronReward)) {
                        } else {
                            DB::table('tbl_history_customer_cron_reward')->insert([
                                'customer_id' => $value->id,
                                'date_check' => null,
                                'date_class' => $dateNow,
                                'date_leader' => null,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }

                        $checkClass = ClassCustomer::where('customer_id',$value->id)->first();
                        if (!empty($class_id)) {
                            if (!empty($checkClass)) {
                                $checkClass->name = $class_name;
                                $checkClass->class_id = $class_id;
                                $checkClass->balance = $class_balance;
                                $checkClass->percent = $class_percent;
                                $checkClass->date = date('Y-m-d');
                                $checkClass->save();
                            } else {
                                $dtClassCustomer = new ClassCustomer();
                                $dtClassCustomer->name = $class_name;
                                $dtClassCustomer->class_id = $class_id;
                                $dtClassCustomer->customer_id = $value->id;
                                $dtClassCustomer->balance = $class_balance;
                                $dtClassCustomer->percent = $class_percent;
                                $dtClassCustomer->date = date('Y-m-d');
                                $dtClassCustomer->save();
                            }
                        } else {
                            DB::table('tbl_class_customer')
                                ->where('customer_id',$value->id)
                                ->delete();
                        }
                        $check = DB::table('tbl_client_class_history')
                            ->where('customer_id',$value->id)
                            ->where('date',$dateNow)->first();
                        if (!empty($total_seo)) {
                            if (!empty($check)) {
                                DB::table('tbl_client_class_history')->where('id', $check->id)
                                    ->update([
                                        'balance' => $total_seo,
                                    ]);
                            } else {
                                DB::table('tbl_client_class_history')->insert([
                                    'customer_id' => $value->id,
                                    'date' => $dateNow,
                                    'balance' => $total_seo,
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                        //end

                        //lấy lại data mới nhất khi lên class
                        $dtReferralLevelValue = get_parent_id_referral_level_new($dataReferralLevel);
                        $dtData = get_parent_id_referral_level($dtReferralLevelValue);
                        $dtReferralLevelValueF = collect($dtReferralLevelValue);
                        //end

                        //leader ship
                        $dtDataDongLeader = $dtReferralLevelValueF->where('class_customer_1',1)->where('branch','!=',0)->groupBy('branch')->count();
                        $dtDataBacLeader = $dtReferralLevelValueF->where('class_customer_2',2)->where('branch','!=',0)->groupBy('branch')->count();
                        $dtDataVangLeader = $dtReferralLevelValueF->where('class_customer_3',3)->where('branch','!=',0)->groupBy('branch')->count();
                        $dtDataKCLeader = $dtReferralLevelValueF->where('class_customer_4',4)->where('branch','!=',0)->groupBy('branch')->count();

//                        $dtDataDongLeader += $dtDataBacLeader + $dtDataVangLeader + $dtDataKCLeader;
//                        $dtDataBacLeader += $dtDataVangLeader + $dtDataKCLeader;
//                        $dtDataVangLeader += $dtDataKCLeader;

                        $dtClassLeaderDong = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataDongLeader)->where('setting_class_id',1)->first();
                        $dtClassLeaderBac = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataBacLeader)->where('setting_class_id',2)->first();
                        $dtClassLeaderVang = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataVangLeader)->where('setting_class_id',3)->first();
                        $dtClassLeaderKC = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataKCLeader)->where('setting_class_id',4)->first();
                        $leader_ship_id = 0;
                        $leader_ship_name = null;
                        $leader_ship_percent = 0;
                        $leader_ship_number = 0;
                        if (!empty($dtClassLeaderKC)){
                            $leader_ship_id = $dtClassLeaderKC->id;
                            $leader_ship_name = $dtClassLeaderKC->name;
                            $leader_ship_percent = $dtClassLeaderKC->percent;
                            $leader_ship_number = $dtClassLeaderKC->number;
                        } else{
                            if (!empty($dtClassLeaderVang)){
                                $leader_ship_id = $dtClassLeaderVang->id;
                                $leader_ship_name = $dtClassLeaderVang->name;
                                $leader_ship_percent = $dtClassLeaderVang->percent;
                                $leader_ship_number = $dtClassLeaderVang->number;
                            } else{
                                if (!empty($dtClassLeaderBac)){
                                    $leader_ship_id = $dtClassLeaderBac->id;
                                    $leader_ship_name = $dtClassLeaderBac->name;
                                    $leader_ship_percent = $dtClassLeaderBac->percent;
                                    $leader_ship_number = $dtClassLeaderBac->number;
                                } else {
                                    if (!empty($dtClassLeaderDong)){
                                        $leader_ship_id = $dtClassLeaderDong->id;
                                        $leader_ship_name = $dtClassLeaderDong->name;
                                        $leader_ship_percent = $dtClassLeaderDong->percent;
                                        $leader_ship_number = $dtClassLeaderDong->number;
                                    }
                                }
                            }
                        }

                        $checkLeaderShip = LeaderShipCustomer::where('customer_id',$value->id)->first();
                        if (!empty($leader_ship_id)) {
                            if (!empty($checkLeaderShip)) {
                                $checkLeaderShip->name = $leader_ship_name;
                                $checkLeaderShip->leader_ship_id = $leader_ship_id;
                                $checkLeaderShip->number = $leader_ship_number;
                                $checkLeaderShip->percent = $leader_ship_percent;
                                $checkLeaderShip->date = date('Y-m-d');
                                $checkLeaderShip->save();
                            } else {
                                $dtLeaderCustomer = new LeaderShipCustomer();
                                $dtLeaderCustomer->name = $leader_ship_name;
                                $dtLeaderCustomer->leader_ship_id = $leader_ship_id;
                                $dtLeaderCustomer->customer_id = $value->id;
                                $dtLeaderCustomer->number = $leader_ship_number;
                                $dtLeaderCustomer->percent = $leader_ship_percent;
                                $dtLeaderCustomer->date = date('Y-m-d');
                                $dtLeaderCustomer->save();
                            }
                        } else {
                            DB::table('tbl_leader_ship_customer')
                                ->where('customer_id',$value->id)
                                ->delete();
                        }
                        $check = DB::table('tbl_client_leader_ship_history')
                            ->where('customer_id',$value->id)
                            ->where('date',$dateNow)->first();
                        if (!empty($leader_ship_id)) {
                            if (!empty($check)) {
                                DB::table('tbl_client_leader_ship_history')->where('id', $check->id)
                                    ->update([
                                        'leader_ship_id' => $leader_ship_id,
                                        'leader_ship_name' => $leader_ship_name,
                                        'leader_ship_percent' => $leader_ship_percent,
                                        'leader_ship_number' => $leader_ship_number,
                                    ]);
                            } else {
                                DB::table('tbl_client_leader_ship_history')->insert([
                                    'customer_id' => $value->id,
                                    'date' => $dateNow,
                                    'leader_ship_id' => $leader_ship_id,
                                    'leader_ship_name' => $leader_ship_name,
                                    'leader_ship_percent' => $leader_ship_percent,
                                    'leader_ship_number' => $leader_ship_number,
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                        //end
                    }
                }
            }
            //end
            //chay leader
            $dtClientCheckAll = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as id'
            )
                ->where('tbl_clients.active',1)
                ->where('tbl_clients.active_reward',1)
                ->doesntHave('history_cron_reward', 'and', function ($q) use ($dateNow) {
                    $q->where(function ($instance) use ($dateNow){
                        $instance->where('date_leader', $dateNow);
                    });
                })
                ->count();
            $count = 1;
            while ($count <= $dtClientCheckAll){
                $dtClientCheck = Clients::select(
                    'tbl_clients.fullname as name',
                    'tbl_clients.id as id'
                )
                    ->where('tbl_clients.active',1)
                    ->where('tbl_clients.active_reward',1)
                    ->doesntHave('history_cron_reward', 'and', function ($q) use ($dateNow) {
                        $q->where(function ($instance) use ($dateNow){
                            $instance->where('date_leader', $dateNow);
                        });
                    })
                    ->limit(50)->get();
                if (!empty($dtClientCheck)){
                    foreach ($dtClientCheck as $key => $value){
                        $count ++;
                        $customer_id = $value->id;
                        $arrId = getDataTreeReferralLevel($value->id);
                        $dataReferralLevel = ReferralLevel::select('id','customer_id','parent_id','referral_code')
                            ->with(['customer' => function ($query) {
                                $query->select('id', 'fullname', 'code','phone','email');
                                $query->with(['customer_class' => function($q){
                                    $q->with(['category_card' => function($instance){
                                        $instance->select('id','code','name','total','number_night');
                                        $instance->selectRaw('CONCAT("' . asset('storage/') . '/", image) as image');
                                        $instance->selectRaw('CONCAT("' . asset('storage/') . '/", avatar) as avatar');
                                    }]);
                                }]);
                            }])->whereIn('customer_id',$arrId)->get();
                        $newElement = new ReferralLevel();
                        $newElement->customer_id = $value->id;
                        $newElement->parent_id = 0;
                        $newElement->referral_code = null;
                        $dataReferralLevel->prepend($newElement);
                        $newElement->load(['customer' => function ($query) {
                            $query->select('id', 'fullname', 'code','phone','email');
                            $query->with(['customer_class' => function($q){
                                $q->with(['category_card' => function($instance){
                                    $instance->select('id','code','name','total','number_night');
                                    $instance->selectRaw('CONCAT("' . asset('storage/') . '/", image) as image');
                                    $instance->selectRaw('CONCAT("' . asset('storage/') . '/", avatar) as avatar');
                                }]);
                            }]);
                        }]);
                        $dtReferralLevelValue = get_parent_id_referral_level_new($dataReferralLevel);
                        $dtData = get_parent_id_referral_level($dtReferralLevelValue);
                        $dtReferralLevelValueF = collect($dtReferralLevelValue);

                        //class lấy gói đầu tư và total seo của nhánh nhỏ f1 lun
                        $dtReferralLevel = collect(Arr::pluck(!empty($dtData[0]->children) ? $dtData[0]->children : [],'total_seo','customer_id'));
                        $maxSeoF1 = $dtReferralLevel->max();
                        $total_seo = ($dtReferralLevel->sum()) - $maxSeoF1;
                        $dtClass = DB::table('tbl_setting_customer_class')->where(function($query) use ($total_seo){
                            $query->where('total_start', '<=',$total_seo);
                            $query->where('total_end', '>',$total_seo);
                        })->first();
                        $class_id = 0;
                        $class_name = null;
                        $class_percent = 0;
                        if (!empty($dtClass)){
                            $class_id = $dtClass->id;
                            $class_name = $dtClass->name;
                            $class_percent = $dtClass->percent;
                        }
                        $class_balance = $total_seo;

                        $checkCronReward = DB::table('tbl_history_customer_cron_reward')
                            ->where('customer_id',$value->id)
                            ->where('date_class',$dateNow)->first();
                        if (!empty($checkCronReward)) {
                            DB::table('tbl_history_customer_cron_reward')->where('id', $checkCronReward->id)
                                ->update([
                                    'date_leader' => $dateNow,
                                ]);
                        } else {
                            DB::table('tbl_history_customer_cron_reward')->insert([
                                'customer_id' => $value->id,
                                'date_check' => null,
                                'date_class' => $dateNow,
                                'date_leader' => $dateNow,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }


                        //leader ship
                        $dtDataDongLeader = $dtReferralLevelValueF->where('class_customer_1',1)->where('branch','!=',0)->groupBy('branch')->count();
                        $dtDataBacLeader = $dtReferralLevelValueF->where('class_customer_2',2)->where('branch','!=',0)->groupBy('branch')->count();
                        $dtDataVangLeader = $dtReferralLevelValueF->where('class_customer_3',3)->where('branch','!=',0)->groupBy('branch')->count();
                        $dtDataKCLeader = $dtReferralLevelValueF->where('class_customer_4',4)->where('branch','!=',0)->groupBy('branch')->count();

//                        $dtDataDongLeader += $dtDataBacLeader + $dtDataVangLeader + $dtDataKCLeader;
//                        $dtDataBacLeader += $dtDataVangLeader + $dtDataKCLeader;
//                        $dtDataVangLeader += $dtDataKCLeader;

                        $dtClassLeaderDong = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataDongLeader)->where('setting_class_id',1)->first();
                        $dtClassLeaderBac = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataBacLeader)->where('setting_class_id',2)->first();
                        $dtClassLeaderVang = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataVangLeader)->where('setting_class_id',3)->first();
                        $dtClassLeaderKC = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataKCLeader)->where('setting_class_id',4)->first();
                        $leader_ship_id = 0;
                        $leader_ship_name = null;
                        $leader_ship_percent = 0;
                        $leader_ship_number = 0;
                        if (!empty($dtClassLeaderKC)){
                            $leader_ship_id = $dtClassLeaderKC->id;
                            $leader_ship_name = $dtClassLeaderKC->name;
                            $leader_ship_percent = $dtClassLeaderKC->percent;
                            $leader_ship_number = $dtClassLeaderKC->number;
                        } else{
                            if (!empty($dtClassLeaderVang)){
                                $leader_ship_id = $dtClassLeaderVang->id;
                                $leader_ship_name = $dtClassLeaderVang->name;
                                $leader_ship_percent = $dtClassLeaderVang->percent;
                                $leader_ship_number = $dtClassLeaderVang->number;
                            } else{
                                if (!empty($dtClassLeaderBac)){
                                    $leader_ship_id = $dtClassLeaderBac->id;
                                    $leader_ship_name = $dtClassLeaderBac->name;
                                    $leader_ship_percent = $dtClassLeaderBac->percent;
                                    $leader_ship_number = $dtClassLeaderBac->number;
                                } else {
                                    if (!empty($dtClassLeaderDong)){
                                        $leader_ship_id = $dtClassLeaderDong->id;
                                        $leader_ship_name = $dtClassLeaderDong->name;
                                        $leader_ship_percent = $dtClassLeaderDong->percent;
                                        $leader_ship_number = $dtClassLeaderDong->number;
                                    }
                                }
                            }
                        }

                        $checkLeaderShip = LeaderShipCustomer::where('customer_id',$value->id)->first();
                        if (!empty($leader_ship_id)) {
                            if (!empty($checkLeaderShip)) {
                                $checkLeaderShip->name = $leader_ship_name;
                                $checkLeaderShip->leader_ship_id = $leader_ship_id;
                                $checkLeaderShip->number = $leader_ship_number;
                                $checkLeaderShip->percent = $leader_ship_percent;
                                $checkLeaderShip->date = date('Y-m-d');
                                $checkLeaderShip->save();
                            } else {
                                $dtLeaderCustomer = new LeaderShipCustomer();
                                $dtLeaderCustomer->name = $leader_ship_name;
                                $dtLeaderCustomer->leader_ship_id = $leader_ship_id;
                                $dtLeaderCustomer->customer_id = $value->id;
                                $dtLeaderCustomer->number = $leader_ship_number;
                                $dtLeaderCustomer->percent = $leader_ship_percent;
                                $dtLeaderCustomer->date = date('Y-m-d');
                                $dtLeaderCustomer->save();
                            }
                        } else {
                            DB::table('tbl_leader_ship_customer')
                                ->where('customer_id',$value->id)
                                ->delete();
                        }
                        $check = DB::table('tbl_client_leader_ship_history')
                            ->where('customer_id',$value->id)
                            ->where('date',$dateNow)->first();
                        if (!empty($leader_ship_id)) {
                            if (!empty($check)) {
                                DB::table('tbl_client_leader_ship_history')->where('id', $check->id)
                                    ->update([
                                        'leader_ship_id' => $leader_ship_id,
                                        'leader_ship_name' => $leader_ship_name,
                                        'leader_ship_percent' => $leader_ship_percent,
                                        'leader_ship_number' => $leader_ship_number,
                                    ]);
                            } else {
                                DB::table('tbl_client_leader_ship_history')->insert([
                                    'customer_id' => $value->id,
                                    'date' => $dateNow,
                                    'leader_ship_id' => $leader_ship_id,
                                    'leader_ship_name' => $leader_ship_name,
                                    'leader_ship_percent' => $leader_ship_percent,
                                    'leader_ship_number' => $leader_ship_number,
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                        //end
                    }
                }
            }
            //end
            $total_seo_day_all = DB::table('tbl_customer_class')->whereBetween('created_at',[$date_start,$date_end])->sum('total');
            $dtDataDongLeaderAll = Clients::whereHas('leadership_customer', function($query) {
                $query->where('leader_ship_id', 1);
            })->get();
            $dtDataBacLeaderAll = Clients::whereHas('leadership_customer', function($query) {
                $query->where('leader_ship_id', 2);
            })->get();
            $dtDataVangLeaderAll = Clients::whereHas('leadership_customer', function($query) {
                $query->where('leader_ship_id', 3);
            })->get();
            $dtDataKCLeaderAll = Clients::whereHas('leadership_customer', function($query) {
                $query->where('leader_ship_id', 4);
            })->get();
            $dtClient = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as id'
            )
                ->where('tbl_clients.active',1)
                ->where('tbl_clients.active_reward',1)
                ->doesntHave('history_cron_reward', 'and', function ($q) use ($dateNow) {
                    $q->where(function ($instance) use ($dateNow){
                        $instance->where('date_check', $dateNow);
                    });
                })
                ->limit($limit)->get();
            if (!empty($dtClient)){
                foreach ($dtClient as $key => $value){
                    $customer_id = $value->id;
                    $arrId = getDataTreeReferralLevel($value->id);
                    $dataReferralLevel = ReferralLevel::select('id','customer_id','parent_id','referral_code')
                        ->with(['customer' => function ($query) {
                            $query->select('id', 'fullname', 'code','phone','email');
                            $query->with(['customer_class' => function($q){
                                $q->with(['category_card' => function($instance){
                                    $instance->select('id','code','name','total','number_night');
                                    $instance->selectRaw('CONCAT("' . asset('storage/') . '/", image) as image');
                                    $instance->selectRaw('CONCAT("' . asset('storage/') . '/", avatar) as avatar');
                                }]);
                            }]);
                        }])->whereIn('customer_id',$arrId)->get();
                    $newElement = new ReferralLevel();
                    $newElement->customer_id = $value->id;
                    $newElement->parent_id = 0;
                    $newElement->referral_code = null;
                    $dataReferralLevel->prepend($newElement);
                    $newElement->load(['customer' => function ($query) {
                        $query->select('id', 'fullname', 'code','phone','email');
                        $query->with(['customer_class' => function($q){
                            $q->with(['category_card' => function($instance){
                                $instance->select('id','code','name','total','number_night');
                                $instance->selectRaw('CONCAT("' . asset('storage/') . '/", image) as image');
                                $instance->selectRaw('CONCAT("' . asset('storage/') . '/", avatar) as avatar');
                            }]);
                        }]);
                    }]);
                    $dtReferralLevelValue = get_parent_id_referral_level_new($dataReferralLevel);
                    $dtData = get_parent_id_referral_level($dtReferralLevelValue);
                    $dtReferralLevelValueF = collect($dtReferralLevelValue);

                    $total_seo_day = !empty($dtData[0]->total_seo_day) ? $dtData[0]->total_seo_day : 0;
                    //class lấy gói đầu tư và total seo của nhánh nhỏ f1 lun
                    $dtReferralLevel = collect(Arr::pluck(!empty($dtData[0]->children) ? $dtData[0]->children : [],'total_seo','customer_id'));
                    $maxSeoF1 = $dtReferralLevel->max();
                    $total_seo = ($dtReferralLevel->sum()) - $maxSeoF1;
                    $dtClass = DB::table('tbl_setting_customer_class')->where(function($query) use ($total_seo){
                        $query->where('total_start', '<=',$total_seo);
                        $query->where('total_end', '>',$total_seo);
                    })->first();
                    $class_id = 0;
                    $class_name = null;
                    $class_percent = 0;
                    if (!empty($dtClass)){
                        $class_id = $dtClass->id;
                        $class_name = $dtClass->name;
                        $class_percent = $dtClass->percent;
                    }
                    $class_balance = $total_seo;

                    $checkCronReward = DB::table('tbl_history_customer_cron_reward')
                        ->where('customer_id',$value->id)
                        ->where('date_leader',$dateNow)->first();
                    if (!empty($checkCronReward)) {
                        DB::table('tbl_history_customer_cron_reward')->where('id', $checkCronReward->id)
                            ->update([
                                'date_check' => $dateNow,
                            ]);
                    } else {
                        DB::table('tbl_history_customer_cron_reward')->insert([
                            'customer_id' => $value->id,
                            'date_check' => $dateNow,
                            'date_class' => $dateNow,
                            'date_leader' => $dateNow,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }

                    $checkClass = ClassCustomer::where('customer_id',$value->id)->first();
                    if (!empty($class_id)) {
                        if (!empty($checkClass)) {
                            $checkClass->name = $class_name;
                            $checkClass->class_id = $class_id;
                            $checkClass->balance = $class_balance;
                            $checkClass->percent = $class_percent;
                            $checkClass->date = date('Y-m-d');
                            $checkClass->save();
                        } else {
                            $dtClassCustomer = new ClassCustomer();
                            $dtClassCustomer->name = $class_name;
                            $dtClassCustomer->class_id = $class_id;
                            $dtClassCustomer->customer_id = $value->id;
                            $dtClassCustomer->balance = $class_balance;
                            $dtClassCustomer->percent = $class_percent;
                            $dtClassCustomer->date = date('Y-m-d');
                            $dtClassCustomer->save();
                        }
                    } else {
                        DB::table('tbl_class_customer')
                            ->where('customer_id',$value->id)
                            ->delete();
                    }
                    $check = DB::table('tbl_client_class_history')
                        ->where('customer_id',$value->id)
                        ->where('date',$dateNow)->first();
                    if (!empty($total_seo)) {
                        if (!empty($check)) {
                            DB::table('tbl_client_class_history')->where('id', $check->id)
                                ->update([
                                    'balance' => $total_seo,
                                ]);
                        } else {
                            DB::table('tbl_client_class_history')->insert([
                                'customer_id' => $value->id,
                                'date' => $dateNow,
                                'balance' => $total_seo,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }

                    if ($class_id == 1){
                        $dtDataDong = $dtReferralLevelValueF->whereBetween('level',[1,5]);
                    } elseif ($class_id == 2){
                        $dtDataDong = $dtReferralLevelValueF->whereBetween('level',[1,10]);
                    } elseif ($class_id == 3){
                        $dtDataDong = $dtReferralLevelValueF->whereBetween('level',[1,15]);
                    } elseif ($class_id == 4){
                        $dtDataDong = $dtReferralLevelValueF->whereBetween('level',[1,20]);
                    }
                    $dtDataDongNew = null;
                    $dtDataBac = null;
                    $dtDataVang = null;
                    $dtDataKC = null;
                    if (!empty($dtDataDong)) {
                        //thêm mới điều kiện ranking bonus
                        $dtDataDongOld = $dtDataDong->where('level', 1)->where('class_customer', '!=', 0);
                        $dtDataDong = $dtDataDong->where('level', 1)->where('class_customer', 0);
                        $customer_id_dong = collect(Arr::pluck($dtDataDong->where('level', 1)->where('class_customer',
                            0), 'total_seo', 'customer_id'))->keys()->toArray();
                        if (empty($customer_id_dong)){
                            $customer_id_dong = [0];
                        }
                        $childrenDong = getDescendants($dtReferralLevelValueF, $customer_id_dong);
                        $dtDataDong = $dtDataDong->merge($childrenDong)->merge($dtDataDongOld);

                        $dtDataDongNew = $dtDataDong->whereBetween('level', [1, 5]);
                        $dtDataBac = $dtDataDong->whereBetween('level', [6, 10]);
                        $dtDataVang = $dtDataDong->whereBetween('level', [11, 15]);
                        $dtDataKC = $dtDataDong->whereBetween('level', [16, 20]);
                    }

                    //end
                    //bonus
                    $totalBonus = 0;
                    $arrRewardProfitDayDetail = collect();
                    if ($class_id == 4){
                        $dtDataDongFilter = $dtDataDongNew->where(function($item) {
                            return ($item->total_reward - $item->total_old_reward) > 0;
                        });
                        $dtDataDongFilter = $dtDataDongFilter->map(function ($item) use ($class_percent_dong) {
                            $item['percent_class'] = $class_percent_dong;
                            $item['total_percent_class'] = ($class_percent_dong * $item->total_reward_day) / 100;
                            return $item;
                        });
                        $totalBonus += ($dtDataDongFilter->sum('total_reward_day') * $class_percent_dong) / 100;

                        $dtDataBacFilter = $dtDataBac->where(function($item) {
                            return ($item->total_reward - $item->total_old_reward) > 0;
                        });
                        $dtDataBacFilter = $dtDataBacFilter->map(function ($item) use ($class_percent_bac) {
                            $item['percent_class'] = $class_percent_bac;
                            $item['total_percent_class'] = ($class_percent_bac * $item->total_reward_day) / 100;
                            return $item;
                        });
                        $totalBonus += ($dtDataBacFilter->sum('total_reward_day') * $class_percent_bac) / 100;

                        $dtDataVangFilter = $dtDataVang->where(function($item) {
                            return ($item->total_reward - $item->total_old_reward) > 0;
                        });
                        $dtDataVangFilter = $dtDataVangFilter->map(function ($item) use ($class_percent_vang) {
                            $item['percent_class'] = $class_percent_vang;
                            $item['total_percent_class'] = ($class_percent_vang * $item->total_reward_day) / 100;
                            return $item;
                        });
                        $totalBonus += ($dtDataVangFilter->sum('total_reward_day') * $class_percent_vang) / 100;

                        $dtDataKCFilter = $dtDataKC->where(function($item) {
                            return ($item->total_reward - $item->total_old_reward) > 0;
                        });
                        $dtDataKCFilter = $dtDataKCFilter->map(function ($item) use ($class_percent) {
                            $item['percent_class'] = $class_percent;
                            $item['total_percent_class'] = ($class_percent * $item->total_reward_day) / 100;
                            return $item;
                        });
                        $totalBonus += ($dtDataKCFilter->sum('total_reward_day') * $class_percent) / 100;
                        $arrRewardProfitDayDetail = $arrRewardProfitDayDetail->merge($dtDataDongFilter)->merge($dtDataBacFilter)->merge($dtDataVangFilter)->merge($dtDataKCFilter);

                    } elseif ($class_id == 3){
                        $dtDataDongFilter = $dtDataDongNew->where(function($item) {
                            return ($item->total_reward - $item->total_old_reward) > 0;
                        });
                        $dtDataDongFilter = $dtDataDongFilter->map(function ($item) use ($class_percent_dong) {
                            $item['percent_class'] = $class_percent_dong;
                            $item['total_percent_class'] = ($class_percent_dong * $item->total_reward_day) / 100;
                            return $item;
                        });
                        $totalBonus += ($dtDataDongFilter->sum('total_reward_day') * $class_percent_dong) / 100;

                        $dtDataBacFilter = $dtDataBac->where(function($item) {
                            return ($item->total_reward - $item->total_old_reward) > 0;
                        });
                        $dtDataBacFilter = $dtDataBacFilter->map(function ($item) use ($class_percent_bac) {
                            $item['percent_class'] = $class_percent_bac;
                            $item['total_percent_class'] = ($class_percent_bac * $item->total_reward_day) / 100;
                            return $item;
                        });
                        $totalBonus += ($dtDataBacFilter->sum('total_reward_day') * $class_percent_bac) / 100;

                        $dtDataVangFilter = $dtDataVang->where(function($item) {
                            return ($item->total_reward - $item->total_old_reward) > 0;
                        });
                        $dtDataVangFilter = $dtDataVangFilter->map(function ($item) use ($class_percent) {
                            $item['percent_class'] = $class_percent;
                            $item['total_percent_class'] = ($class_percent * $item->total_reward_day) / 100;
                            return $item;
                        });
                        $totalBonus += ($dtDataVangFilter->sum('total_reward_day') * $class_percent) / 100;
                        $arrRewardProfitDayDetail = $arrRewardProfitDayDetail->merge($dtDataDongFilter)->merge($dtDataBacFilter)->merge($dtDataVangFilter);

                    } elseif ($class_id == 2){
                        $dtDataDongFilter = $dtDataDongNew->where(function($item) {
                            return ($item->total_reward - $item->total_old_reward) > 0;
                        });
                        $dtDataDongFilter = $dtDataDongFilter->map(function ($item) use ($class_percent_dong) {
                            $item['percent_class'] = $class_percent_dong;
                            $item['total_percent_class'] = ($class_percent_dong * $item->total_reward_day) / 100;
                            return $item;
                        });
                        $totalBonus += ($dtDataDongFilter->sum('total_reward_day') * $class_percent_dong) / 100;
                        $dtDataBacFilter = $dtDataBac->where(function($item) {
                            return ($item->total_reward - $item->total_old_reward) > 0;
                        });
                        $dtDataBacFilter = $dtDataBacFilter->map(function ($item) use ($class_percent) {
                            $item['percent_class'] = $class_percent;
                            $item['total_percent_class'] = ($class_percent * $item->total_reward_day) / 100;
                            return $item;
                        });
                        $totalBonus += ($dtDataBacFilter->sum('total_reward_day') * $class_percent) / 100;
                        $arrRewardProfitDayDetail = $arrRewardProfitDayDetail->merge($dtDataDongFilter)->merge($dtDataBacFilter);
                    } elseif ($class_id == 1){
                        $dtDataDongFilter = $dtDataDongNew->where(function($item) {
                            return ($item->total_reward - $item->total_old_reward) > 0;
                        });
                        $dtDataDongFilter = $dtDataDongFilter->map(function ($item) use ($class_percent) {
                            $item['percent_class'] = $class_percent;
                            $item['total_percent_class'] = ($class_percent * $item->total_reward_day) / 100;
                            return $item;
                        });
                        $totalBonus += ($dtDataDongFilter->sum('total_reward_day') * $class_percent) / 100;
                        $arrRewardProfitDayDetail = $arrRewardProfitDayDetail->merge($dtDataDongFilter);
                    }
                    if (!empty($totalBonus)){

                        $totalBonusOld = 0;
                        $dtProfitDay = DB::table('tbl_customer_reward_profit_day')
                            ->where('customer_id',$customer_id)
                            ->where('type',3)
                            ->where('date',$dateNow)
                            ->first();
                        if(!empty($dtProfitDay)){
                            $profitDayId =  $dtProfitDay->id;
                            $totalBonusOld = $dtProfitDay->balance;
                            DB::table('tbl_customer_reward_profit_day')->where('id', $dtProfitDay->id)
                                ->update([
                                    'balance' => $totalBonus,
                                ]);
                        } else {
                            $profitDayId = DB::table('tbl_customer_reward_profit_day')->insertGetId([
                                'customer_id' => $customer_id,
                                'date' => $dateNow,
                                'balance' => $totalBonus,
                                'type' => 3,
                                'date_start' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                        if ($totalBonusOld == 0){
                            changeBalance($value->id,'reward_ranking_bonus',true,'',$customer_id,$totalBonus,$dateNow);
                        } else {
                            if ($totalBonusOld != $totalBonus) {
                                changeBalance($value->id,'reward_ranking_bonus',false,'',$customer_id,$totalBonusOld,$dateNow);
                                changeBalance($value->id,'reward_ranking_bonus',true,'',$customer_id,$totalBonus,$dateNow);
                            }
                        }

                        $dtProfit = DB::table('tbl_customer_reward_day')
                            ->where('customer_id',$customer_id)
                            ->where('date',$dateNow)
                            ->first();
                        if(!empty($dtProfit)){
                            $balance_day = $dtProfit->balance - $totalBonusOld + $totalBonus;
                            DB::table('tbl_customer_reward_day')->where('id', $dtProfit->id)
                                ->update([
                                    'balance' => $balance_day
                                ]);
                        } else {
                            DB::table('tbl_customer_reward_day')->insert([
                                'customer_id' => $customer_id,
                                'date' => $dateNow,
                                'balance' => $totalBonus,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }

                        DB::table('tbl_customer_reward_profit_day_detail')->where('customer_reward_profit_day_id',$profitDayId)->delete();
                        if (!empty($arrRewardProfitDayDetail)){
                            foreach ($arrRewardProfitDayDetail as $kk => $vv){
                                DB::table('tbl_customer_reward_profit_day_detail')->insert([
                                    'customer_reward_profit_day_id' => $profitDayId,
                                    'customer_id' => $vv->customer_id,
                                    'total_balance' => $vv->total_reward_day,
                                    'balance' => $vv->total_reward_day,
                                    'type' => $vv->level,
                                    'total' => $vv->total_percent_class,
                                    'percent' => $vv->percent_class,
                                    'object_id' => $vv->class_customer,
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                    }
                    //end

                    //leader ship
                    $countDataDongLeader = $dtDataDongLeaderAll->count();
                    $countDataBacLeader = $dtDataBacLeaderAll->count();
                    $countDataVangLeader = $dtDataVangLeaderAll->count();
                    $countDataKcLeader = $dtDataKCLeaderAll->count();


                    $dtDataDongLeader = $dtReferralLevelValueF->where('class_customer_1',1)->where('branch','!=',0)->groupBy('branch')->count();
                    $dtDataBacLeader = $dtReferralLevelValueF->where('class_customer_2',2)->where('branch','!=',0)->groupBy('branch')->count();
                    $dtDataVangLeader = $dtReferralLevelValueF->where('class_customer_3',3)->where('branch','!=',0)->groupBy('branch')->count();
                    $dtDataKCLeader = $dtReferralLevelValueF->where('class_customer_4',4)->where('branch','!=',0)->groupBy('branch')->count();

//                    $dtDataDongLeader += $dtDataBacLeader + $dtDataVangLeader + $dtDataKCLeader;
//                    $dtDataBacLeader += $dtDataVangLeader + $dtDataKCLeader;
//                    $dtDataVangLeader += $dtDataKCLeader;

                    $dtClassLeaderDong = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataDongLeader)->where('setting_class_id',1)->first();
                    $dtClassLeaderBac = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataBacLeader)->where('setting_class_id',2)->first();
                    $dtClassLeaderVang = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataVangLeader)->where('setting_class_id',3)->first();
                    $dtClassLeaderKC = DB::table('tbl_setting_customer_leadership')->where('number','<=',$dtDataKCLeader)->where('setting_class_id',4)->first();
                    $leader_ship_id = 0;
                    $leader_ship_name = null;
                    $leader_ship_percent = 0;
                    $leader_ship_number = 0;
                    if (!empty($dtClassLeaderKC)){
                        $leader_ship_id = $dtClassLeaderKC->id;
                        $leader_ship_name = $dtClassLeaderKC->name;
                        $leader_ship_percent = $dtClassLeaderKC->percent;
                        $leader_ship_number = $dtClassLeaderKC->number;
                    } else{
                        if (!empty($dtClassLeaderVang)){
                            $leader_ship_id = $dtClassLeaderVang->id;
                            $leader_ship_name = $dtClassLeaderVang->name;
                            $leader_ship_percent = $dtClassLeaderVang->percent;
                            $leader_ship_number = $dtClassLeaderVang->number;
                        } else{
                            if (!empty($dtClassLeaderBac)){
                                $leader_ship_id = $dtClassLeaderBac->id;
                                $leader_ship_name = $dtClassLeaderBac->name;
                                $leader_ship_percent = $dtClassLeaderBac->percent;
                                $leader_ship_number = $dtClassLeaderBac->number;
                            } else {
                                if (!empty($dtClassLeaderDong)){
                                    $leader_ship_id = $dtClassLeaderDong->id;
                                    $leader_ship_name = $dtClassLeaderDong->name;
                                    $leader_ship_percent = $dtClassLeaderDong->percent;
                                    $leader_ship_number = $dtClassLeaderDong->number;
                                }
                            }
                        }
                    }
                    $checkLeaderShip = LeaderShipCustomer::where('customer_id',$value->id)->first();
                    if (!empty($leader_ship_id)) {
                        if (!empty($checkLeaderShip)) {
                            $checkLeaderShip->name = $leader_ship_name;
                            $checkLeaderShip->leader_ship_id = $leader_ship_id;
                            $checkLeaderShip->number = $leader_ship_number;
                            $checkLeaderShip->percent = $leader_ship_percent;
                            $checkLeaderShip->date = date('Y-m-d');
                            $checkLeaderShip->save();
                        } else {
                            $dtLeaderCustomer = new LeaderShipCustomer();
                            $dtLeaderCustomer->name = $leader_ship_name;
                            $dtLeaderCustomer->leader_ship_id = $leader_ship_id;
                            $dtLeaderCustomer->customer_id = $value->id;
                            $dtLeaderCustomer->number = $leader_ship_number;
                            $dtLeaderCustomer->percent = $leader_ship_percent;
                            $dtLeaderCustomer->date = date('Y-m-d');
                            $dtLeaderCustomer->save();
                        }
                    } else {
                        DB::table('tbl_leader_ship_customer')
                            ->where('customer_id',$value->id)
                            ->delete();
                    }
                    $check = DB::table('tbl_client_leader_ship_history')
                        ->where('customer_id',$value->id)
                        ->where('date',$dateNow)->first();
                    if (!empty($leader_ship_id)) {
                        if (!empty($check)) {
                            DB::table('tbl_client_leader_ship_history')->where('id', $check->id)
                                ->update([
                                    'leader_ship_id' => $leader_ship_id,
                                    'leader_ship_name' => $leader_ship_name,
                                    'leader_ship_percent' => $leader_ship_percent,
                                    'leader_ship_number' => $leader_ship_number,
                                ]);
                        } else {
                            DB::table('tbl_client_leader_ship_history')->insert([
                                'customer_id' => $value->id,
                                'date' => $dateNow,
                                'leader_ship_id' => $leader_ship_id,
                                'leader_ship_name' => $leader_ship_name,
                                'leader_ship_percent' => $leader_ship_percent,
                                'leader_ship_number' => $leader_ship_number,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                    //end
                    //bonus leader
                    $totalBonusLeader = 0;
                    $arrRewardProfitDayLeaderDetail = collect();
                    $percent_leader_ship = 10;
                    $total_seo_day = ($total_seo_day_all * $percent_leader_ship) / 100;
                    if ($leader_ship_id == 4){
                        $countDataDongLeader = $countDataDongLeader + $countDataBacLeader + $countDataVangLeader + $countDataKcLeader;
                        $dtDataDongLeaderFilter = $dtDataDongLeaderAll->map(function ($item) use ($leader_percent_dong,$total_seo_day,$total_seo_day_all,$countDataDongLeader) {
                            $item['total_seo_day_all'] = $total_seo_day_all;
                            $item['total_seo_day'] = $total_seo_day;
                            $item['percent_class'] = $leader_percent_dong;
                            $item['type_check'] = 2;
                            $item['total_member'] = $countDataDongLeader;
                            $item['total_percent_class'] = !empty($countDataDongLeader) ? ((($leader_percent_dong * $total_seo_day) / 100) / $countDataDongLeader / $countDataDongLeader) : 0;
                            return $item;
                        });
                        $totalBonusLeader += !empty($countDataDongLeader) ? ((($total_seo_day * $leader_percent_dong) / 100) / $countDataDongLeader) : (($total_seo_day * $leader_percent_dong) / 100);
                        $countDataBacLeader = $countDataBacLeader + $countDataVangLeader + $countDataKcLeader;
                        $dtDataBacLeaderFilter = $dtDataBacLeaderAll->map(function ($item) use ($leader_percent_bac,$total_seo_day,$total_seo_day_all,$countDataBacLeader) {
                            $item['total_seo_day_all'] = $total_seo_day_all;
                            $item['total_seo_day'] = $total_seo_day;
                            $item['percent_class'] = $leader_percent_bac;
                            $item['type_check'] = 2;
                            $item['total_member'] = $countDataBacLeader;
                            $item['total_percent_class'] = !empty($countDataBacLeader) ? ((($leader_percent_bac * $total_seo_day) / 100) / $countDataBacLeader / $countDataBacLeader) : 0;
                            return $item;
                        });
                        $totalBonusLeader += !empty($countDataBacLeader) ? ((($total_seo_day * $leader_percent_bac) / 100) / $countDataBacLeader) : (($total_seo_day * $leader_percent_bac) / 100);
                        $countDataVangLeader = $countDataVangLeader + $countDataKcLeader;
                        $dtDataVangLeaderFilter = $dtDataVangLeaderAll->map(function ($item) use ($leader_percent_vang,$total_seo_day,$total_seo_day_all,$countDataVangLeader) {
                            $item['total_seo_day_all'] = $total_seo_day_all;
                            $item['total_seo_day'] = $total_seo_day;
                            $item['percent_class'] = $leader_percent_vang;
                            $item['type_check'] = 2;
                            $item['total_member'] = $countDataVangLeader;
                            $item['total_percent_class'] = !empty($countDataVangLeader) ? ((($leader_percent_vang * $total_seo_day) / 100) / $countDataVangLeader / $countDataVangLeader) : 0;
                            return $item;
                        });
                        $totalBonusLeader += !empty($countDataVangLeader) ? ((($total_seo_day * $leader_percent_vang) / 100) / $countDataVangLeader) : (($total_seo_day * $leader_percent_vang) / 100);
                        $dtDataKCLeaderFilter = $dtDataKCLeaderAll->map(function ($item) use ($leader_ship_percent,$total_seo_day,$total_seo_day_all,$countDataKcLeader) {
                            $item['total_seo_day_all'] = $total_seo_day_all;
                            $item['total_seo_day'] = $total_seo_day;
                            $item['percent_class'] = $leader_ship_percent;
                            $item['type_check'] = 2;
                            $item['total_member'] = $countDataKcLeader;
                            $item['total_percent_class'] = !empty($countDataKcLeader) ? ((($leader_ship_percent * $total_seo_day) / 100) / $countDataKcLeader / $countDataKcLeader) : 0;
                            return $item;
                        });
                        $totalBonusLeader += !empty($countDataKcLeader) ? ((($total_seo_day * $leader_ship_percent) / 100) / $countDataKcLeader) : (($total_seo_day * $leader_ship_percent) / 100);
                        $arrRewardProfitDayLeaderDetail = $arrRewardProfitDayLeaderDetail->merge($dtDataDongLeaderFilter)->merge($dtDataBacLeaderFilter)->merge($dtDataVangLeaderFilter)->merge($dtDataKCLeaderFilter);

                    } elseif ($leader_ship_id == 3){
                        $countDataDongLeader = $countDataDongLeader + $countDataBacLeader + $countDataVangLeader + $countDataKcLeader;
                        $dtDataDongLeaderFilter = $dtDataDongLeaderAll->map(function ($item) use ($leader_percent_dong,$total_seo_day,$total_seo_day_all,$countDataDongLeader) {
                            $item['total_seo_day_all'] = $total_seo_day_all;
                            $item['total_seo_day'] = $total_seo_day;
                            $item['percent_class'] = $leader_percent_dong;
                            $item['type_check'] = 2;
                            $item['total_member'] = $countDataDongLeader;
                            $item['total_percent_class'] = !empty($countDataDongLeader) ? ((($leader_percent_dong * $total_seo_day) / 100) / $countDataDongLeader / $countDataDongLeader) : 0;
                            return $item;
                        });
                        $totalBonusLeader += !empty($countDataDongLeader) ? ((($total_seo_day * $leader_percent_dong) / 100) / $countDataDongLeader) : (($total_seo_day * $leader_percent_dong) / 100);
                        $countDataBacLeader = $countDataBacLeader + $countDataVangLeader + $countDataKcLeader;
                        $dtDataBacLeaderFilter = $dtDataBacLeaderAll->map(function ($item) use ($leader_percent_bac,$total_seo_day,$total_seo_day_all,$countDataBacLeader) {
                            $item['total_seo_day_all'] = $total_seo_day_all;
                            $item['total_seo_day'] = $total_seo_day;
                            $item['percent_class'] = $leader_percent_bac;
                            $item['type_check'] = 2;
                            $item['total_member'] = $countDataBacLeader;
                            $item['total_percent_class'] = !empty($countDataBacLeader) ? ((($leader_percent_bac * $total_seo_day) / 100) / $countDataBacLeader / $countDataBacLeader) : 0;
                            return $item;
                        });
                        $totalBonusLeader += !empty($countDataBacLeader) ? ((($total_seo_day * $leader_percent_bac) / 100) / $countDataBacLeader) : (($total_seo_day * $leader_percent_bac) / 100);
                        $countDataVangLeader = $countDataVangLeader + $countDataKcLeader;
                        $dtDataVangLeaderFilter = $dtDataVangLeaderAll->map(function ($item) use ($leader_ship_percent,$total_seo_day,$total_seo_day_all,$countDataVangLeader) {
                            $item['total_seo_day_all'] = $total_seo_day_all;
                            $item['total_seo_day'] = $total_seo_day;
                            $item['percent_class'] = $leader_ship_percent;
                            $item['type_check'] = 2;
                            $item['total_member'] = $countDataVangLeader;
                            $item['total_percent_class'] = !empty($countDataVangLeader) ? ((($leader_ship_percent * $total_seo_day) / 100) / $countDataVangLeader / $countDataVangLeader) : 0;
                            return $item;
                        });
                        $totalBonusLeader += !empty($countDataVangLeader) ? ((($total_seo_day * $leader_ship_percent) / 100) / $countDataVangLeader) : (($total_seo_day * $leader_ship_percent) / 100);
                        $arrRewardProfitDayLeaderDetail = $arrRewardProfitDayLeaderDetail->merge($dtDataDongLeaderFilter)->merge($dtDataBacLeaderFilter)->merge($dtDataVangLeaderFilter);
                    } elseif ($leader_ship_id == 2){
                        $countDataDongLeader = $countDataDongLeader + $countDataBacLeader + $countDataVangLeader + $countDataKcLeader;
                        $dtDataDongLeaderFilter = $dtDataDongLeaderAll->map(function ($item) use ($leader_percent_dong,$total_seo_day,$total_seo_day_all,$countDataDongLeader) {
                            $item['total_seo_day_all'] = $total_seo_day_all;
                            $item['total_seo_day'] = $total_seo_day;
                            $item['percent_class'] = $leader_percent_dong;
                            $item['type_check'] = 2;
                            $item['total_member'] = $countDataDongLeader;
                            $item['total_percent_class'] = !empty($countDataDongLeader) ? ((($leader_percent_dong * $total_seo_day) / 100) / $countDataDongLeader / $countDataDongLeader) : 0;
                            return $item;
                        });
                        $totalBonusLeader += !empty($countDataDongLeader) ? ((($total_seo_day * $leader_percent_dong) / 100) / $countDataDongLeader) : (($total_seo_day * $leader_percent_dong) / 100);
                        $countDataBacLeader = $countDataBacLeader + $countDataVangLeader + $countDataKcLeader;
                        $dtDataBacLeaderFilter = $dtDataBacLeaderAll->map(function ($item) use ($leader_ship_percent,$total_seo_day,$total_seo_day_all,$countDataBacLeader) {
                            $item['total_seo_day_all'] = $total_seo_day_all;
                            $item['total_seo_day'] = $total_seo_day;
                            $item['percent_class'] = $leader_ship_percent;
                            $item['type_check'] = 2;
                            $item['total_member'] = $countDataBacLeader;
                            $item['total_percent_class'] = !empty($countDataBacLeader) ? ((($leader_ship_percent * $total_seo_day) / 100) / $countDataBacLeader / $countDataBacLeader) : 0;
                            return $item;
                        });
                        $totalBonusLeader += !empty($countDataBacLeader) ? ((($total_seo_day * $leader_ship_percent) / 100) / $countDataBacLeader) : (($total_seo_day * $leader_ship_percent) / 100);
                        $arrRewardProfitDayLeaderDetail = $arrRewardProfitDayLeaderDetail->merge($dtDataDongLeaderFilter)->merge($dtDataBacLeaderFilter);
                    } elseif ($leader_ship_id == 1){
                        $countDataDongLeader = $countDataDongLeader + $countDataBacLeader + $countDataVangLeader + $countDataKcLeader;
                        $dtDataDongLeaderFilter = $dtDataDongLeaderAll->map(function ($item) use ($leader_ship_percent,$total_seo_day,$total_seo_day_all,$countDataDongLeader) {
                            $item['total_seo_day_all'] = $total_seo_day_all;
                            $item['total_seo_day'] = $total_seo_day;
                            $item['percent_class'] = $leader_ship_percent;
                            $item['type_check'] = 2;
                            $item['total_member'] = $countDataDongLeader;
                            $item['total_percent_class'] = !empty($countDataDongLeader) ? ((($leader_ship_percent * $total_seo_day) / 100) / $countDataDongLeader / $countDataDongLeader) : 0;
                            return $item;
                        });
                        $totalBonusLeader += !empty($countDataDongLeader) ? ((($total_seo_day * $leader_ship_percent) / 100) / $countDataDongLeader) : (($total_seo_day * $leader_ship_percent) / 100);
                        $arrRewardProfitDayLeaderDetail = $arrRewardProfitDayLeaderDetail->merge($dtDataDongLeaderFilter);
                    }
                    if (!empty($totalBonusLeader)){

                        $totalBonusLeaderOld = 0;
                        $dtProfitDay = DB::table('tbl_customer_reward_profit_day')
                            ->where('customer_id',$customer_id)
                            ->where('type',4)
                            ->where('date',$dateNow)
                            ->first();
                        if(!empty($dtProfitDay)){
                            $profitDayId =  $dtProfitDay->id;
                            $totalBonusLeaderOld = $dtProfitDay->balance;
                            DB::table('tbl_customer_reward_profit_day')->where('id', $dtProfitDay->id)
                                ->update([
                                    'balance' => $totalBonusLeader,
                                ]);
                        } else {
                            $profitDayId = DB::table('tbl_customer_reward_profit_day')->insertGetId([
                                'customer_id' => $customer_id,
                                'date' => $dateNow,
                                'balance' => $totalBonusLeader,
                                'type' => 4,
                                'date_start' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                        if ($totalBonusLeaderOld == 0){
                            changeBalance($value->id,'reward_leader_bonus',true,'',$customer_id,$totalBonusLeader,$dateNow);
                        } else {
                            if ($totalBonusLeaderOld != $totalBonusLeader) {
                                changeBalance($value->id,'reward_leader_bonus',false,'',$customer_id,$totalBonusLeaderOld,$dateNow);
                                changeBalance($value->id,'reward_leader_bonus',true,'',$customer_id,$totalBonusLeader,$dateNow);
                            }
                        }

                        $dtProfit = DB::table('tbl_customer_reward_day')
                            ->where('customer_id',$customer_id)
                            ->where('date',$dateNow)
                            ->first();
                        if(!empty($dtProfit)){
                            $balance_day = $dtProfit->balance - $totalBonusLeaderOld + $totalBonusLeader;
                            DB::table('tbl_customer_reward_day')->where('id', $dtProfit->id)
                                ->update([
                                    'balance' => $balance_day
                                ]);
                        } else {
                            DB::table('tbl_customer_reward_day')->insert([
                                'customer_id' => $customer_id,
                                'date' => $dateNow,
                                'balance' => $totalBonusLeader,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }

                        DB::table('tbl_customer_reward_profit_day_detail')->where('customer_reward_profit_day_id',$profitDayId)->delete();
                        if (!empty($arrRewardProfitDayLeaderDetail)){
                            foreach ($arrRewardProfitDayLeaderDetail as $kk => $vv){
                                DB::table('tbl_customer_reward_profit_day_detail')->insert([
                                    'customer_reward_profit_day_id' => $profitDayId,
                                    'customer_id' => $vv->id,
                                    'total_balance' => $vv->total_seo_day_all,
                                    'balance' => $vv->total_seo_day,
                                    'type' => 0,
                                    'total' => $vv->total_percent_class,
                                    'percent' => $vv->percent_class,
                                    'type_check' => $vv->type_check,
                                    'total_member' => $vv->total_member,
                                    'object_id' => !empty($vv->leadership_customer->leader_ship_id),
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                    }
                    //end
                }
            }
        }
    }

    public function getWarningWithDraw(){
        $limit = 50;
        $count = 0;
        $dtClient = Clients::select(
            'tbl_clients.fullname as name',
            'tbl_clients.email as email',
            'tbl_clients.account_balance as account_balance',
            'tbl_clients.id as id'
        )
            ->selectRaw('COALESCE((SELECT SUM(tbl_transaction.total) * '.get_option('coefficient_withdraw').' FROM tbl_transaction WHERE tbl_transaction.customer_id = tbl_clients.id AND status = 1 LIMIT 1),0) as max_withdraw')
            ->selectRaw('COALESCE((SELECT SUM(tbl_request_withdraw_money.total)
                FROM tbl_customer_class
                JOIN tbl_request_withdraw_money ON tbl_request_withdraw_money.customer_id = tbl_customer_class.customer_id
                WHERE tbl_customer_class.customer_id = tbl_clients.id
                AND tbl_request_withdraw_money.status IN (0,1)
                LIMIT 1),0) as total_withdraw')
            ->selectRaw('COALESCE((SELECT SUM(tbl_transfer_package.total)
                FROM tbl_customer_class
                JOIN tbl_transfer_package ON tbl_transfer_package.customer_id = tbl_customer_class.customer_id
                WHERE tbl_transfer_package.customer_id = tbl_clients.id
                AND tbl_transfer_package.status IN (0,1)
                LIMIT 1),0) as total_transfer')
            ->whereHas('customer_class',function ($query){
                $query->whereRaw('
                    COALESCE((SELECT SUM(tbl_transaction.total) * '.get_option('coefficient_withdraw').' FROM tbl_transaction WHERE tbl_transaction.customer_id = tbl_customer_class.customer_id AND status = 1 LIMIT 1),0) * '.get_option('coefficient_withdraw').' <=
                     (
                     COALESCE((
                        SELECT SUM(tbl_request_withdraw_money.total)
                        FROM tbl_request_withdraw_money
                        WHERE customer_id = tbl_customer_class.customer_id
                        AND tbl_request_withdraw_money.status IN (0,1)
                    ), 0) +
                    COALESCE((
                       tbl_clients.account_balance
                    ), 0) +
                      COALESCE((
                        SELECT SUM(tbl_transfer_package.total)
                        FROM tbl_transfer_package
                        WHERE customer_id = tbl_customer_class.customer_id
                        AND tbl_transfer_package.status IN (0,1)
                    ), 0)
                    )
                ');
            })
            ->where(function ($query){
                $query->where('tbl_clients.active',1);
                $query->where('tbl_clients.admin_active',0);
                $query->doesntHave('history_customer_wraning');
            })
            ->limit($limit)->get();
        if (!empty($dtClient)) {
            foreach ($dtClient as $key => $value) {
                $name_customer = $value->name;
                $max_withdraw = $value->max_withdraw;
                $total_withdraw = $value->total_withdraw;
                $total_transfer = $value->total_transfer;
                $account_balance = $value->account_balance;
                $total_withdraw += $account_balance;
                $total_withdraw += $total_transfer;
                if ($total_withdraw >= $max_withdraw) {
                    $content_wraning = get_option('content_wraning');
                    $email_company = get_option('phone_company');
                    $phone_company = get_option('phone_company');
                    $content_wraning = str_replace('{name_customer}', $name_customer, $content_wraning);
                    $content_wraning = str_replace('{email_company}', $email_company, $content_wraning);
                    $content_wraning = str_replace('{phone_company}', $phone_company, $content_wraning);
                    if (count($value->history_customer_wraning) <= 0) {
                        $historyCustomerWarning = new HistoryCustomerWarning();
                        $historyCustomerWarning->customer_id = $value->id;
                        $historyCustomerWarning->date = date('Y-m-d H:i:s');
                        $historyCustomerWarning->save();
                    }
                    $count ++;
                }
            }
        }
        echo $count;
    }

    public function updateStatusCustomerWarning(){
        $count = 0;
        $days = get_option('date_wraning');
        $dtClient = Clients::select(
            'tbl_clients.fullname as name',
            'tbl_clients.email as email',
            'tbl_clients.id as id'
        )
            ->whereHas('history_customer_wraning',function ($query) use ($days){
                $query->whereRaw('DATE_ADD(date, INTERVAL ? DAY) <= now()', [$days]);
            })
            ->where(function ($query){
                $query->where('tbl_clients.active',1);
                $query->where('tbl_clients.active_reward',1);
            })
            ->get();
        $arr_object_id = [];
        if(!empty($dtClient)) {
            foreach ($dtClient as $key => $value) {
                $value->active_reward = 0;
                $success = $value->save();
                if ($success){
                    $count++;
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
                    ->where('tbl_clients.id', $value->id)
                    ->get()->toArray();
                if (!empty($dtCustomer)) {
                    $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                }
            }
        }
        echo $count;
        if (!empty($arr_object_id)){
            $arr_object_id = array_values($arr_object_id);
            ConnectPusher('',$arr_object_id,'update-active-customer');
        }
    }

    public function getWarningWithDrawOLd(){
        $limit = 50;
        $count = 0;
        $dtClient = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.email as email',
                'tbl_clients.id as id'
            )
            ->selectRaw('COALESCE((SELECT tbl_customer_class.total * '.get_option('coefficient_withdraw').' FROM tbl_customer_class WHERE tbl_customer_class.customer_id = tbl_clients.id LIMIT 1),0) as max_withdraw')
            ->selectRaw('COALESCE((SELECT SUM(tbl_request_withdraw_money.total)
                FROM tbl_customer_class
                JOIN tbl_request_withdraw_money ON tbl_request_withdraw_money.customer_id = tbl_customer_class.customer_id
                WHERE tbl_customer_class.customer_id = tbl_clients.id
                AND tbl_request_withdraw_money.date >= tbl_customer_class.created_at
                LIMIT 1),0) as total_withdraw')
            ->selectRaw('COALESCE((SELECT SUM(tbl_transfer_package.total)
                FROM tbl_customer_class
                JOIN tbl_transfer_package ON tbl_transfer_package.customer_id = tbl_customer_class.customer_id
                WHERE tbl_transfer_package.customer_id = tbl_clients.id
                AND tbl_transfer_package.date >= tbl_customer_class.created_at
                LIMIT 1),0) as total_transfer')
            ->whereHas('customer_class',function ($query){
                $query->whereRaw('
                    total <=
                     (
                     COALESCE((
                        SELECT SUM(tbl_request_withdraw_money.total)
                        FROM tbl_request_withdraw_money
                        WHERE customer_id = tbl_customer_class.customer_id
                        AND tbl_request_withdraw_money.date >= tbl_customer_class.created_at
                    ), 0) +
                    COALESCE((
                        SELECT SUM(tbl_transfer_package.total)
                        FROM tbl_transfer_package
                        WHERE customer_id = tbl_customer_class.customer_id
                        AND tbl_transfer_package.date >= tbl_customer_class.created_at
                    ), 0)
                    )
                ');
            })
            ->where(function ($query){
                $query->where('tbl_clients.active',1);
                $query->where('tbl_clients.admin_active',0);
                $query->doesntHave('history_customer_wraning_mail');
            })
            ->limit($limit)->get();
        $dataNoti = [];
        if (!empty($dtClient)) {
            foreach ($dtClient as $key => $value) {
                $arr_object_id = [];
                $name_customer = $value->name;
                $max_withdraw = $value->max_withdraw;
                $total_withdraw = $value->total_withdraw;
                $total_transfer = $value->total_transfer;
                $total_withdraw += $total_transfer;
                $email = $value->email;
                if ($total_withdraw >= $max_withdraw) {
                    $content_warning_mail = get_option('content_warning_mail');
                    $email_company = get_option('phone_company');
                    $phone_company = get_option('phone_company');
                    $content_warning_mail = str_replace('{name_customer}',$name_customer,$content_warning_mail);
                    $content_warning_mail = str_replace('{email_company}',$email_company,$content_warning_mail);
                    $content_warning_mail = str_replace('{phone_company}',$phone_company,$content_warning_mail);
                    $dataMail = [
                        'email_company' => $email_company,
                        'phone_company' => $phone_company,
                        'name_customer' => $name_customer,
                        'content_warning_mail' => $content_warning_mail
                    ];
                    $emailCc = $email;
                    Mail::send('admin.email-template.send_warning_withdraw', $dataMail,
                        function ($message) use ($emailCc) {
                            $message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
                            $message->to($emailCc, 'Cảnh Báo Tài Khoản Sẽ Bị Khóa Sau 24h');
                            $message->subject('Cảnh Báo Tài Khoản Sẽ Bị Khóa Sau 24h');
                        });
                    $historyCustomerWarning = new HistoryCustomerWarningMail();
                    $historyCustomerWarning->customer_id = $value->id;
                    $historyCustomerWarning->date = date('Y-m-d H:i:s');
                    $historyCustomerWarning->save();
                    $count++;
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
                        ->where('tbl_clients.id', $value->id)
                        ->get()->toArray();
                    if (!empty($dtCustomer)) {
                        $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                    }
                    if (!empty($arr_object_id)) {
                        $playerId = array_unique(array_column($arr_object_id, 'player_id'));
                        $content = '';
                        $json_data = json_encode([
                            'customer_id' => $value->id,
                            'object' => 'client'
                        ], JSON_UNESCAPED_UNICODE);
                        $title = '';
                        $title_owen = '';
                        $content = "Tài khoản của bạn đang sử dụng gói dịch vụ hiện tại và đã đạt giới hạn. Để tránh bị gián đoạn, vui lòng nâng cấp ngay lập tức.<br>
                                    Nếu không thực hiện nâng cấp trong vòng 24 giờ kể từ khi nhận email này, tài khoản của bạn sẽ bị khóa tạm thời.
                                    ";
                        $title = 'Cảnh Báo Tài Khoản Sẽ Bị Khóa Sau 24h';
                        $title_owen = 'Cảnh Báo Tài Khoản Sẽ Bị Khóa Sau 24h';
                        $data = [
                            'arr_object_id' => $arr_object_id,
                            'player_id' => $playerId,
                            'json_data' => $json_data,
                            'object_id' => $value->id,
                            'content' => $content,
                            'created_by' => 0,
                            'title' => $title,
                            'title_owen' => $title_owen,
                            'type' => null,
                        ];
                        $dataNoti[] = $data;
                    }
                }
            }
        }
        echo $count;
        $this->sendNotiOnesignalMutile($dataNoti, Config::get('constant')['noti_send_warning']);
    }

    public function updateStatusCustomerWarningOld(){
        return;
        $count = 0;
        $dtClient = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.email as email',
                'tbl_clients.id as id'
            )
            ->whereHas('history_customer_wraning_mail',function ($query){
                $query->whereRaw('TIMESTAMPDIFF(HOUR,date,"'.NOW().'") >= 24');
            })
            ->where(function ($query){
                $query->where('tbl_clients.active',1);
            })
            ->get();
        $arr_object_id = [];
        if(!empty($dtClient)) {
            foreach ($dtClient as $key => $value) {
                $value->active = 0;
                $success = $value->save();
                if ($success){
                    $count++;
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
                    ->where('tbl_clients.id', $value->id)
                    ->get()->toArray();
                if (!empty($dtCustomer)) {
                    $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                }
            }
        }
        echo $count;
        if (!empty($arr_object_id)){
            $arr_object_id = array_values($arr_object_id);
            ConnectPusher('',$arr_object_id,'update-active-customer');
        }
    }

    public function createTransactionCertificate(){
        $dtTransaction = Transaction::where(function ($query){
            $query->where('status',1);
            $query->doesntHave('certificate');
        })->get();
        $count = 0;
        if (!empty($dtTransaction)){
            foreach ($dtTransaction as $key => $value){
                $transactionCertificate = new TransactionCertificate();
                $transactionCertificate->transaction_id = $value->id;
                $transactionCertificate->customer_id = $value->customer_id;
                $transactionCertificate->category_card_id = $value->category_card->id;
                $transactionCertificate->code_certificate = get_option('prefix_certificate').generateRandomStringOld('',14);
                $transactionCertificate->content = get_option('Certificate');
                $transactionCertificate->total = $value->total;
                $transactionCertificate->username = $value->customer->referral_code;
                $transactionCertificate->title_logo_certificate = get_option('title_logo_certificate');
                $transactionCertificate->sub_title_logo_certificate = get_option('sub_title_logo_certificate');
                $transactionCertificate->name_certificate = get_option('name_certificate');
                $transactionCertificate->position_certificate = get_option('position_certificate');
                $transactionCertificate->save();
                $count ++;
            }
        }
        echo $count;
    }
}
