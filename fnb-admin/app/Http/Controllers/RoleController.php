<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use App\Models\GroupPermission;

class RoleController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if (!has_permission('role','view',)){
            access_denied();
        }
        return view('admin.role.list');
    }

    public function getRole()
    {
        $role = Role::with('permission')->orderByRaw('id DESC')->get();

        return Datatables::of($role)
            ->addColumn('options', function ($role) {
                $edit = "<a href='admin/role/detail/$role->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_role') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/role/delete/'.$role->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_role') .'</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">'.$edit.'</li>
                                <li style="cursor: pointer">'.$delete.'</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('permission', function ($role) {
                $str = '';
                $id_check = 0;
                if (count($role->permission) > 0) {
                    foreach ($role->permission as $key => $value) {
                        $name = $value->groupPermission->name;
                        $str_parent = '';
                        if ($value->groupPermission->id !== $id_check) {
                            $str_parent = "<div>$name</div>";
                            $id_check = $value->groupPermission->id;
                        }
                        $str .= $str_parent."<div class='label label-success'>".lang($value->name)."</div>".' ';
                    }
                }

                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'permission'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        $groupPermission = GroupPermission::with(['permission' => function ($q){
            $q->orderBy('name', 'asc');
        }])->orderBy('id', 'DESC')->get();
        $role = Role::find($id);
        if (empty($id)){
            if (!has_permission('role','add')){
                access_denied();
            }
            $title = lang('dt_add_role');
        } else {
            if (!has_permission('role','edit',)){
                access_denied();
            }
            $title = lang('dt_edit_role');
        }
        return view('admin.role.detail', [
            'title' => $title,
            'id' => $id,
            'groupPermission' => $groupPermission,
            'role' => $role,
        ]);
    }


    public function submit($id = 0,RoleRequest $roleRequest)
    {
        $permission_items = [];
        $group_permission = $roleRequest->group_permission;
        if (!empty($group_permission)) {
            foreach ($group_permission as $key => $value) {
                $group_permission_id = $value;
                if (empty($roleRequest->permission[$value])) {
                    $permission = 0;
                } else {
                    $permission = $roleRequest->permission[$value];
                }
                if ($permission == 0) {
                    continue;
                }
                foreach ($permission as $k => $v) {
                    $permission_items[] = [
                        'permission_id' => $v,
                        'group_permission_id' => $group_permission_id,
                    ];
                }
            }
        }
        DB::beginTransaction();
        try {
            if (empty($id)){
                $role = new Role();
            } else {
                $role = Role::find($id);
            }
            $role->name = $roleRequest->name;
            $role->display_name = Str::slug($roleRequest->name);
            $role->save();
            if ($role) {
                $role->permission()->detach();
                if (!empty($permission_items)) {
                    foreach ($permission_items as $key => $value) {
                        $value['role_id'] = $role->id;
                        DB::table('tbl_permission_role')->insert($value);
                        $role->flushCache();
                    }
                }
            }
            DB::commit();
            return redirect('admin/role/list')->with('success',lang('dt_success'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect('admin/role/list')->with('error',lang('dt_error'));
        }

    }

    public function delete($id)
    {
        if (!has_permission('role','delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $role = Role::find($id);
        try {
            $role->permission()->detach();
            $role->flushCache();
            $role->delete();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = lang('dt_error');
            return response()->json($data);
        }
    }
}
