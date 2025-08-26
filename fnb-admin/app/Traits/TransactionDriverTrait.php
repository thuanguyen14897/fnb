<?php

namespace App\Traits;

use App\Models\TransactionDriver;
use App\Models\CategoryCarDetail;
use App\Models\Driver;
use App\Models\User;
use App\Models\Clients;
use App\Models\Notification;
use App\Models\TransactionDriverPusher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

trait TransactionDriverTrait{

    public function autoAcceptTransaction($transaction_id = 0,$alepay = false)
    {
        $data = [];
        $request = new Request();
        $customer_id = !empty($request->client) ? $request->client->id : 0;
        $dtTransaction = TransactionDriver::find($transaction_id);
        if (!empty($dtTransaction) && $dtTransaction->status == Config::get('constant')['status_request_driver']) {
            $dtTransactionRoute = $dtTransaction->route_new;
            $lat = $dtTransactionRoute->lat_start;
            $lon = $dtTransactionRoute->lng_start;
            $amount = $dtTransaction->amount;
            $amount = 0;
            $category_car_detail_id = $dtTransaction->category_car_detail_id;
            $categoryCarDetail = CategoryCarDetail::find($category_car_detail_id);
            $type_car = $categoryCarDetail->category_car->type;
            $dtPayment = $dtTransaction->payment;
            $type_payment_mode = $dtPayment->payment_mode->type;
            $orderBy = 'distance asc';
            $dtDriver = Driver::select('tbl_driver.*',
                DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"))
                ->where(function ($query) use ($lat, $lon, $type_car, $customer_id, $type_payment_mode, $amount,$category_car_detail_id) {
                    if (!empty($lat) && !empty($lon)) {
                        $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null)"),
                            '!=', null);
                        $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),
                            '>=', 0);
                        $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),
                            '<=', 10);
                    }
                    $query->where('active', 1);
                    $query->where('status', 1);
                    $query->where('auto_accpet', 1);
                    $query->where('verify_phone', 1);
                    $query->where('status_cccd', 1);
                    $query->where('status_judicial_record', 1);
                    $query->where('status_confirm_conduct', 1);
                    $query->where('status_health_certificate', 1);
                    $query->where('status_certificate_hiv', 1);
                    $query->whereHas('service_register', function ($q) use ($category_car_detail_id) {
                        $q->where('category_car_detail_id', $category_car_detail_id);
                    });
                    $query->doesntHave('transaction_driver', 'and', function ($q) use ($customer_id) {
                        $q->where(function ($instance) use ($customer_id) {
                            $instance->whereIn('status',
                                [
                                    Config::get('constant')['status_approve_driver'],
                                    Config::get('constant')['status_start_driver']
                                ]);
                            $instance->orWhere(function ($ins) use ($customer_id) {
                                // hủy chuyến của khách này thì 15 phút sau mới dc nhân chuyến từ khách hàng này
                                $ins->whereIn('status', [Config::get('constant')['status_driver_cancel_driver']]);
                                $ins->where('customer_id', $customer_id);
                                $ins->where(DB::raw('ADDDATE(date, INTERVAL ' . get_option('driver_cancel_minute') . ' MINUTE)'),
                                    '>=', date('Y-m-d H:i:s'));
                            });
                        });
                    });
                    if ($type_car == 1) {
                        $query->whereHas('driving_liscense_bike', function ($q) {
                            $q->where("status", 1);
                        });
                    } else {
                        $query->whereHas('driving_liscense', function ($q) {
                            $q->where("status", 1);
                        });
                    }
                    if ($type_payment_mode == 1) {
                        $query->where('account_balance', '>=', $amount);
                    }
                })
                ->orderByRaw($orderBy)
                ->first();
            if (empty($dtDriver)) {
                $resultDriver = $this->findDriver($transaction_id);
                if (empty($resultDriver['driver'])) {
                    $refund_money = 0;
                    $owner_refund_money = 0;
                    if (!empty($dtTransaction->payment)) {
                        $cancel_trip_id = 2;
                        $dtCancelTrip = DB::table('tbl_cancel_trip_driver')->where('id', $cancel_trip_id)->first();
                        if (!empty($dtCancelTrip)) {
                            $percent_guest_cancel = $dtCancelTrip->percent_guest_cancel;
                            $refund_money = $dtTransaction->payment->payment * $percent_guest_cancel / 100;

                            $percent_owen_cancel = $dtCancelTrip->percent_owen_cancel;
                            $owner_refund_money = $dtTransaction->payment->payment * $percent_owen_cancel / 100;
                        }
                    }
                    $dtPayment = !empty($dtTransaction->payment) ? $dtTransaction->payment : [];
                    if (!empty($dtPayment)){
                        if ($dtPayment->payment_mode->type == 1){
                            addPaySlip($dtTransaction->id);
                        } else {
                            $dataRefund = [];
                            $dataRefund['tokenKey'] = get_option('token_key');
                            $dataRefund['transactionCode'] = $dtPayment->note;
                            $dataRefund['merchantRefundCode'] = $dtTransaction->reference_no;
                            $dataRefund['refundAmount'] = $dtPayment->payment;
                            $dataRefund['reason'] = 'Hoàn tiền giao dịch '.$dtTransaction->reference_no;
                            $dataRefund['transaction_id'] = $dtTransaction->id;
                            getRefundTransaction($dataRefund);
                        }
                    }
                    TransactionDriver::where('id', $dtTransaction->id)
                        ->update([
                            'not_driver' => 1,
                            'refund_money' => $refund_money,
                            'owner_refund_money' => $owner_refund_money,
                            'status' => Config::get('constant')['status_system_cancel_driver'],
                            'date_status' => date('Y-m-d H:i:s'),
                            'staff_status' => Config::get('constant')['customer_kanow'],
                            'note_status' => 'Hệ thống hủy không tìm thấy tài xế'
                        ]);
                    $data['result'] = false;
                    $data['id'] = $transaction_id;
                    $data['status'] = -1;
                    $data['driver'] = 0;
                    $data['message'] = 'Không tìm thấy tài xế phù hợp!';
                } else {
                    $data['result'] = true;
                    $data['id'] = $transaction_id;
                    $data['status'] = -2;
                    $data['driver'] = 0;
                    $data['message'] = '';
                }
                if ($alepay){
                    return $data;
                } else {
                    return response()->json($data);
                }
            } else {
                $percent_app_customer = !empty($dtDriver->discount_app) ? $dtDriver->discount_app->percent : 0;
                $percent_customer = 100 - $percent_app_customer;
                $amount_rent_cost = $dtTransaction->amount;
                $revenue_customer = ($amount_rent_cost * $percent_customer) / 100;
                $status = Config::get('constant')['status_approve_driver'];
                $date_status = date('Y-m-d H:i:s');
                $staff_status = Config::get('constant')['customer_kanow'];
                TransactionDriver::where('id', $dtTransaction->id)
                    ->update([
                        'percent_customer' => $percent_customer,
                        'amount_rent_cost' => $amount_rent_cost,
                        'revenue_customer' => $revenue_customer,
                        'driver_id' => $dtDriver->id,
                        'status' => $status,
                        'date_status' => $date_status,
                        'staff_status' => $staff_status,
                        'auto_accpet' => 1,
                    ]);
                $data['result'] = true;
                $data['id'] = $dtTransaction->id;
                $data['status'] = $status;
                $data['driver'] = $dtDriver->id;
                $data['message'] = 'Tìm thấy tài xế phù hợp!';
                //tài xế
                $dtTransaction->driver_id = $dtDriver->id;
                $arr_object_id_driver = [];
                $arr_object_id = [];
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
                    ->where('tbl_driver.id', $dtDriver->id)
                    ->get()->toArray();
                if (!empty($dtDriver)) {
                    $arr_object_id_driver = array_merge($arr_object_id_driver, $dtDriver);
                }
                $arr_object_id_driver = array_values($arr_object_id_driver);
                ConnectPusher($dtTransaction, $arr_object_id_driver, 'auto-accpet-driver');
                // noti
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
                    ->where('transaction_id', $dtTransaction->id)
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
                    ->where('tbl_clients.id', $dtTransaction->customer_id)
                    ->get()->toArray();
                if (!empty($dtCustomer)) {
                    $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                }
                $arr_object_id = array_values($arr_object_id);
                Notification::notiBookDriverTransaction($dtTransaction->id,
                    Config::get('constant')['noti_approve_driver'], $customer_id, 2, $arr_object_id,$arr_object_id_driver);
            }
        }
        if ($alepay){
            return $data;
        } else {
            return response()->json($data);
        }
    }

    public function findDriver($transaction_id = 0,$alepay = false)
    {
        $request = new Request();
        $dtTransaction = TransactionDriver::find($transaction_id);
        $customer_id = !empty($request->client) ? $request->client->id : 0;
        if (!empty($dtTransaction) && $dtTransaction->status == Config::get('constant')['status_request_driver']) {
            $dtTransactionRoute = $dtTransaction->route_new;
            $lat = $dtTransactionRoute->lat_start;
            $lon = $dtTransactionRoute->lng_start;
            $amount = $dtTransaction->amount;
            $amount = 0;
            $category_car_detail_id = $dtTransaction->category_car_detail_id;
            $categoryCarDetail = CategoryCarDetail::find($category_car_detail_id);
            $categoryCarDetail->image = asset('storage/' . $categoryCarDetail->image);
            $dtTransaction->categoryCarDetail = $categoryCarDetail;
            $dtTransaction->fullname = $dtTransaction->customer->fullname;
            $type_car = $categoryCarDetail->category_car->type;
            $dtPayment = $dtTransaction->payment;
            $type_payment_mode = $dtPayment->payment_mode->type;
            $orderBy = 'distance asc';

            $countDriver = Driver::select('tbl_driver.*',
                DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"))
                ->where(function ($query) use (
                    $lat,
                    $lon,
                    $type_car,
                    $customer_id,
                    $type_payment_mode,
                    $amount,
                    $transaction_id,
                    $category_car_detail_id
                ) {
                    if (!empty($lat) && !empty($lon)) {
                        $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null)"),
                            '!=', null);
                        $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),
                            '>=', 0);
                        $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),
                            '<=', 10);
                    }
                    $query->where('active', 1);
                    $query->where('status', 1);
                    $query->where('verify_phone', 1);
                    $query->where('auto_accpet', 0);
                    $query->where('status_cccd', 1);
                    $query->where('status_judicial_record', 1);
                    $query->where('status_confirm_conduct', 1);
                    $query->where('status_health_certificate', 1);
                    $query->where('status_certificate_hiv', 1);
                    $query->whereHas('service_register', function ($q) use ($category_car_detail_id) {
                        $q->where('category_car_detail_id', $category_car_detail_id);
                    });
                    $query->doesntHave('transaction_driver', 'and', function ($q) use ($customer_id) {
                        $q->where(function ($instance) use ($customer_id) {
                            $instance->whereIn('status',
                                [
                                    Config::get('constant')['status_approve_driver'],
                                    Config::get('constant')['status_start_driver']
                                ]);
                            $instance->orWhere(function ($ins) use ($customer_id) {
                                // hủy chuyến của khách này thì 15 phút sau mới dc nhân chuyến từ khách hàng này
                                $ins->whereIn('status', [Config::get('constant')['status_driver_cancel_driver']]);
                                $ins->where('customer_id', $customer_id);
                                $ins->where(DB::raw('ADDDATE(date, INTERVAL ' . get_option('driver_cancel_minute') . ' MINUTE)'),
                                    '>=', date('Y-m-d H:i:s'));
                            });
                        });
                    });
                    $query->doesntHave('transaction_not_driver', 'and', function ($q) use ($transaction_id) {
                        $q->where('transaction_id', $transaction_id);
                    });
                    $query->doesntHave('transaction_driver_pusher');
                    if ($type_car == 1) {
                        $query->whereHas('driving_liscense_bike', function ($q) {
                            $q->where("status", 1);
                        });
                    } else {
                        $query->whereHas('driving_liscense', function ($q) {
                            $q->where("status", 1);
                        });
                    }
                    if ($type_payment_mode == 1) {
                        $query->where('account_balance', '>=', $amount);
                    }
                })
                ->count();

            if ($countDriver > 0) {
                $dtDriver = Driver::select('tbl_driver.*',
                    DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"))
                    ->where(function ($query) use (
                        $lat,
                        $lon,
                        $type_car,
                        $customer_id,
                        $type_payment_mode,
                        $amount,
                        $transaction_id,
                        $category_car_detail_id
                    ) {
                        if (!empty($lat) && !empty($lon)) {
                            $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null)"),
                                '!=', null);
                            $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),
                                '>=', 0);
                            $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),
                                '<=', 10);
                        }
                        $query->where('active', 1);
                        $query->where('status', 1);
                        $query->where('verify_phone', 1);
                        $query->where('auto_accpet', 0);
                        $query->whereHas('service_register', function ($q) use ($category_car_detail_id) {
                            $q->where('category_car_detail_id', $category_car_detail_id);
                        });
                        $query->doesntHave('transaction_driver', 'and', function ($q) use ($customer_id) {
                            $q->where(function ($instance) use ($customer_id) {
                                $instance->whereIn('status',
                                    [
                                        Config::get('constant')['status_approve_driver'],
                                        Config::get('constant')['status_start_driver']
                                    ]);
                                $instance->orWhere(function ($ins) use ($customer_id) {
                                    // hủy chuyến của khách này thì 15 phút sau mới dc nhân chuyến từ khách hàng này
                                    $ins->whereIn('status', [Config::get('constant')['status_driver_cancel_driver']]);
                                    $ins->where('customer_id', $customer_id);
                                    $ins->where(DB::raw('ADDDATE(date, INTERVAL ' . get_option('driver_cancel_minute') . ' MINUTE)'),
                                        '>=', date('Y-m-d H:i:s'));
                                });
                            });
                        });
                        $query->doesntHave('transaction_not_driver', 'and', function ($q) use ($transaction_id) {
                            $q->where('transaction_id', $transaction_id);
                        });
                        $query->doesntHave('transaction_driver_pusher');
                        if ($type_car == 1) {
                            $query->whereHas('driving_liscense_bike', function ($q) {
                                $q->where("status", 1);
                            });
                        } else {
                            $query->whereHas('driving_liscense', function ($q) {
                                $q->where("status", 1);
                            });
                        }
                        if ($type_payment_mode == 1) {
                            $query->where('account_balance', '>=', $amount);
                        }
                    })
                    ->orderByRaw($orderBy)
                    ->first();
                if (!empty($dtDriver)) {
                    //add vào pusher
                    $transactionDriverPusher = new TransactionDriverPusher();
                    $transactionDriverPusher->transaction_id = $transaction_id;
                    $transactionDriverPusher->driver_id = $dtDriver->id;
                    $transactionDriverPusher->save();
                    //tài xế
                    $dtTransaction->driver_id = $dtDriver->id;
                    $arr_object_id = [];
                    $dtDriverNew = Driver::select(
                        'tbl_driver.fullname as name',
                        'tbl_driver.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'driver' as 'object_type'")
                    )
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_driver.id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'driver'"));
                        })
                        ->where('tbl_driver.id', $dtDriver->id)
                        ->get()->toArray();
                    if (!empty($dtDriverNew)) {
                        $arr_object_id = array_merge($arr_object_id, $dtDriverNew);
                    }
                    $arr_object_id = array_values($arr_object_id);
                    ConnectPusher($dtTransaction, $arr_object_id, 'accpet-driver');
                    Notification::notiFindDriverTransaction($dtTransaction->id, Config::get('constant')['noti_not_driver'],
                        0, 2, $arr_object_id);
                    $data['result'] = true;
                    $data['driver'] = $dtDriver->id;
                    $data['message'] = '';
                    return $data;
                } else {
                    $refund_money = 0;
                    $owner_refund_money = 0;
                    if (!empty($dtTransaction->payment)) {
                        $cancel_trip_id = 2;
                        $dtCancelTrip = DB::table('tbl_cancel_trip_driver')->where('id', $cancel_trip_id)->first();
                        if (!empty($dtCancelTrip)) {
                            $percent_guest_cancel = $dtCancelTrip->percent_guest_cancel;
                            $refund_money = $dtTransaction->payment->payment * $percent_guest_cancel / 100;

                            $percent_owen_cancel = $dtCancelTrip->percent_owen_cancel;
                            $owner_refund_money = $dtTransaction->payment->payment * $percent_owen_cancel / 100;
                        }
                    }
                    $dtPayment = !empty($dtTransaction->payment) ? $dtTransaction->payment : [];
                    if (!empty($dtPayment)){
                        if ($dtPayment->payment_mode->type == 1){
                            addPaySlip($dtTransaction->id);
                        } else {
                            $dataRefund = [];
                            $dataRefund['tokenKey'] = get_option('token_key');
                            $dataRefund['transactionCode'] = $dtPayment->note;
                            $dataRefund['merchantRefundCode'] = $dtTransaction->reference_no;
                            $dataRefund['refundAmount'] = $dtPayment->payment;
                            $dataRefund['reason'] = 'Hoàn tiền giao dịch '.$dtTransaction->reference_no;
                            $dataRefund['transaction_id'] = $dtTransaction->id;
                            getRefundTransaction($dataRefund);
                        }
                    }
                    TransactionDriver::where('id', $dtTransaction->id)
                        ->update([
                            'not_driver' => 1,
                            'refund_money' => $refund_money,
                            'owner_refund_money' => $owner_refund_money,
                            'status' => Config::get('constant')['status_system_cancel_driver'],
                            'date_status' => date('Y-m-d H:i:s'),
                            'staff_status' => Config::get('constant')['customer_kanow'],
                            'note_status' => 'Hệ thống hủy không tìm thấy tài xế'
                        ]);
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
                        ->where('tbl_clients.id', $dtTransaction->customer_id)
                        ->get()->toArray();
                    if (!empty($dtCustomer)) {
                        $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                    }
                    //delete driver pusher transaction
                    $dtTransaction->transaction_driver_pusher()->delete();
                    ConnectPusher($dtTransaction, $arr_object_id, 'not-driver');
                    $data['result'] = true;
                    $data['driver'] = 0;
                    $data['message'] = '';
                    return $data;
                }
            } else {
                $refund_money = 0;
                $owner_refund_money = 0;
                if (!empty($dtTransaction->payment)) {
                    $cancel_trip_id = 2;
                    $dtCancelTrip = DB::table('tbl_cancel_trip_driver')->where('id', $cancel_trip_id)->first();
                    if (!empty($dtCancelTrip)) {
                        $percent_guest_cancel = $dtCancelTrip->percent_guest_cancel;
                        $refund_money = $dtTransaction->payment->payment * $percent_guest_cancel / 100;

                        $percent_owen_cancel = $dtCancelTrip->percent_owen_cancel;
                        $owner_refund_money = $dtTransaction->payment->payment * $percent_owen_cancel / 100;
                    }
                }
                $dtPayment = !empty($dtTransaction->payment) ? $dtTransaction->payment : [];
                if (!empty($dtPayment)){
                    if ($dtPayment->payment_mode->type == 1){
                        addPaySlip($dtTransaction->id);
                    } else {
                        $dataRefund = [];
                        $dataRefund['tokenKey'] = get_option('token_key');
                        $dataRefund['transactionCode'] = $dtPayment->note;
                        $dataRefund['merchantRefundCode'] = $dtTransaction->reference_no;
                        $dataRefund['refundAmount'] = $dtPayment->payment;
                        $dataRefund['reason'] = 'Hoàn tiền giao dịch '.$dtTransaction->reference_no;
                        $dataRefund['transaction_id'] = $dtTransaction->id;
                        getRefundTransaction($dataRefund);
                    }
                }
                TransactionDriver::where('id', $dtTransaction->id)
                    ->update([
                        'not_driver' => 1,
                        'refund_money' => $refund_money,
                        'owner_refund_money' => $owner_refund_money,
                        'status' => Config::get('constant')['status_system_cancel_driver'],
                        'date_status' => date('Y-m-d H:i:s'),
                        'staff_status' => Config::get('constant')['customer_kanow'],
                        'note_status' => 'Hệ thống hủy không tìm thấy tài xế'
                    ]);
                //delete driver pusher transaction
                $dtTransaction->transaction_driver_pusher()->delete();

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
                    ->where('tbl_clients.id', $dtTransaction->customer_id)
                    ->get()->toArray();
                if (!empty($dtCustomer)) {
                    $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                }
                ConnectPusher($dtTransaction, $arr_object_id, 'not-driver');
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
                    ->where('transaction_id', $dtTransaction->id)
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
                $arr_object_id = array_values($arr_object_id);
                Notification::notiNotDriverTransaction($dtTransaction->id, Config::get('constant')['noti_not_driver'], 0, 2,
                    $arr_object_id);
                $data['result'] = true;
                $data['driver'] = 0;
                $data['message'] = '';
                return $data;
            }
        } else {
            //delete driver pusher transaction
            $dtTransaction->transaction_driver_pusher()->delete();
            $data['result'] = false;
            $data['driver'] = 0;
            $data['message'] = 'Chuyến đã bị hủy bởi người đặt xe !';
            return $data;
        }
    }
}
