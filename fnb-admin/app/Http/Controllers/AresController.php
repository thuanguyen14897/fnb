<?php

namespace App\Http\Controllers;

use App\Models\Ares;
use App\Models\User;
use App\Models\UserAres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\DataTables;
use App\Models\Department;
use App\Services\AresService;
use App\Helpers\AppHelper;
use App\Http\Requests\AresRequest;
use Illuminate\Support\Facades\Validator;

class AresController extends Controller
{
    protected $fnbAres;
    public function __construct(Request $request, AresService $aresService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbAres = $aresService;
    }

    public function get_list()
    {
        if (!has_permission('ares','view') && !has_permission('ares','viewown')){
            access_denied();
        }
        return view('admin.ares.list');
    }

    public function getList()
    {
        $search = $this->request->input('search.value') ?? null;
        if (!has_permission('ares','view') && has_permission('ares','viewown')){
            $user_ares = UserAres::where('id_user', '=', get_staff_user_id())->get();
            $listAres = [];
            foreach($user_ares as $dataAres) {
                $listAres[] = $dataAres->id_ares;
            }
            $this->request->merge(['aresPer' => $listAres]);
            $this->request->merge(['ares_permission' => 1]);
        }


        $response = $this->fnbAres->getList($this->request);
        $data = $response->getData(true);
        $customer_ids = [];
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $edit = "<a class='dt-modal' href='admin/ares/detail/$id'><i class='fa fa-pencil'></i> " . lang('c_edit_ares_short') . "</a>";
                $setup = "<a href='admin/ares/setup/$id'><i class='fa fa-cog'></i> " . lang('c_setup_ares') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/ares/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_ares') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $setup . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('data_province', function ($dtData) {
                $data_province = $dtData['data_province'];
                $viewProvice = '';
                $countWard = [];
                $countProvice = [];
                foreach($data_province as $key => $value) {
                    $countProvice[] = $value['province']['Id'];
                    foreach($value['data_ward'] as $k => $v) {
                        $countWard[] = $v['ward']['Id'];
                    }
//                    $viewProvice .= '<div class="city-name">'.$value['province']['Name'] . (!empty($value['name_province_old']) ? (' - <i style="font-size:12px;">' . $value['name_province_old'] .'</i>') : '').'
//                    <span class="tag district"> ' . count($value['data_ward']) . ' Phường/Xã</span></div>';
////                    $viewProvice .= '<div class="districts"><span class="tag district"> ' . count($value['data_ward']) . ' Phường/Xã</span>';
////                    foreach($value['data_ward'] as $k => $v) {
////                        $viewProvice .= '<span class="tag district">'.$v['ward']['Name'].'</span>';
////                    }
//                    $viewProvice .= '</div>';
                }
                $viewProvice = '<div class="text-center"><span class="tag district"><b>' . count(array_unique($countProvice)) . '</b> Tỉnh/Thành Phố & ' . count(array_unique($countWard)) . ' Phường/Xã</span></div>';
                return $viewProvice;
            })
            ->addColumn('data_user', function ($dtData) {
                $viewUser = '';
                $ListUserAres = User::join('tbl_user_ares', 'tbl_user_ares.id_user', '=', 'tbl_users.id')
                    ->where('tbl_user_ares.id_ares', '=', $dtData['id'])->get();
                if(!empty($ListUserAres)) {
                    foreach($ListUserAres as $UserAres) {
                        $dtImage = !empty($UserAres->image) ? asset('storage/'.$UserAres->image) : 'admin/assets/images/users/avatar-1.jpg';
                        $viewUser .= '<div class="m-t-5" style="display: flex;">
                                        <img src="'.$dtImage.'" alt="image" title="'.$UserAres->name.'"
                                             class="img-responsive img-circle"
                                             style="width: 25px;height: 25px"><span style="padding-top: 2px;padding-left: 2px;"> '.$UserAres->name.'</span>
                                    </div>';
                    }
                }
                return '<div>'.$viewUser.'</div>';
            })
            ->editColumn('name', function ($dtData) {
                $str = '<div>' . $dtData['name'] . '</div>';
                return $str;
            })
            ->editColumn('active', function ($dtData) {
                $checked = $dtData['active'] == 1 ? 'checked' : '';
                $status = $dtData['active'] == 1 ? '0' : '1';
                $str = '<div class="text-center"><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/ares/changeStatus/'.$dtData['id'].'" data-status="'.$status.'"></div>';
                return $str;
            })
            ->rawColumns(['options', 'active', 'name', 'data_province', 'ward', 'id','data_user'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function getDetail($id = '0')
    {
        if(empty($id)) {
            $id = $this->request->input('id');
        }
        $this->request->merge([
            'id' => $id,
        ]);
        if (empty($id)) {
            $title = lang('c_add_ares');
            if (!has_permission('ares','add')){
                access_denied(true);
            }
        } else {
            if (!has_permission('ares','edit')){
                access_denied(true);
            }
            $title = lang('c_edit_ares');
        }
        $data = $this->fnbAres->getDetail($this->request);
        $ares = $data->getData()->dtData;
        return view('admin.ares.detail', [
            'title' => $title,
            'id' => $id,
            'ares' => $ares,
        ]);
    }

    public function detail($id = 0)
    {
        $response = $this->fnbAres->detail($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id){
        if (!has_permission('ares','delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }

        if(empty($id)) {
            $id = $this->request->input('id');
        }
        $this->request->merge([
            'id' => $id,
        ]);
        $response = $this->fnbAres->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function changeStatus($id){
        if (!has_permission('ares','edit')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }

        if(empty($id)) {
            $id = $this->request->input('id');
        }
        $this->request->merge([
            'id' => $id,
        ]);
        $response = $this->fnbAres->ChangeStatus($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function setup($id = '0')
    {
        if(empty($id)) {
            $id = $this->request->input('id');
        }
        $this->request->merge([
            'id' => $id,
        ]);

        $title = lang('c_setup_ares');
        if (!has_permission('ares','edit')){
            access_denied(true);
        }

        $data = $this->fnbAres->getSetup($this->request);
        $data = $data->getData();
        if(!empty($data->dtData)) {
            $ares = $data->dtData;
        }

        return view('admin.ares.setup', [
            'title' => $title,
            'id' => $id ?? 0,
            'ares' => $ares ?? NULL,
        ]);
    }

    public function updateSetup() {
        $response = $this->fnbAres->updateSetup($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

}
