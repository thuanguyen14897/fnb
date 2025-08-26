<?php

namespace App\Http\Controllers\Api_app;

use App\Models\OtherAmenitiesService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OtherAmenitiesController extends AuthController
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getList(){
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data",'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $query = OtherAmenitiesService::where('id','!=',0);
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
                $dtImage = !empty($value->image) ? env('STORAGE_URL').'/'.$value->image : null;
                $data[$key]['image'] = $dtImage;
            }
        }
        $total = OtherAmenitiesService::count();

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
        $dtData = OtherAmenitiesService::find($id);
        if (!empty($dtData)){
            $dtImage = !empty($dtData->image) ? env('STORAGE_URL').'/'.$dtData->image : null;
            $dtData->image = $dtImage;
        }
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }

    public function detail(){
        $id = $this->request->input('id') ?? 0;
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_other_amenities_service,name,' . $id,
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
            $dtData = new OtherAmenitiesService();
        } else {
            $dtData = OtherAmenitiesService::find($id);
        }
        DB::beginTransaction();
        try {
            $dtData->name = $this->request->name;
            $dtData->save();
            if ($dtData) {
                if ($this->request->hasFile('image')) {
                    if (!empty($dtData->image)) {
                        $this->deleteFile($dtData->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'other_amenities_service/' . $dtData->id, 50, 50);
                    $dtData->image = $path;
                    $dtData->save();
                }
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

    public function delete(){
        $id = $this->request->input('id') ?? 0;
        $dtData = OtherAmenitiesService::find($id);
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại data';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if (count($dtData->category) > 0){
                $data['result'] = false;
                $data['message'] = 'Tiện nghi đã được sử dụng';
            } else {
                $dtData->delete();
                if (!empty($dtData->image)) {
                    $this->deleteFile($dtData->image);
                }
                DB::commit();
                $data['result'] = true;
                $data['message'] = lang('c_delete_true');
            }
            return response()->json($data);
        }  catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListData(){
        $search = $this->request->input('search') ?? null;
        $limit = $this->request->input('limit') ?? 50;
        $query = OtherAmenitiesService::where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        $data = $query->limit($limit)->get();
        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtImage = !empty($value->image) ? env('STORAGE_URL').'/'.$value->image : null;
                $data[$key]['image'] = $dtImage;
            }
        }
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }
}
