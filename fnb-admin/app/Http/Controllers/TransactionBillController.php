<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\GroupCategoryService;
use App\Services\ServiceService;
use App\Services\TransactionBillService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class TransactionBillController extends Controller
{
    protected $fnbService;
    protected $fnbCustomerService;
    protected $fnbOtherAmenitisService;
    protected $fnbTransactionBillService;
    use UploadFile;
    public function __construct(Request $request,ServiceService $serviceService,AccountService $accountService,TransactionBillService $transactionBillService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbService = $serviceService;
        $this->fnbCustomerService = $accountService;
        $this->fnbTransactionBillService = $transactionBillService;
    }

    public function get_list(){
        if (!has_permission('transaction_bill','view') && !has_permission('transaction_bill','viewown')) {
            access_denied();
        }
        $title = lang('dt_transaction_bill');
        return view('admin.transaction_bill.list',[
            'title' => $title
        ]);
    }

    public function getList()
    {
        if (!has_permission('transaction_bill', 'view') && !has_permission('transaction_bill','viewown')) {
            $data['result'] = false;
            $data['message'] = lang('Không có quyền xem!');
            $data['data'] = [];
            return response()->json($data);
        }
        if (!has_permission('transaction_bill','view') && has_permission('transaction_bill','viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }
        $response = $this->fnbTransactionBillService->getList($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $view = "<a href='admin/transaction_bill/view/$id' class='dt-modal'><i class='fa fa-eye'></i> " . lang('dt_view_transaction_bill') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/transaction_bill/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_transaction_bill') . '</a>';
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
                return "<a class='dt-modal' href='admin/transaction_bill/view/$id'>".$dtData['reference_no']."</a>";
            })
            ->editColumn('date', function ($dtData) {
                return '<div>'.(!empty($dtData['date']) ? _dt($dtData['date']) : '').'</div>';
            })
            ->editColumn('transaction', function ($dtData) {
                $transaction = $dtData['transaction'] ?? [];
                if (!empty($transaction)) {
                    return '<div><a class="dt-modal" href="admin/transaction/view/' . $transaction['id'] . '">' . ($dtData['transaction']['reference_no'] ?? '') . '</a></div>';
                } else {
                    return '';
                }
            })
            ->editColumn('service', function ($dtData) {
                $service = $dtData['service'] ?? [];
                $url = !empty($service['image']) ? $service['image'] : asset('admin/assets/images/no_service.png');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div><a target="_blank" href="admin/service/view/'.($service['id'] ?? 0).'">'.($dtData['service']['name'] ?? '') . '</a></div></div>';
            })
            ->editColumn('status', function ($transaction) {
                $optionStatus = '<div class="btn-group">
                                 <button type="button" class="btn btn-white dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false" style="min-width: 150px;border: 1px solid '.getValueStatusTransaction($transaction['status'],'color').' !important">
                                 <div class="label" style="color: '.getValueStatusTransaction($transaction['status'],'color').'">'.getValueStatusTransactionBill($transaction['status']).'</div>
                                 <span class="caret"></span> </button>
                                 <ul class="dropdown-menu">';
                foreach (getListStatusTransactionBill() as $key => $value){
                    $index = getValueStatusTransactionBill($transaction['status'],'index');
                    $arr = [Config::get('constant')['status_transaction_bill_cancel']];
                    $check = 0;
                    $classes = '';
                    if ($value['index'] < $index){
                        if (!in_array($value['id'],$arr)) {
                            $classes = 'pointer-events';
                        }
                    }
                    if ($transaction['status'] == Config::get('constant')['status_transaction_bill_approve']){
                        if ($value['id'] != Config::get('constant')['status_transaction_bill_approve']){
                            $classes = 'pointer-events';
                        }
                    }
                    if ($value['id'] == Config::get('constant')['status_transaction_bill_cancel']){
                        if ($value['id'] == Config::get('constant')['status_transaction_bill_approve']){
                            $classes = 'pointer-events';
                        }
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
            ->addColumn('partner', function ($dtData) {
                $partner = $dtData['partner'] ?? [];
                $url = !empty($partner['avatar_new']) ? $partner['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div>'.(!empty($partner['fullname']) ? $partner['fullname'] : '') . '</div></div>';
            })
            ->editColumn('total', function ($dtData) {
                return '<div>'.(!empty($dtData['total']) ? formatMoney($dtData['total']) : 0).'</div>';
            })
            ->editColumn('total_discount', function ($dtData) {
                return '<div>'.(!empty($dtData['total_discount']) ? formatMoney($dtData['total_discount']) : 0).'</div>';
            })
            ->editColumn('grand_total', function ($dtData) {
                return '<div>'.(!empty($dtData['grand_total']) ? formatMoney($dtData['grand_total']) : 0).'</div>';
            })
            ->rawColumns(['options', 'reference_no', 'date', 'date_start','id','service','user_id','status','customer','transaction','partner','grand_total','total_discount','total'])
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
        if (!has_permission('transaction_bill', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbTransactionBillService->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function countAll(){
        if (!has_permission('transaction_bill','view') && has_permission('transaction_bill','viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }
        $response = $this->fnbTransactionBillService->countAll($this->request);
        $data = $response->getData(true);
        return response()->json($data);
    }

    public function view($id = 0){
         if (!has_permission('transaction_bill','view') && !has_permission('transaction_bill', 'viewown')) {
             access_denied(true, lang('dt_access'));
         }
        $title = lang('dt_view_transaction_bill');
        $this->request->merge(['id' => $id]);
        $response = $this->fnbTransactionBillService->getListDetailTransaction($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        return view('admin.transaction_bill.view',[
            'title' => $title,
            'dtData' => $dtData['data'] ?? [],
        ]);
    }

    public function changeStatus(){
        if (!has_permission('transaction_bill','approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $transaction_id = $this->request->input('transaction_id') ?? 0;
        $status = $this->request->input('status') ?? 0;
        $this->request->merge(['status' => $status]);
        $this->request->merge(['staff_status' => Config::get('constant')['user_admin']]);
        $this->request->merge(['transaction_id' => $transaction_id]);
        $responseUpdate =  $this->fnbTransactionBillService->changeStatus($this->request);
        $dtUpdate = $responseUpdate->getData(true);
        $data = $dtUpdate['data'] ?? [];
        return response()->json($data);
    }
}
