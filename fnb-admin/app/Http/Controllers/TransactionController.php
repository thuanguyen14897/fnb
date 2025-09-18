<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\GroupCategoryService;
use App\Services\ServiceService;
use App\Services\TransactionService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class TransactionController extends Controller
{
    protected $fnbService;
    protected $fnbCustomerService;
    protected $fnbGroupCategoryService;
    protected $fnbCategoryService;
    protected $fnbOtherAmenitisService;
    protected $fnbTransactionService;
    use UploadFile;
    public function __construct(Request $request,ServiceService $serviceService,AccountService $accountService,CategoryService $categoryService,GroupCategoryService $groupCategoryService,TransactionService $transactionService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbService = $serviceService;
        $this->fnbCustomerService = $accountService;
        $this->fnbGroupCategoryService = $groupCategoryService;
        $this->fnbCategoryService = $categoryService;
        $this->fnbTransactionService = $transactionService;
    }

    public function get_list(){
        if (!has_permission('transaction','view') && !has_permission('transaction', 'viewown')) {
            access_denied();
        }
        $title = lang('dt_transaction');
        return view('admin.transaction.list',[
            'title' => $title
        ]);
    }

    public function getList()
    {
        if (!has_permission('transaction', 'view') && !has_permission('transaction', 'viewown')) {
            $data['result'] = false;
            $data['message'] = lang('Không có quyền xem!');
            $data['data'] = [];
            return response()->json($data);
        }
        if (!has_permission('transaction','view') && has_permission('transaction','viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }
        $response = $this->fnbTransactionService->getList($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $view = "<a href='admin/transaction/view/$id' class='dt-modal'><i class='fa fa-eye'></i> " . lang('dt_view_transaction') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/transaction/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_transaction') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $view . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->addColumn('reference_no', function ($dtData){
                $id = $dtData['id'];
                return "<a class='dt-modal' href='admin/transaction/view/$id'>".$dtData['reference_no']."</a>";
            })
            ->editColumn('date', function ($dtData) {
                return '<div>'.(!empty($dtData['date']) ? _dt($dtData['date']) : '').'</div>';
            })
            ->editColumn('date_start', function ($dtData) {
                return '<div>'.(!empty($dtData['date_start']) ? _dt_new($dtData['date_start'],false) : '').'</div>';
            })
            ->editColumn('date_end', function ($dtData) {
                return '<div>'.(!empty($dtData['date_end']) ? _dt_new($dtData['date_end'],false) : '').'</div>';
            })
            ->editColumn('info', function ($dtData) {
                $day = date_range(_dthuan($dtData['date_start']), _dthuan($dtData['date_end']));
                $day = count($day);
                $countServiceAgree = collect($dtData['transaction_day_item'])->where('status','=',1)->count();
                $countService = collect($dtData['transaction_day_item'])->count();
                return '<div>
                    <div>
                    <img src="admin/assets/images/day.png" style="width: 20px;height:20px;margin-right: 5px">
                    <b>'.$day.'</b> ngày</div>
                    <div>
                     <img src="admin/assets/images/location.png" style="width: 20px;height:20px;margin-right: 5px">
                     <b>'.$countService.'</b> điểm đến</div>
                    <div style="color:#FF5A1F">
                     <img src="admin/assets/images/location.png" style="width: 20px;height:20px;margin-right: 5px">
                    <b>'.$countServiceAgree.'</b> điểm đã xác nhận</div>
                </div>';
            })
            ->editColumn('status', function ($transaction) {
                $optionStatus = '<div class="btn-group">
                                 <button type="button" class="btn btn-white dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false" style="min-width: 150px;border: 1px solid '.getValueStatusTransaction($transaction['status'],'color').' !important">
                                 <div class="label" style="color: '.getValueStatusTransaction($transaction['status'],'color').'">'.getValueStatusTransaction($transaction['status']).'</div>
                                 <span class="caret"></span> </button>
                                 <ul class="dropdown-menu">';
                foreach (getListStatusTransaction() as $key => $value){
                    $index = getValueStatusTransaction($transaction['status'],'index');
                    $arr = [Config::get('constant')['cancel_guest'],Config::get('constant')['cancel_owen'],Config::get('constant')['cancel_system']];
                    $check = 0;
                    if ($value['id'] == Config::get('constant')['cancel_owen']){
                        $check = 1;
                    }
                    $classes = '';
                    if ($value['index'] < $index){
                        if (!in_array($value['id'],$arr)) {
                            $classes = 'pointer-events';
                        }
                    }
                    if ($transaction['status'] == Config::get('constant')['status_transaction_finish']){
                        if ($value['id'] != Config::get('constant')['status_transaction_finish']){
                            $classes = 'pointer-events';
                        }
                    }
                    if ($value['id'] == Config::get('constant')['cancel_system']){
                        $classes = 'pointer-events';
                    }
                    $optionStatus .= '<li style="cursor: pointer" class="'.$classes.'"><a onclick="changeStatus('.$transaction['id'].','.$value['id'].','.$check.')" data-id="'.$value['id'].'">'.$value['name'].'</a></li>';
                }
                $optionStatus .= '</ul></div>';

                $optionStatus .= '<div>'.(!empty($transaction['date_status']) ? _dt($transaction['date_status']) : '').'</div>';
                return $optionStatus;
            })
            ->addColumn('user_id', function ($transaction) {
                $htmlImage = '';
                if (!empty($transaction['transaction_staff'])){
                    foreach ($transaction['transaction_staff'] as $key => $value){
                        $url = !empty(($value['image'])) ? $value['image'] : asset('admin/assets/images/users/avatar-1.jpg');
                        $htmlImage.= '<div data-toggle="tooltip" data-placement="top" title="'.$value['name'].'">'.loadImageAvatar($url,'40px').'</div>';
                    }
                }
                return '<div>'.$htmlImage.'</div>';
            })
            ->addColumn('customer', function ($dtData) {
                $customer = $dtData['customer'] ?? [];
                $url = !empty($customer['avatar_new']) ? $customer['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div>'.(!empty($customer['fullname']) ? $customer['fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($customer['phone']) ? $customer['phone'] : 'Chưa có sdt').'</div>';
            })
            ->rawColumns(['options', 'reference_no', 'date', 'date_start','id','date_end','user_id','status','customer','info'])
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
        $arrIdOtherAmenitiesCar = [];
        $arrDate = [];
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
            if (!empty($dtData['other_amenities'])) {
                foreach ($dtData['other_amenities'] as $key => $value) {
                    $arrIdOtherAmenitiesCar[] = $value['id'];
                }
            }

            if (!empty($dtData['day'])) {
                foreach ($dtData['day'] as $key => $value) {
                    $arrDate[] = $value['day'];
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
            'arrIdOtherAmenitiesCar' => $arrIdOtherAmenitiesCar,
            'arrDate' => $arrDate,
        ]);
    }

    public function detail($id = 0) {
        $this->request->merge(['id' => $id]);
        $price = !empty($this->request->input('price')) ? number_unformat($this->request->input('price')) : 0;
        $this->request->merge(['price' => $price]);
        $response = $this->fnbService->detail($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        if (!has_permission('transaction', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbTransactionService->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function countAll(){
        if (!has_permission('transaction','view') && has_permission('transaction','viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }
        $response = $this->fnbTransactionService->countAll($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function view($id = 0){
        if (!has_permission('transaction','view') && !has_permission('transaction', 'viewown')) {
            access_denied(true, lang('dt_access'));
        }
        $title = lang('dt_view_transaction');
        $this->request->merge(['id' => $id]);
        $response = $this->fnbTransactionService->getListDetailTransaction($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        return view('admin.transaction.view',[
            'title' => $title,
            'dtData' => $dtData['data'] ?? [],
        ]);
    }

    public function changeStatus(){
        if (!has_permission('transaction','approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $transaction_id = $this->request->input('transaction_id') ?? 0;
        $status = $this->request->input('status') ?? 0;
        $this->request->merge(['status' => $status]);
        $this->request->merge(['staff_status' => Config::get('constant')['user_admin']]);
        $this->request->merge(['transaction_id' => $transaction_id]);
        $responseUpdate =  $this->fnbTransactionService->changeStatus($this->request);
        $dtUpdate = $responseUpdate->getData(true);
        $data = $dtUpdate['data'] ?? [];
        return response()->json($data);
    }

    public function changeStatusItem(){
        if (!has_permission('transaction','approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $transaction_id = $this->request->input('transaction_id') ?? 0;
        $status = $this->request->input('status') ?? 0;
        $this->request->merge(['status' => $status]);
        $this->request->merge(['staff_status' => Config::get('constant')['user_admin']]);
        $this->request->merge(['transaction_id' => $transaction_id]);
        $responseUpdate =  $this->fnbTransactionService->changeStatusItem($this->request);
        $dtUpdate = $responseUpdate->getData(true);
        $data = $dtUpdate['data'] ?? [];
        return response()->json($data);
    }
}
