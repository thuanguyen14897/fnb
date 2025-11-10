<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Clients;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\TransactionPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\UploadFile;
use App\Services\AdminService;
use Illuminate\Support\Facades\Validator;

class PackageController extends AuthController
{
    use UploadFile;

    protected $fnbAdmin;

    public function __construct(Request $request, AdminService $adminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbAdmin = $adminService;
    }

    public function getListPackage()
    {
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $type_search = $this->request->input('type_search', 0);

        $query = Package::where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        if (!empty($type_search)){
            $query->where('type', $type_search);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtImage = !empty($value->image) ? config('app.storage_url') . '/' . $value->image : null;
                $data[$key]['image'] = $dtImage;
            }
        }
        $total = Package::count();

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
        $id = $this->request->input('id') ?? 0;
        $dtData = Package::find($id);
        if (!empty($dtData)) {
            $dtImage = !empty($dtData->image) ? config('app.storage_url') . '/' . $dtData->image : null;
            $dtData->image = $dtImage;
        }
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }

    public function detail()
    {
        $id = $this->request->input('id') ?? 0;
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_package,name,' . $id,
                'number_day' => 'required',
                'total' => 'required',
            ]
            , [
                'name.required' => 'Bạn chưa nhập tên',
                'name.unique' => 'Tên đã tồn tại',
                'number_day.required' => 'Vui lòng nhập số ngày',
                'total.required' => 'Vui lòng nhập số tiền',
            ]);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        if (empty($id)) {
            $dtData = new Package();
        } else {
            $dtData = Package::find($id);
        }
        DB::beginTransaction();
        try {
            $dtData->name = $this->request->input('name');
            $dtData->number_day = $this->request->input('number_day');
            $dtData->total = $this->request->input('total') ?? 0;
            $dtData->percent = $this->request->input('percent') ?? 0;
            $dtData->note = $this->request->input('note') ?? null;
            $dtData->type = $this->request->input('type') ?? 0;
            $dtData->save();
            if ($dtData) {

                if ($this->request->hasFile('image')) {
                    if (!empty($dtData->image)) {
                        $this->deleteFile($dtData->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'package/' . $dtData->id, 70, 70, false);
                    $dtData->image = $path;
                    $dtData->save();
                }
                DB::commit();
                $data['result'] = true;
                if (empty($id)) {
                    $data['message'] = 'Thêm mới thành công';
                } else {
                    $data['message'] = 'Cập nhật thành công';
                }
            } else {
                $data['result'] = false;
                if (empty($id)) {
                    $data['message'] = 'Thêm mới thất bại';
                } else {
                    $data['message'] = 'Cập nhật thất bại';
                }
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function delete()
    {
        $id = $this->request->input('id') ?? 0;
        $dtData = Package::find($id);
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn tại data';
            return response()->json($data);
        }
        if ($dtData['check_default'] == 1){
            $data['result'] = false;
            $data['message'] = 'Gói mặc định không thể xóa';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if (count($dtData->transaction_package) > 0) {
                $data['result'] = false;
                $data['message'] = 'Gói này đã được sử dụng, không thể xóa';
                return response()->json($data);
            }
            $dtData->delete();
            if (!empty($dtData->image)) {
                $this->deleteFile($dtData->image);
            }
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
        $type_client = $this->request->client->type_client ?? 1;
        $search = $this->request->input('search') ?? null;
        $admin = $this->request->input('admin') ?? 0;
        $type = $this->request->input('type') ?? 1;
        $checkPartner = $this->request->input('checkPartner') ?? 0;
        $query = Package::select(
                'id',
                'name',
                'number_day',
                'total',
                'percent',
                'check_default',
                'note'
            );
        $query->where('id', '!=', 0);
        if (!empty($search)){
            $query->where(function($q) use ($search){
                $q->where('name','like',"%$search%");
            });
        }
        if (!empty($admin)) {
            if(!empty($checkPartner)){
                $query->where('check_default', 1);
                $query->where('type', $type);
            } else {
                $query->where('type', $type);
                $query->orWhere('type', '=', -1);
            }
        } else {
            $query->where('type', $type_client);
        }
        $data = $query->get();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $data[$key]['grand_total'] = $value->total - ($value->total * $value->percent / 100);
            }
        }
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function addTransactionPackage()
    {
        $customer_id = $this->request->client->id ?? 0;
        $dataPost = $this->request->input();
        if (empty($customer_id)) {
            return response()->json([
                'data' => [],
                'result' => false,
                'message' => 'Vui lòng đăng nhập để tiếp tục sử dung dịch vụ'
            ]);
        }
        if (empty($dataPost)) {
            return response()->json([
                'data' => [],
                'result' => false,
                'message' => 'Không có dữ liệu để thêm giao dịch'
            ]);
        }

        $validator = Validator::make($this->request->all(),
            [
                'package_id' => 'required',
            ]
            , [
                'package_id.required' => 'Vui lòng chọn gói thành viên',
            ]);
        if ($validator->fails()) {
            $data['data'] = [];
            $data['result'] = false;
            $data['message'] = $validator->errors()->all()[0];
            echo json_encode($data);
            die();
        }

        $dtTransactionPackage = TransactionPackage::where('customer_id', $customer_id)
            ->where('status', 1)
            ->first();
        if (!empty($dtTransactionPackage)){
            $id = $dtTransactionPackage->id;
        } else {
            $id = 0;
        }
        $reference_no = null;
        if (empty($id)) {
            $responseRefCode = $this->fnbAdmin->getOrderRef('package_customer');
            $reference_no = $responseRefCode['reference_no'] ?? null;
        }
        $package_id = $dataPost['package_id'] ?? 0;
        $dtPackage = Package::find($package_id);
        if (empty($dtPackage)) {
            return response()->json([
                'data' => [],
                'result' => false,
                'message' => 'Gói thành viên không tồn tại'
            ]);
        }
        DB::beginTransaction();
        if (empty($id)){
            $dtData = new TransactionPackage();
        } else {
            $dtData = TransactionPackage::find($id);
        }
        try {
            if (empty($id)) {
                $dtData->reference_no = $reference_no;
            }
            $dtData->date = date('Y-m-d H:i:s');
            $dtData->package_id = $package_id;
            $dtData->number_day = $dtPackage->number_day;
            $dtData->total = $dtPackage->total;
            $dtData->discount = $dtPackage->percent;
            $dtData->grand_total = $dtPackage->total - ($dtPackage->total * $dtPackage->percent / 100);
            $dtData->customer_id = $customer_id;
            $dtData->status = 1;
            $dtData->save();
            DB::commit();
            if (empty($id)) {
                $this->fnbAdmin->updateOrderRef('package_customer');
            }
            $data['result'] = true;
            $data['data'] = $dtData;
            $data['message'] = 'Mua gói dịch vụ thành viên thành công';
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['data'] = [];
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
