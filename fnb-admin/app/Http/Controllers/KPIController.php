<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ViolationTicket;
use App\Services\AresService;
use App\Services\ReportService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class KPIController extends Controller
{
    use UploadFile;
    protected $fnbAres;
    protected $fnbReport;
    public function __construct(Request $request,AresService $AresService,ReportService $ReportService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbAres = $AresService;
        $this->fnbReport = $ReportService;
        $this->type = $this->request->query('type') ?? 1;
    }

    public function kpi_user(){
        if (!has_permission('kpi_user','view')){
            access_denied();
        }
        $title = lang('Quy Định Đánh Giá KPI Nhân Viên Kinh Doanh');
        $setting_kpi = !empty(get_option('setting_kpi')) ? json_decode(get_option('setting_kpi')) : [];
        $dtNumberRatioKpi = DB::table('tbl_number_ratio_kpi')->get();
        $dtRatioPercentKpi = DB::table('tbl_ratio_percent_kpi')->get();
        $dtTargetContractKpi = DB::table('tbl_target_contract_kpi')->get();
        $dtRetentionRateCustomerKpi = DB::table('tbl_retention_rate_customer_kpi')->get();
        $dtMemberNumberRatioKpi = DB::table('tbl_member_number_ratio_kpi')->get();
        $dtWeightTagertKpi = DB::table('tbl_weight_tagert_kpi')->get();
        $dtRatingKpi = DB::table('tbl_rating_kpi')->get();
        return view('admin.kpi.kpi_user', [
            'title' => $title,
            'setting_kpi' => $setting_kpi,
            'dtNumberRatioKpi' => $dtNumberRatioKpi,
            'dtRatioPercentKpi' => $dtRatioPercentKpi,
            'dtTargetContractKpi' => $dtTargetContractKpi,
            'dtRetentionRateCustomerKpi' => $dtRetentionRateCustomerKpi,
            'dtMemberNumberRatioKpi' => $dtMemberNumberRatioKpi,
            'dtWeightTagertKpi' => $dtWeightTagertKpi,
            'dtRatingKpi' => $dtRatingKpi,
        ]);
    }

    public function submitKPI(){
        $dataPost = $this->request->input();

        $target_month = !empty($dataPost['target_month']) ? number_unformat($dataPost['target_month']) : 0;
        $target_month_two = !empty($dataPost['target_month_two']) ? number_unformat($dataPost['target_month_two']) : 0;
        $target_member_month = !empty($dataPost['target_member_month']) ? number_unformat($dataPost['target_member_month']) : 0;

        $counter = $dataPost['counter'] ?? [];
        $arrTarget = [];
        if (!empty($counter)){
            foreach ($counter as $key => $value){
                $id = $dataPost['id'][$value] ?? 0;
                $ratio = !empty($dataPost['ratio'][$value]) ? number_unformat($dataPost['ratio'][$value]) : 0;
                $money = $target_month * $ratio / 100;
                $arrTarget[] = [
                    'id' => $id,
                    'ratio' => $ratio,
                    'money' => $money,
                ];
            }
        }

        $counterPercent = $dataPost['counterPercent'] ?? [];
        $arrRatioPercent = [];
        if (!empty($counterPercent)){
            foreach ($counterPercent as $key => $value){
                $id = $dataPost['ratio_percent_id'][$value] ?? 0;
                $percent_start = !empty($dataPost['percent_start'][$value]) ? number_unformat($dataPost['percent_start'][$value]) : 0;
                $percent_end = !empty($dataPost['percent_end'][$value]) ? number_unformat($dataPost['percent_end'][$value]) : 0;
                $point = !empty($dataPost['point'][$value]) ? number_unformat($dataPost['point'][$value]) : 0;
                $note = $dataPost['note'][$value] ?? null;
                $arrRatioPercent[] = [
                    'id' => $id,
                    'percent_start' => $percent_start,
                    'percent_end' => $percent_end,
                    'point' => $point,
                    'note' => $note,
                ];
            }
        }

        $counterContract = $dataPost['counterContract'] ?? [];
        $arrTargetContract= [];
        if (!empty($counterContract)){
            foreach ($counterContract as $key => $value){
                $id = $dataPost['target_contract_id'][$value] ?? 0;
                $contract_number_start = !empty($dataPost['contract_number_start'][$value]) ? number_unformat($dataPost['contract_number_start'][$value]) : 0;
                $contract_number_end = !empty($dataPost['contract_number_end'][$value]) ? number_unformat($dataPost['contract_number_end'][$value]) : 0;
                $point = !empty($dataPost['point_contract'][$value]) ? number_unformat($dataPost['point_contract'][$value]) : 0;
                $percent = !empty($dataPost['percent'][$value]) ? number_unformat($dataPost['percent'][$value]) : 0;
                $arrTargetContract[] = [
                    'id' => $id,
                    'contract_number_start' => $contract_number_start,
                    'contract_number_end' => $contract_number_end,
                    'percent' => $percent,
                    'point' => $point,
                ];
            }
        }

        $counterMemberRatio = $dataPost['counterMemberRatio'] ?? [];
        $arrTargetMember = [];
        if (!empty($counterMemberRatio)){
            foreach ($counterMemberRatio as $key => $value){
                $id = $dataPost['member_number_ratio_kpi_id'][$value] ?? 0;
                $ratio = !empty($dataPost['member_ratio'][$value]) ? number_unformat($dataPost['member_ratio'][$value]) : 0;
                $member = $target_member_month * $ratio / 100;
                $arrTargetMember[] = [
                    'id' => $id,
                    'ratio' => $ratio,
                    'member' => $member,
                ];
            }
        }

        $counterRetentionRate = $dataPost['counterRetentionRate'] ?? [];
        $arrRetentionRate= [];
        if (!empty($counterRetentionRate)){
            foreach ($counterRetentionRate as $key => $value){
                $id = $dataPost['retention_rate_customer_id'][$value] ?? 0;
                $point_start = !empty($dataPost['point_start'][$value]) ? number_unformat($dataPost['point_start'][$value]) : 0;
                $point_end = !empty($dataPost['point_end'][$value]) ? number_unformat($dataPost['point_end'][$value]) : 0;
                $point = !empty($dataPost['point_retention_rate'][$value]) ? number_unformat($dataPost['point_retention_rate'][$value]) : 0;
                $percent = !empty($dataPost['percent_retention_rate'][$value]) ? number_unformat($dataPost['percent_retention_rate'][$value]) : 0;
                $arrRetentionRate[] = [
                    'id' => $id,
                    'point_start' => $point_start,
                    'point_end' => $point_end,
                    'percent' => $percent,
                    'point' => $point,
                ];
            }
        }

        $counterWeightTagert = $dataPost['counterWeightTagert'] ?? [];
        $arrWeightTagert= [];
        if (!empty($counterWeightTagert)){
            foreach ($counterWeightTagert as $key => $value){
                $id = $dataPost['weight_tagert_kpi_id'][$value] ?? 0;
                $type_tagert_kpi = !empty($dataPost['type_tagert_kpi'][$value]) ? ($dataPost['type_tagert_kpi'][$value]) : null;
                $name_tagert = !empty($dataPost['name_tagert'][$value]) ? ($dataPost['name_tagert'][$value]) : null;
                $weight = !empty($dataPost['weight'][$value]) ? number_unformat($dataPost['weight'][$value]) : 0;
                $arrWeightTagert[] = [
                    'id' => $id,
                    'name_tagert' => $name_tagert,
                    'weight' => $weight,
                    'type' => $type_tagert_kpi,
                ];
            }
        }

        $counterRatingKpi = $dataPost['counterRatingKpi'] ?? [];
        $arrRatingKpi= [];
        if (!empty($counterRatingKpi)){
            foreach ($counterRatingKpi as $key => $value){
                $id = $dataPost['rating_kpi_id'][$value] ?? 0;
                $name = !empty($dataPost['name_rating'][$value]) ? ($dataPost['name_rating'][$value]) : null;
                $point_start_kpi = !empty($dataPost['point_start_kpi'][$value]) ? number_unformat($dataPost['point_start_kpi'][$value]) : 0;
                $point_end_kpi = !empty($dataPost['point_end_kpi'][$value]) ? number_unformat($dataPost['point_end_kpi'][$value]) : 0;
                $percent_profit = !empty($dataPost['percent_profit'][$value]) ? number_unformat($dataPost['percent_profit'][$value]) : 0;
                $arrRatingKpi[] = [
                    'id' => $id,
                    'name' => $name,
                    'point_start_kpi' => $point_start_kpi,
                    'point_end_kpi' => $point_end_kpi,
                    'percent_profit' => $percent_profit,
                ];
            }
        }

        DB::beginTransaction();
        try {
            $setting_kpi['target_month'] = $target_month;
            $setting_kpi['target_month_two'] = $target_month_two;
            $setting_kpi['target_member_month'] = $target_member_month;
            $setting_kpi = json_encode($setting_kpi);
            DB::table('tbl_options')->where('name', 'setting_kpi')->update([
                'name' => 'setting_kpi',
                'value' => $setting_kpi
            ]);
            if (!empty($arrTarget)){
                DB::table('tbl_number_ratio_kpi')->delete();
                DB::table('tbl_number_ratio_kpi')->insert($arrTarget);
            }

            if (!empty($arrRatioPercent)){
                DB::table('tbl_ratio_percent_kpi')->delete();
                DB::table('tbl_ratio_percent_kpi')->insert($arrRatioPercent);
            }

            if (!empty($arrTargetContract)){
                DB::table('tbl_target_contract_kpi')->delete();
                DB::table('tbl_target_contract_kpi')->insert($arrTargetContract);
            }

            if (!empty($arrRetentionRate)){
                DB::table('tbl_retention_rate_customer_kpi')->delete();
                DB::table('tbl_retention_rate_customer_kpi')->insert($arrRetentionRate);
            }

           if (!empty($arrTargetMember)){
                DB::table('tbl_member_number_ratio_kpi')->delete();
                DB::table('tbl_member_number_ratio_kpi')->insert($arrTargetMember);
           }

            if (!empty($arrWeightTagert)){
                DB::table('tbl_weight_tagert_kpi')->delete();
                DB::table('tbl_weight_tagert_kpi')->insert($arrWeightTagert);
            }
            if (!empty($arrRatingKpi)){
                DB::table('tbl_rating_kpi')->delete();
                DB::table('tbl_rating_kpi')->insert($arrRatingKpi);
            }
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('Cập nhật thành công');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function kpi_manager(){
        if (!has_permission('kpi_manager','view')){
            access_denied();
        }
        $title = lang('Quy Định Đánh Giá KPI Giám Đốc Kinh Doanh');
        $setting_kpi = !empty(get_option('setting_kpi_manager')) ? json_decode(get_option('setting_kpi_manager')) : [];
        $dtNumberRatioKpi = DB::table('tbl_number_ratio_kpi_manager')->get();
        $dtRatioPercentKpi = DB::table('tbl_ratio_percent_kpi_manager')->get();
        $dtTargetContractKpi = DB::table('tbl_target_contract_kpi_manager')->get();
        $dtRetentionRateCustomerKpi = DB::table('tbl_retention_rate_customer_kpi_manager')->get();
        $dtMemberNumberRatioKpi = DB::table('tbl_member_number_ratio_kpi_manager')->get();
        $dtWeightTagertKpi = DB::table('tbl_weight_tagert_kpi_manager')->get();
        $dtRatingKpi = DB::table('tbl_rating_kpi_manager')->get();
        return view('admin.kpi.kpi_manager', [
            'title' => $title,
            'setting_kpi' => $setting_kpi,
            'dtNumberRatioKpi' => $dtNumberRatioKpi,
            'dtRatioPercentKpi' => $dtRatioPercentKpi,
            'dtTargetContractKpi' => $dtTargetContractKpi,
            'dtRetentionRateCustomerKpi' => $dtRetentionRateCustomerKpi,
            'dtMemberNumberRatioKpi' => $dtMemberNumberRatioKpi,
            'dtWeightTagertKpi' => $dtWeightTagertKpi,
            'dtRatingKpi' => $dtRatingKpi,
        ]);
    }

    public function submitKPIManager(){
        $dataPost = $this->request->input();

        $target_month = !empty($dataPost['target_month']) ? number_unformat($dataPost['target_month']) : 0;
        $target_month_two = !empty($dataPost['target_month_two']) ? number_unformat($dataPost['target_month_two']) : 0;
        $target_member_month = !empty($dataPost['target_member_month']) ? number_unformat($dataPost['target_member_month']) : 0;

        $counter = $dataPost['counter'] ?? [];
        $arrTarget = [];
        if (!empty($counter)){
            foreach ($counter as $key => $value){
                $id = $dataPost['id'][$value] ?? 0;
                $ratio = !empty($dataPost['ratio'][$value]) ? number_unformat($dataPost['ratio'][$value]) : 0;
                $money = $target_month * $ratio / 100;
                $arrTarget[] = [
                    'id' => $id,
                    'ratio' => $ratio,
                    'money' => $money,
                ];
            }
        }

        $counterPercent = $dataPost['counterPercent'] ?? [];
        $arrRatioPercent = [];
        if (!empty($counterPercent)){
            foreach ($counterPercent as $key => $value){
                $id = $dataPost['ratio_percent_id'][$value] ?? 0;
                $percent_start = !empty($dataPost['percent_start'][$value]) ? number_unformat($dataPost['percent_start'][$value]) : 0;
                $percent_end = !empty($dataPost['percent_end'][$value]) ? number_unformat($dataPost['percent_end'][$value]) : 0;
                $point = !empty($dataPost['point'][$value]) ? number_unformat($dataPost['point'][$value]) : 0;
                $note = $dataPost['note'][$value] ?? null;
                $arrRatioPercent[] = [
                    'id' => $id,
                    'percent_start' => $percent_start,
                    'percent_end' => $percent_end,
                    'point' => $point,
                    'note' => $note,
                ];
            }
        }

        $counterContract = $dataPost['counterContract'] ?? [];
        $arrTargetContract= [];
        if (!empty($counterContract)){
            foreach ($counterContract as $key => $value){
                $id = $dataPost['target_contract_id'][$value] ?? 0;
                $contract_number_start = !empty($dataPost['contract_number_start'][$value]) ? number_unformat($dataPost['contract_number_start'][$value]) : 0;
                $contract_number_end = !empty($dataPost['contract_number_end'][$value]) ? number_unformat($dataPost['contract_number_end'][$value]) : 0;
                $point = !empty($dataPost['point_contract'][$value]) ? number_unformat($dataPost['point_contract'][$value]) : 0;
                $percent = !empty($dataPost['percent'][$value]) ? number_unformat($dataPost['percent'][$value]) : 0;
                $arrTargetContract[] = [
                    'id' => $id,
                    'contract_number_start' => $contract_number_start,
                    'contract_number_end' => $contract_number_end,
                    'percent' => $percent,
                    'point' => $point,
                ];
            }
        }

        $counterMemberRatio = $dataPost['counterMemberRatio'] ?? [];
        $arrTargetMember = [];
        if (!empty($counterMemberRatio)){
            foreach ($counterMemberRatio as $key => $value){
                $id = $dataPost['member_number_ratio_kpi_id'][$value] ?? 0;
                $ratio = !empty($dataPost['member_ratio'][$value]) ? number_unformat($dataPost['member_ratio'][$value]) : 0;
                $member = $target_member_month * $ratio / 100;
                $arrTargetMember[] = [
                    'id' => $id,
                    'ratio' => $ratio,
                    'member' => $member,
                ];
            }
        }

        $counterRetentionRate = $dataPost['counterRetentionRate'] ?? [];
        $arrRetentionRate= [];
        if (!empty($counterRetentionRate)){
            foreach ($counterRetentionRate as $key => $value){
                $id = $dataPost['retention_rate_customer_id'][$value] ?? 0;
                $point_start = !empty($dataPost['point_start'][$value]) ? number_unformat($dataPost['point_start'][$value]) : 0;
                $point_end = !empty($dataPost['point_end'][$value]) ? number_unformat($dataPost['point_end'][$value]) : 0;
                $point = !empty($dataPost['point_retention_rate'][$value]) ? number_unformat($dataPost['point_retention_rate'][$value]) : 0;
                $percent = !empty($dataPost['percent_retention_rate'][$value]) ? number_unformat($dataPost['percent_retention_rate'][$value]) : 0;
                $arrRetentionRate[] = [
                    'id' => $id,
                    'point_start' => $point_start,
                    'point_end' => $point_end,
                    'percent' => $percent,
                    'point' => $point,
                ];
            }
        }

        $counterWeightTagert = $dataPost['counterWeightTagert'] ?? [];
        $arrWeightTagert= [];
        if (!empty($counterWeightTagert)){
            foreach ($counterWeightTagert as $key => $value){
                $id = $dataPost['weight_tagert_kpi_id'][$value] ?? 0;
                $type_tagert_kpi = !empty($dataPost['type_tagert_kpi'][$value]) ? ($dataPost['type_tagert_kpi'][$value]) : null;
                $name_tagert = !empty($dataPost['name_tagert'][$value]) ? ($dataPost['name_tagert'][$value]) : null;
                $weight = !empty($dataPost['weight'][$value]) ? number_unformat($dataPost['weight'][$value]) : 0;
                $arrWeightTagert[] = [
                    'id' => $id,
                    'name_tagert' => $name_tagert,
                    'weight' => $weight,
                    'type' => $type_tagert_kpi,
                ];
            }
        }

        $counterRatingKpi = $dataPost['counterRatingKpi'] ?? [];
        $arrRatingKpi= [];
        if (!empty($counterRatingKpi)){
            foreach ($counterRatingKpi as $key => $value){
                $id = $dataPost['rating_kpi_id'][$value] ?? 0;
                $name = !empty($dataPost['name_rating'][$value]) ? ($dataPost['name_rating'][$value]) : null;
                $point_start_kpi = !empty($dataPost['point_start_kpi'][$value]) ? number_unformat($dataPost['point_start_kpi'][$value]) : 0;
                $point_end_kpi = !empty($dataPost['point_end_kpi'][$value]) ? number_unformat($dataPost['point_end_kpi'][$value]) : 0;
                $percent_profit = !empty($dataPost['percent_profit'][$value]) ? number_unformat($dataPost['percent_profit'][$value]) : 0;
                $arrRatingKpi[] = [
                    'id' => $id,
                    'name' => $name,
                    'point_start_kpi' => $point_start_kpi,
                    'point_end_kpi' => $point_end_kpi,
                    'percent_profit' => $percent_profit,
                ];
            }
        }

        DB::beginTransaction();
        try {
            $setting_kpi['target_month'] = $target_month;
            $setting_kpi['target_month_two'] = $target_month_two;
            $setting_kpi['target_member_month'] = $target_member_month;
            $setting_kpi = json_encode($setting_kpi);
            DB::table('tbl_options')->where('name', 'setting_kpi_manager')->update([
                'name' => 'setting_kpi_manager',
                'value' => $setting_kpi
            ]);
            if (!empty($arrTarget)){
                DB::table('tbl_number_ratio_kpi_manager')->delete();
                DB::table('tbl_number_ratio_kpi_manager')->insert($arrTarget);
            }

            if (!empty($arrRatioPercent)){
                DB::table('tbl_ratio_percent_kpi_manager')->delete();
                DB::table('tbl_ratio_percent_kpi_manager')->insert($arrRatioPercent);
            }

            if (!empty($arrTargetContract)){
                DB::table('tbl_target_contract_kpi_manager')->delete();
                DB::table('tbl_target_contract_kpi_manager')->insert($arrTargetContract);
            }

            if (!empty($arrRetentionRate)){
                DB::table('tbl_retention_rate_customer_kpi_manager')->delete();
                DB::table('tbl_retention_rate_customer_kpi_manager')->insert($arrRetentionRate);
            }

            if (!empty($arrTargetMember)){
                DB::table('tbl_member_number_ratio_kpi_manager')->delete();
                DB::table('tbl_member_number_ratio_kpi_manager')->insert($arrTargetMember);
            }

            if (!empty($arrWeightTagert)){
                DB::table('tbl_weight_tagert_kpi_manager')->delete();
                DB::table('tbl_weight_tagert_kpi_manager')->insert($arrWeightTagert);
            }
            if (!empty($arrRatingKpi)){
                DB::table('tbl_rating_kpi_manager')->delete();
                DB::table('tbl_rating_kpi_manager')->insert($arrRatingKpi);
            }
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('Cập nhật thành công');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function violation_ticket(){
        if (!has_permission('violation_ticket','view') && !has_permission('violation_ticket', 'viewown')){
            access_denied();
        }
        $title = lang('dt_violation_ticket');
        return view('admin.kpi.violation_ticket', [
            'title' => $title,
        ]);
    }

    public function getViolationTicket(){
        $checkPermission = true;
        if (!has_permission('violation_ticket', 'view') && has_permission('violation_ticket', 'viewown')) {
            $user_ids = getUserIdByRole();
            $checkPermission = false;
        }

        $query = ViolationTicket::with('user.user_ares');
        if (empty($checkPermission)) {
            $query->whereIn('staff_id', ($user_ids ?? [0]));
        }
        $query = $query->get();
        $id_ares = $query->flatMap(function ($item) {
            return $item->user->user_ares->pluck('id_ares');
        })->unique()->values()->toArray();

        $this->requestAre = clone $this->request;
        $this->requestAre->merge(['are_id' => $id_ares ?? [0]]);
        $this->requestAre->merge(['limit_all' => true]);
        $this->requestAre->merge(['search' => null]);
        $this->requestAre->merge(['show_short' => true]);
        $data_ares = $this->fnbAres->getListData($this->requestAre);
        $dtAres = $data_ares->getData(true);
        $dtAres = collect($dtAres['data']['data'] ?? []);
        $query = $query->map(function ($item) use ($dtAres) {
            $item->user->user_ares->map(function ($it) use ($dtAres) {
                $ares = $dtAres->where('id', '=', $it->id_ares)->first();
                $it->name = $ares['name'] ?? null;
                return $it;
            });
            return $item;
        });
        return Datatables::of($query)
            ->addColumn('options', function ($data) {
                $edit = "<a class='dt-modal' href='admin/kpi/detail_violation_ticket/$data->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_violation_ticket') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/kpi/delete_violation_ticket/'.$data->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_violation_ticket').'</a>';
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
            ->editColumn('date', function ($data) {
                return '<div>'._dt($data->date).'</div>';
            })
            ->addColumn('ares', function ($data) {
                $user = $data->user ?? [];
                $str = '';
                if (!empty($user->user_ares)) {
                    foreach ($user->user_ares as $key => $value) {
                        $str .= "<div class='label label-success' style='margin-bottom: 5px;margin-right: 5px'>" . ($value['name'] ?? '') . "</div>" . ' ';
                    }
                }
                return '<div style="display: flex;flex-wrap: wrap">' . $str . '</div>';
            })
            ->editColumn('staff_id', function ($data) {
                $user = $data->user ?? [];
                if (!empty($user)){
                    $url = !empty($user->image) ? asset('storage/' . $user->image) : asset('admin/assets/images/users/avatar-1.jpg');
                    return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                            '40px') . '<div>'.(!empty($user->name) ? $user->name : '') . '</div></div>';
                } else {
                    return '<div></div>';
                }
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns([
                'options',
                'date',
                'ares',
                'staff_id'
            ])
            ->make(true);
    }

    public function detail_violation_ticket($id = 0){
        $dtData = ViolationTicket::find($id);
        if(empty($id)){
            if (!has_permission('violation_ticket','add')){
                access_denied(true);
            }

            $reference_no = getReference('violation_ticket');
            $title = lang('dt_add_violation_ticket');
        } else {
            if (!has_permission('violation_ticket','edit')){
                access_denied(true);
            }
            $title = lang('dt_edit_violation_ticket');
            if (empty($dtData)){
                $data['result'] = false;
                $data['message'] = lang('Phiếu vi phạm không tồn tại');
                echo json_encode($data);
                die();
            }
            $reference_no = $dtData->reference_no;
        }
        if ($this->request->input()){
            $rules = [
                'reference_no' => 'required|unique:tbl_violation_ticket,reference_no,' . $id,
                'date_new' => 'required',
                'staff_id' => 'required',
            ];
            $messages = [
                'reference_no.required' => 'Vui lòng nhập mã phiếu',
                'reference_no.unique' => 'Mã phiếu đã tồn tại!',
                'date_new.required' => 'Vui lòng nhập ngày',
                'staff_id.required' => 'Vui lòng chọn nhân viên',
            ];
            $validator = Validator::make($this->request->all(), $rules, $messages);
            if ($validator->fails()) {
                $data['result'] = false;
                $data['message'] = $validator->errors()->all();
                return response()->json($data);
            }
            if (empty($id)){
                $dtData = new ViolationTicket();
            }
            DB::beginTransaction();
            try {
                $dtData->date = to_sql_date($this->request->input('date_new'),true);
                $dtData->reference_no = $this->request->input('reference_no');
                $dtData->staff_id = $this->request->input('staff_id');
                $dtData->note = $this->request->input('note') ?? null;
                $dtData->save();
                if (empty($id)){
                    if ($this->request->input('reference_no') == $reference_no){
                        updateReference('violation_ticket');
                    }
                }
                DB::commit();
                $data['result'] = true;
                if (empty($id)){
                    $data['message'] = lang('Thêm phiếu vi phạm thành công');
                } else {
                    $data['message'] = lang('Cập nhật phiếu vi phạm thành công');
                }
                return response()->json($data);
            } catch (\Exception $exception) {
                DB::rollBack();
                $data['result'] = false;
                $data['message'] = $exception->getMessage();
                return response()->json($data);
            }
        }
        return view('admin.kpi.detail_violation_ticket', [
            'title' => $title,
            'reference_no' => $reference_no,
            'id' => $id,
            'dtData' => $dtData
        ]);
    }

    public function delete_violation_ticket($id){
        if (!has_permission('violation_ticket','delete')){
            access_denied(true);
        }
        $dtData = ViolationTicket::find($id);
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = lang('Phiếu vi phạm không tồn tại');
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $dtData->delete();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('Xóa phiếu vi phạm thành công');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function report_synthetic_kpi_user(){
        if ($this->type == 1) {
            if (!has_permission('report_synthetic_kpi_user','view') && !has_permission('report_synthetic_kpi_user', 'viewown')){
                access_denied();
            }
            $title = lang('Thống kê KPI Nhân Viên Kinh Doanh');
        } else {
            if (!has_permission('report_synthetic_kpi_manager','view') && !has_permission('report_synthetic_kpi_manager', 'viewown')){
                access_denied();
            }
            $title = lang('Thống kê KPI Giám Đốc Kinh Doanh');
        }
        return view('admin.kpi.report_synthetic_kpi_user', [
            'title' => $title,
            'type' => $this->type
        ]);
    }

    public function getReportSyntheticKpiUser(){
        $checkPermission = true;
        $type = ($this->request->input('type')) ?? 1;
        if ($type == 1) {
            if (!has_permission('report_synthetic_kpi_user', 'view') && has_permission('report_synthetic_kpi_user',
                    'viewown')) {
                $user_ids = getUserIdByRole([], 0, false);
                $checkPermission = false;
            }
        } else {
            if (!has_permission('report_synthetic_kpi_manager', 'view') && has_permission('report_synthetic_kpi_manager',
                    'viewown')) {
                $user_ids = getUserIdByRole([], 0, false);
                $checkPermission = false;
            }
        }

        $month_search = ($this->request->input('month_search')) ?? date('m');
        $year_search = ($this->request->input('year_search')) ??  date('Y');
        $staff_search = ($this->request->input('staff_search')) ?? null;

        $query = User::select('tbl_calculate_kpi_detail.*','tbl_calculate_kpi_detail.id as calculate_kpi_detail_id','tbl_users.name as name','tbl_users.code as code','tbl_users.image as image','tbl_users.id');
        $query->with('user_ares');
        $query->join('tbl_calculate_kpi_detail','tbl_calculate_kpi_detail.staff_id','=','tbl_users.id');
        $query->join('tbl_calculate_kpi','tbl_calculate_kpi.id','=','tbl_calculate_kpi_detail.calculate_kpi_id');
        if ($this->request->input('ares_search')) {
            $ares_search = $this->request->input('ares_search');
            $query->WhereHas('user_ares', function ($q) use ($ares_search) {
                $q->where('id_ares', '=', "$ares_search");
            });
        }
        if (empty($checkPermission)) {
            $query->whereIn('tbl_calculate_kpi_detail.staff_id', ($user_ids ?? [0]));
        }
        $query->where('tbl_calculate_kpi.month', '=', $month_search);
        $query->where('tbl_calculate_kpi.year', '=', $year_search);
        if ($type == 1) {
            $query->where('tbl_users.check_nvkd', '=', 1);
        } else {
            $query->where('tbl_users.check_manager', '=', 1);
        }
        if (!empty($staff_search)){
            $query->where('tbl_calculate_kpi_detail.staff_id', '=', $staff_search);
        }
        $user = $query->get();


        $id_ares = $user->flatMap(function ($item) {
            return $item->user_ares->pluck('id_ares');
        })->unique()->values()->toArray();
        $this->requestAre = clone $this->request;
        $this->requestAre->merge(['are_id' => $id_ares ?? [0]]);
        $this->requestAre->merge(['limit_all' => true]);
        $this->requestAre->merge(['search' => null]);
        $this->requestAre->merge(['show_short' => true]);
        $data_ares = $this->fnbAres->getListData($this->requestAre);
        $dtAres = $data_ares->getData(true);
        $dtAres = collect($dtAres['data']['data'] ?? []);
        $user = $user->map(function ($item) use ($dtAres) {
            $item->user_ares->map(function ($it) use ($dtAres) {
                $ares = $dtAres->where('id', '=', $it->id_ares)->first();
                $it->name = $ares['name'] ?? null;
                return $it;
            });
            return $item;
        });

        return Datatables::of($user)
            ->addColumn('ares', function ($data) {
                $str = '';
                if (!empty($data->user_ares)) {
                    foreach ($data->user_ares as $key => $value) {
                        $str .= "<div class='label label-success' style='margin-bottom: 5px;margin-right: 5px'>" . ($value['name'] ?? '') . "</div>" . ' ';
                    }
                }
                return '<div style="display: flex;flex-wrap: wrap">' . $str . '</div>';
            })
            ->editColumn('name', function ($data) {
                $str = '<div>'.$data['name'].'</div>';
                return $str;
            })
            ->editColumn('code', function ($data) {
                $str = "<a target='_blank' href='admin/user/detail/$data->id'>$data->code</a>";
                return $str;
            })
            ->addColumn('total_payment', function ($data) {
                $str = '<div>'.($data['total_payment'] > 0 ? formatMoney($data['total_payment']) : '-').'</div>';
                return $str;
            })
            ->addColumn('total_service', function ($data) {
                $str = '<div>'.($data['total_service'] > 0 ? ($data['total_service']) : '-').'</div>';
                return $str;
            })
            ->addColumn('total_member', function ($data) {
                $str = '<div>'.($data['total_member'] > 0 ? ($data['total_member']) : '-').'</div>';
                return $str;
            })
            ->addColumn('total_violate', function ($data) {
                $str = '<div>'.($data['total_violate'] > 0 ? ($data['total_violate']) : '-').'</div>';
                return $str;
            })
            ->editColumn('image', function ($data) {
                $dtImage = !empty($data->image) ? asset('storage/' . $data->image) : 'admin/assets/images/users/avatar-1.jpg';
                $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="image"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';

                return $str;
            })
            ->addColumn('point_payment', function ($data) {
                $str = '<div>'.($data['point_payment'] > 0 ? ($data['point_payment']) : '-').'</div>';
                return $str;
            })
            ->addColumn('point_service', function ($data) {
                $str = '<div>'.($data['point_service'] > 0 ? ($data['point_service']) : '-').'</div>';
                return $str;
            })
            ->addColumn('point_member', function ($data) {
                $str = '<div>'.($data['point_member'] > 0 ? ($data['point_member']) : '-').'</div>';
                return $str;
            })
            ->addColumn('point_violation', function ($data) {
                $str = '<div>'.($data['point_violation'] > 0 ? ($data['point_violation']) : '-').'</div>';
                return $str;
            })
            ->addColumn('point_kpi', function ($data) {
                $str = '<div>'.($data['point_kpi'] > 0 ? ($data['point_kpi']) : '-').'</div>';
                return $str;
            })
            ->addColumn('name_kpi', function ($data) {
                $str = '<div>'.$data['name_kpi'].'</div>';
                return $str;
            })
            ->addColumn('calculate_kpi_detail_id', function ($data) {
                $str = '<div><input type="checkbox" name="items[]" id="check-item' . $data->calculate_kpi_detail_id . '" value="' . $data->calculate_kpi_detail_id . '"><label for="check-item' . $data->calculate_kpi_detail_id . '"></div>';
                return $str;
            })
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns([
                'total_payment',
                'total_service',
                'total_member',
                'total_violate',
                'point_payment',
                'point_service',
                'point_member',
                'point_violation',
                'point_kpi',
                'name_kpi',
                'calculate_kpi_detail_id',
                'image',
                'name',
                'ares',
                'code'
            ])
            ->make(true);
    }

    public function detail_report_synthetic_kpi_user(){
        if ($this->type == 1) {
            if (!has_permission('report_synthetic_kpi_user', 'add')) {
                access_denied();
            }
            $title = lang('Tạo KPI Nhân Viên Kinh Doanh');
        } else {
            if (!has_permission('report_synthetic_kpi_manager', 'add')) {
                access_denied();
            }
            $title = lang('Tạo KPI Giám Đốc Kinh Doanh');
        }
        if ($this->request->post()){
            $rules = [
                'month_search' => 'required',
                'year_search' => 'required',
            ];
            $messages = [
                'month_search.required' => 'Vui lòng chọn tháng !',
                'year_search.required' => 'Vui lòng chọn năm !',
            ];
            $validator = Validator::make($this->request->all(), $rules, $messages);
            if ($validator->fails()) {
                $data['result'] = false;
                $data['message'] = $validator->errors()->all();
                return response()->json($data);
            }
            $month = $this->request->input('month_search');
            $year = $this->request->input('year_search');

            $counter = $this->request->input('counter') ?? [];
            $staff_id_post = $this->request->input('staff_id');
            $total_payment_post = $this->request->input('total_payment');
            $total_service_post = $this->request->input('total_service');
            $total_member_post = $this->request->input('total_member');
            $total_violate_post = $this->request->input('total_violate');
            $target_month_post = $this->request->input('target_month');
            $target_member_month_post = $this->request->input('target_member_month');
            $point_payment_post = $this->request->input('point_payment');
            $point_service_post = $this->request->input('point_service');
            $point_member_post = $this->request->input('point_member');
            $point_violation_post = $this->request->input('point_violation');
            $dt_ratio_percent_kpi_post = $this->request->input('dt_ratio_percent_kpi');
            $dt_target_contract_kpi_post = $this->request->input('dt_target_contract_kpi');
            $dt_retention_rate_customer_kpi_post = $this->request->input('dt_retention_rate_customer_kpi');
            $dt_weight_tagert_kpi_post = $this->request->input('dt_weight_tagert_kpi');
            $dt_rating_kpi_post = $this->request->input('dt_rating_kpi');
            $total_ward_post = $this->request->input('total_ward');
            $check_manager_post = $this->request->input('check_manager');
            $arrKpi = [];
            if (!empty($counter)) {
                DB::beginTransaction();
                try {
                    foreach ($counter as $key => $value) {
                        $staff_id = $staff_id_post[$value] ?? 0;
                        if (empty($staff_id)){
                            continue;
                        }
                        $total_payment = !empty($total_payment_post[$value]) ? number_unformat($total_payment_post[$value]) : 0;
                        $total_service = !empty($total_service_post[$value]) ? number_unformat($total_service_post[$value]) : 0;
                        $total_member = !empty($total_member_post[$value]) ? number_unformat($total_member_post[$value]) : 0;
                        $total_violate = !empty($total_violate_post[$value]) ? number_unformat($total_violate_post[$value]) : 0;
                        $target_month = !empty($target_month_post[$value]) ? number_unformat($target_month_post[$value]) : 0;
                        $target_member_month = !empty($target_member_month_post[$value]) ? number_unformat($target_member_month_post[$value]) : 0;
                        $point_payment = !empty($point_payment_post[$value]) ? number_unformat($point_payment_post[$value]) : 0;
                        $point_service = !empty($point_service_post[$value]) ? number_unformat($point_service_post[$value]) : 0;
                        $point_member = !empty($point_member_post[$value]) ? number_unformat($point_member_post[$value]) : 0;
                        $point_violation = !empty($point_violation_post[$value]) ? number_unformat($point_violation_post[$value]) : 0;
                        $dt_ratio_percent_kpi = !empty($dt_ratio_percent_kpi_post[$value]) ? json_decode($dt_ratio_percent_kpi_post[$value]) : null;
                        $dt_target_contract_kpi = !empty($dt_target_contract_kpi_post[$value]) ? json_decode($dt_target_contract_kpi_post[$value]) : null;
                        $dt_retention_rate_customer_kpi = !empty($dt_retention_rate_customer_kpi_post[$value]) ? json_decode($dt_retention_rate_customer_kpi_post[$value]) : null;
                        $dt_weight_tagert_kpi = !empty($dt_weight_tagert_kpi_post[$value]) ? json_decode($dt_weight_tagert_kpi_post[$value]) : null;
                        $dt_rating_kpi = !empty($dt_rating_kpi_post[$value]) ? json_decode($dt_rating_kpi_post[$value]) : null;
                        $total_ward = !empty($total_ward_post[$value]) ? number_unformat($total_ward_post[$value]) : 0;
                        $check_manager = !empty($check_manager_post[$value]) ? ($check_manager_post[$value]) : 0;

                        $point_kpi = 0;
                        if (!empty($dt_weight_tagert_kpi)){
                            foreach ($dt_weight_tagert_kpi as $k => $v){
                                if ($v->type == 'payment'){
                                    $point_kpi += ($point_payment * $v->weight) / 100;
                                }
                                if ($v->type == 'service'){
                                    $point_kpi += ($point_service * $v->weight) / 100;
                                }
                                if ($v->type == 'member'){
                                    $point_kpi += ($point_member * $v->weight) / 100;
                                }
                                if ($v->type == 'violate'){
                                    $point_kpi += ($point_violation * $v->weight) / 100;
                                }
                            }
                        }

                        $name_kpi = '';
                        $rating_kpi_id = 0;
                        if (!empty($dt_rating_kpi)){
                            foreach ($dt_rating_kpi as $k => $v){
                                if ($point_kpi >= $v->point_start_kpi && $point_kpi < $v->point_end_kpi){
                                    $name_kpi = $v->name;
                                    $rating_kpi_id = $v->id;
                                    break;
                                }
                            }
                        }

                        if ($check_manager == 1){
                            $target_month = $total_ward > 0 ? $target_month / $total_ward : $target_month;
                        }

                        $arrKpi[] = [
                            'staff_id' => $staff_id,
                            'target_month' => $target_month,
                            'target_member_month' => $target_member_month,
                            'point_payment' => $point_payment,
                            'point_service' => $point_service,
                            'point_member' => $point_member,
                            'point_violation' => $point_violation,
                            'total_payment' => $total_payment,
                            'total_service' => $total_service,
                            'total_member' => $total_member,
                            'total_violate' => $total_violate,
                            'point_kpi' => $point_kpi,
                            'name_kpi' => $name_kpi,
                            'rating_kpi_id' => $rating_kpi_id,
                            'total_ward' => $total_ward ?? 0,
                            'check_manager' => $check_manager ?? 0,
                            'dt_ratio_percent_kpi' => json_encode($dt_ratio_percent_kpi),
                            'dt_target_contract_kpi' => json_encode($dt_target_contract_kpi),
                            'dt_retention_rate_customer_kpi' => json_encode($dt_retention_rate_customer_kpi),
                            'dt_weight_tagert_kpi' => json_encode($dt_weight_tagert_kpi),
                        ];
                    }
                    if (empty($arrKpi)) {
                        $data['result'] = false;
                        $data['message'] = lang('Không có dữ liệu');
                        return response()->json($data);
                    }
                    $calculate_kpi_id = 0;
                    $dtCalculateKpi = DB::table('tbl_calculate_kpi')
                        ->where('month','=',$month)
                        ->where('year','=',$year)->first();
                    if (!empty($dtCalculateKpi)) {
                        $calculate_kpi_id = $dtCalculateKpi->id;
                    } else {
                        $calculate_kpi_id = DB::table('tbl_calculate_kpi')->insertGetId([
                            'month' => $month,
                            'year' => $year,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                    if (!empty($calculate_kpi_id)){
                        foreach ($arrKpi as $key => $value){
                            $arrKpi[$key]['calculate_kpi_id'] = $calculate_kpi_id;
                        }
                        $inserted = DB::table('tbl_calculate_kpi_detail')->insert($arrKpi);
                        DB::commit();
                        if ($inserted > 0){
                            $data['result'] = true;
                            $data['message'] = lang('Thêm thành công');
                            return response()->json($data);
                        } else {
                            $data['result'] = false;
                            $data['message'] = lang('Không có dữ liệu được thêm');
                            return response()->json($data);
                        }
                    } else {
                        $data['result'] = false;
                        $data['message'] = lang('Không có dữ liệu');
                        return response()->json($data);
                    }
                } catch (\Exception $exception) {
                    DB::rollBack();
                    $data['result'] = false;
                    $data['message'] = $exception->getMessage();
                    return response()->json($data);
                }
            } else {
                $data['result'] = false;
                $data['message'] = lang('Không có dữ liệu');
                return response()->json($data);
            }
        }
        if ($this->type == 1){
            return view('admin.kpi.detail_report_synthetic_kpi_user', [
                'title' => $title,
                'type' => $this->type
            ]);
        } else {
            return view('admin.kpi.detail_report_synthetic_kpi_manager', [
                'title' => $title,
                'type' => $this->type
            ]);
        }
    }

    public function load_add_report_synthetic_kpi_user()
    {
        $checkPermission = true;
        if (!has_permission('report_synthetic_kpi_user', 'view') && has_permission('report_synthetic_kpi_user',
                'viewown')) {
            $user_ids = getUserIdByRole([],0,false);
            $checkPermission = false;
        }

        $month_search = ($this->request->input('month_search')) ?? date('m');
        $year_search = ($this->request->input('year_search')) ?? date('Y');
        $year_month = $year_search . '-' . $month_search;

        $tHead = '';
        $html = '';
        $tHead .= '<th class="text-center" style="min-width: 80px;">' . lang('STT') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Mã NV') . '</th>
            <th class="text-center" style="min-width: 150px;">' . lang('Tên NV') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Doanh số kinh doanh') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Chỉ số hợp đồng mới') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Duy trì khách hàng') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Vi phạm') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Điểm chuẩn hóa doanh số') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Điểm chuẩn hóa hợp đồng mới') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Điểm chuẩn hóa duy trì khách hàng') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Điểm chuẩn hóa vi phạm') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Tổng điểm KPI') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Xếp hạng KPI') . '</th>
        </tr>';


        $responseReport = $this->fnbReport->getSyntheticKpiUser($this->request);
        $dataReport = $responseReport->getData(true);
        $dataReport = ($dataReport['data'] ?? []);
        if(empty($dataReport)){
            $dataReport[] = [
                'staff_id' => 0,
                'payment' => 0,
                'total_service' => 0,
                'total_member' => 0
            ];
        }

        $reportQuery = DB::table(DB::raw('(' .
            collect($dataReport)->map(function ($value) {
                return "(SELECT '{$value['staff_id']}' as staff_id, '{$value['payment']}' as payment, '{$value['total_service']}' as total_service, '{$value['total_member']}' as total_member)";
            })->implode(' UNION ALL ') . ') AS tb_report'));


        $tb_violation = DB::table('tbl_violation_ticket')
            ->select('staff_id', DB::raw('COUNT(id) as total_violate'))
            ->whereRaw("DATE_FORMAT(tbl_violation_ticket.date, '%Y-%m') = ?", [$year_month])
            ->groupBy('staff_id');

        $query = User::select('tbl_users.*', 'tb_report.payment', 'tb_report.total_service',
            'tb_report.total_member', 'tb_violation.total_violate')
            ->with('role')
            ->with('department')
            ->with('user_ares');
        $query->leftJoinSub($reportQuery, 'tb_report', function ($join) {
            $join->on('tbl_users.id', '=', 'tb_report.staff_id');
        });
        $query->leftJoinSub($tb_violation, 'tb_violation', function ($join) {
            $join->on('tbl_users.id', '=', 'tb_violation.staff_id');
        });
        if ($this->request->input('ares_search')) {
            $ares_search = $this->request->input('ares_search');
            $query->WhereHas('user_ares', function ($q) use ($ares_search) {
                $q->where('id_ares', '=', "$ares_search");
            });
        }
        if (empty($checkPermission)) {
            $query->whereIn('id', ($user_ids ?? [0]));
        }
        $query->where('check_nvkd', '=', 1);
        $query->whereNotExists(function ($subquery) use ($month_search, $year_search) {
            $subquery->select(DB::raw(1))
                ->from('tbl_calculate_kpi_detail')
                ->join('tbl_calculate_kpi', 'tbl_calculate_kpi.id', '=', 'tbl_calculate_kpi_detail.calculate_kpi_id')
                ->where('tbl_calculate_kpi.month', '=', $month_search)
                ->where('tbl_calculate_kpi.year', '=', $year_search)
                ->whereRaw('tbl_calculate_kpi_detail.staff_id = tbl_users.id');
        });
        $user = $query->get();

        $dt_ratio_percent_kpi = DB::table('tbl_ratio_percent_kpi')->get();
        $dt_target_contract_kpi = DB::table('tbl_target_contract_kpi')->get();
        $dt_retention_rate_customer_kpi = DB::table('tbl_retention_rate_customer_kpi')->get();
        $dt_weight_tagert_kpi = DB::table('tbl_weight_tagert_kpi')->get();
        $dt_rating_kpi = DB::table('tbl_rating_kpi')->get();
        $setting_kpi = get_option('setting_kpi') ? json_decode(get_option('setting_kpi'), true) : [];
        $counter = 0;
        if (!empty($user)) {
            foreach ($user as $key => $value) {
                $point_payment = 0;
                $point_service = 0;
                $point_member = 0;
                $point_violation = 100;
                $percent_payment = 0;
                $percent_member = 0;
                $date_start = strtotime($value->created_at);
                $date_end = strtotime(date('Y-m-d H:i:s'));
                $diffInSeconds = abs($date_end - $date_start);
                $time_user = floor($diffInSeconds / 86400);
                $target_member_month = $setting_kpi['target_member_month'] ?? 0;
                if($time_user > 365) {
                    $target_month = $setting_kpi['target_month_two'] ?? 0;
                } else {
                    $target_month = $setting_kpi['target_month'] ?? 0;
                }
                $percent_payment = ($value->payment > 0 && $target_month > 0) ? ($value->payment / $target_month) * 100 : 0;
                if (!empty($dt_ratio_percent_kpi)){
                    foreach ($dt_ratio_percent_kpi as $k => $v){
                        if ($percent_payment >= $v->percent_start && $percent_payment < $v->percent_end){
                            $point_payment = $v->point;
                            break;
                        }
                    }
                }

                if (empty($percent_payment)){
                    $point_payment = 0;
                }

                if (!empty($dt_target_contract_kpi)){
                    foreach ($dt_target_contract_kpi as $k => $v){
                        if ($value->total_service >= $v->contract_number_start && $value->total_service < $v->contract_number_end){
                            $point_service += $v->point;
                            break;
                        }
                    }
                }

                if (empty($value->total_service)){
                    $point_service = 0;
                }

                $percent_member = ($value->total_member > 0 && $target_member_month > 0) ? ($value->total_member / $target_member_month) * 100 : 0;
                if (!empty($dt_retention_rate_customer_kpi)){
                    foreach ($dt_retention_rate_customer_kpi as $k => $v){
                        if ($v->id == 4) {
                            if ($percent_member >= $v->point_start && $percent_member <= $v->point_end) {
                                $point_member = $v->point;
                                break;
                            }
                        } else {
                            if ($percent_member >= $v->point_start && $percent_member < $v->point_end) {
                                $point_member = $v->point;
                                break;
                            }
                        }
                    }
                }
                //k có thành viên thì không có điểm
                if (empty($percent_member)){
                    $point_member = 0;
                }

                if ($value->total_violate > 0){
                    $point_violation = 0;
                }

                $point_kpi = 0;
                if (!empty($dt_weight_tagert_kpi)){
                    foreach ($dt_weight_tagert_kpi as $k => $v){
                        if ($v->type == 'payment'){
                            $point_kpi += ($point_payment * $v->weight) / 100;
                        }
                        if ($v->type == 'service'){
                            $point_kpi += ($point_service * $v->weight) / 100;
                        }
                        if ($v->type == 'member'){
                            $point_kpi += ($point_member * $v->weight) / 100;
                        }
                        if ($v->type == 'violate'){
                            $point_kpi += ($point_violation * $v->weight) / 100;
                        }
                    }
                }

                $name_kpi = '';
                if (!empty($dt_rating_kpi)){
                    foreach ($dt_rating_kpi as $k => $v){
                        if ($point_kpi >= $v->point_start_kpi && $point_kpi < $v->point_end_kpi){
                            $name_kpi = $v->name;
                            break;
                        }
                    }
                }

                $html .= '<tr class="tr_total">';
                $html .= '<td class="text-center" style="width: 50px;">' . ($key + 1) . '</td>';
                $html .= '<td class="text-center">' . (!empty($value->code) ? $value->code : '') . '</td>';
                $html .= '<td class="text-center">' . (!empty($value->name) ? $value->name : '') . '</td>';
                $html .= '<td class="text-center">' . (!empty($value->payment) ? formatMoney($value->payment) : '-') . '</td>';
                $html .= '<td class="text-center">' . (!empty($value->total_service) ? $value->total_service : '-') . '</td>';
                $html .= '<td class="text-center">' . (!empty($value->total_member) ? $value->total_member : '-') . '</td>';
                $html .= '<td class="text-center">' . (!empty($value->total_violate) ? $value->total_violate : '-') . '</td>';
                $html .= '<td style="min-width: 50px;text-align:center">
                    <input type="text" class="form-control point_payment" onchange="formatNumBerKeyChange(this)" name="point_payment[]" style="width: 100px" value="'.$point_payment.'"></td>';
                $html .= '<td style="min-width: 50px;text-align:center">
                    <input type="text" class="form-control number-format point_service" onchange="formatNumBerKeyChange(this)" name="point_service[]" style="width: 100px" value="'.$point_service.'"></td>';
                $html .= '<td style="min-width: 50px;text-align:center">
                    <input type="text" class="form-control number-format point_member" onchange="formatNumBerKeyChange(this)" name="point_member[]" style="width: 100px" value="'.$point_member.'"></td>';
                $html .= '<td style="min-width: 50px;text-align:center">
                    <input type="text" class="form-control number-format point_violation" onchange="formatNumBerKeyChange(this)" name="point_violation[]" style="width: 100px" value="'.$point_violation.'"></td>';
                $html .= '<td style="min-width: 50px;text-align:center">
                    <input type="text" class="form-control number-format point_kpi" onchange="formatNumBerKeyChange(this)" readonly name="point_kpi[]" style="width: 100px" value="'.$point_kpi.'"></td>';
                $html .= '<td style="min-width: 50px;text-align:center"><div class="result_name_kpi">'.$name_kpi.'</div>
                    <input type="hidden" name="counter[]" value="'.$counter.'">
                    <input type="hidden" name="staff_id[]" value="'.$value->id.'">
                    <input type="hidden" name="total_payment[]" value="'.$value->payment.'">
                    <input type="hidden" name="total_service[]" value="'.$value->total_service.'">
                    <input type="hidden" name="total_member[]" value="'.$value->total_member.'">
                    <input type="hidden" name="total_violate[]" value="'.$value->total_violate.'">
                    <input type="hidden" name="target_month[]" value="'.$target_month.'">
                    <input type="hidden" name="target_member_month[]" value="'.$target_member_month.'">
                    <input type="hidden" name="dt_ratio_percent_kpi[]" value="'.htmlspecialchars(json_encode($dt_ratio_percent_kpi)).'">
                    <input type="hidden" name="dt_target_contract_kpi[]" value="'.htmlspecialchars(json_encode($dt_target_contract_kpi)).'">
                    <input type="hidden" name="dt_retention_rate_customer_kpi[]" value="'.htmlspecialchars(json_encode($dt_retention_rate_customer_kpi)).'">
                    <input type="hidden" name="dt_weight_tagert_kpi[]" value="'.htmlspecialchars(json_encode($dt_weight_tagert_kpi)).'">
                    <input type="hidden" name="dt_rating_kpi[]" value="'.htmlspecialchars(json_encode($dt_rating_kpi)).'">
                </td>';
                $html .= '</tr>';
                $counter ++;
            }
        }
        return view('admin.kpi.load_add_report_synthetic_kpi_user', [
            'tHead' => $tHead,
            'html' => $html,
            'dt_weight_tagert_kpi' => $dt_weight_tagert_kpi,
            'dt_rating_kpi' => $dt_rating_kpi,
        ]);
    }

    public function load_add_report_synthetic_kpi_manager()
    {
        $checkPermission = true;
        if (!has_permission('report_synthetic_kpi_manager', 'view') && has_permission('report_synthetic_kpi_manager',
                'viewown')) {
            $user_ids = getUserIdByRole([],0,false);
            $checkPermission = false;
        }

        $month_search = ($this->request->input('month_search')) ?? date('m');
        $year_search = ($this->request->input('year_search')) ?? date('Y');
        $year_month = $year_search . '-' . $month_search;

        $tHead = '';
        $html = '';
        $tHead .= '<th class="text-center" style="min-width: 80px;">' . lang('STT') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Mã NV') . '</th>
            <th class="text-center" style="min-width: 150px;">' . lang('Tên NV') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Doanh số kinh doanh') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Chỉ số hợp đồng mới') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Duy trì khách hàng') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Vi phạm') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Điểm chuẩn hóa doanh số') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Điểm chuẩn hóa hợp đồng mới') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Điểm chuẩn hóa duy trì khách hàng') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Điểm chuẩn hóa vi phạm') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Tổng điểm KPI') . '</th>
            <th class="text-center" style="min-width: 80px;">' . lang('Xếp hạng KPI') . '</th>
        </tr>';


        $responseReport = $this->fnbReport->getSyntheticKpiUser($this->request);
        $dataReport = $responseReport->getData(true);
        $dataReport = ($dataReport['data'] ?? []);
        if(empty($dataReport)){
            $dataReport[] = [
                'staff_id' => 0,
                'payment' => 0,
                'total_service' => 0,
                'total_member' => 0
            ];
        }

        $reportQuery = DB::table(DB::raw('(' .
            collect($dataReport)->map(function ($value) {
                return "(SELECT '{$value['staff_id']}' as staff_id, '{$value['payment']}' as payment, '{$value['total_service']}' as total_service, '{$value['total_member']}' as total_member)";
            })->implode(' UNION ALL ') . ') AS tb_report'));

        $tb_violation = DB::table('tbl_violation_ticket')
            ->select('staff_id', DB::raw('COUNT(id) as total_violate'))
            ->whereRaw("DATE_FORMAT(tbl_violation_ticket.date, '%Y-%m') = ?", [$year_month])
            ->groupBy('staff_id');

        $query = User::select('tbl_users.*', 'tb_violation.total_violate')
            ->with('role')
            ->with('department')
            ->with('user_ares');
        $query->leftJoinSub($tb_violation, 'tb_violation', function ($join) {
            $join->on('tbl_users.id', '=', 'tb_violation.staff_id');
        });
        if ($this->request->input('ares_search')) {
            $ares_search = $this->request->input('ares_search');
            $query->WhereHas('user_ares', function ($q) use ($ares_search) {
                $q->where('id_ares', '=', "$ares_search");
            });
        }
        if (empty($checkPermission)) {
            $query->whereIn('id', ($user_ids ?? [0]));
        }
        $query->where('check_manager', '=', 1);
        $query->whereNotExists(function ($subquery) use ($month_search, $year_search) {
            $subquery->select(DB::raw(1))
                ->from('tbl_calculate_kpi_detail')
                ->join('tbl_calculate_kpi', 'tbl_calculate_kpi.id', '=', 'tbl_calculate_kpi_detail.calculate_kpi_id')
                ->where('tbl_calculate_kpi.month', '=', $month_search)
                ->where('tbl_calculate_kpi.year', '=', $year_search)
                ->whereRaw('tbl_calculate_kpi_detail.staff_id = tbl_users.id');
        });
        $user = $query->get();

        $user_id = $user->pluck('id')->toArray() ?? [0];

        $dtUserWard = DB::table('tbl_user_ares_ward')->whereIn('id_user', $user_id)->get();

        $dtUserWard = $dtUserWard->groupBy('id_user')->map(function ($group) {
            return $group->pluck('id_ward');
        })->toArray();

        $dt_ratio_percent_kpi = DB::table('tbl_ratio_percent_kpi_manager')->get();
        $dt_target_contract_kpi = DB::table('tbl_target_contract_kpi_manager')->get();
        $dt_retention_rate_customer_kpi = DB::table('tbl_retention_rate_customer_kpi_manager')->get();
        $dt_weight_tagert_kpi = DB::table('tbl_weight_tagert_kpi_manager')->get();
        $dt_rating_kpi = DB::table('tbl_rating_kpi_manager')->get();
        $setting_kpi = get_option('setting_kpi_manager') ? json_decode(get_option('setting_kpi_manager'), true) : [];
        $counter = 0;
        if (!empty($user)) {
            foreach ($user as $key => $value) {
                $arrWard = $dtUserWard[$value->id] ?? [];
                $totalWard = count($arrWard);
                $arrID = getUserIdByRole([],$value->id,false);
                $tb_services = DB::query()
                    ->fromSub($reportQuery, 'tb_services')
                    ->select(
                        DB::raw('SUM(tb_services.payment) as payment'),
                        DB::raw('SUM(tb_services.total_service) as total_service'),
                        DB::raw('SUM(tb_services.total_member) as total_member'),
                    )
                    ->whereIn('tb_services.staff_id', $arrID)
                    ->first();

                $total_payment = $tb_services->payment ?? 0;
                $total_service = $tb_services->total_service ?? 0;
                $total_member = $tb_services->total_member ?? 0;

                $point_payment = 0;
                $point_service = 0;
                $point_member = 0;
                $point_violation = 100;
                $percent_payment = 0;
                $percent_member = 0;
                $date_start = strtotime($value->created_at);
                $date_end = strtotime(date('Y-m-d H:i:s'));
                $diffInSeconds = abs($date_end - $date_start);
                $time_user = floor($diffInSeconds / 86400);
                $target_member_month = $setting_kpi['target_member_month'] ?? 0;
                if($time_user > 365) {
                    $target_month = $setting_kpi['target_month_two'] ?? 0;
                } else {
                    $target_month = $setting_kpi['target_month'] ?? 0;
                }

                $target_month = $target_month * $totalWard;

                $percent_payment = ($total_payment > 0 && $target_month > 0) ? ($total_payment / $target_month) * 100 : 0;
                if (!empty($dt_ratio_percent_kpi)){
                    foreach ($dt_ratio_percent_kpi as $k => $v){
                        if ($percent_payment >= $v->percent_start && $percent_payment < $v->percent_end){
                            $point_payment = $v->point;
                            break;
                        }
                    }
                }

                if (empty($percent_payment)){
                    $point_payment = 0;
                }

                if (!empty($dt_target_contract_kpi)){
                    foreach ($dt_target_contract_kpi as $k => $v){
                        if ($total_service >= $v->contract_number_start && $total_service < $v->contract_number_end){
                            $point_service += $v->point;
                            break;
                        }
                    }
                }

                if (empty($total_service)){
                    $point_service = 0;
                }

                $percent_member = ($total_member > 0 && $target_member_month > 0) ? ($total_member / $target_member_month) * 100 : 0;

                if (!empty($dt_retention_rate_customer_kpi)){
                    foreach ($dt_retention_rate_customer_kpi as $k => $v){
                        if ($v->id == 4) {
                            if ($percent_member >= $v->point_start && $percent_member <= $v->point_end) {
                                $point_member = $v->point;
                                break;
                            }
                        } else {
                            if ($percent_member >= $v->point_start && $percent_member < $v->point_end) {
                                $point_member = $v->point;
                                break;
                            }
                        }
                    }
                }

                if (empty($percent_member)){
                    $point_member = 0;
                }

                if ($value->total_violate > 0){
                    $point_violation = 0;
                }

                $point_kpi = 0;
                if (!empty($dt_weight_tagert_kpi)){
                    foreach ($dt_weight_tagert_kpi as $k => $v){
                        if ($v->type == 'payment'){
                            $point_kpi += ($point_payment * $v->weight) / 100;
                        }
                        if ($v->type == 'service'){
                            $point_kpi += ($point_service * $v->weight) / 100;
                        }
                        if ($v->type == 'member'){
                            $point_kpi += ($point_member * $v->weight) / 100;
                        }
                        if ($v->type == 'violate'){
                            $point_kpi += ($point_violation * $v->weight) / 100;
                        }
                    }
                }

                $name_kpi = '';
                if (!empty($dt_rating_kpi)){
                    foreach ($dt_rating_kpi as $k => $v){
                        if ($point_kpi >= $v->point_start_kpi && $point_kpi < $v->point_end_kpi){
                            $name_kpi = $v->name;
                            break;
                        }
                    }
                }

                $html .= '<tr class="tr_total">';
                $html .= '<td class="text-center" style="width: 50px;">' . ($key + 1) . '</td>';
                $html .= '<td class="text-center">' . (!empty($value->code) ? $value->code : '') . '</td>';
                $html .= '<td class="text-center">' . (!empty($value->name) ? $value->name : '') . '</td>';
                $html .= '<td class="text-center">' . (!empty($total_payment) ? formatMoney($total_payment) : '-') . '</td>';
                $html .= '<td class="text-center">' . (!empty($total_service) ? $total_service : '-') . '</td>';
                $html .= '<td class="text-center">' . (!empty($total_member) ? $total_member : '-') . '</td>';
                $html .= '<td class="text-center">' . (!empty($value->total_violate) ? $value->total_violate : '-') . '</td>';
                $html .= '<td style="min-width: 50px;text-align:center">
                    <input type="text" class="form-control point_payment" onchange="formatNumBerKeyChange(this)" name="point_payment[]" style="width: 100px" value="'.$point_payment.'"></td>';
                $html .= '<td style="min-width: 50px;text-align:center">
                    <input type="text" class="form-control number-format point_service" onchange="formatNumBerKeyChange(this)" name="point_service[]" style="width: 100px" value="'.$point_service.'"></td>';
                $html .= '<td style="min-width: 50px;text-align:center">
                    <input type="text" class="form-control number-format point_member" onchange="formatNumBerKeyChange(this)" name="point_member[]" style="width: 100px" value="'.$point_member.'"></td>';
                $html .= '<td style="min-width: 50px;text-align:center">
                    <input type="text" class="form-control number-format point_violation" onchange="formatNumBerKeyChange(this)" name="point_violation[]" style="width: 100px" value="'.$point_violation.'"></td>';
                $html .= '<td style="min-width: 50px;text-align:center">
                    <input type="text" class="form-control number-format point_kpi" onchange="formatNumBerKeyChange(this)" readonly name="point_kpi[]" style="width: 100px" value="'.$point_kpi.'"></td>';
                $html .= '<td style="min-width: 50px;text-align:center"><div class="result_name_kpi">'.$name_kpi.'</div>
                    <input type="hidden" name="counter[]" value="'.$counter.'">
                    <input type="hidden" name="staff_id[]" value="'.$value->id.'">
                    <input type="hidden" name="total_payment[]" value="'.$total_payment.'">
                    <input type="hidden" name="total_service[]" value="'.$total_service.'">
                    <input type="hidden" name="total_member[]" value="'.$total_member.'">
                    <input type="hidden" name="total_violate[]" value="'.$value->total_violate.'">
                    <input type="hidden" name="target_month[]" value="'.$target_month.'">
                    <input type="hidden" name="total_ward[]" value="'.$totalWard.'">
                    <input type="hidden" name="target_member_month[]" value="'.$target_member_month.'">
                    <input type="hidden" name="dt_ratio_percent_kpi[]" value="'.htmlspecialchars(json_encode($dt_ratio_percent_kpi)).'">
                    <input type="hidden" name="dt_target_contract_kpi[]" value="'.htmlspecialchars(json_encode($dt_target_contract_kpi)).'">
                    <input type="hidden" name="dt_retention_rate_customer_kpi[]" value="'.htmlspecialchars(json_encode($dt_retention_rate_customer_kpi)).'">
                    <input type="hidden" name="dt_weight_tagert_kpi[]" value="'.htmlspecialchars(json_encode($dt_weight_tagert_kpi)).'">
                    <input type="hidden" name="dt_rating_kpi[]" value="'.htmlspecialchars(json_encode($dt_rating_kpi)).'">
                    <input type="hidden" name="check_manager[]" value="1">
                </td>';
                $html .= '</tr>';
                $counter ++;
            }
        }
        return view('admin.kpi.load_add_report_synthetic_kpi_user', [
            'tHead' => $tHead,
            'html' => $html,
            'dt_weight_tagert_kpi' => $dt_weight_tagert_kpi,
            'dt_rating_kpi' => $dt_rating_kpi,
        ]);
    }

    public function deleteKPI(){
        if (!has_permission('report_synthetic_kpi_user','delete')){
            $data['result'] = false;
            $data['message'] = lang('Bạn không có quyền xóa!');
            return response()->json($data);
        }
        $data = [];

        $ids = trim($this->request->input('ids'), ',');
        if (!$ids) {
            $data['result'] = false;
            $data['message'] = lang('Không có dữ liệu để xóa!');
            return response()->json($data);
        }
        $count = 0;
        $ids = explode(',', $ids);
        $ids = array_unique($ids);
        if (!empty($ids)) {
            DB::beginTransaction();
            foreach ($ids as $key => $id) {
                $item = DB::table('tbl_calculate_kpi_detail')->where('id','=', $id)->first();
                $success = DB::table('tbl_calculate_kpi_detail')->where('id','=', $id)->delete();
                if ($success){
                    $count++;

                    $checkItem = DB::table('tbl_calculate_kpi_detail')->where('calculate_kpi_id','=',$item->calculate_kpi_id)->first();
                    if (empty($checkItem)){
                        DB::table('tbl_calculate_kpi')->where('id','=',$item->calculate_kpi_id)->delete();
                    }
                }
            }
            DB::commit();
        }
        if ($count) {
            $data['result'] = true;
            $data['message'] = lang('Xóa thành công');
        } else {
            $data['result'] = false;
            $data['message'] = lang('Xóa thất bại');
        }
        return response()->json($data);
    }
}
