<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Clients;
use App\Models\Payment;
use App\Models\ReferralLevel;
use App\Models\Transaction;
use App\Models\TransactionBill;
use App\Models\TransactionDayItem;
use App\Services\AdminService;
use App\Services\NotiService;
use App\Services\ServiceService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends AuthController
{
    use UploadFile;
    protected $fnbServiceService;
    protected $fnbAdminService;
    protected $fnbNoti;
    public function __construct(Request $request,ServiceService $fnbServiceService,AdminService $adminService,NotiService $notiService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbServiceService = $fnbServiceService;
        $this->fnbAdminService = $adminService;
        $this->fnbNoti = $notiService;
    }

    public function getList(){
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data",'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $customer_search = $this->request->input('customer_search') ?? 0;
        $partner_search = $this->request->input('partner_search') ?? 0;
        $date_search = $this->request->input('date_search') ?? null;
        $status_search = $this->request->input('status_search');
        $transaction_bill_search = $this->request->input('transaction_bill_search') ?? 0;


        if (!empty($date_search)){
            $date_search = explode(' - ',$date_search);
            $start_date = to_sql_date($date_search[0],true);
            $end_date = to_sql_date($date_search[1],true);
        } else {
            $start_date = null;
            $end_date = null;
        }

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }
        $storageUrl = config('app.storage_url');
        $query = Payment::with(['customer' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            }])
            ->with(['transaction_bill' => function ($q) use ($storageUrl) {
                $q->select('id', 'reference_no', 'date','partner_id');
                $q->with([
                    'partner' => function ($qr) use ($storageUrl) {
                        $qr->select('id', 'fullname', 'email','phone','type_client');
                        $qr->selectRaw(DB::raw("IF(avatar IS NOT NULL, CONCAT('$storageUrl/', avatar), NULL) as avatar"));
                    }
                ]);
            }])
            ->with(['payment_mode' => function ($q) {
                $q->select('id', 'name','code');
            }])
            ->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('reference_no', 'like', "%$search%");
                $q->orWhere('date', 'like', "%$search%");
                $q->orWhereHas('customer',function ($instance) use ($search) {
                    $instance->where('fullname', 'like', "%$search%");
                    $instance->orWhere('phone', 'like', "%$search%");
                });
                $q->orWhereHas('transaction_bill',function ($instance) use ($search) {
                    $instance->where('reference_no', 'like', "%$search%");
                });
            });
        }
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $this->requestWard = clone $this->request;
                $ListWard = $this->fnbAdminService->getWardUser($this->requestWard);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $ward_id = $ListWard['data'];
                        $query->whereHas('customer', function ($inst) use ($ward_id) {
                            $inst->whereIn('wards_id', $ward_id);
                        });
                    } else {
                        $query->where('tbl_payment.id', 0);
                    }
                } else {
                    $query->where('tbl_payment.id', 0);
                }
            }
        }
        if ($status_search != -1) {
            $query->where('status', $status_search);
        }
        if(!empty($transaction_bill_search)){
            $query->where('transaction_bill_id', $transaction_bill_search);
        }
        if (!empty($customer_search)){
            $query->where('customer_id', $customer_search);
        }
        if (!empty($partner_search)){
            $query->whereHas('transaction_bill',function ($instance) use ($partner_search) {
                $instance->where('partner_id', $partner_search);
            });
        }
        if (!empty($date_search)){
            $query->whereBetween('date', [$start_date, $end_date]);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtCustomer = $value->customer ?? null;
                if (!empty($dtCustomer)){
                    $dtImage = !empty($dtCustomer->avatar) ? env('STORAGE_URL').'/'.$dtCustomer->avatar : null;
                    $data[$key]['customer']['avatar_new'] = $dtImage;
                } else {
                    $data[$key]->customer = null;
                }
            }
        }
        $total = Payment::count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getDetail(){
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        if (!empty($client)){
            $dtImage = !empty($client->avatar) ? env('STORAGE_URL').'/'.$client->avatar : null;
            $client->avatar = $dtImage;
        }
        $data['result'] = true;
        $data['client'] = $client;
        $data['message'] = 'Lấy thông tin khách hàng thành công';
        return response()->json($data);
    }

    public function delete(){
        $id = $this->request->input('id') ?? 0;
        $dtData = Payment::find($id);
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại phiếu thanh toán';
            return response()->json($data);
        }
        $transactionBill = TransactionBill::find($dtData->transaction_bill_id);
        DB::beginTransaction();
        try {
            $dtData->delete();

            $transactionBill->status = Config::get('constant')['status_transaction_bill_request'];
            $transactionBill->type_staff_status = 0;
            $transactionBill->staff_status = 0;
            $transactionBill->date_status = null;
            $transactionBill->save();

            $dtData->transaction_bill()->delete();

            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('c_delete_true');
            return response()->json($data);
        }  catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListData(){
        $current_page = 1;
        $per_page = 10;
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page =$this->request->query('per_page');
        }
        $customer_id = $this->request->client->id ?? 0;
        $status_search = $this->request->input('status_search');
        $customer_search = $this->request->input('customer_search');
        $search = $this->request->input('search') ?? null;
        //admin
        $service_id = $this->request->input('service_id') ?? 0;
        $date_search = $this->request->input('date_search') ?? null;
        $date_search_end = $this->request->input('date_search_end') ?? null;
        if (!empty($date_search)){
            $date_search = explode(' - ',$date_search);
            $start_date = to_sql_date($date_search[0],true);
            $end_date = to_sql_date($date_search[1],true);
        } else {
            $start_date = null;
            $end_date = null;
        }
        if (!empty($date_search_end)){
            $date_search_end = explode(' - ',$date_search_end);
            $start_date_end = to_sql_date($date_search_end[0],true);
            $end_date_end = to_sql_date($date_search_end[1],true);
        } else {
            $start_date_end = null;
            $end_date_end = null;
        }
        //end
        //cron
        $check_cancel = $this->request->input('check_cancel') ?? 0;
        $cron = $this->request->input('cron') ?? 0;
        //
        $query = Transaction::with('customer')
            ->with('transaction_day_item')
            ->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('reference_no', 'like', "%$search%");
            });
        }
        $query->where(function ($q) use ($status_search,$customer_search,$customer_id,$service_id,$start_date,$end_date,$start_date_end,$end_date_end,$date_search,$date_search_end,$check_cancel,$cron){
            if ($status_search != -1) {
                $status_search = is_array($status_search) ? $status_search : [$status_search];
                $q->whereIn('status', $status_search);
            }
            if (empty($customer_search)) {
                //trong admin không cần lọc theo customer id
                if (empty($cron)) {
                    $q->where(function ($q) use ($customer_id) {
                        $q->where('customer_id', $customer_id);
//                    $q->orWhereHas('transaction_day_item',  function ($instance) use ($customer_id){
//                        $instance->where('s',$customer_id);
//                    });
                    });
                }
            }
            if (!empty($service_id)){
                $q->whereHas('transaction_day_item',  function ($instance) use ($service_id){
                    $instance->where('service_id',$service_id);
                });
            }
            if (!empty($date_search)){
                $q->whereBetween('date_start', [$start_date, $end_date]);
            }
            if (!empty($date_search_end)){
                $q->whereBetween('date_end', [$start_date_end, $end_date_end]);
            }
            if (!empty($check_cancel)){
                $q->whereDate('date_end', '<', now()->toDateString());
                $q->where('status', '!=',Config::get('constant')['status_transaction_finish']);
            }
        });
        $query->orderByRaw("id desc");
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);
        //gian hàng
        $allServiceIds = $dtData->map(function ($item) {
            return $item->transaction_day_item->pluck('service_id')->toArray();
        })->flatten()->unique()->toArray();
        $this->requestService = clone $this->request;
        $this->requestService->merge(['service_id' => $allServiceIds]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbServiceService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);
        $dtData->getCollection()->transform(function ($item) use ($services) {
            $item->transaction_day_item->transform(function ($dayItem) use ($services) {
                $service = $services->where('id', $dayItem->service_id)->first();
                $dayItem->service = $service;
                return $dayItem;
            });
            return $item;
        });

        //end
        $collection = new TransactionCollection($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListDataDetail($id = 0){
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        $dtData = Payment::with(['customer' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            }])
            ->with(['transaction_bill' => function ($q) {
                $q->select('id', 'reference_no', 'date');
            }])
            ->with(['payment_mode' => function ($q) {
                $q->select('id', 'name','code','image');
            }])
            ->find($id);
        if (!empty($dtData)){
            $dtCustomer = $dtData->customer ?? null;
            if (!empty($dtCustomer)){
                $dtImage = !empty($dtCustomer->avatar) ? env('STORAGE_URL').'/'.$dtCustomer->avatar : null;
                $dtData->customer->avatar_new = $dtImage;
            } else {
                $dtData->customer = null;
            }
        }
        return response()->json([
            'data' => $dtData,
            'result' => true,
            'message' => 'Lấy thông tin thành công'
        ]);
    }

    public function changeStatus(){
        $id = $this->request->input('payment_id') ?? 0;
        $app = $this->request->input('app') ?? 0;
        if ($app == 1){
            $partner_id = $this->request->client->id ?? 0;
            if (empty($partner_id)){
                $data['result'] = false;
                $data['data'] = [];
                $data['message'] = 'Vui lòng đăng nhập để sử dụng tính năng!.';
                return response()->json($data);
            }
            $type_staff_status = 2;
            $staff_status = $partner_id;
            $type = 'customer';
        } else {
            $partner_id = 0;
            $type_staff_status = 1;
            $staff_status = $this->request->input('staff_status') ?? 0;
            $type = 'staff';
        }
        $payment = Payment::find($id);
        $transactionBill = TransactionBill::find($payment->transaction_bill_id);
        $customer_id = $payment->customer_id;
        if (in_array($transactionBill->status, [
            Config::get('constant')['status_transaction_bill_cancel'],
        ])) {
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = 'Hóa đơn đã hủy không thể thực hiện.';
            return response()->json($data);
        }

        $dtParent = ReferralLevel::where('customer_id','=',$customer_id)->first();
        $parent_id = $dtParent['parent_id'] ?? 0;
        $dtCheckPayment = Payment::where('customer_id','=',$customer_id)
            ->where('status','=',2)
            ->first();

        $transactionDayItem = $transactionBill->transaction_day_item ?? null;

        DB::beginTransaction();
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
            ->where('tbl_clients.id', $customer_id)
            ->get()->toArray();
//        if (!empty($dtCustomer)) {
//            $arr_object_id = array_merge($arr_object_id, $dtCustomer);
//        }

        $dtPartner = Clients::select(
            'tbl_clients.fullname as name',
            'tbl_clients.id as object_id',
            'tbl_player_id.player_id as player_id',
            DB::raw("'owen' as 'object_type'")
        )
            ->leftJoin('tbl_player_id', function ($join) {
                $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
            })
            ->where('tbl_clients.id', $transactionBill->partner_id)
            ->get()->toArray();
        if (!empty($dtPartner)) {
            $arr_object_id = array_merge($arr_object_id, $dtPartner);
        }

        $arr_object_id = array_values($arr_object_id);
        try
        {
            if ($payment->status == 1){
                $payment->status = 2;
                $payment->type_staff_status = $type_staff_status;
                $payment->staff_status = $staff_status;
                $payment->date_status = date('Y-m-d H:i:s');
                $payment->save();

                //cập nhật trạng thái thanh toán cho hóa đơn
                $transactionBill->status = Config::get('constant')['status_transaction_bill_approve'];
                $transactionBill->type_staff_status = $type_staff_status;
                $transactionBill->staff_status = $staff_status;
                $transactionBill->date_status = date('Y-m-d H:i:s');
                $transactionBill->save();

                if (!empty($transactionDayItem)){
                    $transactionDayItem->status = Config::get('constant')['status_transaction_item_finish'];
                    $transactionDayItem->staff_status = $this->request->input('staff_status');
                    $transactionDayItem->date_status = date('Y-m-d H:i:s');
                    $transactionDayItem->save();
                }

                //gửi thông báo
                $payment->data_customer = [
                    'id' => $payment->customer->id,
                    'fullname' => $payment->customer->fullname,
                    'phone' => $payment->customer->phone,
                    'email' => $payment->customer->email,
                ];
                $payment->makeHidden(['customer']);

                $payment->data_transaction_bill = [
                    'id' => $payment->transaction_bill->id,
                    'reference_no' => $payment->transaction_bill->reference_no,
                ];
                $payment->makeHidden(['transaction_bill']);

                $this->requestNoti = clone $this->request;
                $this->requestNoti->merge(['type_noti' => 'change_status_payment']);
                $this->requestNoti->merge(['arr_object_id' => $arr_object_id]);
                $this->requestNoti->merge(['dtData' => $payment]);
                $this->requestNoti->merge(['customer_id' => $partner_id]);
                $this->requestNoti->merge(['type' => $type]);
                $this->requestNoti->merge(['staff_id' => $this->request->input('staff_status')]);
                $this->fnbNoti->addNoti($this->requestNoti);

                //công điểm khi giới thiệu thành công
                if (!empty($parent_id) && empty($dtCheckPayment)){
                    changePoint($payment->id, 'payment',$this->request->input('staff_status') ?? 0,$parent_id);
                }

                checkCustomerCommission($customer_id,$payment->id);

                DB::commit();
                $data['result'] = true;
                $data['data'] = $payment;
                $data['message'] = lang('Thanh toán thành công');
                return response()->json($data);
            } else {
                $data['result'] = false;
                $data['data'] = [];
                $data['message'] = 'Phiếu thu đã được thanh toán.';
                return response()->json($data);
            }
        } catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

}
