<?php

namespace App\Http\Controllers;

use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class KPIController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function kpi_user(){
        if (!has_permission('kpi_user','view')){
            access_denied();
        }
        $title = lang('Quy Định Đánh Giá KPI Nhân Viên Kinh Doanh');
        return view('admin.kpi.kpi_user', [
            'title' => $title
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

        $ward_ares = $this->request->ward_ares;
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
            DB::table('tbl_user_ares_ward')->where('id_user', $user->id)->delete();
            if(!empty($list_ares)) {
                foreach ($list_ares as $key => $value) {
                    $UserAres = new UserAres();
                    $UserAres->id_user = $user->id;
                    $UserAres->id_ares = $value;
                    $UserAres->save();
                    if(!empty($ward_ares[$value])) {
                        foreach($ward_ares[$value] as $k => $v) {
                            DB::table('tbl_user_ares_ward')->insert([
                                'id_user' => $user->id,
                                'id_ares' => $value,
                                'id_ward' => $v,
                            ]);
                        }
                    }
                    else {
                        $this->request->merge(['id_ares' => $value ?? 0]);
                        $this->request->merge(['limit' => -1]);
                        $response = $this->fnbCategorySystemService->getListWardToAres($this->request);
                        $dataWard = $response->getData(true);
                        if ($dataWard['result'] != false) {
                            $dtData = ($dataWard['data']) ?? [];
                            foreach ($dtData as $k => $v) {
                                DB::table('tbl_user_ares_ward')->insert([
                                    'id_user' => $user->id,
                                    'id_ares' => $value,
                                    'id_ward' => $v['Id'],
                                ]);
                            }
                        }
                    }
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
}
