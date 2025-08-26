<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Models\Department;
use App\Helpers\AppHelper;
use App\Http\Requests\DepartmentRequest;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if (!has_permission('department','view')){
            access_denied();
        }
        return view('admin.department.list');
    }

    public function getDepartment()
    {
        $dtDepartment = Department::orderByRaw('id desc')->get();
        return Datatables::of($dtDepartment)
            ->addColumn('options', function ($department) {
                $edit = "<a class='dt-modal' href='admin/department/detail/$department->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_department') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/department/delete/'.$department->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_department') .'</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('check_transaction',function ($department){
                $checked = $department->check_transaction == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/department/changeStatus/'.$department->id.'" data-status="'.$department->check_transaction.'">';
                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options','check_transaction'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_department');
            if (!has_permission('department','add')){
                access_denied(true);
            }
        } else {
            if (!has_permission('department','edit')){
                access_denied(true);
            }
            $title = lang('dt_edit_department');
        }
        $department = Department::find($id);
        return view('admin.department.detail', [
            'title' => $title,
            'id' => $id,
            'department' => $department,
        ]);
    }

    public function submit($id = 0,DepartmentRequest $departmentRequest)
    {
        $data = [];
        if (!empty($id)) {
            $department = Department::find($id);
        }

        DB::beginTransaction();
        try {
            if(empty($id)){
                $department = new Department();
            }
            $department->code = $departmentRequest->code;
            $department->name = $departmentRequest->name;
            $department->save();
            DB::commit();

            if ($department) {
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = lang('dt_error');
            return response()->json($data);
        }
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
}
