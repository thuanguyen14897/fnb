<?php

namespace App\Http\Controllers;

use App\Models\GroupPermission;
use App\Models\Permission;
use App\Models\Department;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\TransactionDriver;
use App\Models\User;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list(){
        if (!has_permission('user','view')){
            access_denied();
        }
        return view('admin.user.list');
    }

    public function getUsers(){
        $user = User::with('role')->with('department')->get();
        return Datatables::of($user)
            ->addColumn('options', function ($user) {
                $edit = "<a href='admin/user/detail/$user->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_user') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/user/delete/'.$user->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_user') .'</a>';
                $user->id == Config::get('constant')['user_admin'] ? ($delete = '') : $delete;
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-left" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">'.$edit.'</li>
                                <li style="cursor: pointer">'.$delete.'</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('department', function ($user) {
                $str = '';
                if (count($user->department) > 0) {
                    foreach ($user->department as $key => $value) {
                        $str .= "<div class='label label-success'>$value->name</div>".' ';
                    }
                }

                return $str;
            })
            ->addColumn('role', function ($user) {
                $str = '';
                if (count($user->role) > 0) {
                    foreach ($user->role as $key => $value) {
                        $str .= "<div class='label label-success'>$value->name</div>".' ';
                    }
                }

                return $str;
            })
            ->editColumn('active', function ($user) {
                $classes = $user->active == 1 ? "btn-info" : "btn-danger";
                $content = $user->active == 1 ? "Hoạt động" : "Khoá";
                $str = "<a class='dt-update text-center btn btn-xs $classes' href='admin/user/active/$user->id'>$content</a>";
                return $str;
            })
            ->editColumn('image', function ($user) {
                $dtImage = !empty($user->image) ? asset('storage/'.$user->image) : 'admin/assets/images/users/avatar-1.jpg';
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="'.$dtImage.'" alt="image"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';

                return $str;
            })
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'department','role','active','image','service_support','priority'])
            ->make(true);
    }

    public function get_detail($id = 0){
        if (empty($id)){
            if (!has_permission('user','add')){
                access_denied();
            }
            $title = lang('dt_add_user');
        } else {
            if (!has_permission('user','edit')){
                access_denied();
            }
            $title = lang('dt_edit_user');
        }
        $role = Role::all();
        $department = Department::all();
        $user = User::find($id);
        $dtTypeCar = getListTypeCar();
        return view('admin.user.detail',[
            'id' => $id,
            'title' => $title,
            'role' => $role,
            'department' => $department,
            'user' => $user,
            'dtTypeCar' => $dtTypeCar,
        ]);
    }

    public function submit($id = 0){
        if (empty($id)){
            $user = new User();
            $dtUserCheck = User::orderBy('id','desc')->limit(1)->first();
            $user->priority = ($dtUserCheck->priority + 1);
        } else {
            $user = User::find($id);
        }
        $user->code = $this->request->code;
        $user->name = $this->request->name;
        $user->phone = $this->request->phone;
        $user->email = $this->request->email;
        $user->active = $this->request->active;
        $user->admin = !empty($this->request->admin) ? 1 : 0;
        if (!empty($this->request->password)){
            $user->password = bcrypt($this->request->password);
        }
        $permission_items = [];
        $group_permission = $this->request->group_permission;
        if (!empty($group_permission)) {
            foreach ($group_permission as $key => $value) {
                $group_permission_id = $value;
                if (empty($this->request->permission[$value])) {
                    $permission = 0;
                } else {
                    $permission = $this->request->permission[$value];
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
        $user->save();
        if ($user) {

            $department = $this->request->department;
            $user->department()->detach();
            if (!empty($department)) {
                foreach ($department as $id) {
                    $user->department()->attach($id);
                }
            }
            $user->role()->detach();
            $user->permission()->detach();
            if ($user->admin == 0) {
                $role = $this->request->role;
                if (!empty($role)) {
                    foreach ($role as $id) {
                        $user->role()->attach($id);
                    }
                }
                if (!empty($permission_items)) {
                    foreach ($permission_items as $key => $value) {
                        $value['user_id'] = $user->id;
                        DB::table('tbl_user_permission')->insert($value);
                        $user->flushCache();
                    }
                }
            }
            if ($this->request->hasFile('image')) {
                if (!empty($user->image)){
                    $this->deleteFile($user->image);
                }
                $path = $this->UploadFile($this->request->file('image'),'users/'.$user->id);
                $user->image = $path;
                $user->save();
            }
        }

        return redirect('admin/user/list')->with('success', lang('dt_success'));
    }

    public function getPermissonByRole()
    {
        $role = $this->request->role;
        $user_id = $this->request->user_id;
        $data = [];
        if (!empty($role)) {
            DB::enableQueryLog();
            $roles = GroupPermission::with('role')->whereHas('role', function ($query) use ($role) {
                $query->whereIn('role_id', $role);
            })->get()->toArray();
            if (!empty($roles)) {
                foreach ($roles as $key => $value) {
                    $id_group = $value['id'];
                    $permission = Permission::with('role')->whereHas('role', function ($query) use ($role, $id_group) {
                        $query->whereIn('role_id', $role);
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
        if (!empty($user_id)) {
            $permission = Permission::with('user')->whereHas('user', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->get()->pluck('id')->toArray();
            if (!empty($permission)) {
                $data['permission'] = $permission;
            } else {
                $data['permission'] = [];
            }
        }

        return response()->json($data);
    }

    public function active($id){
        if (!has_permission('user','edit')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $user = User::find($id);
        try {
            $user->active = $user->active == 0 ? 1 : 0;
            $user->save();
            $dtTransactionCheckDriver = TransactionDriver::select('id','date','created_at',DB::raw("1 as type"))->orderBy('created_at', 'desc')->limit(1);
            $dtTransactionCheckVs1 = Transaction::select('id','date','created_at',DB::raw("2 as type"))->orderBy('created_at', 'desc')->limit(1)->unionall($dtTransactionCheckDriver);
            $dtTransactionCheckNew = DB::query()
                ->fromSub($dtTransactionCheckVs1, 'union_query')
                ->select('id','type')
                ->orderBy('id', 'desc')
                ->first();
            if (!empty($dtTransactionCheckNew)){
                if ($dtTransactionCheckNew->type == 1){
                    $dtTransactionCheck = TransactionDriver::select('id','date',DB::raw("3 as type"))->find($dtTransactionCheckNew->id);
                } else {
                    $dtTransactionCheck = Transaction::select('id','date','type')->find($dtTransactionCheckNew->id);
                }
                if (!empty($dtTransactionCheck->transaction_staff_new())){
                    $service = $dtTransactionCheck->type;
                    $priority = $dtTransactionCheck->transaction_staff_new()->priority;
                    User::whereHas('department',function ($query){
                            $query->where('check_transaction',1);
                        })
                        ->whereExists(function ($query) use ($service) {
                            $query->select("tbl_user_service.user_id")
                                ->from('tbl_user_service')
                                ->whereRaw('tbl_user_service.user_id = tbl_users.id')
                                ->where('tbl_user_service.service',$service);
                        })->where('priority','<=',$priority)->update([
                            'check_tran' => 1
                        ]);
                    User::where('id',$dtTransactionCheck->transaction_staff_new()->id)->update([
                        'check_tran' => 1
                    ]);
                }
            }
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function delete($id){
        if (!has_permission('user','delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $user = User::find($id);
        try {
            $user->delete();
            if (!empty($user->image)){
                $this->deleteFile($user->image);
            }
            $user->role()->detach();
            $user->department()->detach();
            $user->permission()->detach();
            $user->flushCache();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function updatePriority(){
        $user_id = $this->request->input('user_id');
        $priority = $this->request->input('priority');
        $user = User::find($user_id);
        try {
            $user->priority = $priority;
            $user->save();
            $dtTransactionCheckDriver = TransactionDriver::select('id','date','created_at',DB::raw("1 as type"))->orderBy('created_at', 'desc')->limit(1);
            $dtTransactionCheckVs1 = Transaction::select('id','date','created_at',DB::raw("2 as type"))->orderBy('created_at', 'desc')->limit(1)->unionall($dtTransactionCheckDriver);
            $dtTransactionCheckNew = DB::query()
                ->fromSub($dtTransactionCheckVs1, 'union_query')
                ->select('id','type')
                ->orderBy('id', 'desc')
                ->first();
            if (!empty($dtTransactionCheckNew)){
                if ($dtTransactionCheckNew->type == 1){
                    $dtTransactionCheck = TransactionDriver::select('id','date',DB::raw("3 as type"))->find($dtTransactionCheckNew->id);
                } else {
                    $dtTransactionCheck = Transaction::select('id','date','type')->find($dtTransactionCheckNew->id);
                }
                if (!empty($dtTransactionCheck->transaction_staff_new())){
                    $service = $dtTransactionCheck->type;
                    $priority = $dtTransactionCheck->transaction_staff_new()->priority;
                    User::whereHas('department',function ($query){
                        $query->where('check_transaction',1);
                    })
                        ->whereExists(function ($query) use ($service) {
                            $query->select("tbl_user_service.user_id")
                                ->from('tbl_user_service')
                                ->whereRaw('tbl_user_service.user_id = tbl_users.id')
                                ->where('tbl_user_service.service',$service);
                        })->where('priority','<=',$priority)->update([
                            'check_tran' => 1
                        ]);
                    User::where('id',$dtTransactionCheck->transaction_staff_new()->id)->update([
                        'check_tran' => 1
                    ]);
                }
            }
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
