<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAres;
use App\Services\AresService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AccountService;
use Yajra\DataTables\CollectionDataTable;

class PartnerController extends Controller
{
    protected $fnbAccount;
    protected $fnbAres;
    use UploadFile;
    public function __construct(Request $request,AccountService $accountService,AresService $aresService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbAccount = $accountService;
        $this->fnbAres = $aresService;
    }

    public function get_list(){
        if (!has_permission('partner','view') && !has_permission('partner','viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1
        ]);//show chỉ thông tin cơ bản
        $data_ares = $this->fnbAres->getListData($this->request);
        if(!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        return view('admin.partner.list',[
            'ares' => $ares
        ]);
    }

    public function get_detail($id = 0) {
        if (!has_permission('partner', 'edit')){
            access_denied();
        }
        $checkPermission = true;
        if (!has_permission('partner','view') && has_permission('partner','viewown')) {
            $checkPermission = false;
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $this->request->merge(['id' => $id]);
        $response = $this->fnbAccount->getDetailCustomer($this->request);
        $data = $response->getData(true);
        $client = $data['client'] ?? [];
        if (empty($checkPermission)) {
            if (!empty($id) && empty($client['id'])) {
                access_denied();
            }
        }
        $title = lang('c_title_edit_client');
        return view('admin.partner.detail',[
            'id' => $id,
            'title' => $title,
            'client' => $client,
        ]);
    }

    public function view($id = 0){
        if (!has_permission('partner', 'view') && !has_permission('partner', 'viewown')) {
            access_denied();
        }
        $checkPermission = true;
        if (!has_permission('partner','view') && has_permission('partner','viewown')) {
            $checkPermission = false;
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbAccount->getDetailCustomer($this->request);
        $data = $response->getData(true);
        $client = $data['client'] ?? [];
        if (empty($checkPermission)) {
            if (!empty($id) && empty($client['id'])) {
                access_denied();
            }
        }

        $title = lang('dt_view_client');
        return view('admin.partner.view',[
            'title' => $title,
            'client' => $client,
        ]);
    }

    public function getListCustomer()
    {
        $this->request->merge(['type_client' => 2]);

        if (!has_permission('partner','view') && has_permission('partner','viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbAccount->getListCustomer($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $partner = collect($data['data']);

        return (new CollectionDataTable($partner))
            ->addColumn('options', function ($client) {
                $customer_id = $client['id'];
                $view = "<a href='admin/partner/view/$customer_id'><i class='fa fa-eye'></i> " . lang('dt_view') . "</a>";
                $edit = "<a href='admin/partner/detail/$customer_id'><i class='fa fa-pencil'></i> " . lang('c_edit_client') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/partner/delete/' . $customer_id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_partner') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $view . '</li>
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('fullname', function ($client) {
                $str = '<div><a href="admin/partner/view/' . $client['id'] . '">' . $client['fullname'] . '</a></div>';
                return $str;
            })
            ->editColumn('phone', function ($client) {
                $str = $client['phone'];
                return $str;
            })
            ->editColumn('referral_code', function ($client) {
                $str = '<div class="label label-default">'.$client['referral_code'].'</div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('created_at', function ($client) {
                $str = _dt($client['created_at']);
                return $str;
            })
            ->editColumn('date_active', function ($client) {
                $customer_package = $client['customer_package'] ?? null;
                $namePackage = '';
                $checkDefault = 0;
                if (!empty($customer_package)){
                    $namePackage = $customer_package['name'];
                    $checkDefault = $customer_package['package']['check_default'] ?? 0;
                }
                $str = !empty($client['date_active']) ? _dthuan($client['date_active']) : null;
                return '<div>'.$str.'</div><div><span class="label '.($checkDefault == 1 ? 'label-default': 'label-info').'">'.$namePackage.'</span></div>';
            })
            ->editColumn('active', function ($client) {
                $customer_id = $client['id'];
                $classes = $client['active'] == 1 ? "btn-info" : "btn-danger";
                $content = $client['active'] == 1 ? "Hoạt động" : "Khoá";
                $str = "<a class='dt-update text-center btn btn-xs $classes' href='admin/partner/active/$customer_id'>$content</a>";
                return $str;
            })
            ->editColumn('avatar', function ($client) {
                $dtImage = !empty($client['avatar']) ? $client['avatar'] : imgDefault();
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';

                return $str;
            })

            ->addColumn('ares', function ($client) {
                $str = '';
                if(!empty($client['province_id']) && !empty($client['wards_id'])) {
                    $this->request->merge(['province' => $client['province_id']]);
                    $this->request->merge(['ward' => $client['wards_id']]);
                    $data_ares = $this->fnbAres->getDetailWhere($this->request);
                    $_ares = $data_ares->getData(true);
                    if(!empty($_ares['result'])){
                        if(!empty($_ares['dtData'])) {
                            foreach ($_ares['dtData'] as $k => $v) {
                                $str .= "<div class='label label-success'>" . ($v['name'] ?? '') . "</div>" . ' ';
                            }
                        }
                        else {
                            $str = "<div class='label label-danger'>Chưa thiết lập</div>";
                        }
                    }
                }
                return $str;
            })
            ->rawColumns(['options', 'active', 'avatar', 'phone', 'created_at', 'fullname','referral_code', 'ares','date_active'])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function countAll(){
        $response = $this->fnbAccount->countAll($this->request);
        $data = $response->getData(true);
        $data['all'] = $data['total'] ?? 0;
        $data['arrType'] = $data['arrType'] ?? [];
        return response()->json($data);
    }

    public function detail(){
        $response = $this->fnbAccount->detailCustomer($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        $this->request->merge(['id' => $id]);
        $response = $this->fnbAccount->deleteCustomer($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function active($id = 0){
        $this->request->merge(['id' => $id]);
        $response = $this->fnbAccount->active($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function detailRepresentativePartner($id){
        $this->request->merge(['partner_id' => $id]);
        $response = $this->fnbAccount->detailRepresentativePartner($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }
}
