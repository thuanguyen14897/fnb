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
use Illuminate\Support\Facades\Validator;

class MemberShipLevelController extends Controller
{
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
                $memberShipLevel->point_end = number_unformat($value['point_end']);
                $memberShipLevel->invoice_limit = number_unformat($value['invoice_limit']);
                $memberShipLevel->save();
            }
            foreach ($membership_expense as $id => $value) {
                $memberShipExpense = MemberShipExpense::find($id);
                $memberShipExpense->money_start = number_unformat($value['money_start']);
                $memberShipExpense->money_end = number_unformat($value['money_end']);
                $memberShipExpense->point = number_unformat($value['point']);
                $memberShipExpense->save();
            }
            foreach ($membership_long_term as $id => $value) {
                $memberShipLongTerm = MemberShipLongTerm::find($id);
                $memberShipLongTerm->month_start = number_unformat($value['month_start']);
                $memberShipLongTerm->month_end = number_unformat($value['month_end']);
                $memberShipLongTerm->point = number_unformat($value['point']);
                $memberShipLongTerm->save();
            }
            foreach ($membership_purchases as $id => $value) {
                $memberShipPurchases = MemberShipPurchases::find($id);
                $memberShipPurchases->number_purchases_start = number_unformat($value['number_purchases_start']);
                $memberShipPurchases->number_purchases_end = number_unformat($value['number_purchases_end']);
                $memberShipPurchases->point = number_unformat($value['point']);
                $memberShipPurchases->save();
            }

            DB::commit();
            $data['result'] = true;
            $data['message'] = 'Cập nhập thành công';
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
