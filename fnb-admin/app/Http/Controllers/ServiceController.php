<?php

namespace App\Http\Controllers;

use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ServiceService;
use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\GroupCategoryService;
use App\Services\OtherAmenitisService;
use Yajra\DataTables\CollectionDataTable;

class ServiceController extends Controller
{
    protected $fnbService;
    protected $fnbCustomerService;
    protected $fnbGroupCategoryService;
    protected $fnbCategoryService;
    protected $fnbOtherAmenitisService;
    use UploadFile;
    public function __construct(Request $request,ServiceService $serviceService,AccountService $accountService,CategoryService $categoryService,GroupCategoryService $groupCategoryService,OtherAmenitisService $otherAmenitisService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbService = $serviceService;
        $this->fnbCustomerService = $accountService;
        $this->fnbGroupCategoryService = $groupCategoryService;
        $this->fnbCategoryService = $categoryService;
        $this->fnbOtherAmenitisService = $otherAmenitisService;
    }

    public function get_list(){
        if (!has_permission('service','view')) {
            access_denied();
        }
        $title = lang('dt_service_list');
        return view('admin.service.list',[
            'title' => $title
        ]);
    }

    public function getList()
    {
        $search = $this->request->input('search.value') ?? null;
        $customer_search_value = [];
        if (!empty($search)){
            $this->requestCustomer = clone $this->request;
            $this->requestCustomer->merge(['search' => $search]);
            $responseCustomer = $this->fnbCustomerService->getListData($this->requestCustomer);
            $dataCustomer = $responseCustomer->getData(true);
            $customer_search_value = array_merge($customer_search_value, array_column($dataCustomer['data'], 'id'));
        }
        $this->request->merge(['customer_search_value' => array_unique($customer_search_value)]);
        $response = $this->fnbService->getList($this->request);
        $data = $response->getData(true);
        $customer_ids = [];
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $customer_ids = $data['customer_ids'] ?? [];
        $this->requestCustomer = clone $this->request;
        $this->requestCustomer->merge(['customer_id' => $customer_ids]);
        $this->requestCustomer->merge(['search' => null]);
        $responseCustomer = $this->fnbCustomerService->getListData($this->requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);
        $customers = collect($dataCustomer['data']);
        $dtData = $dtData->map(function ($item) use ($customers) {
            $customer = $customers->where('id','=',$item['customer_id'])->first();
            return [
                ...$item,
                'customer' => $customer,
            ];
        });
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $edit = "<a href='admin/service/detail/$id'><i class='fa fa-pencil'></i> " . lang('dt_edit_service') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/service/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_service') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('name', function ($dtData) {
                $str = '<div>' . $dtData['name'] . '</div>';
                return $str;
            })
            ->editColumn('price', function ($dtData) {
                $str = '<div class="text-right">' . (!empty($dtData['price']) ? formatMoney($dtData['price']) : 0) . '</div>';
                return $str;
            })
            ->editColumn('active', function ($dtData) {
                $checked = $dtData['active'] == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/service/active/'.$dtData['id'].'" data-status="'.$dtData['active'].'"></div>';
                return $str;
            })
            ->editColumn('hot', function ($dtData) {
                $checked = $dtData['hot'] == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/service/changeHot/'.$dtData['id'].'" data-status="'.$dtData['hot'].'"></div>';
                return $str;
            })
            ->editColumn('image', function ($dtData) {
                $dtImage = !empty($dtData['image']) ? $dtData['image'] : null;
                return loadImageNew($dtImage,'110px','img-rounded','',false);
            })
            ->addColumn('group_category_service_id', function ($dtData) {
                $str = '<div class="text-center">' . (!empty($dtData['group_category_service']) ? $dtData['group_category_service']['name'] : '' ) . '</div>';
                return $str;
            })
            ->addColumn('category_service_id', function ($dtData) {
                $str = '<div class="text-center">' . (!empty($dtData['category_service']) ? $dtData['category_service']['name'] : '' ) . '</div>';
                return $str;
            })
            ->addColumn('province_id', function ($dtData) {
                $str = '<div class="text-left">' . (!empty($dtData['province']) ? $dtData['province']['Type'].' '.$dtData['province']['Name'] : '' ).',' .(!empty($dtData['ward']) ? $dtData['ward']['Type'].' '. $dtData['ward']['Name'] : '' ). '</div>';
                return $str;
            })
            ->addColumn('customer_id', function ($dtData) {
                $customer = $dtData['customer'] ?? [];
                $url = !empty($customer['avatar']) ? $customer['avatar'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div>'.(!empty($customer['fullname']) ? $customer['fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($customer['phone']) ? $customer['phone'] : 'Chưa có sdt').'</div>';
            })
            ->rawColumns(['options', 'active', 'image', 'name','id','category_service_id','group_category_service_id','province_id','customer_id','hot','price'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function get_detail($id = 0) {
        $this->request->merge(['id' => $id]);
        $arrDate = [];
        $arrIdOtherAmenitiesCar = [];
        if (empty($id)){
            if (!has_permission('service', 'add')) {
                access_denied(true, lang('Không có quyền thêm'));
            }
            $title = lang('dt_add_service');
        } else {
            if (!has_permission('service', 'edit')) {
                access_denied(true, lang('Không có quyền sửa'));
            }
            $title = lang('dt_edit_service');
            $response = $this->fnbService->getDetail($this->request);
            $data = $response->getData(true);
            $dtData = $data['dtData'] ?? [];
            $this->request->merge(['customer_id' => [$dtData['customer_id'] ?? [0]]]);
            $responseCustomer = $this->fnbCustomerService->getListData($this->request);
            $dataCustomer = $responseCustomer->getData(true);
            $customers = collect($dataCustomer['data']);
            $dtData['customer'] = $customers->where('id','=',$dtData['customer_id'])->first();
            $days = $dtData['day'] ?? [];
            if (!empty($days)){
                foreach ($days as $day){
                    $arrDate[] = $day['day'];
                }
            }
            $other_amenities = $dtData['other_amenities'] ?? [];
            if (!empty($other_amenities)){
                foreach ($other_amenities as $key => $value){
                    $arrIdOtherAmenitiesCar[] = $value['id'];
                }
            }
        }
        $titleService = lang('dt_service_list');
        $dtCategoryService = [];
        $dtProvince = [];
        $dtWard = [];
        $otherAmenities = $this->fnbOtherAmenitisService->getListData($this->request)->getData(true)['data'] ?? [];
        return view('admin.service.detail',[
            'id' => $id,
            'title' => $title,
            'titleService' => $titleService,
            'dtData' => $dtData ?? [],
            'dtCategoryService' => $dtCategoryService,
            'dtProvince' => $dtProvince,
            'dtWard'=> $dtWard,
            'otherAmenities' => $otherAmenities,
            'arrDate' => $arrDate,
            'arrIdOtherAmenitiesCar' => $arrIdOtherAmenitiesCar,
        ]);
    }

    public function detail($id = 0) {
        $price = number_unformat($this->request->input('price',0));
        $this->request->merge(['id' => $id]);
        $this->request->merge(['price' => $price]);
        $response = $this->fnbService->detail($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        if (!has_permission('service', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbService->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function active($id = 0){
        if (!has_permission('service', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbService->active($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function changeHot($id = 0){
        if (!has_permission('service', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbService->changeHot($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }
}
