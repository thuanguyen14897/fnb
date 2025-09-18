<?php

namespace App\Http\Controllers;

use App\Models\Ares;
use App\Models\Clients;
use App\Models\MemberShipLevel;
use App\Models\Province;
use App\Models\UserAres;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AccountService;
use App\Services\AresService;
use Yajra\DataTables\CollectionDataTable;

class ClientsController extends Controller
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

        if (!has_permission('clients','view') && !has_permission('clients','viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1
        ]);//show chỉ thông tin cơ bản
        $data_ares = $this->fnbAres->getListData($this->request);
        if(!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        return view('admin.clients.list',[
            'ares'=> $ares ?? []
        ]);
    }

    public function get_detail($id = 0) {
        if (!has_permission('clients', 'edit')){
            access_denied();
        }
        $checkPermission = true;
        if (!has_permission('clients','view') && has_permission('clients','viewown')) {
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
        if (!empty($client)){
            $membership_level = MemberShipLevel::find($client['membership_level'])->toArray();
            $client['membership_level'] = $membership_level;
        }
        $title = lang('c_title_edit_client');
        return view('admin.clients.detail',[
            'id' => $id,
            'title' => $title,
            'client' => $client,
        ]);
    }

    public function view($id = 0){
        $checkPermission = true;
        if (!has_permission('clients','view') && has_permission('clients','viewown')) {
            $checkPermission = false;
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $this->request->merge(['id' => $id]);
        $response = $this->fnbAccount->getDetailCustomer($this->request);
        $data = $response->getData(true);
        $client = $data['client'] ?? [];
        if(!empty($client['id'])) {
            if ($client['active_limit_private'] == 0) {
                $membership_level = MemberShipLevel::find($client['membership_level']);
                $client['invoice_limit_member'] = $membership_level->invoice_limit;
                $client['radio_discount_member'] = $membership_level->radio_discount;
            }
        }
        if (empty($checkPermission)) {
            if (!empty($id) && empty($client['id'])) {
                access_denied();
            }
        }
        $title = lang('dt_view_client');
        return view('admin.clients.view',[
            'title' => $title,
            'client' => $client,
        ]);
    }

    public function getListCustomer()
    {
        $this->request->merge(['type_client' => 1]);
        if (!has_permission('clients','view') && has_permission('clients','viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbAccount->getListCustomer($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $clients = collect($data['data']);

        return (new CollectionDataTable($clients))
            ->addColumn('options', function ($client) {
                $customer_id = $client['id'];
                $view = "<a href='admin/clients/view/$customer_id'><i class='fa fa-eye'></i> " . lang('dt_view') . "</a>";
                $edit = "<a href='admin/clients/detail/$customer_id'><i class='fa fa-pencil'></i> " . lang('c_edit_client') . "</a>";
                if ($customer_id != 22) {
                    $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/clients/delete/' . $customer_id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_client') . '</a>';
                } else {
                    $delete = '';
                }
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
                $str = '<div><a href="admin/clients/view/' . $client['id'] . '">' . $client['fullname'] . '</a></div>';
                return $str;
            })
            ->editColumn('phone', function ($client) {
                $str = $client['phone'];
                return $str;
            })
            ->addColumn('img_membership_level', function ($client) {
                $memberLevel = MemberShipLevel::find($client['membership_level']);
//                $dtImage = !empty($client['membership_level']) ? url('/upload/membership_level/'.$client['membership_level'].'.png') : null;
                $dtImage = !empty($memberLevel->icon) ? asset('storage/'.$memberLevel->icon) : null;
                if($client['active_limit_private'] == 1) {
                    $radio_discount = $client['radio_discount_private'];
                }
                else {
                    $radio_discount = $memberLevel->radio_discount;
                }

                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 30px;height: 30px"><span class="m-t-5" style="color:'.$memberLevel->color.'"><strong>Hạng ' . $memberLevel->name. '</strong> ('.$radio_discount.'%)</span>
                </div>';
                return $str;
            })
            ->addColumn('invoice_limit', function ($client) {
                if(!empty($client['active_limit_private'])) {
                    return '<div class="text-center">'.(!empty($client['invoice_limit_private']) ? number_format($client['invoice_limit_private']) : 'Chưa đặt hạn mức').'</div>';
                }
                else {
                    $membership_level = MemberShipLevel::find($client['membership_level']);
                    return '<div class="text-center">' . (!empty($membership_level->invoice_limit) ? number_format($membership_level->invoice_limit) : 'Không giới hạn') . '</div>';
                }
            })
            ->editColumn('point_membership', function ($client) {
                $str = '<div class="label label-default">'.number_format($client['point_membership']).'</div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('ranking_date', function ($client) {
                $str = _dthuan($client['ranking_date']);
                return '<div class="text-center">'.$str.'</div>';
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
                $str = "<a class='dt-update text-center btn btn-xs $classes' href='admin/clients/active/$customer_id'>$content</a>";
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
                                $str .= "<div class='label label-success' style='line-height: 25px;'>" . ($v['name'] ?? '') . "</div>" . ' ';
                            }
                        }
                        else {
                            $str = "<div class='label label-danger'>Chưa thiết lập</div>";
                        }
                    }
                }
                return $str;
            })
            ->rawColumns(['options', 'active', 'avatar','img_membership_level', 'phone', 'created_at', 'fullname','referral_code','point_membership','ranking_date','invoice_limit','ares','date_active'])
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

    public function detail() {
        if(!empty($this->request->invoice_limit_private)) {
            $this->request->merge(['invoice_limit_private' => number_unformat($this->request->invoice_limit_private)]);
        }
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
}
