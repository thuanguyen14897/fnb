<?php
namespace App\Http\Controllers\Api_app;

use App\Services\AdminService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Ares;
use App\Models\AresDetail;
use App\Models\AresWard;
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
        $province_search = $this->request->input('province_search');
        $ward_search = $this->request->input('ward_search');

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
            $query->orWhereHas('ares_ward',function ($q) use ($search){
                $q->where('Name', 'like', "%$search%");
            });
            $query->orWhereHas('ares_province',function ($q) use ($search){
                $q->where('Name', 'like', "%$search%");
            });
        }
        if(!empty($province_search)) {
            $query->WhereHas('ares_province',function ($q) use ($province_search){
                $q->where('tbl_province.Id', '=', $province_search);
            });
        }
        if(!empty($ward_search)) {
            $query->WhereHas('ares_ward',function ($q) use ($ward_search){
                $q->where('tbl_wards.Id', '=', $ward_search);
            });
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)){
            foreach ($data as $key => $value){
                $data_province = AresDetail::select('id', 'id_ares', 'id_province')
                    ->where('id_ares', $value->id)->with('province')->get();
                foreach($data_province as $kItem => $vItem) {
                    $data_province[$kItem]->data_ward = AresWard::select('id', 'id_ward')
                        ->where('id_ares', $value->id)
                        ->where('id_province', $vItem->id_province)
                        ->with('ward')->get();
                }
                $data[$key]->data_province = $data_province;
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
            d
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
        $data_detail = DB::table('tbl_ares_detail')
            ->select('tbl_ares_detail.*', 'tbl_province.Name as name_province')
            ->join('tbl_province', 'tbl_province.Id', '=', 'tbl_ares_detail.id_province')
            ->where('tbl_ares_detail.id_ares', '=', $id)->get();
        foreach($data_detail as $key => $value) {
            $data_detail[$key]->item = DB::table('tbl_ares_ward')
                ->select('tbl_ares_ward.*', 'tbl_wards.Name as name_ward')
                ->join('tbl_wards', 'tbl_wards.Id', '=', 'tbl_ares_ward.id_ward')
                ->where('tbl_ares_ward.id_ares', '=', $value->id_ares)
                ->where('tbl_ares_ward.id_province', '=', $value->id_province)
                ->get();
        }
        $dtData->detail = $data_detail;
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = lang('Lấy thông tin thành công');
        return response()->json($data);
    }

    public function updateSetup() {
        try {
            $id = $this->request->input('id') ?? 0;
            $items = $this->request->input('item');
            $ares_detail = [];
            $ares_ward = [];
            DB::beginTransaction();
            foreach ($items as $key => $value) {
                $id_province = $value['province_id'] ?? [];
                $id_ward = $value['ward_id'] ?? [];
                if(!empty($id_province) && !empty($id_ward)) {
                    $ares_detail[] = [
                        'id_ares' => $id,
                        'id_province' => $id_province,
                        'list_wards' => implode(',', $id_ward),
                    ];
                    foreach ($id_ward as $k => $v) {
                        if(!empty($v)) {
                            $ares_ward[] = [
                                'id_ares' => $id,
                                'id_province' => $id_province,
                                'id_ward' => $v,
                            ];
                        }
                    }
                }
            }
            AresDetail::where('id_ares', $id)->delete();
            AresWard::where('id_ares', $id)->delete();
            if(!empty($ares_detail)) {
                $insertDetail = AresDetail::insert($ares_detail);
            }
            if(!empty($ares_ward)) {
                $insertWard = AresWard::insert($ares_ward);
            }
            DB::commit();
            if (!empty($insertDetail) && !empty($insertWard)) {
                $data['result'] = true;
                $data['message'] = lang('Cập nhật dữ liệu thành công');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = lang('Cập nhật dữ liệu không thành công');
            return response()->json($data);
        }
    }

    public function delete() {
        try {
            $id = $this->request->input('id') ?? 0;
            DB::beginTransaction();
            $success = Ares::find($id)->delete();
            if(!empty($success)) {
                AresDetail::where('id_ares', $id)->delete();
                AresWard::where('id_ares', $id)->delete();
                DB::commit();
                if (!empty($success)) {
                    $data['result'] = true;
                    $data['message'] = lang('Xóa dữ liệu thành công');
                    return response()->json($data);
                }
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = lang('Xóa dữ liệu không thành công');
            return response()->json($data);
        }
    }

    public function ChangeStatus() {
        try {
            $id = $this->request->input('id') ?? 0;
            $status = $this->request->input('status') ?? 0;
            $ares = Ares::find($id);
            $ares->active = $status;
            $success = $ares->save();
            if(!empty($success)) {
                $data['result'] = true;
                $data['message'] = lang('Đổi trạng thái thành công');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = lang('Đổi trạng thái không thành công');
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
        $show_short = $this->request->query('show_short');
        $search = $this->request->input('search') ?? null;
        //gian hàng liên quan
        $id = $this->request->input('id') ?? 0;
        //end
        $orderBy = 'id desc';
        $query = Ares::select('tbl_ares.*')
            ->where('id','!=',0)
            ->where('active','=',1);
        if(empty($show_short)) {
            $query->with('ares_province');
            $query->with('ares_ward');
        }
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        if (!empty($id)){
            $query->where('id','!=',$id);
        }
        $query->orderByRaw($orderBy);
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);
        return response()->json([
            'data' => $dtData,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

}
