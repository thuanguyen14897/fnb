<?php

namespace App\Http\Controllers;

use App\Models\ModuleNoti;
use App\Models\ModuleNotiDay;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ModuleNotiController extends Controller
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getList(){
        $title = lang('dt_module_noti');
        return view('admin.module_noti.list',[
           'title' => $title
        ]);
    }

    public function getModuleNoti()
    {
        $dtModuleNoti = ModuleNoti::orderByRaw('id desc');
        return Datatables::of($dtModuleNoti)
            ->addColumn('options', function ($module_noti) {
                $edit = "<a class='dt-modal' href='admin/module_noti/detail/$module_noti->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_module_noti') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/module_noti/delete/' . $module_noti->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_module_noti') . '</a>';
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
            ->editColumn('active', function ($module_noti) {
                $checked = $module_noti->active == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/module_noti/changeStatus/'.$module_noti->id.'" data-status="'.$module_noti->active.'">';
                return $str;
            })
            ->editColumn('type_user',function ($module_noti){
                $htmlCustomer = '';
                $html = '';
                $htmlClass = '';
                if ($module_noti->type_user == 1){
                    $html = 'Khách thuê';
                    $htmlClass = 'label-default';
                } elseif ($module_noti->type_user == 2){
                    $html = 'Chủ xe';
                    $htmlClass = 'label-primary';
                } elseif ($module_noti->type_user == 3){
                    $html = 'Tài xế';
                    $htmlClass = 'label-primary';
                } elseif ($module_noti->type_user == 4){
                    $html = 'Tất cả';
                    $htmlClass = 'label-default';
                } elseif ($module_noti->type_user == 5){
                    $html = 'Gửi test';
                    $htmlClass = 'label-danger';
                    $htmlCustomer = 'Gửi cho: '.$module_noti->customer->fullname;
                }
                return '<div class="label '.$htmlClass.'">' . $html . '</div>'.'<div style="margin-top: 5px">'.$htmlCustomer.'</div>';
            })
            ->editColumn('type',function ($module_noti){
                $html = '';
                $htmlClass = '';
                if ($module_noti->type == 1){
                    $html = 'Gửi lặp lại';
                    $htmlClass = 'label-danger';
                } elseif ($module_noti->type == 2){
                    $html = 'Hẹn giờ';
                    $htmlClass = 'label-warning';
                }
                return '<div class="label '.$htmlClass.'">' . $html . '</div>';
            })
            ->editColumn('banner',function ($module_noti){
                $dtImage = !empty($module_noti->banner) ? asset('storage/' . $module_noti->banner) : null;
                return loadImageNew($dtImage,'300px','img-rounded',$module_noti->banner,false,'250px','banner_old');
            })
            ->addColumn('quantity_send',function ($module_noti){
                $object_id = $module_noti->id;
                $countSend = DB::table('tbl_notification_staff')
                    ->join('tbl_notification','tbl_notification.id','=','tbl_notification_staff.notification_id')
                    ->where(function ($query) use ($object_id){
                        $query->where('tbl_notification.object_type',401);
                        $query->where('tbl_notification.object_id',$object_id);
                    })->count();
                return '<div>'.$countSend.'</div>';
            })
            ->addColumn('repeat',function ($module_noti){
                $html = '';
                if ($module_noti->type == 1) {
                    if (!empty($module_noti->day)) {
                        foreach ($module_noti->day as $key => $value) {
                            $html .= '<label class="label label-default" style="margin-right: 5px">' . convertDate($value->day) . '</label>';
                        }
                    }
                } else {
                    $html = '<div class="label label-default">'.(!empty($module_noti->date_send) ? _dt($module_noti->date_send) : "").'</div>';
                }
                return $html;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'active','repeat','type_user','type','banner','quantity_send','detail'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_module_noti');
        } else {
            $title = lang('dt_edit_module_noti');
        }
        $moduleNoti = ModuleNoti::find($id);
        $dtTypeUser = [
            [
                'id' => 1,
                'name' => 'Khách hàng'
            ],
            [
                'id' => 5,
                'name' => 'Gửi test'
            ],
        ];
        $dtType = [
            [
                'id' => 1,
                'name' => 'Gửi lặp lại'
            ],
            [
                'id' => 2,
                'name' => 'Hẹn ngày'
            ],
        ];
        $arrDate = [];
        if (!empty($moduleNoti->day)){
            foreach ($moduleNoti->day as $key => $value){
                $arrDate []= $value->day;
            }
        }
        return view('admin.module_noti.detail', [
            'title' => $title,
            'id' => $id,
            'moduleNoti' => $moduleNoti,
            'dtType' => $dtType,
            'dtTypeUser' => $dtTypeUser,
            'arrDate' => $arrDate,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required',
                'type_user' => 'required',
                'type' => 'required',
            ]
            , [
                'name.required' => 'Bạn chưa nhập tên',
                'type_user.required' => 'Vui lòng chọn loại người dùng',
                'type.required' => 'Vui lòng chọn loại',
            ]);

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            return response()->json($data);
        }
        if (!empty($id)) {
            $moduleNoti = ModuleNoti::find($id);
        } else {
            $moduleNoti = new ModuleNoti();
        }
        $day = !empty($this->request->input('day')) ? $this->request->input('day') : [];
        $date_send = !empty($this->request->input('date_send')) ? $this->request->input('date_send') : null;
        if ($this->request->input('type') == 1){
            if (empty($day)){
                $data['result'] = 0;
                $data['message'] = 'Vui lòng chọn ngày lặp lại hàng tuần!';
                return response()->json($data);
            }
        } else {
            if (empty($date_send)){
                $data['result'] = 0;
                $data['message'] = 'Vui lòng chọn ngày gửi!';
                return response()->json($data);
            }
        }
        $customer_id = !empty($this->request->input('customer_id')) ? $this->request->input('customer_id') : 0;
        $type_user = $this->request->input('type_user');
        if ($type_user == 5){
            if (empty($customer_id)){
                $data['result'] = 0;
                $data['message'] = 'Vui lòng chọn khách thuê để test!';
                return response()->json($data);
            }
        }
        DB::beginTransaction();
        try {
            $moduleNoti->name = $this->request->input('name');
            $moduleNoti->type = $this->request->input('type');
            $moduleNoti->type_user = $this->request->input('type_user');
            $moduleNoti->detail = $this->request->input('detail');
            $moduleNoti->content = $this->request->input('note');
            $moduleNoti->customer_id = $customer_id;
            $moduleNoti->date_send = !empty($date_send) ? to_sql_date($date_send,true) : null;
            $moduleNoti->created_by = get_staff_user_id();
            $moduleNoti->save();
            DB::commit();
            if ($moduleNoti) {
                $moduleNoti->day()->delete();
                if (!empty($day)){
                    foreach ($day as $key => $value){
                        $moduleNotiDay = new ModuleNotiDay();
                        $moduleNotiDay->module_noti_id = $moduleNoti->id;
                        $moduleNotiDay->day = $value;
                        $moduleNotiDay->save();
                    }
                }
                $image_old = !empty( $this->request->input('image_old')) ?  $this->request->input('image_old') : [];
                if (!empty($moduleNoti->image)) {
                    if (!in_array($moduleNoti->image, $image_old)) {
                        $this->deleteFile($moduleNoti->image);
                        $moduleNoti->image = null;
                        $moduleNoti->save();
                    }
                }
                if ($this->request->hasFile('image')) {
                    if (!empty($moduleNoti->image)){
                        $this->deleteFile($moduleNoti->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'),'module_noti/'.$moduleNoti->id,80,80);
                    $moduleNoti->image = $path;
                    $moduleNoti->save();
                }

                $banner_old = !empty( $this->request->input('banner_old')) ?  $this->request->input('banner_old') : [];
                if (!empty($moduleNoti->banner)) {
                    if (!in_array($moduleNoti->banner, $banner_old)) {
                        $this->deleteFile($moduleNoti->banner);
                        $moduleNoti->banner = null;
                        $moduleNoti->save();
                    }
                }
                if ($this->request->hasFile('banner')) {
                    if (!empty($moduleNoti->banner)){
                        $this->deleteFile($moduleNoti->banner);
                    }
                    $path = $this->UploadFile($this->request->file('banner'),'module_noti/banner/'.$moduleNoti->id,300,250);
                    $moduleNoti->banner = $path;
                    $moduleNoti->save();
                }
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function delete($id){
        $moduleNoti = ModuleNoti::find($id);
        try {
            $success = $moduleNoti->delete();
            if ($success) {
                $moduleNoti->day()->delete();
                if (!empty($moduleNoti->image)) {
                    $this->deleteFile($moduleNoti->image);
                }
                if (!empty($moduleNoti->banner)) {
                    $this->deleteFile($moduleNoti->banner);
                }
                $data['result'] = true;
                $data['message'] = lang('dt_success');
            } else {
                $data['result'] = false;
                $data['message'] = lang('dt_error');
            }
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function changeStatus($id){
        $moduleNoti = ModuleNoti::find($id);
        try {
            $moduleNoti->active = $this->request->status == 0 ? 1 : 0;
            $moduleNoti->save();
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
