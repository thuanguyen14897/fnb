<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Notification;
use App\Http\Resources\Transaction as TransactionResource;
use App\Models\User;
use App\Traits\SocketTrait;
use App\Traits\UploadFile;
use App\Libraries\Pay2s;
use App\Services\TransactionPackageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Pay2sController extends AuthController
{
    use SocketTrait;
    public $fnbTransactionPackageService;

    public function __construct(Request $request, TransactionPackageService $fnbTransactionPackageService)
    {
        $this->opts = [];
        parent::__construct($request);
        DB::enableQueryLog();
        $opts = [
            'callbackUrl' => config('app.url').'/api/pay2s/resultPay2s',
        ];
        $this->opts = $opts;
        $this->pay2s = new Pay2s($opts);
        $this->fnbTransactionPackageService = $fnbTransactionPackageService;
    }

    public function resultPay2s()
    {
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        $paymentCode = $this->request->orderInfo;
        $resultCode = $this->request->resultCode;
        $payment_mode_id = $this->request->payment_mode_id;
        $transaction_package_id = $this->request->transaction_package_id;
        $this->request->merge(['id' => $transaction_package_id]);
        $response = $this->fnbTransactionPackageService->getDetail($this->request);
        $data = $response->getData(true);
        $dtData = $data['dtData'] ?? [];
        $arr_object_id = [];
        $customer_id = 0;
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = 'Giao dịch mua gói không tồn tại.';
            return response()->json($data);
        }
        if (!empty($dtData)) {
            $dtCustomer[] = [
                'name' => $dtData['data_customer']['fullname'],
                'object_id' => $dtData['customer_id'],
                'player_id' => null,
                'object_type' => 'customer',
            ];
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
            if (!empty($dtCustomer)){
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
        }
        if ($resultCode === "0") {
            if ($dtData['status'] == 1) {
                $this->request->merge(['id' => $dtData['id']]);
                $this->request->merge(['staff_status' => Config::get('constant')['user_admin']]);
                $response = $this->fnbTransactionPackageService->changeStatus($this->request);
                $dataRes = $response->getData(true);
                $data = $dataRes['data'];
                $dataResult['result'] = $data['result'] ?? false;
                $dataResult['transaction_package_id'] = $dtData['id'];
                $dataResult['reference_no'] = $dtData['reference_no'];
                $this->sendNotificationSocket([
                    'channels' => $arr_object_id,
                    'event' => 'check-payment',
                    'data' => $dataResult,
                    'db_name' => config('database.connections.mysql.database')
                ], 'change-status');
                $data['result'] = true;
                $data['message'] = $data['message'] ?? 'Thanh toán thành công.';
                return response()->json($data);
            } else {
                $data['result'] = false;
                $data['message'] = 'Giao dịch mua gói đã được thanh toán.';
                return response()->json($data);
            }

        } else {
            if (!empty($arr_object_id)) {
                $dataResult['result'] = false;
                $dataResult['transaction_package_id'] = $dtData['id'];
                $dataResult['reference_no'] = $dtData['reference_no'];
                $this->sendNotificationSocket([
                    'channels' => $arr_object_id,
                    'event' => 'check-payment',
                    'data' => $dataResult,
                    'db_name' => config('database.connections.mysql.database')
                ], 'change-status');
            }
            return response()->json([
                "result" => false,
                "errorCode" => $resultCode,
                "message" => 'Giao dịch mua gói hết hạn thanh toán.'
            ]);
        }
    }

    public function requestPaymentPay2s()
    {
        $dataPost = $this->request->input();
        $transaction_package_id = $dataPost['transaction_package_id'];
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $this->request->merge(['id' => $transaction_package_id]);
        $response = $this->fnbTransactionPackageService->getDetail($this->request);
        $data = $response->getData(true);
        $dtData = $data['dtData'] ?? [];
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = 'Giao dịch mua gói không tồn tại.';
            return response()->json($data);
        }
        if ($dtData['status'] == 2) {
            $data['result'] = false;
            $data['message'] = 'Giao dịch mua gói đã được thanh toán.';
            return response()->json($data);
        }
        $result = $this->pay2s->sendOrderToPay2s($dataPost);
        return response()->json($result);
    }
}
