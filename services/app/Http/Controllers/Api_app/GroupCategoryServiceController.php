<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\Service;
use App\Models\GroupCategoryService;
use App\Models\PaymentMode;
use App\Models\Province;
use App\Models\Ward;
use App\Traits\UploadFile;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GroupCategoryServiceController extends AuthController
{
    use UploadFile;
    public $fnbAdminService;
    public function __construct(Request $request,AdminService $adminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbAdminService = $adminService;
    }

    public function getListGroupCategory(){
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data",'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $query = GroupCategoryService::where('id','!=',0);
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
                $background = !empty($value->background) ? env('STORAGE_URL').'/'.$value->background : null;
                $data[$key]['background'] = $background;
            }
        }
        $total = GroupCategoryService::count();

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
        $dtData = GroupCategoryService::find($id);
        if (!empty($dtData)){
            $dtIcon = !empty($dtData->icon) ? env('STORAGE_URL').'/'.$dtData->icon : null;
            $dtData->icon = $dtIcon;
            $dtImage = !empty($dtData->image) ? env('STORAGE_URL').'/'.$dtData->image : null;
            $dtData->image = $dtImage;
            $background = !empty($dtData->background) ? env('STORAGE_URL').'/'.$dtData->background : null;
            $dtData->background = $background;
        }
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = 'Lấy thông tin nhóm danh mục dịch vụ thành công';
        return response()->json($data);
    }

    public function detail(){
        $id = $this->request->input('id') ?? 0;
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_group_category_service,name,' . $id,
            ]
            , [
                'name.required' => 'Bạn chưa nhập tên',
                'name.unique' => 'Tên đã tồn tại',
            ]);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        if (empty($id)){
            $dtData = new GroupCategoryService();
        } else {
            $dtData = GroupCategoryService::find($id);
        }
        DB::beginTransaction();
        try {
            $dtData->name = $this->request->name;
            $dtData->color = $this->request->color;
            $dtData->color_border = $this->request->color_border;
            $dtData->index = $this->request->index;
            $dtData->active = $this->request->active ?? 1;
            $dtData->save();
            if ($dtData) {
                if ($this->request->hasFile('icon')) {
                    if (!empty($dtData->icon)) {
                        $this->deleteFile($dtData->icon);
                    }
                    $path = $this->UploadFile($this->request->file('icon'), 'group_category_service/' . $dtData->id, 70, 70, false);
                    $dtData->icon = $path;
                    $dtData->save();
                }

                if ($this->request->hasFile('image')) {
                    if (!empty($dtData->image)) {
                        $this->deleteFile($dtData->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'group_category_service/' . $dtData->id, 70, 70, false);
                    $dtData->image = $path;
                    $dtData->save();
                }

                if ($this->request->hasFile('background')) {
                    if (!empty($dtData->background)) {
                        $this->deleteFile($dtData->background);
                    }
                    $path = $this->UploadFile($this->request->file('background'), 'group_category_service/' . $dtData->id, 70, 70, false);
                    $dtData->background = $path;
                    $dtData->save();
                }
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

    public function delete(){
        $id = $this->request->input('id') ?? 0;
        $dtData = GroupCategoryService::find($id);
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại data';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if (count($dtData->service) > 0) {
                $data['result'] = false;
                $data['message'] = 'Nhóm danh mục này đang được sử dụng trong dịch vụ, không thể xóa';
                return response()->json($data);
            }
            $dtData->delete();
            if (!empty($dtData->icon)) {
                $this->deleteFile($dtData->icon);
            }
            if (!empty($dtData->image)) {
                $this->deleteFile($dtData->image);
            }
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

    public function active(){
        $id = $this->request->input('id') ?? 0;
        $dtData = GroupCategoryService::find($id);
        DB::beginTransaction();
        try {
            $dtData->active = $dtData->active == 0 ? 1 : 0;
            $dtData->save();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        }
        catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListData(){
        $search = $this->request->input('search') ?? null;
        $limit = $this->request->input('limit') ?? 50;
        $query = GroupCategoryService::with('category_service')->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        $query->orderBy('index','asc');
        $data = $query->limit($limit)->get();
        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtIcon = !empty($value->icon) ? env('STORAGE_URL').'/'.$value->icon : null;
                $data[$key]['icon'] = $dtIcon;
                $dtImage = !empty($value->image) ? env('STORAGE_URL').'/'.$value->image : null;
                $data[$key]['image'] = $dtImage;
                $background = !empty($value->background) ? env('STORAGE_URL').'/'.$value->background : null;
                $data[$key]['background'] = $background;
                $value->category_service->map(function ($item){
                    $item->icon = !empty($item->icon) ? env('STORAGE_URL').'/'.$item->icon : null;;
                    return $item;
                });
            }
        }
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListDataHomePage(){
        $lat = !empty($this->request->input('lat')) ? $this->request->input('lat') : 0;
        $lon = !empty($this->request->input('lon')) ? $this->request->input('lon') : 0;

        $customer_id = $this->request->client->id ?? 0;

        $ward_id = $this->request->input('ward_id') ?? 0;
        $province_id = $this->request->input('province_id') ?? 0;

        $google_api_key = $this->fnbAdminService->get_option('google_api_key');

        $checkWard = false;
        $checkProvince = false;
        if (empty($lat) && empty($lon)){
            $dtWard = Ward::where('Id',$ward_id)->first();
            if (!empty($dtWard)){
                $lat = $dtWard->lat ?? 0;
                $lon = $dtWard->lon ?? 0;
                $checkWard = true;
            } else {
                $dtProvince = Province::where('Id',$province_id)->first();
                if (!empty($dtProvince)){
                    $lat = $dtProvince->lat ?? 0;
                    $lon = $dtProvince->lon ?? 0;
                    $checkProvince = true;
                }
            }
        }

        $query = GroupCategoryService::where('id','!=',0);
        $query->with(['service' => function ($q) use($lat,$lon,$checkProvince,$checkWard,$province_id,$ward_id) {
            $q->select('*',DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"));
            $q->with('other_amenities');
            $q->with('favourite');
            $q->where('hot', 1);
            if (!empty($lat) && !empty($lon)){
                $q->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null)"),'!=',NULL);
                $q->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),
                    '>=', 0);
                $q->where(function ($inst) use ($lat,$lon,$ward_id,$province_id,$checkWard,$checkProvince){
                    $inst->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),
                        '<=', 50);
                    if (!empty($checkWard)) {
                        $inst->orWhere('wards_id', $ward_id);
                    }
                    if(!empty($checkProvince)) {
                        $inst->orWhere('province_id', $province_id);
                    }
                });
            }
            $q->limit(30);
        }]);
        $query->where('active',1);
        $data = $query->get();
        $arrLatLng = $data->flatMap(function ($item) {
            return $item->service->map(function ($it){
                return [
                    'lat' => $it->latitude,
                    'lng' => $it->longitude,
                    'service_id' => $it->id,
                ];
            });
        })->toArray();
        $distances = getDistancesToMultipleDestinations($lat, $lon, $arrLatLng, $google_api_key);
        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtIcon = !empty($value->icon) ? env('STORAGE_URL').'/'.$value->icon : null;
                $data[$key]['icon'] = $dtIcon;
                $dtImage = !empty($value->image) ? env('STORAGE_URL').'/'.$value->image : null;
                $data[$key]['image'] = $dtImage;
                $background = !empty($value->background) ? env('STORAGE_URL').'/'.$value->background : null;
                $data[$key]['background'] = $background;
                $service = $value->service;
                unset($value->service);
                $service = $service->map(function ($item) use ($distances,$lat,$lon,$customer_id){
                    $duration_text = $item->distance > 0 ? round(($item->distance / 40) * 60) : 0;
                    $dtDataInstance = $distances[$item->id] ?? [];
                    $item->distance = $customer_id == 22 ? ['distance_km' => $item->distance,'duration_text' => $duration_text] : $dtDataInstance;
                    $item->homepage = true;
                    $item->location_address = [
                        'lat' => $lat,
                        'lon' => $lon,
                    ];
                    return $item;
                });
                $value->list_service = Service::collection($service);
            }
        }
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

}
