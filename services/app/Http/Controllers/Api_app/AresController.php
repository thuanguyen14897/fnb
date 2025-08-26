<?php
namespace App\Http\Controllers\Api_app;

use App\Services\AdminService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ares;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AresController extends AuthController
{
    use UploadFile;
    protected $fnbAdmin;
    public function __construct(Request $request, AdminService $adminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbAdmin = $adminService;
    }

    public function getList() {
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
//
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data",'id');
        if($orderBy == 'DT_RowIndex') {
            $orderBy = 'id';
        }
        $orderDir = $this->request->input('order.0.dir', 'asc');
        $query = Ares::where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtIcon = !empty($value->icon) ? env('STORAGE_URL').'/'.$value->icon : null;
                $data[$key]['icon'] = $dtIcon;
                $dtImage = !empty($value->image) ? env('STORAGE_URL').'/'.$value->image : null;
                $data[$key]['image'] = $dtImage;
            }
        }
        $total = Ares::count();

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
        $dtData = Ares::find($id);
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = 'Lấy thông tin khu vực kinh doanh thành công';
        return response()->json($data);
    }

    public function detail($id = 0){
        if(empty($id)) {
            $id = $this->request->input('id') ?? 0;
        }
        $validator = Validator::make($this->request->all(),
            ['name' => 'required'],
            ['name.required' => 'Bạn chưa nhập tên']);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        if (empty($id)){
            $dtData = new Ares();
        } else {
            $dtData = Ares::find($id);
        }
        DB::beginTransaction();
        try {
            $dtData->name = $this->request->name;
            $dtData->save();
            if ($dtData) {
                DB::commit();
                $data['result'] = true;
                if (empty($id)){
                    $data['message'] = 'Thêm mới thành công';
                } else {
                    $data['message'] = 'Cập nhập thành công';
                }
            } else {
                $data['result'] = false;
                if (empty($id)){
                    $data['message'] = 'Thêm mới thất bại';
                } else {
                    $data['message'] = 'Cập nhập thất bại';
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

    public function getSetup(){
        $id = $this->request->input('id') ?? 0;
        $dtData = Ares::find($id);
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = lang('Lấy thông tin thành công');
        return response()->json($data);
    }

    public function updateSetup(){
        $id = $this->request->input('id') ?? 0;
    }

}
