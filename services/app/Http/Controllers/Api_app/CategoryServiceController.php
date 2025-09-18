<?php

namespace App\Http\Controllers\Api_app;

use App\Models\CategoryService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryServiceController extends AuthController
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getListCategory(){
        $group_category_search_service = $this->request->input('group_category_search_service');
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data",'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $query = CategoryService::with('group_category_service')
            ->with('other_amenities')
            ->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
                $q->orWhereHas('group_category_service', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%$search%");
                });
                $q->orWhereHas('other_amenities', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%$search%");
                });
            });
        }
        if (!empty($group_category_search_service)){
            $query->where('group_category_service_id', $group_category_search_service);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtImage = !empty($value->icon) ? env('STORAGE_URL').'/'.$value->icon : null;
                $data[$key]['icon'] = $dtImage;
            }
        }
        $total = CategoryService::count();

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
        $dtData = CategoryService::with('group_category_service')
            ->with('other_amenities')
            ->find($id);
        if (!empty($dtData)){
            $dtImage = !empty($dtData->icon) ? env('STORAGE_URL').'/'.$dtData->icon : null;
            $dtData->icon = $dtImage;
        }
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = 'Lấy thông tin danh mục dịch vụ thành công';
        return response()->json($data);
    }

    public function detail(){
        $id = $this->request->input('id') ?? 0;
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_group_category_service,name,' . $id,
                'group_category_service_id' => 'required'
            ]
            , [
                'name.required' => 'Bạn chưa nhập tên',
                'name.unique' => 'Tên đã tồn tại',
                'group_category_service_id.required' => 'Vui lòng chọn nhóm danh mục',
            ]);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        if (empty($id)){
            $dtData = new CategoryService();
        } else {
            $dtData = CategoryService::find($id);
        }
        $other_amenities = $this->request->input('other_amenities');
        $arrOtherAmenities = [];
        if (!empty($other_amenities)) {
            foreach (explode(',', $other_amenities) as $k => $v) {
                $arrOtherAmenities[] = [
                    'other_amenities_service_id' => $v,
                ];
            }
        }
        DB::beginTransaction();
        try {
            $dtData->name = $this->request->name;
            $dtData->group_category_service_id = $this->request->group_category_service_id;
            $dtData->active = $this->request->active ?? 1;
            $dtData->order_by = $this->request->input('index');
            $dtData->save();
            if ($dtData) {

                $dtData->other_amenities()->detach();
                if (!empty($arrOtherAmenities)) {
                    foreach ($arrOtherAmenities as $key => $value) {
                        $value['category_service_id'] = $dtData->id;
                        DB::table('tbl_other_amenities_service_category')->insert($value);;
                    }
                }

                if ($this->request->hasFile('icon')) {
                    if (!empty($dtData->icon)) {
                        $this->deleteFile($dtData->icon);
                    }
                    $path = $this->UploadFile($this->request->file('icon'), 'category_service/' . $dtData->id, 70, 70, false);
                    $dtData->icon = $path;
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
        $dtData = CategoryService::find($id);
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại data';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if (count($dtData->service) > 0) {
                $data['result'] = false;
                $data['message'] = 'Danh mục này đang được sử dụng trong dịch vụ, không thể xóa';
                return response()->json($data);
            }
            $dtData->delete();
            if (!empty($dtData->icon)) {
                $this->deleteFile($dtData->icon);
            }
            $dtData->other_amenities()->detach();
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
        $dtData = CategoryService::find($id);
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
        $id = $this->request->input('id') ?? 0;
        $group_category_service_id = $this->request->input('group_category_service_id') ?? 0;
        $query = CategoryService::where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        if (!empty($group_category_service_id)){
            $query->where('group_category_service_id', $group_category_service_id);
        }
        if (!empty($id)){
            $query->where('id', $id);
        }
        $query->orderByRaw('order_by asc');
        $data = $query->limit($limit)->get();
        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtImage = !empty($value->icon) ? env('STORAGE_URL').'/'.$value->icon : null;
                $data[$key]['icon'] = $dtImage;
            }
        }
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

}
