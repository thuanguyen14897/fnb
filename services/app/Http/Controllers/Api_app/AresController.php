<?php
namespace App\Http\Controllers\Api_app;

use App\Services\AdminService;
use App\Services\ReportService;
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
    protected $fnbReport;
    public function __construct(Request $request, AdminService $adminService,ReportService $reportService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbAdmin = $adminService;
        $this->fnbReport = $reportService;
    }

    public function getList() {
        $ares_permission = (int) ($this->request->input('ares_permission') ?? 0);
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        if($length < 0) {
            $length = PHP_INT_MAX;
        }
        $province_search = $this->request->input('province_search');
        $ward_search = $this->request->input('ward_search');

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data",'id');
        if($orderBy == 'DT_RowIndex') {
            $orderBy = 'id';
        }
        $orderDir = $this->request->input('order.0.dir', 'asc');
        $query = Ares::where('id','!=',0);

        if ($ares_permission) {
            $aresPer = $this->request->input('aresPer'); // có thể là null/0
            if (empty($aresPer)) {
                $query->where('tbl_ares.id', 0);
            } else {
                $query->whereIn('id', $aresPer);
            }

        }



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
        $data = $query->skip($start)->take($length)
            ->get();
        if (!empty($data)){
            foreach ($data as $key => $value){
                $data_province = AresDetail::select('id', 'id_ares', 'id_province', 'id_province_old')
                    ->where('id_ares', $value->id)->with('province')->get();
                foreach($data_province as $kItem => $vItem) {
                    $data_province[$kItem]->data_ward = AresWard::select('id', 'id_ward')
                        ->where('id_ares', $value->id)
                        ->where('id_province', $vItem->id_province)
                        ->with('ward')->get();
                    if(!empty($vItem->id_province_old)) {
                        $data_province[$kItem]->name_province_old = DB::table('tbl_province_sixty_four')->select(DB::raw('CONCAT(type, " ", name) as name'))
                            ->where('provinceid', $vItem->id_province_old)->first()->name;
                    }
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
                    $data['message'] = 'Cập nhật thành công';
                }
            } else {
                $data['result'] = false;
                if (empty($id)){
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

    public function getSetup(){
        $id = $this->request->input('id') ?? 0;
        $dtData = Ares::find($id);
        $data_detail = DB::table('tbl_ares_detail')
            ->select('tbl_ares_detail.*', 'tbl_province.Name as name_province')
            ->join('tbl_province', 'tbl_province.Id', '=', 'tbl_ares_detail.id_province')
            ->where('tbl_ares_detail.id_ares', '=', $id)->get();
        foreach($data_detail as $key => $value) {
            $id_province_old = $value->id_province_old ?? 0;
            $list_ward = DB::table('tbl_ares_ward')
//                ->select('tbl_ares_ward.*', 'tbl_wards.Name as name_ward')
                ->select(DB::raw('GROUP_CONCAT(DISTINCT tbl_ares_ward.id_ward) as list_ward'))
                ->join('tbl_wards', 'tbl_wards.Id', '=', 'tbl_ares_ward.id_ward')
                ->where('tbl_ares_ward.id_ares', '=', $value->id_ares)
                ->where('tbl_ares_ward.id_province', '=', $value->id_province)
                ->first();
            $data_detail[$key]->list_id = explode(',', $list_ward->list_ward);
            $data_detail[$key]->items = Ward::where('ProvinceId', $value->id_province)
                ->where(function($q) use ($id_province_old) {
                if(!empty($id_province_old)) {
                    $q->where('ProvinceId_old', '=', "$id_province_old");
                }
            })->get();
            $data_detail[$key]->province_sixty_four = DB::table('tbl_province_sixty_four')->where('province_new', $value->id_province)->get();
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
                $id_province = $value['province_id'] ?? 0;
                $id_province_old = $value['province_old_id'] ?? 0;
                $id_ward = $value['ward_id'] ?? [];
                if(!empty($id_province) && !empty($id_ward)) {
                    $ares_detail[] = [
                        'id_ares' => $id,
                        'id_province' => $id_province,
                        'id_province_old' => $id_province_old ?? 0,
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
            $per_page = $this->request->query('per_page');
        }

        if ($this->request->query('limit_all')) {
            $current_page = 1;
            $per_page = PHP_INT_MAX;
        }
        $show_short = $this->request->query('show_short');
        $search = $this->request->input('search') ?? null;
        $are_id = $this->request->input('are_id') ?? null;
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
        if (!empty($are_id)){
            $query->whereIn('id',$are_id);
        }
        $query->orderByRaw($orderBy);
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);
        return response()->json([
            'data' => $dtData,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }


    public function getDetailWhere() {
        $province = $this->request->input('province') ?? 0;
        $ward = $this->request->input('ward') ?? 0;
        $province = is_array($province) ? $province : [$province];
        $ward = is_array($ward) ? $ward : [$ward];
        $dtData = Ares::with(['aresWard' => function($query){
            $query->select('id_province','id_ward','id_ares');
        }])
        ->whereHas('aresWard', function($q) use ($province, $ward) {
            if(!empty($province)) {
                $q->whereIn('id_province', $province);
            }
            if(!empty($ward)) {
                $q->whereIn('id_ward', $ward);
            }
        })->get();
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = 'Lấy thông tin khu vực kinh doanh thành công';
        return response()->json($data);
    }

    public function getWardsWhereAres() {
        $id_ares = $this->request->input('id_ares') ?? 0;
//        $dtData = AresWard::select('tbl_ares_ward.id_ward');
//        if(is_array($id_ares)) {
//            $dtData->whereIn('id_ares', $id_ares);
//        }
//        else {
//            $dtData->where('id_ares','=', $id_ares);
//        }
//        $dtData->get();
        $dtData = AresWard::select('id_ward')
            ->when(is_array($id_ares), function ($q) use ($id_ares) {
                return $q->whereIn('id_ares', $id_ares);
            }, function ($q) use ($id_ares) {
                return $q->where('id_ares', $id_ares);
            })->get();


        $dataWard = [];
        foreach($dtData as $key => $value) {
            $dataWard[] = $value->id_ward;
        }
        $data['result'] = true;
        $data['data'] = $dataWard;
        $data['message'] = 'Lấy thông tin khu vực kinh doanh thành công';
        return response()->json($data);
    }

    public function getListDataWhereName(){
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        $list_name = $this->request->input('list_name') ?? null;
        $ward_ares = $this->request->input('ward_ares') ?? null;
        $orderBy = 'id desc';
        $query = Ares::select('tbl_ares.*')
            ->where('id','!=',0);
//            ->where('active','=',1);
        if (!empty($list_name)) {
            $query->where(function($q) use ($list_name) {
                if(is_array($list_name)) {
                    $q->whereIn('name', $list_name);
                }
                else {
                    $q->where('name', '=', "$list_name");
                }
            });
        }
        $query->orderByRaw($orderBy);
        $dtData = $query->get();
        if(!empty($ward_ares)) {
            foreach ($dtData as $key => $value) {
                $dtData[$key]->ward = Ward::select('tbl_wards.Id', 'tbl_wards.Name')->whereIn('Name', $ward_ares)
                    ->join('tbl_ares_ward', 'tbl_ares_ward.id_ward', '=', 'tbl_wards.Id')
                    ->where('tbl_ares_ward.id_ares', '=', $value->id)
                    ->get();
            }
        }
        else {
            foreach ($dtData as $key => $value) {
                $dtData[$key]->ward = Ward::select('tbl_wards.Id', 'tbl_wards.Name')->join('tbl_ares_ward', 'tbl_ares_ward.id_ward', '=', 'tbl_wards.Id')
                    ->where('tbl_ares_ward.id_ares', '=', $value->id)
                    ->get();
                $dtData[$key]->all_ward = 1;
            }
        }
        return response()->json([
            'data' => $dtData,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

//    public function create_auto_ares() {
//        $arr = [
//            "bến tre",
//            "trà vinh",
//            "vĩnh long",
//            "bình phước",
//            "đồng nai",
//            "bạc liêu",
//            "cà mau",
//            "tiền giang",
//            "đồng tháp",
//            "sóc trăng",
//            "hậu giang",
//            "cần thơ",
//            "kiên giang",
//            "an giang",
//            "long an",
//            "tây ninh",
//            "bến tre",
//            "daknong",
//            "lâm đồng",
//            "bình thuận",
//            "daklak",
//            "phú yên",
//            "gia lai",
//            "bình định",
//            "quảng ngãi",
//            "kon tum",
//            "ninh thuận",
//            "khánh hòa",
//            "đà nẵng",
//            "quảng nam",
//            "huế",
//            "vũng tàu",
//            "bình dương",
//            "quận 1",
//            "quận 3",
//            "quận 4",
//            "quận 5",
//            "quận 6",
//            "quận 7",
//            "quận 8",
//            "quận 10",
//            "quận 11",
//            "quận 12",
//            "thủ đức",
//            "bình thạnh",
//            "quận phú nhuận",
//            "quận tân bình",
//            "quận gò vấp",
//            "quận tân phú",
//            "hóc môn _ hcm",
//            "cần giờ _ hcm",
//            "củ chi _ hcm",
//            "bình chánh _ hcm",
//            "nhà bè _ hcm",
//            "Hồ Chí Minh",
//            "Bà Rịa - Vũng Tàu",
//        ];
//
//       $province_sixty_four = DB::table('tbl_province_sixty_four')
//           ->where(function($q) use ($arr) {
//               foreach($arr as $value) {
//                   $q->where('name', '!=', "$value");
//               }
//        })->get();
//       foreach($province_sixty_four as $key => $value) {
//           $ares = new Ares();
//           $ares->name = $value->name;
//           $ares->active = 0;
//           $ares->save();
//           $province = Province::find($value->province_new);
//            $wards = Ward::where('ProvinceId', $value->province_new)->where('ProvinceId_old', $value->provinceid)->get();
//            $listWard = [];
//            foreach($wards as $k => $v) {
//                $ares_ward = new AresWard();
//                $ares_ward->id_ares = $ares->id;
//                $ares_ward->id_province = $province->Id;
//                $ares_ward->id_ward = $v->Id;
//                $ares_ward->save();
//                $listWard[] = $v->Id;
//            }
//            $aresDetail = new AresDetail();
//            $aresDetail->id_ares = $ares->id;
//            $aresDetail->id_province = $province->Id;
//            $aresDetail->list_wards = implode(',', $listWard);
//           $aresDetail->save();
//       }
//    }


    public function getListSyntheticFeePartner(){
        $storageUrl = config('app.storage_url');

        $response = $this->fnbReport->getListSyntheticFeePartner($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']);
        if(empty($dtData)){
            $dtData[] = [
                'province_id' => 0,
                'wards_id' => 0,
                'total' => 0,
            ];
        }

        $dataQuery = DB::table(DB::raw('(' .
            collect($dtData)->map(function ($value) {
                return "(SELECT '{$value['province_id']}' as province_id, '{$value['wards_id']}' as wards_id, '{$value['total']}' as total)";
            })->implode(' UNION ALL ') . ') AS tb_data'));

        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $ares_search = $this->request->input('ares_search') ?? 0;

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $query = Ares::where('tbl_ares.id', '!=',0);
        $query->join('tbl_ares_ward', 'tbl_ares_ward.id_ares', '=', 'tbl_ares.id');
        $query->joinSub($dataQuery, 'tb_data', function ($join) {
            $join->on('tbl_ares_ward.id_province', '=', 'tb_data.province_id');
            $join->on('tbl_ares_ward.id_ward', '=', 'tb_data.wards_id');
        });
        $query->select(
            'tbl_ares.id as id',
            'tbl_ares.name as name',
            DB::raw('SUM(tb_data.total) as total')
        );
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereHas('ares_ward', function ($q) use ($ListWard) {
                            $q->whereIn('id_ward', $ListWard['data']);
                        });
                    } else {
                        $query->where('tbl_ares.id', 0);
                    }
                } else {
                    $query->where('tbl_ares.id', 0);
                }
            }
        }
        if (!empty($ares_search)){
            $query->where('tbl_ares.id','=',$ares_search);
        }
        $query->groupBy('tbl_ares.id', 'tbl_ares.name');
        $filtered = $query->get()->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
            }
        }

        $query = Ares::where('tbl_ares.id', '!=',0);
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereHas('ares_ward', function ($q) use ($ListWard) {
                            $q->whereIn('id_ward', $ListWard['data']);
                        });
                    } else {
                        $query->where('tbl_ares.id', 0);
                    }
                } else {
                    $query->where('tbl_ares.id', 0);
                }
            }
        }
        $query->select(
            'tbl_ares.id as id',
            'tbl_ares.name'
        );
        $query->join('tbl_ares_ward', 'tbl_ares_ward.id_ares', '=', 'tbl_ares.id');
        $query->joinSub($dataQuery, 'tb_data', function ($join) {
            $join->on('tbl_ares_ward.id_province', '=', 'tb_data.province_id');
            $join->on('tbl_ares_ward.id_ward', '=', 'tb_data.wards_id');
        });
        $query->groupBy('tbl_ares.id', 'tbl_ares.name');
        $total = $query->get()->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListSyntheticRosePartner(){
        $storageUrl = config('app.storage_url');

        $response = $this->fnbReport->getListSyntheticRosePartner($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']);
        if(empty($dtData)){
            $dtData[] = [
                'province_id' => 0,
                'wards_id' => 0,
                'total' => 0,
                'total_payment' => 0,
            ];
        }

        $dataQuery = DB::table(DB::raw('(' .
            collect($dtData)->map(function ($value) {
                return "(SELECT '{$value['province_id']}' as province_id, '{$value['wards_id']}' as wards_id, '{$value['total']}' as total,'{$value['total_payment']}' as total_payment)";
            })->implode(' UNION ALL ') . ') AS tb_data'));

        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $ares_search = $this->request->input('ares_search') ?? 0;

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $query = Ares::where('tbl_ares.id', '!=',0);
        $query->join('tbl_ares_ward', 'tbl_ares_ward.id_ares', '=', 'tbl_ares.id');
        $query->joinSub($dataQuery, 'tb_data', function ($join) {
            $join->on('tbl_ares_ward.id_province', '=', 'tb_data.province_id');
            $join->on('tbl_ares_ward.id_ward', '=', 'tb_data.wards_id');
        });
        $query->select(
            'tbl_ares.id as id',
            'tbl_ares.name as name',
            DB::raw('SUM(tb_data.total) as total'),
            DB::raw('SUM(tb_data.total_payment) as total_payment')
        );
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereHas('ares_ward', function ($q) use ($ListWard) {
                            $q->whereIn('id_ward', $ListWard['data']);
                        });
                    } else {
                        $query->where('tbl_ares.id', 0);
                    }
                } else {
                    $query->where('tbl_ares.id', 0);
                }
            }
        }
        if (!empty($ares_search)){
            $query->where('tbl_ares.id','=',$ares_search);
        }
        $query->groupBy('tbl_ares.id', 'tbl_ares.name');
        $filtered = $query->get()->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
            }
        }

        $query = Ares::where('tbl_ares.id', '!=',0);
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereHas('ares_ward', function ($q) use ($ListWard) {
                            $q->whereIn('id_ward', $ListWard['data']);
                        });
                    } else {
                        $query->where('tbl_ares.id', 0);
                    }
                } else {
                    $query->where('tbl_ares.id', 0);
                }
            }
        }
        $query->select(
            'tbl_ares.id as id',
            'tbl_ares.name'
        );
        $query->join('tbl_ares_ward', 'tbl_ares_ward.id_ares', '=', 'tbl_ares.id');
        $query->joinSub($dataQuery, 'tb_data', function ($join) {
            $join->on('tbl_ares_ward.id_province', '=', 'tb_data.province_id');
            $join->on('tbl_ares_ward.id_ward', '=', 'tb_data.wards_id');
        });
        $query->groupBy('tbl_ares.id', 'tbl_ares.name');
        $total = $query->get()->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListSyntheticKPI(){
        $storageUrl = config('app.storage_url');

        $response = $this->fnbAdmin->getListDataKPI($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']['data'] ?? []);
        if(empty($dtData)){
            $dtData[] = [
                'name_kpi' => 0,
                'id_ares' => 0,
                'total' => 0,
            ];
        }

        $dataQuery = DB::table(DB::raw('(' .
            collect($dtData)->map(function ($value) {
                return "(SELECT '{$value['name_kpi']}' as name_kpi, '{$value['id_ares']}' as id_ares, '{$value['total']}' as total)";
            })->implode(' UNION ALL ') . ') AS tb_data'));

        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $ares_search = $this->request->input('ares_search') ?? 0;

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $query = Ares::where('tbl_ares.id', '!=',0);
        $query->joinSub($dataQuery, 'tb_data', function ($join) {
            $join->on('tbl_ares.id', '=', 'tb_data.id_ares');
        });
        $query->select(
            'tbl_ares.id as id',
            'tbl_ares.name as name',
            'tb_data.name_kpi as name_kpi',
            DB::raw('(tb_data.total) as total'),
        );
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereHas('ares_ward', function ($q) use ($ListWard) {
                            $q->whereIn('id_ward', $ListWard['data']);
                        });
                    } else {
                        $query->where('tbl_ares.id', 0);
                    }
                } else {
                    $query->where('tbl_ares.id', 0);
                }
            }
        }
        if (!empty($ares_search)){
            $query->where('tbl_ares.id','=',$ares_search);
        }
        $filtered = $query->get()->count();
        $query->orderByRaw('id desc,name_kpi asc');
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
            }
        }

        $query = Ares::where('tbl_ares.id', '!=',0);
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereHas('ares_ward', function ($q) use ($ListWard) {
                            $q->whereIn('id_ward', $ListWard['data']);
                        });
                    } else {
                        $query->where('tbl_ares.id', 0);
                    }
                } else {
                    $query->where('tbl_ares.id', 0);
                }
            }
        }
        $query->select(
            'tbl_ares.id as id',
            'tbl_ares.name'
        );
        $query->joinSub($dataQuery, 'tb_data', function ($join) {
            $join->on('tbl_ares.id', '=', 'tb_data.id_ares');
        });
        $total = $query->get()->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }
}
