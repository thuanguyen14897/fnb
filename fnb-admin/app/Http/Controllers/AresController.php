<?php

namespace App\Http\Controllers;

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
    public function __construct(Request $request, AresService $aresService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbAres = $aresService;
    }

    public function get_list()
    {
        if (!has_permission('ares','view')){
            access_denied();
        }
        return view('admin.ares.list');
    }

    public function getList()
    {
        $search = $this->request->input('search.value') ?? null;

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
                $setup = "<a href='admin/ares/setup/$id'><i class='fa fa-pencil'></i> " . lang('c_setup_ares') . "</a>";
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
            ->editColumn('name', function ($dtData) {
                $str = '<div>' . $dtData['name'] . '</div>';
                return $str;
            })

            ->editColumn('active', function ($dtData) {
                $checked = $dtData['active'] == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/service/active/'.$dtData['id'].'" data-status="'.$dtData['active'].'"></div>';
                return $str;
            })
            ->rawColumns(['options', 'active', 'name','id'])
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
        if (!has_permission('department','delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $department = Department::find($id);
        try {
            if (count($department->user) > 0) {
                $data['result'] = false;
                $data['message'] = lang('dt_name_department_exist');
            } else {
                $department->delete();
                $data['result'] = true;
                $data['message'] = lang('dt_success');
            }
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = lang('dt_error');
            return response()->json($data);
        }
    }

    public function changeStatus($id){
        if (!has_permission('department','edit')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $deparment = Department::find($id);
        try {
            $deparment->check_transaction = $this->request->status == 0 ? 1 : 0;
            $deparment->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
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
}
