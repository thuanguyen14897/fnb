<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\GroupCategoryService;
use App\Services\PaymentService;
use App\Services\ServiceService;
use App\Services\TransactionBillService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class PaymentController extends Controller
{
    protected $fnbService;
    protected $fnbCustomerService;
    protected $fnbOtherAmenitisService;
    protected $fnbPaymentService;
    use UploadFile;
    public function __construct(Request $request,ServiceService $serviceService,AccountService $accountService,PaymentService $paymentService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbService = $serviceService;
        $this->fnbCustomerService = $accountService;
        $this->fnbPaymentService = $paymentService;
    }

    public function get_list(){
        if (!has_permission('payment','view') && !has_permission('payment','viewown')) {
            access_denied();
        }
        $title = lang('dt_payment');
        return view('admin.payment.list',[
            'title' => $title
        ]);
    }

    public function getList()
    {
        if (!has_permission('payment', 'view') && !has_permission('payment','viewown')) {
            $data['result'] = false;
            $data['message'] = lang('Không có quyền xem!');
            $data['data'] = [];
            return response()->json($data);
        }
        if (!has_permission('payment','view') && has_permission('payment','viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }
        $response = $this->fnbPaymentService->getList($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $view = "<a href='admin/payment/view/$id' class='dt-modal'><i class='fa fa-eye'></i> " . lang('dt_view_payment') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/payment/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_payment') . '</a>';
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
                return "<a class='dt-modal' href='admin/payment/view/$id'>".$dtData['reference_no']."</a>";
            })
            ->editColumn('date', function ($dtData) {
                return '<div>'.(!empty($dtData['date']) ? _dt($dtData['date']) : '').'</div>';
            })
            ->editColumn('transaction_bill', function ($dtData) {
                $transaction = $dtData['transaction_bill'] ?? [];
                return '<div><a class="dt-modal" href="admin/transaction_bill/view/'.$transaction['id'].'">'.($transaction['reference_no'] ?? '').'</a></div>';
            })
            ->editColumn('partner', function ($dtData) {
                $partner = $dtData['transaction_bill']['partner'] ?? [];
                if (!empty($partner)) {
                    $url = !empty($partner['avatar']) ? $partner['avatar'] : asset('admin/assets/images/users/avatar-1.jpg');
                    return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                            '40px') . '<div>' . (!empty($partner['fullname']) ? $partner['fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($partner['phone']) ? $partner['phone'] : 'Chưa có sdt').'</div>';
                } else {
                    return '<div></div>';
                }
            })
            ->editColumn('payment_mode', function ($dtData) {
                $payment_mode = $dtData['payment_mode'] ?? [];
                return '<div>'.($payment_mode['name'] ?? '').'</div>';
            })
            ->addColumn('status', function ($dtData) {
                if ($dtData['status'] == 1){
                    $htmlStatus = '<div class="dt-update label label-danger" data-type="payment" style="cursor: pointer;" href="admin/payment/changeStatus/'.$dtData['id'].'">Chờ thanh toán</div>';
                } else {
                    $htmlStatus = '<div class="label label-success">Đã thanh toán</div>';
                }
                return '<div class="text-center">'.$htmlStatus.'</div>';
            })
            ->addColumn('customer', function ($dtData) {
                $customer = $dtData['customer'] ?? [];
                $url = !empty($customer['avatar_new']) ? $customer['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div>'.(!empty($customer['fullname']) ? $customer['fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($customer['phone']) ? $customer['phone'] : 'Chưa có sdt').'</div>';
            })
            ->editColumn('grand_total', function ($dtData) {
                return '<div>'.(!empty($dtData['payment']) ? formatMoney($dtData['payment']) : 0).'</div>';
            })
            ->editColumn('percent_partner', function ($dtData) {
                return '<div>'.(!empty($dtData['percent_partner']) ? ($dtData['percent_partner']) : '-').'</div>';
            })
            ->editColumn('revenue_partner', function ($dtData) {
                return '<div>'.(!empty($dtData['revenue_partner']) ? formatMoney($dtData['revenue_partner']) : 0).'</div>';
            })
            ->editColumn('percent_customer', function ($dtData) {
                return '<div>'.(!empty($dtData['percent_f1']) ? ($dtData['percent_f1']) : '-').'</div>';
            })
            ->editColumn('revenue_customer', function ($dtData) {
                return '<div>'.(!empty($dtData['revenue_f1']) ? formatMoney($dtData['revenue_f1']) : 0).'</div>';
            })
            ->rawColumns(['options', 'reference_no', 'date', 'status','id','status','customer','transaction_bill','grand_total','payment_mode','percent_partner','revenue_partner','percent_customer','revenue_customer','partner'])
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
        if (!has_permission('payment', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbPaymentService->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function view($id = 0){
         if (!has_permission('payment','view') && !has_permission('payment', 'viewown')) {
             access_denied(true, lang('dt_access'));
         }
        $title = lang('dt_view_payment');
        $this->request->merge(['id' => $id]);
        $response = $this->fnbPaymentService->getListDetail($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        return view('admin.payment.view',[
            'title' => $title,
            'dtData' => $dtData ?? [],
        ]);
    }

    public function changeStatus($id = 0){
        if (!has_permission('payment','approve')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $payment_id = $id;
        $this->request->merge(['staff_status' => Config::get('constant')['user_admin']]);
        $this->request->merge(['payment_id' => $payment_id]);
        $responseUpdate =  $this->fnbPaymentService->changeStatus($this->request);
        $dtUpdate = $responseUpdate->getData(true);
        $data = $dtUpdate['data'] ?? [];
        return response()->json($data);
    }
}
