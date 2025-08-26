<?php

namespace App\Http\Controllers\Api_app;
use App\Traits\UploadFile;
use Google\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\CategorySystemService;
use Illuminate\Support\Facades\Http;

class CategoryController extends AuthController
{
    protected $fnbCategorySystemService;
    public function __construct(Request $request,CategorySystemService $categorySystemService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbCategorySystemService = $categorySystemService;
    }

    public function getListProvince($id = 0)
    {
        $params = $this->request->input('paramsCus');
        $select2 = !empty($params['select2']) ? $params['select2'] : false;
        if ($select2){
            $search = $this->request->input('term');
        } else {
            $search = $this->request->input('search') ?? null;
            $id = $this->request->input('id') ?? 0;
        }
        $limit = 50;
        $this->request->merge(['search' => $search]);
        $this->request->merge(['limit' => $limit]);
        $this->request->merge(['id' => $id]);
        $response = $this->fnbCategorySystemService->getListProvince($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        if ($select2){
            $results = [];
            foreach ($dtData as $key => $value) {
                $results[] = [
                    'id' => $value['Id'],
                    'text' => $value['Type'].' '.$value['Name'],
                ];
            }
            $data = [
                'items' => $results
            ];
        } else {
            $data = [
                'data' => $dtData
            ];
        }
        return response()->json($data);
    }

    public function getListWard($id = 0)
    {
        $limit = 150;
        $params = $this->request->input('paramsCus');
        $select2 = !empty($params['select2']) ? $params['select2'] : false;
        if ($select2){
            $search = $this->request->input('term');
            $province_id = $params['province_id'] ?? 0;
        } else {
            $search = $this->request->input('search') ?? null;
            $id = $this->request->input('id') ?? 0;
            $province_id = $this->request->input('province_id') ?? 0;
        }
        $this->request->merge(['search' => $search]);
        $this->request->merge(['limit' => $limit]);
        $this->request->merge(['id' => $id]);
        $this->request->merge(['province_id' => $province_id]);
        $response = $this->fnbCategorySystemService->getListWard($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        if ($select2){
            $results = [];
            foreach ($dtData as $key => $value) {
                $results[] = [
                    'id' => $value['Id'],
                    'text' =>$value['Name'],
                ];
            }
            $data = [
                'items' => $results
            ];
        } else {
            $data = [
                'data' => $dtData
            ];
        }
        return response()->json($data);
    }

    public function getListAddress(){
        $province_id = $this->request->input('province_id') ?? 0;
        $ward_id = $this->request->input('ward_id') ?? 0;
        $keyword = $this->request->input('keyword') ?? null;
        $this->request->merge(['province_id' => $province_id]);
        $this->request->merge(['ward_id' => $ward_id]);
        $this->request->merge(['keyword' => $keyword]);
        $this->request->merge(['google_api_key' => get_option('google_api_key')]);

        $response = $this->fnbCategorySystemService->getListAddress($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        $data = [
            'data' => $dtData
        ];
        return response()->json($data);
    }

    public function getListPaymentMode()
    {
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $type_driver = !empty($this->request->input('type_driver')) ? $this->request->input('type_driver') : 1;
        $dtPaymentMode = PaymentMode::select('id','name','code','type','note')
            ->selectRaw('CONCAT("' . asset('storage') . '/", image) as image')
            ->where(function ($query) use ($search,$type_driver) {
                if (!empty($search)) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
                if ($type_driver == 1) {
                    $query->where('active', 1);
                } else {
                    $query->where('active', 1);
                    $query->orWhere('id', 4);
                }
            })
            ->orderByRaw('id desc')->get();
        $data['data'] = $dtPaymentMode;
        return response()->json($data);
    }
}
