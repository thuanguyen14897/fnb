<?php

namespace App\Http\Controllers;

use App\Models\MemberShipLevel;
use App\Models\MemberShipExpense;
use App\Models\MemberShipLongTerm;
use App\Models\MemberShipPurchases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\DataTables;
use App\Models\Department;
use App\Services\AresService;
use App\Helpers\AppHelper;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\Validator;

class MemberShipLevelController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {
        if (!has_permission('membership_level','view')){
            access_denied();
        }
        $membership_level = MemberShipLevel::get();
        $membership_expense = MemberShipExpense::get();
        $membership_long_term = MemberShipLongTerm::get();
        $membership_purchases = MemberShipPurchases::get();
        return view('admin.membership_level.list', [
            'title' => lang('c_membership_level'),
            'membership_level' => $membership_level,
            'membership_expense' => $membership_expense,
            'membership_long_term' => $membership_long_term,
            'membership_purchases' => $membership_purchases,
        ]);
    }

    public function updateMember() {
        $membership_level = $this->request->get('membership_level');
        $membership_expense = $this->request->get('membership_expense');
        $membership_long_term = $this->request->get('membership_long_term');
        $membership_purchases = $this->request->get('membership_purchases');

        DB::beginTransaction();
        try {
            foreach ($membership_level as $id => $value) {
                $memberShipLevel = MemberShipLevel::find($id);
                $memberShipLevel->point_start = number_unformat($value['point_start']);
                if(is_numeric($memberShipLevel->point_end)) {
                    if (empty($value['point_end'])) {
                        $memberShipLevel->point_end = number_unformat($value['point_end']);
                    } else {
                        $memberShipLevel->point_end = number_unformat($value['point_end']) + 1;
                    }
                }
                if(is_numeric($memberShipLevel->invoice_limit)) {
                    $memberShipLevel->invoice_limit = number_unformat($value['invoice_limit']);
                }
                if(is_numeric($memberShipLevel->radio_discount)) {
                    $memberShipLevel->radio_discount = number_unformat($value['radio_discount']);
                }
                $memberShipLevel->save();
            }
            foreach ($membership_expense as $id => $value) {
                $memberShipExpense = MemberShipExpense::find($id);
                $memberShipExpense->money_start = number_unformat($value['money_start']);
                if(is_numeric($memberShipExpense->money_end)) {
                    $memberShipExpense->money_end = number_unformat($value['money_end']);
                }
                $memberShipExpense->point = number_unformat($value['point']);
                $memberShipExpense->save();
            }
            foreach ($membership_long_term as $id => $value) {
                $memberShipLongTerm = MemberShipLongTerm::find($id);
                $memberShipLongTerm->month_start = number_unformat($value['month_start']);
                if(is_numeric($memberShipLongTerm->month_end)) {
                    $memberShipLongTerm->month_end = number_unformat($value['month_end']);
                }
                $memberShipLongTerm->point = number_unformat($value['point']);
                $memberShipLongTerm->save();
            }
            foreach ($membership_purchases as $id => $value) {
                $memberShipPurchases = MemberShipPurchases::find($id);
                $memberShipPurchases->number_purchases_start = number_unformat($value['number_purchases_start']);
                if(is_numeric($memberShipPurchases->number_purchases_end)) {
                    if (empty($value['number_purchases_end'])) {
                        $memberShipPurchases->number_purchases_end = number_unformat($value['number_purchases_end']);
                    } else {
                        $memberShipPurchases->number_purchases_end = number_unformat($value['number_purchases_end']) + 1;
                    }
                }
                $memberShipPurchases->point = number_unformat($value['point']);
                $memberShipPurchases->save();
            }

            DB::commit();
            $data['result'] = true;
            $data['message'] = 'Cập nhật thành công';
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function list_level() {
        if (!has_permission('membership_level','view')){
            access_denied();
        }

        return view('admin.membership_level.list_level', [
            'title' => lang('c_list_membership_level'),
        ]);

    }


    public function getListLevel()
    {
        $dtMemberLevel = MemberShipLevel::orderByRaw('id asc');
        return Datatables::of($dtMemberLevel)
            ->addColumn('options', function ($dtMemberLevel) {
                $edit = "<a class='dt-modal' href='admin/membership_level/detail/$dtMemberLevel->id'><i class='fa fa-pencil'></i> " . lang('c_edit_membership_level') . "</a>";
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->editColumn('name', function ($dtMemberLevel) {
                return '<div class="text-center">'.$dtMemberLevel->name.'</div>';
            })
            ->editColumn('color', function ($dtMemberLevel) {
                return '<div class="text-center"><span class="tag" style="padding:5px;background: '.$dtMemberLevel->color.'">'.$dtMemberLevel->color.'</span></div>';
            })
            ->editColumn('color_button', function ($dtMemberLevel) {
                return '<div class="text-center"><span class="tag" style="padding:5px;background: '.$dtMemberLevel->color_button.'">'.$dtMemberLevel->color_button.'</span></div>';
            })
            ->addColumn('color_background', function ($dtMemberLevel) {
                return '<div class="text-center"><span class="tag" style="padding:5px;background: '.$dtMemberLevel->color_background.'">'.$dtMemberLevel->color_background.'</span></div>';
            })
            ->addColumn('color_header', function ($dtMemberLevel) {
                return '<div class="text-center"><span class="tag" style="padding:5px;background: '.$dtMemberLevel->color_header.'">'.$dtMemberLevel->color_header.'</span></div>';
            })
            ->editColumn('icon', function ($dtMemberLevel) {
                $dtImage = !empty($dtMemberLevel->icon) ? asset('storage/'.$dtMemberLevel->icon) : null;
                return loadImageNew($dtImage,'40px','','',false,'40px');
            })
            ->editColumn('image', function ($dtMemberLevel) {
                $dtImage = !empty($dtMemberLevel->image) ? asset('storage/'.$dtMemberLevel->image) : null;
                return loadImageNew($dtImage,'100px','','',false,'75px');
            })
            ->editColumn('background_header', function ($dtMemberLevel) {
                $dtImage = !empty($dtMemberLevel->background_header) ? asset('storage/'.$dtMemberLevel->background_header) : null;
                return loadImageNew($dtImage,'100px','','',false,'75px');
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options', 'name', 'image', 'icon', 'color', 'color_background', 'color_header', 'background_header', 'color_button'])
            ->make(true);
    }

    public function detail($id = '') {
        if (!has_permission('memership_level','edit')){
            access_denied(true);
        }
        $title = lang('c_edit_membership_level');
        $memberShip = MemberShipLevel::find($id);
        return view('admin.membership_level.detail', [
            'title' => $title,
            'id' => $id,
            'dtData' => $memberShip,
        ]);
    }
    public function submit_detail($id) {
        $data = [];
        $MemberShip = MemberShipLevel::find($id);

        if (empty($this->request->input('name'))){
            $data['result'] = false;
            $data['message'] = 'Vui lòng nhập tên thứ hạng';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $MemberShip->name = $this->request->input('name');
            $MemberShip->color = $this->request->input('color');
            $MemberShip->color_background = $this->request->input('color_background');
            $MemberShip->color_header = $this->request->input('color_header');
            $MemberShip->color_button = $this->request->input('color_button');
            $MemberShip->save();
            if ($MemberShip) {
                if ($this->request->hasFile('image')) {
                    if (!empty($MemberShip->image)){
                        $this->deleteFile($MemberShip->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'),'membership_level/'.$MemberShip->id,260,390, false);
                    $MemberShip->image = $path;
                    $MemberShip->save();
                }
                if ($this->request->hasFile('icon')) {
                    if (!empty($MemberShip->icon)){
                        $this->deleteFile($MemberShip->icon);
                    }
                    $path = $this->UploadFile($this->request->file('icon'),'membership_level/'.$MemberShip->id,260,390, false);
                    $MemberShip->icon = $path;
                    $MemberShip->save();
                }
                if ($this->request->hasFile('background_header')) {
                    if (!empty($MemberShip->background_header)){
                        $this->deleteFile($MemberShip->background_header);
                    }
                    $path = $this->UploadFile($this->request->file('background_header'),'membership_level/'.$MemberShip->id,260,390, false);
                    $MemberShip->background_header = $path;
                    $MemberShip->save();
                }
                DB::commit();
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
