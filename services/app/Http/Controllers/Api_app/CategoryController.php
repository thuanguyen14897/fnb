<?php

namespace App\Http\Controllers\Api_app;

use App\Models\AresWard;
use App\Services\AdminService;
use App\Models\CategoryService;
use App\Models\Province;
use App\Models\Ward;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class CategoryController extends AuthController
{
    use UploadFile;
    protected $fnbAdmin;
    public function __construct(Request $request,AdminService $adminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbAdmin = $adminService;
    }

    public function getListProvince($id = 0){
        $search = $this->request->input('search') ?? null;
        $limit = $this->request->input('limit') ?? 50;
        if(empty($id)) {
            $id = $this->request->input('id') ?? 0;
        }
        $query = Province::where('Id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('Name', 'like', "%$search%");
            });
        }
        if (!empty($id)){
            $query->where('Id', $id);
        }
        $query->orderBy('order_by', 'desc')
            ->orderBy('Name', 'asc');
        $data = $query->limit($limit)->get();
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListWard(){
        $search = $this->request->input('search') ?? null;
        $province_id = $this->request->input('province_id') ?? 0;
        $province_id_old = $this->request->input('province_id_old') ?? 0;
        $limit = $this->request->input('limit') ?? 50;
        $id = $this->request->input('id') ?? 0;
        $query = Ward::where('Id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('Name', 'like', "%$search%");
            });
        }
        if (!empty($id)){
            $query->where('Id', $id);
        }
        if (!empty($province_id_old)){
            $query->where('ProvinceId_old', $province_id_old);
        }
        $query->where('ProvinceId', $province_id);
        $data = $query->limit($limit)->get();
        if (!empty($data)){
            foreach ($data as $key => $value) {
                $data[$key]->Name = $value->Type.' '.$value->Name;
            }
        }
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListAddress(){
        $keyword = $this->request->input('keyword') ?? '';
        $province_id = $this->request->input('province_id') ?? 0;
        $ward_id = $this->request->input('ward_id') ?? 0;
        $google_api_key = $this->request->input('google_api_key') ?? 0;
        $province = Province::find($province_id);
        if (!empty($province) && !empty($keyword)) {
            $ward = Ward::find($ward_id);
            $province_name = !empty($province) ? $province->Type.' '.$province->Name : '';
            $ward_name = !empty($ward) ? $ward->Type.' '.$ward->Name : '';
            $searchLocation = $ward_name . ', ' .$province_name . ', Vietnam';
            $cacheKey = 'geocode_' . md5($searchLocation);
            $apiKey = $google_api_key;

            $location = Cache::remember($cacheKey, now()->addDays(7), function () use ($searchLocation,$apiKey) {
                $res = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $searchLocation,
                    'key' => $apiKey,
                ]);

                return $res->json()['results'][0]['geometry']['location'] ?? null;
            });
            $placesRes = Http::get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
                'query' => $keyword,
                'location' => "{$location['lat']},{$location['lng']}",
                'radius' => 1500, // 1.5km
                'key' => $apiKey,
            ]);
            $places = $placesRes->json()['results'] ?? [];

            $filtered = collect($places)->filter(function ($place) use ($ward_name, $province_name) {
                $addr = strtolower($place['formatted_address']);
                return str_contains($addr, strtolower($ward_name)) && str_contains($addr, strtolower($province_name));
            })->values();
            return response()->json([
                'data' => $filtered,
                'result' => true,
                'message' => 'Lấy danh sách thành công'
            ]);
        }
        return response()->json([
            'data' => [],
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }


    //lấy thông tin thành phố cũ
    public function getListProvinceSixtyFour($id = 0){
        $search = $this->request->input('search') ?? null;
        $limit = $this->request->input('limit') ?? 50;
        if(empty($id)) {
            $id = $this->request->input('id') ?? 0;
        }
        $id_province = $this->request->input('id_province') ?? 0;
        $query = DB::table('tbl_province_sixty_four');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        if (!empty($id_province)){
            $query->where('province_new', $id_province);
        }
        if (!empty($id)){
            $query->where('provinceid', $id);
        }
        $query->orderBy('name', 'asc');
        $data = $query->limit($limit)->get();
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }
    public function getListWardToAres(){
        $search = $this->request->input('search') ?? null;
        $limit = $this->request->input('limit') ?? 50;
        $id = $this->request->input('id') ?? 0;
        $id_ares = $this->request->input('id_ares') ?? [];
        if(!is_array($id_ares)) {
            $id_ares = explode(',', $id_ares);
        }
        $query = AresWard::where('tbl_ares_ward.id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('Name', 'like', "%$search%");
            });
        }
        $query->Join('tbl_wards', 'tbl_wards.id', '=', 'tbl_ares_ward.id_ward');
        if (!empty($id)){
//            $query->where('Id', $id);
        }
        $query->whereIn('id_ares', $id_ares);
        $data = $query->limit($limit)->get();
        if (!empty($data)){
            foreach ($data as $key => $value) {
                $data[$key]->Name = $value->Type.' '.$value->Name;
            }
        }
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }


}
