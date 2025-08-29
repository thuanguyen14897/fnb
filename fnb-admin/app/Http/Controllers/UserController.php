<?php

namespace App\Http\Controllers;

use App\Models\GroupPermission;
use App\Models\Permission;
use App\Models\Department;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserAres;
use App\Services\AresService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    protected $fnbAres;
    use UploadFile;
    public function __construct(Request $request, AresService $aresService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbAres = $aresService;
    }

    public function get_list(){
        if (!has_permission('user','view')){
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1
        ]);//show chỉ thông tin cơ bản
        $data_ares = $this->fnbAres->getListData($this->request);
        if(!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $department = Department::get();
        $role = Role::get();
        return view('admin.user.list', [
            'ares'=> $ares ?? [],
            'department' => $department ?? [],
            'role' => $role ?? [],
        ]);
    }

    public function getUsers(){
        $user = User::with('role')
            ->with('department')
            ->with('user_ares');
        if($this->request->input('ares_search')) {
            $ares_search = $this->request->input('ares_search');
            $user->WhereHas('user_ares',function ($q) use ($ares_search){
                $q->where('id_ares', '=', "$ares_search");
            });
        }
        if($this->request->input('department_search')) {
            $department_search = $this->request->input('department_search');
            $user->WhereHas('department',function ($q) use ($department_search){
                $q->where('department_id', '=', "$department_search");
            });
        }
        if($this->request->input('role_search')) {
            $role_search = $this->request->input('role_search');
            $user->WhereHas('role',function ($q) use ($role_search){
                $q->where('role_id', '=', "$role_search");
            });
        }
        $user->get();
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
//                        $str .= "<div class='label label-success'>$value->name</div>".' ';
                        $str .= $value->name.', ';
                    }
                    $str = trim($str, ', ');
                }

                return $str;
            })
            ->addColumn('role', function ($user) {
                $str = '';
                if (count($user->role) > 0) {
                    foreach ($user->role as $key => $value) {
//                        $str .= "<div class='label label-success'>$value->name</div>".' ';
                        $str .= $value->name.', ';
                    }
                    $str = trim($str, ', ');
                }

                return $str;
            })
            ->addColumn('ares', function ($user) {
                $str = '';
                if(!empty($user->user_ares)) {
                    foreach ($user->user_ares as $key => $value) {
                        $data_ares = $this->fnbAres->getDetail($this->request, $value->id_ares);
                        $_ares = $data_ares->getData(true);
                        if(!empty($_ares['result'])){
                            $str .= "<div class='label label-success'>". ($_ares['dtData']['name'] ?? '')."</div>".' ';
                        }
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
            ->rawColumns(['options', 'department','role','active','image','service_support','priority','ares'])
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
        $this->request->merge([
            'show_short' => 1
        ]);//show chỉ thông tin cơ bản
        $data_ares = $this->fnbAres->getListData($this->request);
        if(!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        if(!empty($user->id)) {
            $user->ares = UserAres::where('id_user', $user->id)->get();
        }
        return view('admin.user.detail',[
            'id' => $id,
            'title' => $title,
            'role' => $role,
            'department' => $department,
            'user' => $user,
            'ares' => $ares ?? [],
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
            $list_ares = $this->request->list_ares;
            UserAres::where('id_user', $user->id)
                ->delete();
            if(!empty($list_ares)) {
                foreach ($list_ares as $key => $value) {
                    $UserAres = new UserAres();
                    $UserAres->id_user = $user->id;
                    $UserAres->id_ares = $value;
                    $UserAres->save();
                }
            }

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



    public function import_excel() {
        if (!has_permission('user', 'import')) {
            access_denied();
        }
        $title = lang('c_import_user');
        return view('admin.user.import_excel', [
            'title' => $title,
        ]);
    }

    public function action_import(Request $request) {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

//        try {
        $path = $request->file('file')->getRealPath();

        // Đọc dữ liệu thành mảng
        $dataExcel = Excel::toArray([], $request->file('file'));
        if (!empty($dataExcel) && count($dataExcel) > 0) {
            $rows = $dataExcel[0]; // Sheet đầu tiên
            $dataExcelRow = $dataExcel[0]; // Sheet đầu tiên
            $dataExcelRow[0][] = 'KẾT QUẢ';

            // Nếu có header, bỏ qua dòng đầu tiên
            $header = array_map('strtolower', $rows[0]);
            unset($rows[0]);

            foreach ($rows as $key => $row) {
                if(empty($row[2])) {
                    $dataExcelRow[$key]['result'] = '<span class="label label-warning">Tên nhân viên không được để trống</span>';
                    continue;
                }

                $code = !empty($row[1]) ? $row[1] : ('NS-' . time(). rand(1000, 9999));
                $name = $row[2];
                $email = $row[3];
                $phone = $row[4];
                $password = !empty($row[6]) ? bcrypt($row[6]) : NULL;
                $dataExcelRow[$key][6] = !empty($password) ? '******' : '';
                $active = $row[8] ?? 0;
                $dataExcelRow[$key][8] = !empty($active) ? 'Hoạt động' : 'Khóa';
                $ares = $row[7];
                $department = $row[5];
//
//                $arrayDataAppend = [
//                    'gender' => 9,
//                    'birthday' => 10,
//                    'place_of_birth' => 11,
//                    'national_id' => 12,
//                    'date_of_issue' => 13,
//                    'place_of_issue' => 14,
//                    'marital_status' => 15,
//                    'nationality' => 16,
//                    'ethnicity' => 17,
//                    'religion' => 18,
//                    'address' => 19,
//                    'current_address' => 20,
//                    'emergency_contact_name' => 21,
//                    'emergency_contact_phone' => 22,
//                    'relationship_with_emergency' => 23,
//                    'education_level' => 24,
//                    'major' => 25,
//                    'university' => 26,
//                    'graduation_year' => 27,
//                    'certificates' => 28,
//                    'employment_type' => 29,
//                    'start_date_work' => 30,
//                    'contract_number' => 31,
//                    'bank_account' => 32,
//                    'bank_name' => 33,
//                    'address_bank_account' => 34,
//                    'social_insurance_number' => 35,
//                    'tax_code' => 36,
//                    'area' => 37,
//                ];
                $arrayDataAppend = [];
//                if(!empty($row[$arrayDataAppend['gender']])) {
//                    if(mb_strtolower($row[$arrayDataAppend['gender']], 'UTF-8') == 'nam') {
//                        $row[$arrayDataAppend['gender']] = 1;
//                    }
//                    else if(mb_strtolower($row[$arrayDataAppend['gender']], 'UTF-8') == 'nữ'){
//                        $row[$arrayDataAppend['gender']] = 2;
//                    }
//                    else {
//                        $row[$arrayDataAppend['gender']] = 0;
//                    }
//                }
//                if(!empty($row[$arrayDataAppend['birthday']])) {
//                    $row[$arrayDataAppend['birthday']] = dateExcelToDatime($row[$arrayDataAppend['birthday']], true);
//                    $dataExcelRow[$key][$arrayDataAppend['birthday']] = _dt($row[$arrayDataAppend['birthday']]);
//                }
//                if(!empty($row[$arrayDataAppend['date_of_issue']])) {
//                    $row[$arrayDataAppend['date_of_issue']] = dateExcelToDatime($row[$arrayDataAppend['date_of_issue']]);
//                    $dataExcelRow[$key][$arrayDataAppend['date_of_issue']] = _dthuan($row[$arrayDataAppend['date_of_issue']]);
//                }
//                if(!empty($row[$arrayDataAppend['start_date_work']])) {
//                    $row[$arrayDataAppend['start_date_work']] = dateExcelToDatime($row[$arrayDataAppend['start_date_work']]);
//                    $dataExcelRow[$key][$arrayDataAppend['start_date_work']] = _dthuan($row[$arrayDataAppend['start_date_work']]);
//                }
//                if(!empty($row[$arrayDataAppend['area']])) {
//                    $area = Province::where('name', 'like', ('%' . $row[$arrayDataAppend['area']].'%'))->first();
//                    $row[$arrayDataAppend['area']] = $area->province_id ?? NULL;
//                    $dataExcelRow[$key][$arrayDataAppend['area']] = $area->name ?? '';
//                }


                $ares = explode(',', $ares);
                $department = explode(',', $department);
                $listDeparment = Department::where(function ($query) use ($department) {
                    foreach($department as $item) {
                        $query->orWhere('name', '=', trim($item));
                    }
                })->get();
                if($listDeparment->isEmpty() && !empty($department)) {
                    $dataExcelRow[$key]['result'] = '<span class="label label-warning">Không tìm thấy phòng ban</span>';
                    continue;
                }
                else if(count($department) > count($listDeparment)) {
                    $dataExcelRow[$key]['result'] = '<span class="label label-warning">Không tìm thấy phòng ban</span>';
                    continue;
                }

                $this->request->merge(['list_name' => $ares]);
                $listAres = $this->fnbAres->getListDataWhereName($this->request);
                if(!empty($listAres->getData(true))) {
                    $listAres = $listAres->getData(true)['data'];
                    if(empty($listAres)) {
                        $dataExcelRow[$key]['result'] = '<span class="label label-warning">Không tìm khu vực</span>';
                        continue;
                    }
                    else if(count($ares) > count($listAres)) {
                        $dataExcelRow[$key]['result'] = '<span class="label label-warning">Không tìm khu vực</span>';
                        continue;
                    }
                }


                $listDeparmentID = [];
                if(!empty($listDeparment)) {
                    foreach ($listDeparment as $item) {
                        $listDeparmentID[] = $item->id;
                    }
                }

                $listAresID = [];
                if(!empty($listAres)) {
                    foreach ($listAres as $item) {
                        $listAresID[] = $item['id'];
                    }
                }

                $user = User::where('code', '=', $code)->first();
                if(!empty($user->id) && $user->admin == 1) {
                    $dataExcelRow[$key]['result'] = '<span class="label label-danger">Không thể cập nhật tài khoản quản trị viên</span>';
                    continue;

                }
                if(empty($user->id)) {
                    $dataInsert = [
                        'code' => $code,
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => $password,
                        'active' => $active ?? 0,
                    ];

                    foreach($arrayDataAppend as $kd => $vd) {
                        $dataInsert[$kd] = $row[$vd] ?? NULL;
                    }

                    $idUser = DB::table('tbl_users')->insertGetId($dataInsert);
                    if(!empty($idUser)) {
                        if(!empty($listDeparmentID)) {
                            foreach($listDeparmentID as $listDeparmentIDItem) {
                                DB::table('tbl_user_department')->insert([
                                    'user_id' => $idUser,
                                    'department_id' => $listDeparmentIDItem,
                                ]);
                            }
                        }
                        if(!empty($listAresID)) {
                            foreach($listAresID as $listAresIDItem) {
                                DB::table('tbl_user_ares')->insert([
                                    'id_user' => $idUser,
                                    'id_ares' => $listAresIDItem,
                                ]);
                            }
                        }
                        $dataExcelRow[$key]['result'] = '<span class="label label-success">Thêm Thành công</span>';
                    }
                    else {
                        $dataExcelRow[$key]['result'] = '<span class="label label-danger">Thêm thất bại</span>';
                    }
                }
                else {
                    $dataUpdate = [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => $password,
                        'active' => $active ?? 0,
                    ];

                    foreach($arrayDataAppend as $kd => $vd) {
                        $dataUpdate[$kd] = $row[$vd] ?? NULL;
                    }


                    $success = User::where('id', '=', $user->id)
                        ->update($dataUpdate);
                    if(!empty($success)) {
                        if(!empty($listDeparmentID)) {
                            DB::table('tbl_user_department')->where('user_id', '=', $user->id)->delete();
                            foreach($listDeparmentID as $listDeparmentIDItem) {
                                DB::table('tbl_user_department')->insert([
                                    'user_id' => $user->id,
                                    'department_id' => $listDeparmentIDItem,
                                ]);
                            }
                        }

                        if(!empty($listAresID)) {
                            DB::table('tbl_user_ares')->where('id_user', '=', $user->id)->delete();
                            foreach($listAresID as $listAresIDItem) {
                                DB::table('tbl_user_ares')->insert([
                                    'id_user' => $user->id,
                                    'id_ares' => $listAresIDItem,
                                ]);
                            }
                        }

                        $dataExcelRow[$key]['result'] = '<span class="label label-success">Cập nhật Thành công</span>';
                    }
                    else {
                        $dataExcelRow[$key]['result'] = '<span class="label label-danger">Cập nhật thất bại</span>';
                    }
                }
            }
        }
        $data['data'] = $dataExcelRow;
        $data['result'] = true;
        $data['message'] = 'Import dữ liệu thành công';
        return response()->json($data);
//        } catch (\Exception $e) {
//            $data['result'] = false;
//            $data['message'] = 'Import thất bại: ' . $e->getMessage();
//            return response()->json($data);
//        }
    }
}
