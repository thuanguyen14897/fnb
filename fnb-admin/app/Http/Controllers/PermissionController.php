<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use Yajra\DataTables\DataTables;
use App\Models\GroupPermission;
use Illuminate\Support\Str;

class PermissionController extends Controller
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if(!is_admin()) {
            access_denied();
        }

        return view('admin.permission.list');
    }

    public function getPermission()
    {
        $permission = Permission::with('groupPermission')->orderByRaw('id DESC')->get();
        return Datatables::of($permission)
            ->addColumn('options', function ($permission) {
                $edit = "<a class='dt-modal' href='admin/permission/detail/$permission->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_permission') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/permission/delete/'.$permission->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_permission') .'</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             '.lang('dt_actions').'
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">'.$edit.'</li>
                                <li style="cursor: pointer">'.$delete.'</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('group_permission_id', function ($permission) {
                return '<span class="label label-primary">'.$permission->groupPermission->name.'</span>';
            })
            ->editColumn('name', function ($permission) {
                return lang($permission->name);
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options','group_permission_id'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_permission');
        } else {
            $title = lang('dt_edit_permission');
        }
        $groupPermission = GroupPermission::all();
        $permission = Permission::find($id);
        return view('admin.permission.detail', [
            'groupPermission' => $groupPermission,
            'permission' => $permission,
            'title' => $title,
            'id' => $id,
        ]);
    }

    public function submit($id = 0,PermissionRequest $permissionRequest)
    {
        $data = [];
        if (!empty($id)) {
            $permission = Permission::find($id);
        }

        DB::beginTransaction();
        try {
            if (empty($id)){
                $permission = new Permission();
            }
            $permission->name = $permissionRequest->name;
            $permission->display_name = Str::slug($permissionRequest->name);
            $permission->group_permission_id = $permissionRequest->group_permission_id;

            $permission->save();

            DB::commit();
            if ($permission) {
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

    public function delete($id)
    {
        $permission = Permission::find($id);
        try {
            if (count($permission->role) > 0) {
                $data['result'] = false;
                $data['message'] = lang('dt_name_permission_exist');
            } else {
                $permission->delete();
                $data['result'] = true;
                $data['message'] = lang('dt_success');
            }
            return response()->json($data);
        } catch (Exception $exception) {
            $data['result'] = false;
            $data['message'] = lang('dt_error');
            return response()->json($data);
        }
    }
}
