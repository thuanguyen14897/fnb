<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\TransactionBillResource;
use App\Http\Resources\TransactionDayItemResource;
use App\Models\Clients;
use App\Models\Payment;
use App\Models\Service;
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

class TransactionBillController extends AuthController
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
        $service_search = $this->request->input('service_search');


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

        $query = TransactionBill::with(['customer' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            }])
            ->with(['transaction' => function ($q) {
                $q->select('id', 'reference_no', 'date');
            }])
            ->with(['partner' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
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
                        $query->where('tbl_transaction_bill.id', 0);
                    }
                } else {
                    $query->where('tbl_transaction_bill.id', 0);
                }
            }
        }
        if ($status_search != -1) {
            if ($status_search == -2){
                $query->whereIn('status', [
                    Config::get('constant')['status_transaction_bill_request'],
                    Config::get('constant')['status_transaction_bill_approve'],
                ]);
            } else {
                $query->where('status', $status_search);
            }
        }
        if (!empty($customer_search)){
            $query->where('customer_id', $customer_search);
        }
        if (!empty($partner_search)){
            $query->where('partner_id', $partner_search);
        }
        if (!empty($date_search)){
            $query->whereBetween('date', [$start_date, $end_date]);
        }
        if (!empty($service_search)){
            $query->where('service_id',$service_search);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        $allServiceIds = $data->pluck('service_id')->unique()->values()->toArray();
        $this->requestService = clone $this->request;
        $this->requestService->merge(['service_id' => $allServiceIds]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbServiceService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);

        $data->transform(function ($item) use ($services) {
            $service = $services->where('id', $item->service_id)->first();
            $item->service = $service;
            return $item;
        });

        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtCustomer = $value->customer ?? null;
                if (!empty($dtCustomer)){
                    $dtImage = !empty($dtCustomer->avatar) ? env('STORAGE_URL').'/'.$dtCustomer->avatar : null;
                    $data[$key]['customer']['avatar_new'] = $dtImage;
                } else {
                    $data[$key]->customer = null;
                }

                $dtPartner = $value->partner ?? null;
                if (!empty($dtPartner)){
                    $dtImage = !empty($dtPartner->avatar) ? env('STORAGE_URL').'/'.$dtPartner->avatar : null;
                    $data[$key]['partner']['avatar_new'] = $dtImage;
                } else {
                    $data[$key]->partner = null;
                }
            }
        }
        $total = TransactionBill::count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function countAll(){
        $customer_search = $this->request->input('customer_search') ?? 0;
        $partner_search = $this->request->input('partner_search') ?? 0;
        $date_search = $this->request->input('date_search') ?? null;
        $date_search_end = $this->request->input('date_search_end') ?? null;
        $service_search = $this->request->input('service_search');
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

        $query = TransactionBill::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }])->where('id','!=',0);
        if (($customer_search)){
            $query->where('customer_id', $customer_search);
        }
        if (!empty($date_search)){
            $query->whereBetween('date', [$start_date, $end_date]);
        }
        $query->whereIn('status', [
            Config::get('constant')['status_transaction_bill_request'],
            Config::get('constant')['status_transaction_bill_approve'],
        ]);
        if (!empty($service_search)){
            $query->where('service_id',$service_search);
        }
        if (!empty($partner_search)){
            $query->where('partner_id', $partner_search);
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
                        $query->where('tbl_transaction_bill.id', 0);
                    }
                } else {
                    $query->where('tbl_transaction_bill.id', 0);
                }
            }
        }
        $follow = $query->count();

        $arr = getListStatusTransactionBill();
        foreach ($arr as $key => $value) {
            $status = $value['id'];
            $query = TransactionBill::where('id','!=',0);
            if (($customer_search)){
                $query->where('customer_id', $customer_search);
            }
            if (!empty($date_search)){
                $query->whereBetween('date', [$start_date, $end_date]);
            }
            if (!empty($service_search)){
                $query->where('service_id',$service_search);
            }
            if (!empty($partner_search)){
                $query->where('partner_id', $partner_search);
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
                            $query->where('tbl_transaction_bill.id', 0);
                        }
                    } else {
                        $query->where('tbl_transaction_bill.id', 0);
                    }
                }
            }
            $query->where('status',$status);
            $arr[$key]['count'] = $query->count();
        }

        return response()->json([
            'follow' => $follow,
            'arr' => $arr,
            'result' => true,
            'message' => 'Thành công'
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
        $dtData = TransactionBill::find($id);
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại hóa đơn';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $dtData->delete();
            $dtData->payment()->delete();
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
        $type = $this->request->input('type') ?? 1;
        $partner_id = $this->request->client->id ?? 0;
        $status_search = $this->request->input('status_search');
        $customer_search = $this->request->input('customer_search');
        $search = $this->request->input('search') ?? null;
        $year_month = $this->request->input('year_month') ?? date('Y-m');
        //admin
        $cron = $this->request->input('cron') ?? 0;
        $service_id = $this->request->input('service_id') ?? 0;
        $date_search = $this->request->input('date_search') ?? null;
        if (!empty($date_search)){
            $date_search = explode(' - ',$date_search);
            $start_date = to_sql_date($date_search[0],true);
            $end_date = to_sql_date($date_search[1],true);
        } else {
            $start_date = null;
            $end_date = null;
        }
        //end
        $query = TransactionBill::with(['customer' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            }])
            ->with(['transaction' => function ($q) {
                $q->select('id', 'reference_no', 'date');
            }])
            ->with(['partner' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            }])
            ->with(['payment' => function ($q) {
                $q->select('id', 'reference_no', 'date', 'payment_mode_id', 'total', 'payment', 'note','transaction_bill_id','status');
                $q->with(['payment_mode' => function ($inst) {
                    $inst->select('id', 'name','code','image');
                }]);
            }])
            ->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('reference_no', 'like', "%$search%");
            });
        }
        $query->where(function ($q) use ($status_search,$customer_search,$partner_id,$service_id,$start_date,$end_date,$date_search,$cron,$type,$year_month){
            if ($status_search != -1) {
                $status_search = is_array($status_search) ? $status_search : [$status_search];
                $q->whereIn('status', $status_search);
            }
            if (!empty($customer_search)) {
                $q->where('customer_id', $customer_search);
            }
            if (empty($cron)) {
                $q->where(function ($q) use ($partner_id,$type) {
                    if ($type == 1){
                        //đối tác
                        $q->where('partner_id', $partner_id);
                    } else {
                        //thành viên
                        $q->where('customer_id', $partner_id);
                    }
                });
            }

            if (!empty($service_id)){
                $q->where('service_id',$service_id);
            }
            if (!empty($date_search)){
                $q->whereBetween('date', [$start_date, $end_date]);
            }
            if (!empty($year_month)){
                $q->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?",[$year_month]);
            }
        });
        $query->orderByRaw("id desc");
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);
        //gian hàng
        $allServiceIds = $dtData->pluck('service_id')->unique()->values()->toArray();
        $this->requestService = clone $this->request;
        $this->requestService->merge(['service_id' => $allServiceIds]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbServiceService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);
        $dtData->getCollection()->transform(function ($item) use ($services) {
            $service = $services->where('id', $item->service_id)->first();
            $item->service = $service;
            return $item;
        });

        //end
        $collection = TransactionBillResource::collection($dtData);
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
        $dtData = TransactionBill::with(['customer' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar','membership_level','active_limit_private','radio_discount_private');
            }])
            ->with(['transaction' => function ($q) {
                $q->select('id', 'reference_no', 'date');
            }])
            ->with(['partner' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            }])
            ->with(['payment' => function ($q) {
                $q->select('id', 'reference_no', 'date', 'payment_mode_id', 'total', 'payment', 'note','transaction_bill_id','status');
                $q->with(['payment_mode' => function ($inst) {
                    $inst->select('id', 'name','code','image');
                }]);
            }])
            ->find($id);
        //gian hàng
        $allServiceIds = $dtData->service_id ?? 0;
        $this->requestService = clone $this->request;
        $this->requestService->merge(['service_id' => $allServiceIds]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbServiceService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);
        $service = $services->where('id', $dtData->service_id)->first();
        $dtData->service = $service;
        //end
        if (!empty($dtData)){
            $percent = 0;
            $name_level = null;
            $dtCustomer = $dtData->customer ?? null;
            if (!empty($dtCustomer)){
                if ($dtCustomer->active_limit_private == 1){
                    $percent = $dtClient->radio_discount_private ?? 0;
                } else {
                    if (!empty($dtCustomer->membership_level)) {
                        $dataLevelData = $this->fnbAdminService->getMemberShipLevel($dtCustomer->membership_level);
                        if (!empty($dataLevelData['result'])) {
                            $dataLevel = $dataLevelData['data'][0];
                            if (!empty($dataLevel)) {
                                $percent = $dataLevel['radio_discount'] ?? 0;
                                $name_level = $dataLevel['name'] ?? null;
                            }
                        }
                    }
                }
                $dtImage = !empty($dtCustomer->avatar) ? env('STORAGE_URL').'/'.$dtCustomer->avatar : null;
                $dtData->customer->avatar_new = $dtImage;
                $dtData->customer->membership = [
                    'membership_level' => $name_level,
                    'percent' => $percent
                ];
            } else {
                $dtData->customer = null;
            }

            $dtPartner = $dtData->partner ?? null;
            if (!empty($dtPartner)){
                $dtImage = !empty($dtPartner->avatar) ? env('STORAGE_URL').'/'.$dtPartner->avatar : null;
                $dtData->partner->avatar_new = $dtImage;
            } else {
                $dtData->partner = null;
            }
        }
        $collection = TransactionBillResource::make($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy thông tin thành công'
        ]);
    }

    public function addTransaction(){
        $customer_id = $this->request->client->id ?? 0;
        $dataPost = $this->request->input();
        if (empty($customer_id)){
            return response()->json([
                'data' => [],
                'result' => false,
                'message' => 'Vui lòng đăng nhập để tiếp tục sử dung dịch vụ'
            ]);
        }
        if (empty($dataPost)){
            return response()->json([
                'data' => [],
                'result' => false,
                'message' => 'Không có dữ liệu để thêm hóa đơn'
            ]);
        }
        $validator = Validator::make($this->request->all(),
            [
                'service_id' => 'required',
                'customer_id' => 'required',
            ]
            , [
                'service_id.required' => 'Vui lòng chọn gian hàng!',
                'customer_id.required' => 'Vui lòng chọn khách hàng!',
            ]);
        if ($validator->fails()) {
            $data['data'] = [];
            $data['result'] = false;
            $data['message'] = $validator->errors()->all()[0];
            echo json_encode($data);
            die();
        }
        $app = $dataPost['app'] ?? 0;
        $this->requestService = clone $this->request;
        $this->requestService->merge(['id' => $dataPost['service_id'] ?? 0]);
        $responseService = $this->fnbServiceService->getDetail($this->requestService);
        $dataService = $responseService->getData(true);
        $dtService = collect($dataService['dtData'] ?? []);
        if (empty($dtService)){
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = 'Gian hàng không tồn tại!';
            return response()->json($data);
        }
        $dtClient = Clients::find($dataPost['customer_id']);
        if (empty($dtClient)){
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = 'Khách hàng không tồn tại!';
            return response()->json($data);
        }
        $percent = 0;
        if ($dtClient->active_limit_private == 1){
            $percent = $dtClient->radio_discount_private ?? 0;
        } else {
            if (!empty($dtClient->membership_level)) {
                $dataLevelData = $this->fnbAdminService->getMemberShipLevel($dtClient->membership_level);
                if (!empty($dataLevelData['result'])) {
                    $dataLevel = $dataLevelData['data'][0];
                    if (!empty($dataLevel)) {
                        $percent = $dataLevel['radio_discount'] ?? 0;
                    }
                }
            }
        }
        $membership_level_id = $dtClient->membership_level ?? 0;
        $service_id = $dataPost['service_id'] ?? 0;
        $customer_id_new = $dtClient->id;

        $dtTransactionDayItem = TransactionDayItem::where(function ($query) use ($service_id){
                $query->where('service_id',$service_id);
                $query->whereNotIn('status', [
                    Config::get('constant')['status_transaction_item_cancel'],
                    Config::get('constant')['status_transaction_item_finish'],
                ]);
            })
            ->whereHas('transaction_day', function ($q) {
                $q->whereDate('date', date('Y-m-d'));
            })
            ->whereHas('transaction', function ($q) use ($customer_id_new) {
                $q->where('customer_id', $customer_id_new);
                $q->whereNotIn('status', [
                    Config::get('constant')['status_transaction_cancel'],
                    Config::get('constant')['status_transaction_finish'],
                ]);
            })
            ->first();
        if ($app == 1) {
            if ($customer_id != $dtService['customer_id']) {
                $data['result'] = false;
                $data['data'] = [];
                $data['message'] = 'Bạn không thuộc chủ sở hữu của gian hàng đang tạo hóa đơn!';
                return response()->json($data);
            }
        }

        $percent = !empty($percent) ? $percent : 0;
        $total = $dataPost['total'] ?? 0;
        $transaction_id = $dtTransactionDayItem->transaction->id ?? 0;
        $transaction_day_item_id = $dtTransactionDayItem->id ?? 0;
        $date = date('Y-m-d H:i:s');
        $total = $this->fnbAdminService->getSetting($total);
        $discount = $this->fnbAdminService->getSetting($percent);
        $total_discount = ($total * $discount) / 100;
        $grand_total = $total - $total_discount;
        $note = $dataPost['note'] ?? null;
        $reference_no = $this->fnbAdminService->getOrderRef('transaction_bill')['reference_no'] ?? null;
        if (empty($reference_no)){
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = 'Có lỗi trong quá trình tạo mã hóa đơn, vui lòng thử lại!';
            return response()->json($data);
        }

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
            ->where('tbl_clients.id', $customer_id_new)
            ->get()->toArray();
        if (!empty($dtCustomer)) {
            $arr_object_id = array_merge($arr_object_id, $dtCustomer);
        }
        $arr_object_id = array_values($arr_object_id);

        try {
            $transactionBill = new TransactionBill();
            $transactionBill->reference_no = $reference_no;
            $transactionBill->date = $date;
            $transactionBill->customer_id = $customer_id_new;
            $transactionBill->transaction_id = $transaction_id;
            $transactionBill->transaction_day_item_id = $transaction_day_item_id;
            $transactionBill->service_id = $dtService['id'];
            $transactionBill->partner_id = $dtService['customer_id'];
            $transactionBill->total = $total;
            $transactionBill->discount = $discount;
            $transactionBill->total_discount = $total_discount;
            $transactionBill->grand_total = $grand_total;
            $transactionBill->note = $note;
            $transactionBill->app = 1;
            $transactionBill->created_by = $customer_id;
            $transactionBill->type_created = 2;
            $transactionBill->membership_level_id = $membership_level_id;
            $transactionBill->save();
            $this->fnbAdminService->updateOrderRef('transaction_bill');
            //thêm phiếu thanh toán
            $payment = new Payment();
            $payment->date = date('Y-m-d H:i:s');
            $payment->reference_no = $this->fnbAdminService->getOrderRef('payment')['reference_no'];
            $payment->customer_id = $customer_id_new;
            $payment->payment_mode_id = 2;
            $payment->transaction_bill_id = $transactionBill->id;
            $payment->created_by = $customer_id;
            $payment->type_create = 2;
            $payment->total = $grand_total;
            $payment->payment = $grand_total;
            $payment->note = 'Thanh toán hóa đơn';
            $payment->type = 1;
            $payment->status = 1;
            $payment->save();
            if ($payment) {
                $this->fnbAdminService->updateOrderRef('payment');
                $this->requestNoti = $this->request->duplicate(
                    [],
                    $this->request->only(['client'])
                );
                $transactionBill->data_customer = [
                    'id' => $transactionBill->customer->id,
                    'fullname' => $transactionBill->customer->fullname,
                    'phone' => $transactionBill->customer->phone,
                    'email' => $transactionBill->customer->email,
                ];
                $transactionBill->makeHidden(['customer']);
                $this->requestNoti->merge(['arr_object_id' => $arr_object_id]);
                $this->requestNoti->merge(['dtData' => $transactionBill]);
                $this->requestNoti->merge(['customer_id' => $customer_id_new]);
                $this->requestNoti->merge(['type' => 'customer']);
                $this->requestNoti->merge(['staff_id' => 0]);
                $this->requestNoti->merge(['type_noti' => 'remind_payment']);
//                $this->fnbNoti->addNoti($this->requestNoti);
            }
            //end
            DB::commit();
            $data['result'] = true;
            $data['data'] = $transactionBill;
            $data['message'] = 'Thêm hóa đơn thành công!';
            return response()->json($data);
        } catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListStatusTransaction(){
        $result = getListStatusTransaction();
        $data['result'] = true;
        $data['message'] = 'Lấy thông tin thành công';
        $data['data'] = $result;
        return response()->json($data);
    }

    public function countTransaction(){
        $service_id = $this->request->input('service_id') ?? 0;
        $type_search = $this->request->input('type_search') ?? 'all';
        $query = Transaction::where('id','!=',0);
        if (!empty($service_id)){
            $query->whereHas('transaction_day_item',function ($q) use ($service_id){
                $q->where('service_id',$service_id);
            });
        }
        if ($type_search == 'finish'){
            $query->where('status', Config::get('constant')['status_transaction_finish']);
        }
        $total = $query->count();
        $data['result'] = true;
        $data['message'] = 'Thành công';
        $data['data'] = $total;
        return response()->json($data);
    }

    public function changeStatus(){
        $transaction_id = $this->request->input('transaction_id');
        $status = $this->request->input('status');
        $noteStatus = $this->request->input('note');

        $transaction = TransactionBill::with('customer')->find($transaction_id);
        $index = getValueStatusTransactionBill($transaction->status,'index');
        $index_current = getValueStatusTransactionBill($status,'index');
        $status_current = $transaction->status;
        $arr = [Config::get('constant')['status_transaction_cancel']];
        if ($index_current < $index){
            if (!in_array($status,$arr)) {
                $data['result'] = false;
                $data['message'] = 'Không thể thay đổi trạng thái nhỏ hơn trạng thái hiện tại';
                return response()->json($data);
            }
        }

        if ($transaction->status == $this->request->status){
            $data['result'] = false;
            $data['message'] = 'Trạng thái đã được cập nhật vui lòng kiểm tra lại!';
            return response()->json($data);
        }
        $payment = $transaction->payment ?? null;
        $transactionDayItem = $transaction->transaction_day_item ?? null;
        $customer_id = $transaction->customer_id;

        if ($status == Config::get('constant')['status_transaction_bill_approve']) {
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
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
            $arr_object_id = array_values($arr_object_id);
        }

        DB::beginTransaction();
        try
        {
            $transaction->status = $status;
            $transaction->date_status = date('Y-m-d H:i:s');
            $transaction->staff_status = $this->request->input('staff_status');
            $transaction->save();

            if ($status == Config::get('constant')['status_transaction_bill_approve']) {
                if (!empty($payment)) {
                    $payment->status = 2;
                    $payment->save();

                    //gửi thông báo
                    $payment->data_customer = [
                        'id' => $payment->customer->id,
                        'fullname' => $payment->customer->fullname,
                        'phone' => $payment->customer->phone,
                        'email' => $payment->customer->email,
                    ];
                    $payment->makeHidden(['customer']);
                    $this->requestNoti = clone $this->request;
                    $this->requestNoti->merge(['type_noti' => 'change_status_payment']);
                    $this->requestNoti->merge(['arr_object_id' => $arr_object_id]);
                    $this->requestNoti->merge(['dtData' => $payment]);
                    $this->requestNoti->merge(['customer_id' => $customer_id]);
                    $this->requestNoti->merge(['type' => 'staff']);
                    $this->requestNoti->merge(['staff_id' => $this->request->input('staff_status')]);
//                    $this->fnbNoti->addNoti($this->requestNoti);
                }

                if (!empty($transactionDayItem)){
                    $transactionDayItem->status = Config::get('constant')['status_transaction_item_finish'];
                    $transactionDayItem->staff_status = $this->request->input('staff_status');
                    $transactionDayItem->date_status = date('Y-m-d H:i:s');
                    $transactionDayItem->save();
                }
            }
            DB::commit();
            $data['result'] = true;
            $data['data'] = $transaction;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListDataTransactionBill(){
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
        $search = $this->request->input('search') ?? null;
        $check = !empty($this->request->input('check')) ? $this->request->input('check') : 0; // loc trang thai
        $date_search = $this->request->input('date_search') ?? null;
        $query = TransactionDayItem::with('transaction')
            ->with('transaction_day')
            ->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->whereHas('transaction',function ($instance) use ($search) {
                    $instance->where('reference_no', 'like', "%$search%");
                });
            });
        }
        if (!empty($date_search)){
            $query->whereHas('transaction_day',function ($instance) use ($date_search) {
                $instance->whereDate('date', to_sql_date($date_search));
            });
        }
        $query->whereHas('transaction',function ($instance) use ($search) {
            $instance->whereNotIn('status', [
                Config::get('constant')['status_transaction_cancel'],
                Config::get('constant')['status_transaction_finish'],
            ]);
        });
        if (!empty($check)) {
            if ($check == 1) {
                $query->whereNotIn('status', [
                    Config::get('constant')['status_transaction_item_cancel'],
                    Config::get('constant')['status_transaction_item_finish'],
                ]);
            }
        }
        $query->where('partner_id', $customer_id);
        $query->orderByRaw("id desc");
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);

        //gian hàng
        $allServiceIds = $dtData->pluck('service_id')->unique()->values()->toArray();
        $this->requestService = clone $this->request;
        $this->requestService->merge(['service_id' => $allServiceIds]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbServiceService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);
        $dtData->getCollection()->transform(function ($item) use ($services) {
            $service = $services->where('id', $item->service_id)->first();
            $item->service = $service;
            $item->check_list = true;
            return $item;
        });
        //end
        $collection = TransactionDayItemResource::collection($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListService(){
        $customer_id = $this->request->input('customer_id') ?? 0;
        $partner_id = $this->request->client->id ?? 0;
        $this->requestService = clone $this->request;
        $this->requestService->merge(['partner_id' => $partner_id]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbServiceService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);

        $dtTransactionDayItem = TransactionDayItem::where(function ($query) use ($partner_id){
            $query->where('partner_id',$partner_id);
            $query->whereNotIn('status', [
                Config::get('constant')['status_transaction_item_cancel'],
                Config::get('constant')['status_transaction_item_finish'],
            ]);
        })
            ->whereHas('transaction_day', function ($q) {
                $q->whereDate('date', date('Y-m-d'));
            })
            ->whereHas('transaction', function ($q) use ($customer_id) {
                $q->where('customer_id', $customer_id);
                $q->whereNotIn('status', [
                    Config::get('constant')['status_transaction_cancel'],
                    Config::get('constant')['status_transaction_finish'],
                ]);
            })
            ->first();
        if (!empty($dtTransactionDayItem)){
            $this->requestService->merge(['id' => $dtTransactionDayItem->service_id]);
            $responseService = $this->fnbServiceService->getDetail($this->requestService);
            $dataService = $responseService->getData(true);
            $dataService = collect($dataService['dtData'] ?? []);
            if (!empty($dataService)){
                $dtServiceSelect = [
                    'id' => $dataService['id'],
                    'name' => $dataService['name'],
                    'image' => $dataService['image'],
                ];
            } else {
                $dtServiceSelect = null;
            }
        } else {
            if (count($services) > 0){
                $dtServiceSelect = [
                    'id' => $services[0]['id'],
                    'name' => $services[0]['name'],
                    'image' => $services[0]['image'],
                ];
            } else {
                $dtServiceSelect = null;
            }
        }
        return response()->json([
            'data' => $services,
            'dataSelected' => $dtServiceSelect,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function checkService(){
        $service_id = $this->request->input('service_id') ?? 0;
        $dtCheck = TransactionBill::where('service_id',$service_id)->first();
        $data['result'] = true;
        $data['data'] = $dtCheck;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }

    public function getListMonthTransaction(){
        $customer_id = $this->request->client->id ?? 0;
        $query = TransactionBill::where('id','!=',0);
        $query->where('customer_id', $customer_id);
        $query->select(DB::raw("DATE_FORMAT(date, '%m-%Y') as month_year"));
        $query->groupBy(DB::raw("DATE_FORMAT(date, '%m-%Y')"));
        $query->orderByRaw("DATE_FORMAT(date, '%m-%Y') desc");
        $dtData = $query->get();
        $data['result'] = true;
        $data['data'] = $dtData;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }
}
