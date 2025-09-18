<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Permission;
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
        $roles = Role::with('permission')
            ->orderBy('parent_id')
            ->orderBy('id')
            ->get();

        $result = $this->flattenRoles($roles);
        $start = intval($this->request->input('start', 0));
        return Datatables::of($result)
            ->addColumn('options', function ($role) {
                $role_id = $role['id'];
                $edit = "<a href='admin/role/detail/$role_id'><i class='fa fa-pencil'></i> " . lang('dt_edit_role') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/role/delete/'.$role_id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
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
                ->editColumn('id', function ($role) use (&$start) {
                    return '<div>'.(++$start).'</div>';
                })
            ->addColumn('name', function ($role) {
                $indent = str_repeat("&nbsp;&nbsp;&nbsp;", $role['level']);
                $prefix = $role['level'] > 0 ? "|--- " : "";
                return $indent.$prefix.$role['name'];
            })
            ->addColumn('permission', function ($role) {
                $str = '';
                $id_check = 0;
                foreach ($role['permissions'] as $perm) {
                    $name = $perm['group_name'];
                    $str_parent = '';
                    if ($perm['group_id'] !== $id_check) {
                        $str_parent = "<div>$name</div>";
                        $id_check = $perm['group_id'];
                    }
                    $str .= $str_parent."<div class='label label-success' style='margin-bottom: 5px'>".lang($perm['name'])."</div> ";
                }
                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'permission','name','id'])
            ->make(true);
    }

    private function flattenRoles($roles, $parent_id = 0, $level = 0)
    {
        $result = [];
        foreach ($roles->where('parent_id', $parent_id) as $role) {
            $result[] = [
                'id' => $role->id,
                'name' => $role->name,
                'level' => $level,
                'permissions' => $role->permission()->orderBy('group_permission_id','asc')->get()->map(function($p){
                    return [
                        'name' => $p->name,
                        'group_id' => $p->group_permission_id,
                        'group_name' => $p->groupPermission->name,
                    ];
                })->toArray()
            ];
            $result = array_merge($result, $this->flattenRoles($roles, $role->id, $level+1));
        }
        return $result;
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
            $role->parent_id = $this->request->input('parent_id',0);
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

    public function getPermissonByRole()
    {
        $role = $this->request->role;
        $data = [];
        if (!empty($role)) {
            DB::enableQueryLog();
            $roles = GroupPermission::with('role')->whereHas('role', function ($query) use ($role) {
                $query->where('role_id', $role);
            })->get()->toArray();
            if (!empty($roles)) {
                foreach ($roles as $key => $value) {
                    $id_group = $value['id'];
                    $permission = Permission::with('role')->whereHas('role', function ($query) use ($role, $id_group) {
                        $query->where('role_id', $role);
                        $query->where('group_permission_id', $id_group);
                    })->get()->toArray();
                    if (!empty($permission)){
                        foreach ($permission as $kk => $vv){
                            $permission[$kk]['name'] = lang($vv['name']);
                        }
                    }
                    $roles[$key]['permission'] = $permission;
                }
                $data['roles'] = $roles;
            }
        }
        return response()->json($data);
    }
}
