<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\Transaction as TransactionResource;
use App\Http\Resources\TransactionCollection;
use App\Models\Clients;
use App\Models\Transaction;
use App\Models\TransactionDay;
use App\Models\TransactionDayItem;
use App\Services\AdminService;
use App\Services\NotiService;
use App\Services\ServiceService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends AuthController
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
        $date_search = $this->request->input('date_search') ?? null;
        $date_search_end = $this->request->input('date_search_end') ?? null;
        $status_search = $this->request->input('status_search');
        $service_search = $this->request->input('service_search');

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

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

        $query = Transaction::with(['customer' => function ($q) {
                $q->select('id', 'fullname', 'phone', 'email', 'avatar');
            }])
            ->with('transaction_day_item')
            ->where('id','!=',0);
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
                        $query->where('tbl_transaction.id', 0);
                    }
                } else {
                    $query->where('tbl_transaction.id', 0);
                }
            }
        }
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
        if ($status_search != -1) {
            if ($status_search == -2){
                $query->whereIn('status', [
                    Config::get('constant')['status_request'],
                    Config::get('constant')['status_start'],
                ]);
            } else {
                $query->where('status', $status_search);
            }
        }
        if (($customer_search)){
            $query->where('customer_id', $customer_search);
        }
        if (!empty($date_search)){
            $query->whereBetween('date_start', [$start_date, $end_date]);
        }
        if (!empty($date_search_end)){
            $query->whereBetween('date_end', [$start_date_end, $end_date_end]);
        }
        if (!empty($service_search)){
            $query->whereHas('transaction_day_item',function ($q) use ($service_search){
               $q->where('service_id',$service_search);
            });
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        $allServiceIds = $data->map(function ($item) {
            return $item->transaction_day_item->pluck('service_id')->toArray();
        })->flatten()->unique()->toArray();
        $this->requestService = clone $this->request;
        $this->requestService->merge(['service_id' => $allServiceIds]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbServiceService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);

        $data->transform(function ($item) use ($services) {
            $item->transaction_day_item->transform(function ($dayItem) use ($services) {
                $service = $services->where('id', $dayItem->service_id)->first();
                $dayItem->service = $service;
                return $dayItem;
            });
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
            }
        }
        $total = Transaction::count();

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
        if (!empty($date_search_end)){
            $date_search_end = explode(' - ',$date_search_end);
            $start_date_end = to_sql_date($date_search_end[0],true);
            $end_date_end = to_sql_date($date_search_end[1],true);
        } else {
            $start_date_end = null;
            $end_date_end = null;
        }

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $query = Transaction::with('customer')->where('id','!=',0);
        if (($customer_search)){
            $query->where('customer_id', $customer_search);
        }
        if (!empty($date_search)){
            $query->whereBetween('date_start', [$start_date, $end_date]);
        }
        if (!empty($date_search_end)){
            $query->whereBetween('date_end', [$start_date_end, $end_date_end]);
        }
        $query->whereIn('status', [
            Config::get('constant')['status_request'],
            Config::get('constant')['status_start'],
        ]);
        if (!empty($service_search)){
            $query->whereHas('transaction_day_item',function ($q) use ($service_search){
                $q->where('service_id',$service_search);
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
                        $query->where('tbl_transaction.id', 0);
                    }
                } else {
                    $query->where('tbl_transaction.id', 0);
                }
            }
        }
        $follow = $query->count();

        $arr = getListStatusTransaction();
        foreach ($arr as $key => $value) {
            $status = $value['id'];
            $query = Transaction::with('customer')->where('id','!=',0);
            if (($customer_search)){
                $query->where('customer_id', $customer_search);
            }
            if (!empty($date_search)){
                $query->whereBetween('date_start', [$start_date, $end_date]);
            }
            if (!empty($date_search_end)){
                $query->whereBetween('date_end', [$start_date_end, $end_date_end]);
            }
            if (!empty($service_search)){
                $query->whereHas('transaction_day_item',function ($q) use ($service_search){
                    $q->where('service_id',$service_search);
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
                            $query->where('tbl_transaction.id', 0);
                        }
                    } else {
                        $query->where('tbl_transaction.id', 0);
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
        $dtData = Transaction::find($id);
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại chuyến đi';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $dtData->delete();
            $dtData->transaction_day()->delete();
            $dtData->transaction_day_item()->delete();
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
        $check_start = $this->request->input('check_start') ?? 0;
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
        $query->where(function ($q) use ($status_search,$customer_search,$customer_id,$service_id,$start_date,$end_date,$start_date_end,$end_date_end,$date_search,$date_search_end,$check_cancel,$cron,$check_start){
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
                $q->whereNotIn('status',[Config::get('constant')['status_transaction_finish'], Config::get('constant')['status_transaction_cancel']]);
            }
            if (!empty($check_start)){
                $q->whereIn('status',[Config::get('constant')['status_request']]);
                $q->whereDate('date_start', '=', now()->toDateString());
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
        $dtData = Transaction::with('customer')
            ->with('transaction_day')
            ->with('transaction_day_item')
            ->find($id);
        //gian hàng
        $allServiceIds = $dtData->transaction_day_item->pluck('service_id')->unique()->toArray();
        $this->requestService = clone $this->request;
        $this->requestService->merge(['service_id' => $allServiceIds]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbServiceService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);
        $dtData->transaction_day_item->transform(function ($dayItem) use ($services) {
            $service = $services->where('id', $dayItem->service_id)->first();
            $dayItem->service = $service;
            return $dayItem;
        });
        $dtData->transaction_day->transform(function ($dayItem) use ($services) {
             $dayItem->transaction_day_item->transform(function ($item) use ($services) {
                $service = $services->where('id', $item->service_id)->first();
                $item->status = [
                    'status' => $item->status,
                    'name' => getValueStatusTransactionItem($item->status,'name'),
                    'color' => getValueStatusTransactionItem($item->status,'color'),
                    'date_status' => $item->date_status,
                    'note' => $item->note_status,
                ];
                $item->service = $service;
                return $item;
            });
            $dayItem->transaction_day_item = $dayItem->transaction_day_item->filter();
            return $dayItem;
        });
        //end
        $dtData->check_detail = true;
        $collection = TransactionResource::make($dtData);
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
                'message' => 'Không có dữ liệu để thêm giao dịch'
            ]);
        }

        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required',
                'date_start' => 'required',
                'date_end' => 'required',
            ]
            , [
                'name.required' => 'Vui lòng nhập tên lịch trình',
                'date_start.required' => 'Vui lòng nhập ngày bắt đầu',
                'date_end.required' => 'Vui lòng nhập ngày kết thúc',
            ]);
        if ($validator->fails()) {
            $data['data'] = [];
            $data['result'] = false;
            $data['message'] = $validator->errors()->all()[0];
            echo json_encode($data);
            die();
        }
        $name = $dataPost['name'] ?? null;
        $date_all = date('Y-m-d H:i:s');
        $date_start_post = $dataPost['date_start'] ?? null;
        $date_end_post = $dataPost['date_end'] ?? null;
        $date_start = to_sql_date($date_start_post);
        $date_end = to_sql_date($date_end_post);
        $note = $dataPost['note'] ?? null;
        $reference_no = $this->fnbAdminService->getOrderRef('transaction')['reference_no'];

        if ((strtotime($date_start) < strtotime(date('Y-m-d'))) || (strtotime($date_end) < strtotime(date('Y-m-d')))) {
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = 'Ngày lên lịch trình phải lớn hơn ngày hiện tại!';
            return response()->json($data);
        }

        if (strtotime($date_end) <= strtotime($date_start)) {
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = 'Ngày kết thúc phải lớn hơn ngày bắt đầu!';
            return response()->json($data);
        }

        $arrItems = [];
        $items = $dataPost['items'];
        if (empty($items)){
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = 'Vui lòng chọn data ngày lên!';
            return response()->json($data);
        }
        foreach ($items as $key => $value){
            $date = $value['date'] ?? null;
            $dayItem = $value['items'] ?? [];
            if (empty($date)){
                $data['result'] = false;
                $data['data'] = [];
                $data['message'] = 'Chi tiết ngày không được để trống!';
                return response()->json($data);
            }
//            if (empty($dayItem)){
//                $data['result'] = false;
//                $data['data'] = [];
//                $data['message'] = 'Vui lòng chọn dịch vụ cho ngày: '.$date. '!';
//                return response()->json($data);
//            }
            $date = to_sql_date($date);
            $arrDayItem = [];
            if (!empty($dayItem)) {
                foreach ($dayItem as $k => $v) {
                    $service_id = $v['service_id'] ?? 0;
                    $hour = $v['hour'] ?? null;
                    $note = $v['note'] ?? null;
                    $lat_location = $v['lat_location'] ?? null;
                    $lon_location = $v['lon_location'] ?? null;
                    $this->requestService = new Request();
                    $this->requestService->merge(['id' => $service_id]);
                    $this->requestService->merge(['client' => $this->request->client]);
                    $responseService = $this->fnbServiceService->getDetail($this->requestService);
                    $dataService = $responseService->getData(true);
                    $dtService = collect($dataService['dtData']);
                    if (count($dtService) <= 0) {
                        $data['result'] = false;
                        $data['data'] = [];
                        $data['message'] = 'Gian hàng không tồn tại!';
                        return response()->json($data);
                    }
                    if (empty($hour)) {
                        $data['result'] = false;
                        $data['data'] = [];
                        $data['message'] = 'Vui lòng chọn giờ cho gian hàng: ' . $dtService['name'] . '!';
                        return response()->json($data);
                    }
                    if (strtotime($hour) < strtotime(date('H:i'))) {
                        $data['result'] = false;
                        $data['data'] = [];
                        $data['message'] = 'Giờ không thể nhỏ hơn giờ hiện tại - gian hàng: ' . $dtService['name'] . '!';
                        return response()->json($data);
                    }
                    $latitude = $dtService['latitude'] ?? null;
                    $longitude = $dtService['longitude'] ?? null;
                    $partner_id = $dtService['customer_id'] ?? 0;
                    $arrDayItem[] = [
                        'service_id' => $service_id,
                        'hour' => $hour,
                        'note' => $note,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'partner_id' => $partner_id,
                        'lat_location' => $lat_location,
                        'lon_location' => $lon_location,
                    ];
                }
            }
//            if (empty($arrDayItem)){
//                $data['result'] = false;
//                $data['data'] = [];
//                $data['message'] = 'Không tồn tại dữ liệu gian hàng cho ngày: '._dthuan($date). '!';
//                return response()->json($data);
//            }
            $arrItems[] = [
                'date' => $date,
                'items' => $arrDayItem
            ];
        }

        if (empty($arrItems)){
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = 'Không có dữ liệu để thêm lịch trình!';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $transaction = new Transaction();
            $transaction->name = $name;
            $transaction->reference_no = $reference_no;
            $transaction->date = $date_all;
            $transaction->customer_id = $customer_id;
            $transaction->date_start = $date_start;
            $transaction->date_end = $date_end;
            $transaction->note = $note;
            $transaction->created_by = $customer_id;
            $transaction->type_created = 2;
            $transaction->save();
            if ($transaction){
                foreach ($arrItems as $item){
                    $dayItem = $item['items'] ?? [];
                    $transactionDay = new TransactionDay();
                    $transactionDay->transaction_id = $transaction->id;
                    $transactionDay->date = $item['date'];
                    $transactionDay->save();

                    if (!empty($dayItem)){
                        foreach ($dayItem as $value){
                            $transactionDayItem = new TransactionDayItem();
                            $transactionDayItem->transaction_id = $transaction->id;
                            $transactionDayItem->transaction_day_id = $transactionDay->id;
                            $transactionDayItem->service_id = $value['service_id'] ?? 0;
                            $transactionDayItem->hour = $value['hour'] ?? null;
                            $transactionDayItem->note = $value['note'] ?? null;
                            $transactionDayItem->latitude = $value['latitude'] ?? null;
                            $transactionDayItem->longitude = $value['longitude'] ?? null;
                            $transactionDayItem->partner_id = $value['partner_id'] ?? 0;
                            $transactionDayItem->lat_location = $value['lat_location'] ?? null;
                            $transactionDayItem->lon_location = $value['lon_location'] ?? null;
                            $transactionDayItem->save();
                        }
                    }

                }
                $this->fnbAdminService->updateOrderRef('transaction');
                DB::commit();
                $data['result'] = true;
                $data['data'] = [];
                $data['message'] = 'Lên lịch trình chuyến đi thành công!';
                return response()->json($data);
            } else {
                DB::rollBack();
                $data['result'] = false;
                $data['data'] = [];
                $data['message'] = 'Lên lịch trình chuyến đi thất bại!';
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
        $app = $this->request->input('app') ?? 0;

        if ($app == 1){
            $customer_id = $this->request->client->id ?? 0;
            if (empty($customer_id)){
                $data['result'] = false;
                $data['message'] = 'Vui lòng đăng nhập để tiếp tục sử dung dịch vụ';
                return response()->json($data);
            }
        }

        $transaction = Transaction::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }])->find($transaction_id);
        $index = getValueStatusTransaction($transaction->status,'index');
        $index_current = getValueStatusTransaction($status,'index');
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
        if ($app == 1){
            $customer_id = $this->request->client->id ?? 0;
            if ($transaction->customer_id != $customer_id){
                $data['result'] = false;
                $data['message'] = 'Chuyến đi không thuộc quyền của bạn, vui lòng kiểm tra lại!';
                return response()->json($data);
            }
        } else {
            $customer_id = $transaction->customer_id;
        }
        DB::beginTransaction();
        $partner_id = $transaction->transaction_day_item->pluck('partner_id')->unique()->toArray();

        $arr_object_id = [];
        $dtCustomer = Clients::select(
            'tbl_clients.fullname as name',
            'tbl_clients.id as object_id',
            'tbl_player_id.player_id as player_id',
            DB::raw("'owen' as 'object_type'")
        )
            ->leftJoin('tbl_player_id', function ($join) {
                $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                $join->on('tbl_player_id.object_type', '=', DB::raw("'owen'"));
            })
            ->whereIn('tbl_clients.id', $partner_id ?? [0])
            ->get()->toArray();
        if (!empty($dtCustomer)) {
            $arr_object_id = array_merge($arr_object_id, $dtCustomer);
        }
        $arr_object_id = array_values($arr_object_id);

        if (in_array($status,[Config::get('constant')['status_transaction_finish'],Config::get('constant')['status_start']])){
            $arr_object_id_admin = [];
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
                $arr_object_id_admin = array_merge($arr_object_id_admin, $dtCustomer);
            }
            $arr_object_id_admin = array_values($arr_object_id_admin);
            $transaction->customer->arr_object_id = $arr_object_id_admin;
        }

        try
        {
            if ($status == Config::get('constant')['status_transaction_finish']){
                $transaction->cancel_end = $this->request->input('cancel_end') ?? 0;
            }
            $transaction->status = $status;
            $transaction->note_status = !empty($noteStatus) ? $noteStatus : null;
            $transaction->date_status = date('Y-m-d H:i:s');
            $transaction->staff_status = $this->request->input('staff_status') ?? 0;
            $transaction->customer_status = $app == 1 ? $customer_id : 0;
            $transaction->save();

            if ($status == Config::get('constant')['status_transaction_cancel']) {
                //noti
                $this->requestNoti = clone $this->request;
                $this->requestNoti->merge(['arr_object_id' => $arr_object_id]);
                $this->requestNoti->merge(['dtData' => $transaction]);
                $this->requestNoti->merge(['customer_id' => $customer_id]);
                $this->requestNoti->merge(['type' => $app == 1 ? 'customer' : 'staff']);
                $this->requestNoti->merge(['type_noti' => 'change_status_transaction']);
                $this->requestNoti->merge(['staff_id' => $this->request->input('staff_status')]);
                $this->fnbNoti->addNoti($this->requestNoti);
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

    public function changeStatusItem(){
        $transaction_item_id = $this->request->input('transaction_id');
        $status = $this->request->input('status');
        $noteStatus = $this->request->input('note');

        $transaction = TransactionDayItem::with('transaction')->find($transaction_item_id);
        if (empty($transaction)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại chi tiết chuyến đi!';
            return response()->json($data);
        }

        if ($transaction->transaction->status == Config::get('constant')['status_transaction_finish']){
            $data['result'] = false;
            $data['message'] = 'Chuyến đi đã kết thúc không thể thay đổi trạng thái!';
            return response()->json($data);
        }

        $index = getValueStatusTransactionItem($transaction->status,'index');
        $index_current = getValueStatusTransactionItem($status,'index');
        $status_current = $transaction->status;
        $arr = [Config::get('constant')['status_transaction_item_cancel']];
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
        $customer_id = $transaction->customer_id;
        DB::beginTransaction();
        try
        {
            $transaction->status = $status;
            $transaction->note_status = !empty($noteStatus) ? $noteStatus : null;
            $transaction->date_status = date('Y-m-d H:i:s');
            $transaction->staff_status = $this->request->input('staff_status');
            $transaction->save();
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

    public function checkService(){
        $service_id = $this->request->input('service_id') ?? 0;
        $dtCheck = TransactionDayItem::where('service_id',$service_id)->first();
        $data['result'] = true;
        $data['data'] = $dtCheck;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }
}
