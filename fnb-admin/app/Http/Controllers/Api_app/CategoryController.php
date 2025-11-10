<?php

namespace App\Http\Controllers\Api_app;

use App\Models\User;
use App\Services\AccountService;
use App\Traits\UploadFile;
use Google\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\CategorySystemService;
use Illuminate\Support\Facades\Http;
use App\Models\MemberShipLevel;

class CategoryController extends AuthController
{
    protected $fnbCategorySystemService;
    protected $fnbCustomerService;

    public function __construct(Request $request, CategorySystemService $categorySystemService,AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbCategorySystemService = $categorySystemService;
        $this->fnbCustomerService = $accountService;
    }

    public function getListProvince($id = 0)
    {
        $params = $this->request->input('paramsCus');
        $select2 = !empty($params['select2']) ? $params['select2'] : false;
        if ($select2) {
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
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        if ($select2) {
            $results = [];
            foreach ($dtData as $key => $value) {
                $results[] = [
                    'id' => $value['Id'],
                    'text' => $value['Type'] . ' ' . $value['Name'],
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
        if ($select2) {
            $search = $this->request->input('term');
            $province_id = $params['province_id'] ?? 0;
            $province_id_old = $params['province_id_old'] ?? 0;
            $id_ares = $params['id_ares'] ?? 0;
        } else {
            $search = $this->request->input('search') ?? null;
            $id = $this->request->input('id') ?? 0;
            $province_id = $this->request->input('province_id') ?? 0;
            $province_id_old = $this->request->input('province_id_old') ?? 0;
            $id_ares = $this->request->input('id_ares') ?? 0;
        }
        $this->request->merge(['search' => $search]);
        $this->request->merge(['limit' => $limit]);
        $this->request->merge(['id' => $id]);
        $this->request->merge(['province_id' => $province_id]);
        $this->request->merge(['province_id_old' => $province_id_old ?? 0]);
        $this->request->merge(['id_ares' => $id_ares ?? 0]);
        $response = $this->fnbCategorySystemService->getListWard($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        if ($select2) {
            $results = [];
            foreach ($dtData as $key => $value) {
                $results[] = [
                    'id' => $value['Id'],
                    'text' => $value['Name'],
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


    public function getListWardToAres($id = 0)
    {
        $limit = $this->request->input('limit') ?? 150;
        $params = $this->request->input('paramsCus');
        $select2 = !empty($params['select2']) ? $params['select2'] : false;
        if ($select2) {
            $search = $this->request->input('term');
            $id_ares = $params['id_ares'] ?? 0;
        } else {
            $search = $this->request->input('search') ?? null;
            $id = $this->request->input('id') ?? 0;
            $id_ares = $this->request->input('id_ares') ?? 0;
        }
        if ($limit < 0) {
            $limit = PHP_INT_MAX;
        }
        $this->request->merge(['search' => $search]);
        $this->request->merge(['limit' => $limit]);
        $this->request->merge(['id' => $id]);
        $this->request->merge(['id_ares' => $id_ares ?? 0]);
        $response = $this->fnbCategorySystemService->getListWardToAres($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        if ($select2) {
            $results = [];
            foreach ($dtData as $key => $value) {
                $results[] = [
                    'id' => $value['Id'],
                    'text' => $value['Name'],
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

    public function getListAddress()
    {
        $province_id = $this->request->input('province_id') ?? 0;
        $ward_id = $this->request->input('ward_id') ?? 0;
        $keyword = $this->request->input('keyword') ?? null;
        $this->request->merge(['province_id' => $province_id]);
        $this->request->merge(['ward_id' => $ward_id]);
        $this->request->merge(['keyword' => $keyword]);
        $this->request->merge(['google_api_key' => get_option('google_api_key')]);

        $response = $this->fnbCategorySystemService->getListAddress($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
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
        $dtPaymentMode = PaymentMode::select('id', 'name', 'code', 'type', 'note')
            ->selectRaw('CONCAT("' . asset('storage') . '/", image) as image')
            ->where(function ($query) use ($search, $type_driver) {
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


    public function getListProvinceSixtyFour($id = 0)
    {
        $params = $this->request->input('paramsCus');
        $select2 = !empty($params['select2']) ? $params['select2'] : false;
        if ($select2) {
            $search = $this->request->input('term');
        } else {
            $search = $this->request->input('search') ?? null;
        }

        $id_province = $params['id_province'] ?? 0;
        $limit = 50;
        $this->request->merge(['search' => $search]);
        $this->request->merge(['limit' => $limit]);
        $this->request->merge(['id_province' => $id_province]);
        $response = $this->fnbCategorySystemService->getListProvinceSixtyFour($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        if ($select2) {
            $results = [];
            foreach ($dtData as $key => $value) {
                $results[] = [
                    'id' => $value['provinceid'],
                    'text' => $value['type'] . ' ' . $value['name'],
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

    public function getListWardToUser($id_user = 0)
    {
        if (empty($id_user)) {
            $id_user = $this->request->input('user_id');
        }
        $id_user = is_array($id_user) ? $id_user : [$id_user];
        $data = DB::table('tbl_user_ares_ward')->whereIn('id_user', $id_user)->get();
        $dataWard = [];
        foreach ($data as $key => $value) {
            $dataWard[] = $value->id_ward;
        }
        return response()->json([
            'data' => array_unique($dataWard),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListMemberShip($id = '0')
    {
        $customer_id = $this->request->input('customer_id') ?? 0;
        $data = MemberShipLevel::select(
            'id', 'name', 'color', 'icon', 'image', 'color_background', 'background_header', 'radio_discount',
            'point_end', 'point_start', 'color_button',
        )->where(function ($q) use ($id) {
            if (!empty($id)) {
                $q->where('id', $id);
            }
        })->get();


        $dataMemberShip = [];
        $arrId = [0];
        foreach ($data as $key => $value) {
            $url = env('STORAGE_URL') ?? config('app.storage_url');
            $value->icon = !empty($value->icon) ? ($url . '/' . $value->icon) : null;
            $value->image = !empty($value->image) ? ($url . '/' . $value->image) : null;
            $value->background_header = !empty($value->background_header) ? ($url . '/' . $value->background_header) : null;

            if ($value->id == 4) {
                $value->name_radio_discount = 'Hạn mức thẻ công nợ tối đa 50 triệu đồng';
            } else {
                $value->name_radio_discount = !empty($value->radio_discount) ? ($value->radio_discount . '%') : null;
            }
            $dataMemberShip[] = $value;
            if ($id == 1){
                $arrId = [1,2,3];
            } elseif ($id == 2){
                $arrId = [2,3,4];
            } elseif ($id == 3){
                $arrId = [3,4];
            } elseif ($id == 4){
                $arrId = [4];
            }
        }

        $point_current = 0;
        $this->requestCustomer = new Request();
        $this->requestCustomer->merge(['id' => $customer_id]);
        $dtCustomer = $this->fnbCustomerService->getDetailCustomer($this->requestCustomer);
        $dtCustomer = $dtCustomer->getData(true);
        $dtCustomer = $dtCustomer['client'] ?? [];
        $point_current = $dtCustomer['point_membership'] ?? 0;

        if (!empty($id)) {
            $dataNext = MemberShipLevel::select(
                'id', 'name', 'color', 'icon', 'image', 'color_background', 'background_header', 'radio_discount',
                'point_end', 'point_start', 'invoice_limit'
            )->where(function ($q) use ($id) {
                if (!empty($id)) {
                    $q->where('id', ($id + 1));
                }
            })->first();
            if (!empty($dataNext->id)) {
                $url = env('STORAGE_URL') ?? config('app.storage_url');
                $dataNext->icon = !empty($dataNext->icon) ? ($url . '/' . $dataNext->icon) : null;
                $dataNext->image = !empty($dataNext->image) ? ($url . '/' . $dataNext->image) : null;
                $dataNext->background_header = !empty($dataNext->background_header) ? ($url . '/' . $dataNext->background_header) : null;

                if ($dataNext->id == 4) {
                    $dataNext->name_radio_discount = 'Hạn mức thẻ công nợ tối đa 50 triệu đồng';
                } else {
                    $dataNext->name_radio_discount = !empty($dataNext->radio_discount) ? ($dataNext->radio_discount . '%') : null;
                }
                $dataMemberShipNext = $dataNext;
            }

            $dtDataCurrent = MemberShipLevel::select(
                'id', 'name', 'color', 'icon', 'image', 'color_background', 'background_header', 'radio_discount',
                'point_end', 'point_start', 'invoice_limit'
            )->where(function ($q) use ($arrId) {
              $q->whereIn('id', $arrId);
            })->get();
            if (!empty($dtDataCurrent)) {
                foreach ($dtDataCurrent as $key => $value){
                    $url = env('STORAGE_URL') ?? config('app.storage_url');
                    $value->icon = !empty($value->icon) ? ($url . '/' . $value->icon) : null;
                    $value->image = !empty($value->image) ? ($url . '/' . $value->image) : null;
                    $value->background_header = !empty($value->background_header) ? ($url . '/' . $value->background_header) : null;

                    if ($value->id == 4) {
                        $value->name_radio_discount = 'Hạn mức thẻ công nợ tối đa 50 triệu đồng';
                    } else {
                        $value->name_radio_discount = !empty($value->radio_discount) ? ($value->radio_discount . '%') : null;
                    }
                }
            }
        }

        $htmlPoint = '';
        if ($id == 1){
            $dtDataCurrentNew = $dtDataCurrent->filter(function ($item) {
                return in_array($item->id, [2]);
            })->values()->all()[0] ?? null;
            if (!empty($dtDataCurrentNew)){
                $htmlPoint = 'Tích thêm '. ($dtDataCurrentNew->point_start - $point_current <= 0 ? 0 : ($dtDataCurrentNew->point_start - $point_current)) .' điểm để đạt hạng '.$dtDataCurrentNew->name;
            }
        } elseif($id == 2){
            $dtDataCurrentNew = $dtDataCurrent->filter(function ($item) {
                    return in_array($item->id, [3]);
                })->values()->all()[0] ?? null;
            if (!empty($dtDataCurrentNew)){
                $htmlPoint = 'Tích thêm '. ($dtDataCurrentNew->point_start - $point_current <= 0 ? 0 : ($dtDataCurrentNew->point_start - $point_current)) .' điểm để đạt hạng '.$dtDataCurrentNew->name;
            }
        } elseif ($id == 3){
            $dtDataCurrentNew = $dtDataCurrent->filter(function ($item) {
                    return in_array($item->id, [4]);
                })->values()->all()[0] ?? null;
            if (!empty($dtDataCurrentNew)){
                $htmlPoint = 'Tích thêm '. ($dtDataCurrentNew->point_start - $point_current <= 0 ? 0 : ($dtDataCurrentNew->point_start - $point_current)) .' điểm để đạt hạng '.$dtDataCurrentNew->name;
            }
        } elseif ($id == 4){
            $dtDataCurrentNew = $dtDataCurrent->filter(function ($item) {
                    return in_array($item->id, [4]);
                })->values()->all()[0] ?? null;
            if (!empty($dtDataCurrentNew)){
                $htmlPoint = 'Duy trì '. $dtDataCurrentNew->point_start .' điểm để giữ hạng '.$dtDataCurrentNew->name;
            }
        }

        $dataMemberShipAll = [];
        if (!empty($customer_id)){
            $data = MemberShipLevel::select(
                'id', 'name', 'color', 'icon', 'image', 'color_background', 'background_header', 'radio_discount',
                'point_end', 'point_start', 'color_button',
            )->get();

            foreach ($data as $key => $value) {
                $url = env('STORAGE_URL') ?? config('app.storage_url');
                $value->icon = !empty($value->icon) ? ($url . '/' . $value->icon) : null;
                $value->image = !empty($value->image) ? ($url . '/' . $value->image) : null;
                $value->background_header = !empty($value->background_header) ? ($url . '/' . $value->background_header) : null;

                if ($value->id == 4) {
                    $value->name_radio_discount = 'Hạn mức thẻ công nợ tối đa 50 triệu đồng';
                } else {
                    $value->name_radio_discount = !empty($value->radio_discount) ? ($value->radio_discount . '%') : null;
                }
                $active = 0;
                if ($id >= $value->id){
                    $active = 1;
                }
                $value->active = $active;
                $dataMemberShipAll[] = $value;
            }
        }


        return response()->json([
            'data' => $dataMemberShip,
            'data_all' => $dataMemberShipAll,
            'data_current' => $dtDataCurrent ?? [],
            'data_next' => $dataMemberShipNext ?? [],
            'data_point' => [
                'point_current' => $point_current,
                'text' => $htmlPoint
            ],
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }


    public function getLisMembershipExpense()
    {
        $data = DB::table('tbl_membership_expense')->select('*')->get();
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getLisMembershipPurchases()
    {
        $data = DB::table('tbl_membership_purchases')->select('*')->get();
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getLisMembershipLongTerm()
    {
        $data = DB::table('tbl_membership_long_term')->select('*')->get();
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListStaff()
    {
        $params = $this->request->input('paramsCus');
        $select2 = !empty($params['select2']) ? $params['select2'] : false;
        if ($select2) {
            $search = $this->request->input('term');
        } else {
            $search = $this->request->input('search') ?? null;
            $id = $this->request->input('id') ?? 0;
        }
        $limit = 50;
        $query = User::select('id','name','code','email','image','phone')->where('id','!=',0);
        if (!empty($search)){
            $query->where(function($q) use ($search){
                $q->where('name','like','%'.$search.'%');
                $q->orWhere('code','like','%'.$search.'%');
                $q->orWhere('phone','like','%'.$search.'%');
            });
        }
        if (!empty($id)){
            $query->where('id',$id);
        }
        $query->limit($limit);
        $dtData = $query->get();
        if ($select2) {
            $results = [];
            foreach ($dtData as $key => $value) {
                $results[] = [
                    'id' => $value['id'],
                    'text' => $value['name'] . ' (' . $value['code'].')',
                ];
            }
            $data = [
                'items' => $results
            ];
        } else {
            $storageUrl = config('app.storage_url');
            if (!empty($dtData)){
                foreach ($dtData as $key => $value){
                    $value->image = !empty($value->image) ? $storageUrl.'/'.$value->image : null;
                }
            }
            $data = [
                'data' => $dtData,
                'message' => lang('Lấy danh sách thành công')
            ];
        }
        return response()->json($data);
    }

    public function getListDataKPI(){
        $month_search = $this->request->input('month_search') ?? date('m');
        $year_search = $this->request->input('year_search') ?? date('Y');

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $query = DB::table('tbl_calculate_kpi');
        $query ->join('tbl_calculate_kpi_detail', 'tbl_calculate_kpi_detail.calculate_kpi_id', '=', 'tbl_calculate_kpi.id');
        $query ->join('tbl_user_ares', 'tbl_user_ares.id_user', '=', 'tbl_calculate_kpi_detail.staff_id');
        $query->select(
            'tbl_calculate_kpi_detail.name_kpi as name_kpi',
            'tbl_user_ares.id_ares as id_ares',
            DB::raw('COUNT(tbl_calculate_kpi_detail.id) as total'),
        );
        if (!empty($ares_permission)){
            $query->whereIn('tbl_calculate_kpi_detail.staff_id', ($user_id ?? [0]));
        }
        $query->where('tbl_calculate_kpi.month', '=',$month_search);
        $query->where('tbl_calculate_kpi.year', '=',$year_search);
        $query->groupBy('tbl_calculate_kpi_detail.name_kpi', 'tbl_user_ares.id_ares');
        $data = $query->get();

        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }
}
