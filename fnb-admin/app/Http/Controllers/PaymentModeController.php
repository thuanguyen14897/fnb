<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\Notification;
use App\Models\PaymentMode;
use App\Models\ReferralLevel;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Mail;

class PaymentModeController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if (!has_permission('payment_mode','view')){
            access_denied();
        }
        return view('admin.payment_mode.list');
    }

    public function getPaymentMode()
    {
        $dtPaymentMode = PaymentMode::orderByRaw('id desc')->get();
        return Datatables::of($dtPaymentMode)
            ->addColumn('options', function ($payment_mode) {
                $edit = "<a class='dt-modal' href='admin/payment_mode/detail/$payment_mode->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_payment_mode') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/payment_mode/delete/'.$payment_mode->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_payment_mode') .'</a>';
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
            ->editColumn('image', function ($payment_mode) {
                $dtImage = !empty($payment_mode->image) ? asset('storage/'.$payment_mode->image) : null;
                return loadImage($dtImage);
            })
            ->editColumn('balance', function ($payment_mode) {
                return '<div>'.formatMoney($payment_mode->balance).'</div>';
            })
            ->editColumn('active', function ($payment_mode) {
                $checked = $payment_mode->active == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/payment_mode/changeStatus/'.$payment_mode->id.'" data-status="'.$payment_mode->active.'">';
                return $str;
            })
            ->editColumn('type', function ($payment_mode) {
                $str = $payment_mode->type == 1 ? '<div class="label label-primary">Tiền mặt</div>' : '<div class="label label-default">Ngân hàng</div>';
                return $str;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options','active','type','image','balance'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_payment_mode');
            if (!has_permission('payment_mode','add')){
                access_denied(true);
            }
        } else {
            if (!has_permission('payment_mode','edit')){
                access_denied(true);
            }
            $title = lang('dt_edit_payment_mode');
        }
        $paymentMode = PaymentMode::find($id);
        return view('admin.payment_mode.detail', [
            'title' => $title,
            'id' => $id,
            'paymentMode' => $paymentMode,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required|unique:tbl_payment_mode,name,' . $id,
            ]
            , [
                'name.required' => 'Bạn chưa nhập tên',
                'name.unique' => 'Tên đã tồn tại',
            ]);
        if (!empty($id)){
            $paymentMode = PaymentMode::find($id);
        } else {
            $paymentMode = new PaymentMode();
        }

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        DB::beginTransaction();
        try {
            $paymentMode->name = $this->request->name;
            $paymentMode->type = $this->request->type;
            $paymentMode->note = $this->request->note;
            $paymentMode->balance = number_unformat($this->request->balance);
            $paymentMode->save();
            DB::commit();
            if ($paymentMode) {
                if ($this->request->hasFile('image')) {
                    if (!empty($paymentMode->image)){
                        $this->deleteFile($paymentMode->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'),'payment_mode/'.$paymentMode->id,70,70);
                    $paymentMode->image = $path;
                    $paymentMode->save();
                }
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

    public function changeStatus($id){
        if (!has_permission('payment_mode','edit')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $paymentMode = PaymentMode::find($id);
        try {
            $paymentMode->active = $this->request->status == 0 ? 1 : 0;
            $paymentMode->save();
            activity()
                ->causedBy(get_staff_user_id())
                ->performedOn($paymentMode)
                ->useLog('payment_mode')
                ->withProperties(['payment_mode' => 'change_status'])
                ->log('Thay đổi trạng thái pttt ['.$paymentMode->name.']');
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
        if (!has_permission('payment_mode','delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $paymentMode = PaymentMode::find($id);
        try {
            if ($paymentMode->id == Config::get('constant')['payment_mode_bank']){
                $data['result'] = false;
                $data['message'] = 'Phương thức thanh toán của hệ thống không thể xóa!';
                return response()->json($data);
            }
            if (count($paymentMode->payment) > 0){
                $data['result'] = false;
                $data['message'] = 'Phương thức thanh toán đã được sử dụng';
            } else {
                $paymentMode->delete();
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
}
