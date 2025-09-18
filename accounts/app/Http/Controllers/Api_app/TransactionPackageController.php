<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Clients;
use App\Models\CustomerPackage;
use App\Models\CustomerPackageHistory;
use App\Models\Package;
use App\Models\TransactionPackage;
use App\Services\NotiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\UploadFile;
use App\Services\AdminService;
use Illuminate\Support\Facades\Validator;

class TransactionPackageController extends AuthController
{
    use UploadFile;

    protected $fnbAdmin;
    protected $fnbNoti;

    public function __construct(Request $request, AdminService $adminService,NotiService $notiService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbAdmin = $adminService;
        $this->fnbNoti = $notiService;
    }

    public function getListTransactionPackage()
    {
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $customer_id = $this->request->input('customer_search') ?? 0;
        $package_search = $this->request->input('package_search') ?? 0;
        $status_search = $this->request->input('status_search') ?? 0;

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $query = TransactionPackage::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }])
        ->with(['package' => function ($q) {
             $q->select('id', 'name', 'number_day', 'total');
         }])
        ->where('id', '!=', 0);
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $this->requestWard = clone $this->request;
                $ListWard = $this->fnbAdmin->getWardUser($this->requestWard);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $ward_id = $ListWard['data'];
                        $query->whereHas('customer', function ($inst) use ($ward_id) {
                            $inst->whereIn('wards_id', $ward_id);
                        });
                    } else {
                        $query->where('tbl_transaction_package.id', 0);
                    }
                } else {
                    $query->where('tbl_transaction_package.id', 0);
                }
            }
        }
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%$search%");
                $q->whereHas('package', function ($inst) use ($search) {
                    $inst->where('name', 'like', "%$search%");
                });
            });
        }
        if (!empty($customer_id)){
            $query->where('customer_id',$customer_id);
        }
        if (!empty($package_search)){
            $query->where('package_id',$package_search);
        }
        if (!empty($status_search)){
            $query->where('status',$status_search);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtCustomer = $value->customer ?? null;
                if (!empty($dtCustomer)){
                    $dtImage = !empty($dtCustomer->avatar) ? env('STORAGE_URL').'/'.$dtCustomer->avatar : null;
                    $data[$key]['customer']['avatar_new'] = $dtImage;
                } else {
                    $data[$key]->customer = null;
                }
            }
        }
        $total = TransactionPackage::count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getDetail()
    {
        $reference_no = $this->request->input('reference_no') ?? null;
        $id = $this->request->input('id') ?? 0;
        if (!empty($reference_no)){
            $dtData = TransactionPackage::where('reference_no',$reference_no)->first();
        } else {
            $dtData = TransactionPackage::find($id);
        }
        if (!empty($dtData)) {
            $dtData->data_customer = [
                'id' => $dtData->customer->id,
                'fullname' => $dtData->customer->fullname,
                'email' => $dtData->customer->email,
                'phone' => $dtData->customer->phone,
            ];
            $dtData->makeHidden(['customer']);
            $dtImage = !empty($dtData->image) ? config('app.storage_url') . '/' . $dtData->image : null;
            $dtData->image = $dtImage;
        }
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }

    public function delete()
    {
        $id = $this->request->input('id') ?? 0;
        $dtData = TransactionPackage::find($id);
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn tại data';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $dtData->delete();
            $dtData->customer_package()->delete();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('c_delete_true');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListData()
    {
        $data = TransactionPackage::with(['customer' => function ($q) {
            $q->select('id', 'fullname', 'phone', 'email', 'avatar');
        }])->where('id', '!=', 0)->get();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtCustomer = $value->customer ?? null;
                if (!empty($dtCustomer)){
                    $dtImage = !empty($dtCustomer->avatar) ? env('STORAGE_URL').'/'.$dtCustomer->avatar : null;
                    $data[$key]['customer']['avatar_new'] = $dtImage;
                } else {
                    $data[$key]->customer = null;
                }
            }
        }
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function changeStatus(){
        $id = $this->request->input('id') ?? 0;
        $dtData = TransactionPackage::find($id);
        if (empty($dtData)){
            $data['data'] = [];
            $data['result'] = false;
            $data['message'] = 'Không tồn tại dữ liệu';
            return response()->json($data);
        }
        $customer_id = $dtData->customer_id;
        $dtClient = Clients::find($customer_id);
        $dtPackage = $dtData->package ?? null;
        $data_active = $dtClient->date_active ?? date('Y-m-d');

        DB::BeginTransaction();
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
        try {
            if ($dtData->status == 1){
                $dtData->status = 2;
                $dtData->type_staff_status = 1;
                $dtData->payment = $this->request->input('payment') ?? $dtData['grand_total'];
                $dtData->staff_status = $this->request->input('staff_status') ?? 0;
                $dtData->date_status = date('Y-m-d H:i:s');
                $dtData->check_pay2s = $this->request->input('check_pay2s') ?? 0;
                $dtData->save();

                $number_day = $dtData->number_day ?? 0;
                $data_active_new = date('Y-m-d',strtotime("+$number_day day",strtotime($data_active)));
                $dtClient->date_active = $data_active_new;
                $dtClient->save();

                $customerPackageOld = $dtClient->customer_package ?? null;
                if (!empty($customerPackageOld)){
                    $customerPackageHistory = new CustomerPackageHistory();
                    $customerPackageHistory->transaction_package_id = $customerPackageOld->transaction_package_id;
                    $customerPackageHistory->package_id = $customerPackageOld->package_id;
                    $customerPackageHistory->customer_id = $customerPackageOld->customer_id;
                    $customerPackageHistory->name = $customerPackageOld->name;
                    $customerPackageHistory->total = $customerPackageOld->total;
                    $customerPackageHistory->percent = $customerPackageOld->percent;
                    $customerPackageHistory->grand_total = $customerPackageOld->grand_total;
                    $customerPackageHistory->number_day = $customerPackageOld->number_day;
                    $customerPackageHistory->check_default = $customerPackageOld->check_default;
                    $customerPackageHistory->save();
                }

                $dtClient->customer_package()->delete();

                $customerPackage = new CustomerPackage();
                $customerPackage->transaction_package_id = $dtData->id;
                $customerPackage->package_id = $dtData->package_id;
                $customerPackage->customer_id = $dtData->customer_id;
                $customerPackage->name = $dtPackage->name;
                $customerPackage->total = $dtPackage->total;
                $customerPackage->percent = $dtPackage->percent;
                $customerPackage->grand_total = $dtPackage->total - ($dtPackage->total * $dtPackage->percent / 100);
                $customerPackage->number_day = $dtPackage->number_day;
                $customerPackage->check_default = $dtPackage->check_default;
                $customerPackage->save();

                $dtData->data_customer = [
                    'id' => $dtData->customer->id,
                    'fullname' => $dtData->customer->fullname,
                    'phone' => $dtData->customer->phone,
                    'email' => $dtData->customer->email,
                ];
                $dtData->makeHidden(['customer']);
                $this->requestNoti = clone $this->request;
                $this->requestNoti->merge(['arr_object_id' => $arr_object_id]);
                $this->requestNoti->merge(['dtData' => $dtData]);
                $this->requestNoti->merge(['customer_id' => $customer_id]);
                $this->requestNoti->merge(['type' => 'staff']);
                $this->requestNoti->merge(['staff_id' => $this->request->input('staff_status')]);
                $this->fnbNoti->addNoti($this->requestNoti);
                DB::commit();
                $data['data'] = $dtData;
                $data['result'] = true;
                $data['message'] = lang('Thanh toán thành công');
                return response()->json($data);
            } else {
                $data['data'] = $dtData;
                $data['result'] = false;
                $data['message'] = 'Phiếu đã được thanh toán.';
                return response()->json($data);
            }
        } catch (\Exception $exception){
            DB::rollBack();
            $data['data'] = [];
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function updateTransaction(){
        $id = $this->request->input('id') ?? 0;
        $dtData = TransactionPackage::find($id);
        if (empty($dtData)){
            $data['data'] = [];
            $data['result'] = false;
            $data['message'] = 'Không tồn tại dữ liệu';
            return response()->json($data);
        }

        DB::BeginTransaction();
        try {
            $dtData->payment_error = $this->request->input('payment_error');
            $dtData->payment = $this->request->input('payment');
            $dtData->save();
            DB::commit();
            $data['data'] = $dtData;
            $data['result'] = true;
            $data['message'] = lang('Cập nhật thành công');
            return response()->json($data);
        } catch (\Exception $exception){
            DB::rollBack();
            $data['data'] = [];
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

}
