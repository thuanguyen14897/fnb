<?php

namespace App\Http\Controllers;

use App\Models\Ares;
use App\Models\Clients;
use App\Models\MemberShipLevel;
use App\Models\Province;
use App\Models\User;
use App\Models\UserAres;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Services\AccountService;
use App\Services\AresService;
use Yajra\DataTables\CollectionDataTable;

class ClientsController extends Controller
{
    protected $fnbAccount;
    protected $fnbAres;
    use UploadFile;

    public function __construct(Request $request, AccountService $accountService, AresService $aresService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbAccount = $accountService;
        $this->fnbAres = $aresService;
    }

    public function get_list()
    {

        if (!has_permission('clients', 'view') && !has_permission('clients', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);//show chỉ thông tin cơ bản
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        return view('admin.clients.list', [
            'ares' => $ares ?? []
        ]);
    }

    public function get_detail($id = 0)
    {
        if (!has_permission('clients', 'edit')) {
            access_denied();
        }
        $checkPermission = true;
        if (!has_permission('clients', 'view') && has_permission('clients', 'viewown')) {
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
        if (!empty($client)) {
            $membership_level = MemberShipLevel::find($client['membership_level']);
            $client['membership_level'] = $membership_level ?? null;
        }
        $title = lang('c_title_edit_client');
        return view('admin.clients.detail', [
            'id' => $id,
            'title' => $title,
            'client' => $client,
        ]);
    }

    public function view($id = 0)
    {
        $checkPermission = true;
        if (!has_permission('clients', 'view') && has_permission('clients', 'viewown')) {
            $checkPermission = false;
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $this->request->merge(['id' => $id]);
        $response = $this->fnbAccount->getDetailCustomer($this->request);
        $data = $response->getData(true);
        $client = $data['client'] ?? [];
        $referral = $data['referral'] ?? [];
        if (!empty($client['id'])) {
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
        $level = $referral['level'] ?? 0;
        $countMember = $referral['count_member'] ?? 0;
        $dataReferralLevel = $referral['data'] ?? [];
        $title = lang('dt_view_client');
        return view('admin.clients.view', [
            'title' => $title,
            'level' => $level,
            'dataReferralLevel' => $dataReferralLevel,
            'countMember' => $countMember,
            'client' => $client,
        ]);
    }

    public function getListCustomer()
    {
        $this->request->merge(['type_client' => 1]);
        if (!has_permission('clients', 'view') && has_permission('clients', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbAccount->getListCustomer($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $clients = collect($data['data']);

        $staff_id = $clients->pluck('staff_id')->unique()->values()->toArray();

        $staffs = User::select('id','code','name','email','image')->whereIn('id', $staff_id)->get();
        $storageUrl = config('app.storage_url');
        $staffs = $staffs->map(function ($item) use ($storageUrl) {
            $item->image = !empty($item->image) ? $storageUrl.'/'.$item->image : 'admin/assets/images/users/avatar-1.jpg';
            return $item;
        });

        $membership_level = $clients->pluck('membership_level')->unique()->values()->toArray();
        $dtMemberShip = MemberShipLevel::select('id','icon','name','radio_discount','color')->whereIn('id', $membership_level)->get();
        $dtMemberShip = $dtMemberShip->map(function ($item) use ($storageUrl) {
            $item->icon = !empty($item->icon) ? $storageUrl.'/'.$item->icon : 'admin/assets/images/users/avatar-1.jpg';
            return $item;
        });

        $province_id = $clients->pluck('province_id')->unique()->values()->toArray();
        $wards_id = $clients->pluck('wards_id')->unique()->values()->toArray();
        $this->request->merge(['province' => $province_id]);
        $this->request->merge(['ward' => $wards_id]);
        $data_ares = $this->fnbAres->getDetailWhere($this->request);
        $dtAres = $data_ares->getData(true);
        $dtAres = collect($dtAres['dtData'] ?? []);
        $clients = $clients->map(function ($item) use ($dtAres,$staffs,$dtMemberShip) {
            $ares = $dtAres->filter(function ($row) use ($item) {
                return collect($row['ares_ward'] ?? [])->contains(function ($r) use ($item,) {
                    return ($r['id_ward'] ?? 0) == ($item['wards_id'] ?? 0)
                        && ($r['id_province'] ?? 0) == ($item['province_id'] ?? 0);
                });
            })->map(function ($row) {
                return Arr::except($row, ['ares_ward']);
            })->values();
            $item['ares'] = $ares;

            $staff = $staffs->where('id', '=', $item['staff_id'])->first();
            $item['staff'] = $staff;

            $member_ship = $dtMemberShip->where('id', '=', $item['membership_level'])->first();
            $item['member_ship'] = $member_ship;
            return $item;
        });

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
                $memberLevel = $client['member_ship'] ?? null;
                if (!empty($memberLevel)) {
                    $dtImage = $memberLevel['icon'];
                    if ($client['active_limit_private'] == 1) {
                        $radio_discount = $client['radio_discount_private'];
                    } else {
                        $radio_discount = $memberLevel['radio_discount'];
                    }

                    $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                         class="show_image">
                        <img src="' . $dtImage . '" alt="avatar"
                             class="img-responsive img-circle"
                             style="width: 30px;height: 30px"><span class="m-t-5" style="color:' . $memberLevel['color'] . '"><strong>Hạng ' . $memberLevel['name'] . '</strong> (' . $radio_discount . '%)</span>
                    </div>';
                }  else {
                    $str = '';
                }
                return $str;
            })
            ->addColumn('count_number', function ($client) {
                return '<div class="text-center" style="font-weight: bold">' . ((!empty($client['count_member']) && $client['count_member'] > 0) ? $client['count_member'] : '') . '</div>';
            })
            ->editColumn('account_balance', function ($client) {
                $str = '<div>' . ($client['account_balance'] > 0 ? formatMoney($client['account_balance']) : '') . '</div>';
                return '<div class="text-right">' . $str . '</div>';
            })
            ->editColumn('point_membership', function ($client) {
                $str = '<div style="font-weight: bold">' . ($client['point_membership'] > 0 ? $client['point_membership'] : '') . '</div>';
                return '<div class="text-center">' . $str . '</div>';
            })
            ->editColumn('ranking_date', function ($client) {
                $str = _dthuan($client['ranking_date']);
                return '<div class="text-center">' . $str . '</div>';
            })
            ->editColumn('referral_code_customer', function ($client) {
                $str = '';
                $referral_level = $client['referral_level'] ?? [];
                if (!empty($referral_level)) {
                    if (!empty($referral_level['parent'])) {
                        if ($referral_level['parent']['type_client'] == 1) {
                            $str = "<a class='text-center label label-danger' target='_blank' href='admin/clients/view/" . $referral_level['parent_id'] . "'>" . $referral_level['referral_code'] . "</a>";
                        } else {
                            $str = "<a class='text-center label label-danger' target='_blank' href='admin/partner/view/" . $referral_level['parent_id'] . "'>" . $referral_level['referral_code'] . "</a>";
                        }
                    }
                }
                return $str;
            })
            ->editColumn('referral_code', function ($client) {
                $str = '<div class="label label-default">' . $client['referral_code'] . '</div>';
                return '<div class="text-center">' . $str . '</div>';
            })
            ->editColumn('created_at', function ($client) {
                $str = _dt($client['created_at']);
                return $str;
            })
            ->editColumn('date_active', function ($client) {
                $customer_package = $client['customer_package'] ?? null;
                $namePackage = '';
                $checkDefault = 0;
                if (!empty($customer_package)) {
                    $namePackage = $customer_package['name'];
                    $checkDefault = $customer_package['package']['check_default'] ?? 0;
                }
                $str = !empty($client['date_active']) ? _dthuan($client['date_active']) : null;
                return '<div>' . $str . ' <span style="cursor: pointer"><a class="dt-modal" href="admin/clients/updateDateActive/'.$client['id'].'"><i class="fa fa-pencil"></i></a></span></div><div><span class="label ' . ($checkDefault == 1 ? 'label-default' : 'label-info') . '">' . $namePackage . '</span></div>';
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
                if (!empty($client['province_id']) && !empty($client['wards_id'])) {
//                    $this->request->merge(['province' => $client['province_id']]);
//                    $this->request->merge(['ward' => $client['wards_id']]);
//                    $data_ares = $this->fnbAres->getDetailWhere($this->request);
//                    $_ares = $data_ares->getData(true);
//                    if(!empty($_ares['result'])){
//                        if(!empty($_ares['dtData'])) {
//                            foreach ($_ares['dtData'] as $k => $v) {
//                                $str .= "<div class='label label-success' style='line-height: 25px;'>" . ($v['name'] ?? '') . "</div>" . ' ';
//                            }
//                        }
//                        else {
//                            $str = "<div class='label label-danger'>Chưa thiết lập</div>";
//                        }
//                    }
                    $ares = $client['ares'] ?? [];
                    if (!empty($ares)) {
                        foreach ($ares as $k => $v) {
                            $str .= "<div class='label label-success' style='margin-bottom: 5px;margin-right: 5px'>" . ($v['name'] ?? '') . "</div>" . ' ';
                        }
                    } else {
                        $str = "<div class='label label-danger'>Chưa thiết lập</div>";
                    }
                }
                return '<div style="display: flex;flex-wrap: wrap">' . $str . '</div>';
            })
            ->addColumn('staff_id', function ($client) {
                $staff = $client['staff'] ?? null;
                if (!empty($staff)){
                    $dtImage = $staff->image;
                    $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 35px;height: 35px">

                </div>';
                    $str = '<div style="margin-left: 5px;text-align: center">' . $staff['name'] .' ('.$staff['code'].')</div>';
                    return '<div style="display: flex;align-items: center;flex-wrap: wrap;justify-content: center">'.$image.$str.'</div>';
                } else {
                    return '<div class="text-center"></div>';
                }
            })
            ->rawColumns([
                'options',
                'active',
                'avatar',
                'img_membership_level',
                'phone',
                'created_at',
                'fullname',
                'referral_code',
                'point_membership',
                'ranking_date',
                'invoice_limit',
                'ares',
                'staff_id',
                'date_active',
                'referral_code_customer',
                'account_balance',
                'count_number'
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function countAll()
    {
        $response = $this->fnbAccount->countAll($this->request);
        $data = $response->getData(true);
        $data['all'] = $data['total'] ?? 0;
        $data['arrType'] = $data['arrType'] ?? [];
        return response()->json($data);
    }

    public function detail()
    {
        if (!empty($this->request->invoice_limit_private)) {
            $this->request->merge(['invoice_limit_private' => number_unformat($this->request->invoice_limit_private)]);
        }
        $response = $this->fnbAccount->detailCustomer($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0)
    {
        if (!has_permission('clients', 'delete')) {
            access_denied(true);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbAccount->deleteCustomer($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function active($id = 0)
    {
        if (!has_permission('clients', 'edit')) {
            access_denied(true);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbAccount->active($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function updateDateActive($id = 0){
        if (!has_permission('clients', 'edit')) {
            access_denied(true);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbAccount->getDetailCustomer($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['client'] ?? [];
        if ($this->request->post()){
            $response = $this->fnbAccount->updateDateActive($this->request);
            $dataRes = $response->getData(true);
            $data = $dataRes['data'];
            return response()->json($data);
        }
        $title = lang('Cập nhập ngày hết hạn sử dụng');
        return view('admin.clients.update_date_active', [
            'dtData' => $data,
            'id' => $id,
            'title' => $title,
        ]);
    }
}
