<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AccountService;
use Yajra\DataTables\CollectionDataTable;

class ClientsController extends Controller
{
    protected $fnbAccount;
    use UploadFile;
    public function __construct(Request $request,AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbAccount = $accountService;
    }

    public function get_list(){
        if (!has_permission('clients','view')) {
            access_denied();
        }
        return view('admin.clients.list',[]);
    }

    public function get_detail($id = 0) {
        if (!has_permission('clients', 'edit')){
            access_denied();
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbAccount->getDetailCustomer($this->request);
        $data = $response->getData(true);
        $client = $data['client'] ?? [];
        $title = lang('c_title_edit_client');
        return view('admin.clients.detail',[
            'id' => $id,
            'title' => $title,
            'client' => $client,
        ]);
    }

    public function view($id = 0){
        if (!has_permission('clients', 'view')){
            access_denied();
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbAccount->getDetailCustomer($this->request);
        $data = $response->getData(true);
        $client = $data['client'] ?? [];
        $title = lang('dt_view_client');
        return view('admin.clients.view',[
            'title' => $title,
            'client' => $client,
        ]);
    }

    public function getListCustomer()
    {
        $this->request->merge(['type_client' => 1]);
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
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/clients/delete/' . $customer_id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_client') . '</a>';
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
            ->editColumn('referral_code', function ($client) {
                $str = '<div class="label label-default">'.$client['referral_code'].'</div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('created_at', function ($client) {
                $str = _dt($client['created_at']);
                return $str;
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
            ->rawColumns(['options', 'active', 'avatar', 'phone', 'created_at', 'fullname','referral_code'])
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
}
