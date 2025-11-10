<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ReportResource;
use App\Models\Clients;
use App\Models\Payment;
use App\Models\ReferralLevel;
use App\Models\TransactionBill;
use App\Services\AresService;
use App\Services\ReportService;
use App\Traits\UploadFile;
use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\ServiceService;
use App\Services\AdminService;
use Illuminate\Support\Facades\Validator;
use function Aws\map;

class ReportController extends AuthController
{
    protected $fnbService;
    protected $fnbAdmin;
    protected $fnbReportService;
    protected $fnbAres;
    use UploadFile;

    public function __construct(Request $request, ServiceService $ServiceService, AdminService $AdminService,ReportService $ReportService,AresService $AresService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbService = $ServiceService;
        $this->fnbAdmin = $AdminService;
        $this->fnbReportService = $ReportService;
        $this->fnbAres = $AresService;
    }

    public function getListReportRevenuePartner()
    {
//        $dtData = DB::table('tbl_referral_level')
//            ->orderBy('parent_id','asc')
//            ->orderBy('created_at','asc')
//            ->get();
//        $parent_id= 0;
//        $stt = 1;
//        $arrUpdate = [];
//        if (!empty($dtData)){
//            foreach ($dtData as $key => $value){
//                if ($parent_id != $value->parent_id){
//                    $stt = 1;
//                }
//                $parent_id= $value->parent_id;
//                $arrUpdate[] = [
//                    'id' => $value->id,
//                    'stt' => $stt
//                ];
//                $stt ++;
//            }
//        }
//        foreach ($arrUpdate as $item){
//            DB::table('tbl_referral_level')->where('id',$item['id'])->
//            update([
//                'stt' => $item['stt']
//            ]);
//        }

        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $partner_search = $this->request->input('partner_search') ?? null;
        $representative_search = $this->request->input('representative_search') ?? null;
        $ares_search = $this->request->input('ares_search') ?? 0;

        $month_search = $this->request->input('month_search') ?? date('m');
        $year_search = $this->request->input('year_search') ?? date('Y');
        $month_year = $year_search. '-' . $month_search;


        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }


        $f1Revenue = DB::table('tbl_referral_level as f1')
            ->leftJoin('tbl_payment as p', 'p.customer_id', '=', 'f1.customer_id')
            ->select(
                'f1.parent_id as partner_id',
                'f1.customer_id as f1_id',
                DB::raw("DATE_FORMAT(p.date, '%Y-%m') as month"),
                DB::raw('COALESCE(SUM(p.payment),0) as revenue'),
                DB::raw('COALESCE(SUM(p.revenue_partner),0) as revenue_partner_f1'),
                DB::raw('COALESCE(SUM(p.revenue_f1),0) as revenue_revenue_f1'),
            )
            ->whereRaw("DATE_FORMAT(p.date, '%Y-%m') = ?", [$month_year])
            ->where('f1.level', 0)
            ->where('p.status', '=',2)
            ->groupBy('f1.parent_id', 'f1.customer_id', 'month');


        $f2Revenue = DB::table('tbl_referral_level as f1')
            ->where('f1.level', 0)
            ->leftJoin('tbl_referral_level as f2', function($join) {
                $join->on('f2.parent_id', '=', 'f1.customer_id')
                    ->where('f2.level', '=', 1);
            })
            ->leftJoin('tbl_payment as p', 'p.customer_id', '=', 'f2.customer_id')
            ->select(
                'f1.parent_id as partner_id',
                'f1.customer_id as f1_id',
                DB::raw("DATE_FORMAT(p.date, '%Y-%m') as month"),
                DB::raw('COALESCE(SUM(p.payment),0) as revenue'),
                DB::raw('COALESCE(SUM(p.revenue_partner),0) as revenue_partner_f1'),
                DB::raw('COALESCE(SUM(p.revenue_f1),0) as revenue_revenue_f1'),
            )
            ->whereRaw("DATE_FORMAT(p.date, '%Y-%m') = ?", [$month_year])
            ->where('p.status', '=',2)
            ->groupBy('f1.parent_id', 'f1.customer_id', 'month');

        $tb_payment = $f1Revenue->unionAll($f2Revenue);
        $tb_payment = DB::query()
            ->fromSub($tb_payment, 'u')
            ->select(
                'u.partner_id',
                'u.f1_id',
                'u.month',
                DB::raw('SUM(u.revenue) as f1_revenue'),
                DB::raw('SUM(u.revenue_partner_f1) as revenue_partner'),
                DB::raw('SUM(u.revenue_revenue_f1) as revenue_f1')
            )
            ->groupBy('u.partner_id', 'u.f1_id', 'u.month');

        $tb_f1_count_f2 = DB::table('tbl_clients')
            ->select(
                'tbl_referral_level.parent_id as f1_id',
                DB::raw('COUNT(tbl_clients.id) as total_f2'),
            )
            ->join('tbl_referral_level','tbl_referral_level.customer_id','=','tbl_clients.id')
            ->where('tbl_referral_level.level', '=',1)
            ->groupBy('tbl_referral_level.parent_id');


        $tb_f1 = DB::table('tbl_clients')
            ->join('tbl_referral_level','tbl_referral_level.customer_id','=','tbl_clients.id')
            ->leftJoinSub($tb_payment,'tb_payment',function ($join){
                $join->on('tb_payment.f1_id','=','tbl_clients.id');
            })
            ->leftJoinSub($tb_f1_count_f2,'tb_f1_count_f2',function ($join){
                $join->on('tb_f1_count_f2.f1_id','=','tbl_clients.id');
            })
            ->select(
                'tbl_referral_level.parent_id as partner_id',
                'tbl_clients.id as f1_id',
                'tbl_clients.fullname as f1_fullname',
                'tbl_clients.avatar as f1_avatar',
                'tbl_clients.phone as f1_phone',
                'tb_payment.month as month',
                DB::raw('COALESCE(tb_f1_count_f2.total_f2,0) as total_f2'),
                DB::raw('COALESCE(tb_payment.f1_revenue,0) as payment'),
                DB::raw("CASE WHEN COALESCE(tb_f1_count_f2.total_f2,0) <= 50 THEN 5 ELSE 2 END as partner_percent"),
                DB::raw("COALESCE(tb_payment.revenue_partner,0) as partner_commission"),
                DB::raw("CASE WHEN COALESCE(tb_f1_count_f2.total_f2,0) > 50 THEN 3 ELSE 0 END as f1_percent"),
                DB::raw("COALESCE(tb_payment.revenue_f1,0) as f1_commission"),
//                DB::raw("CASE WHEN COALESCE(tb_f1_count_f2.total_f2,0) <= 50 THEN 5 ELSE 2 END as partner_percent"),
//                DB::raw("CASE WHEN COALESCE(tb_f1_count_f2.total_f2,0) <= 50
//                      THEN COALESCE(tb_payment.f1_revenue,0) * 0.05
//                      ELSE COALESCE(tb_payment.f1_revenue,0) * 0.02 END as partner_commission"),
//                DB::raw("CASE WHEN COALESCE(tb_f1_count_f2.total_f2,0) > 50 THEN 3 ELSE 0 END as f1_percent"),
//                DB::raw("CASE WHEN COALESCE(tb_f1_count_f2.total_f2,0) > 50
//                      THEN COALESCE(tb_payment.f1_revenue,0) * 0.03 ELSE 0 END as f1_commission")
            )
            ->where('tbl_referral_level.level', 0)
            ->orderBy('total_f2', 'desc');

        $storageUrl = config('app.storage_url');
        $query = Clients::where('tbl_clients.type_client', '=',2);
        $query->leftJoinSub($tb_f1,'tb_f1',function ($join){
            $join->on('tb_f1.partner_id','=','tbl_clients.id');
        });
        $query->select(
            'tbl_clients.id as partner_id',
            'tbl_clients.fullname as partner_name',
            'tbl_clients.phone as partner_phone',
            'tbl_clients.province_id as province_id',
            'tbl_clients.wards_id as wards_id',
             DB::raw("IF(tbl_clients.avatar IS NOT NULL, CONCAT('$storageUrl/', tbl_clients.avatar), NULL) as partner_avatar"),
            'tbl_partner_representative_info.name as name_representative',
            'tbl_partner_representative_info.phone as phone_representative',
             DB::raw("IF(tbl_partner_representative_info.image IS NOT NULL, CONCAT('$storageUrl/', tbl_partner_representative_info.image), NULL) as avatar_representative"),
            'tb_f1.f1_id as f1_id',
            'tb_f1.f1_fullname as f1_fullname',
            'tb_f1.f1_phone as f1_phone',
            DB::raw("IF(tb_f1.f1_avatar IS NOT NULL, CONCAT('$storageUrl/', tb_f1.f1_avatar), NULL) as f1_avatar"),
            'tb_f1.total_f2 as total_f2',
            DB::raw("'$month_year' as month_year"),
            'tb_f1.payment as payment',
            'tb_f1.partner_percent as partner_percent',
            'tb_f1.partner_commission as partner_commission',
            'tb_f1.f1_percent as f1_percent',
            'tb_f1.f1_commission as f1_commission',
        );
        $query->leftJoin('tbl_partner_representative_info','tbl_partner_representative_info.customer_id','=','tbl_clients.id');
        $query->where('tb_f1.payment','>',0);
        $query->whereRaw("EXISTS (
            SELECT 1
            FROM tbl_referral_level
            WHERE tbl_referral_level.parent_id = tbl_clients.id
            AND tbl_referral_level.level = 0
        )");
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $ListWard['data']);
                    } else {
                        $query->where('tbl_clients.id', 0);
                    }
                } else {
                    $query->where('tbl_clients.id', 0);
                }
            }
        }
        if (!empty($ares_search)){
            $dataAres = $this->fnbService->getWardsWhereAres($this->request, $ares_search);
            if (!empty($dataAres)) {
                $WardSearch = $dataAres->getData(true);
                if (!empty($WardSearch['result'])) {
                    if (!empty($WardSearch['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $WardSearch['data']);
                    }
                }
            }
        }
        if(!empty($partner_search)){
            $query->where('tbl_clients.id','=',$partner_search);
        }
        if(!empty($representative_search)){
            $query->where('tbl_partner_representative_info.id','=',$representative_search);
        }
        $filtered = $query->count();
        $data = $query->skip($start)->take($length)->get();

        $grouped = $data->groupBy('partner_id')->map(function ($items) {
            $partner = $items->first();
            return [
                'partner_id' => $partner->partner_id,
                'partner_name' => $partner->partner_name,
                'month' => $partner->month,
                'total_partner_commission' => $items->sum('partner_commission'),
                'f1_list' => $items->map(function ($f1) {
                    return [
                        'f1_id' => $f1->f1_id,
                        'f1_name' => $f1->f1_fullname,
                        'f1_members' => $f1->total_f2,
                        'f1_revenue' => $f1->payment,
                        'partner_percent' => $f1->partner_percent,
                        'partner_commission' => $f1->partner_commission,
                        'f1_percent' => $f1->f1_percent,
                        'f1_commission' => $f1->f1_commission,
                    ];
                })->values()
            ];
        })->values();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
            }
        }
        $f1Revenue = DB::table('tbl_referral_level as f1')
            ->leftJoin('tbl_payment as p', 'p.customer_id', '=', 'f1.customer_id')
            ->select(
                'f1.parent_id as partner_id',
                'f1.customer_id as f1_id',
                DB::raw("DATE_FORMAT(p.date, '%Y-%m') as month"),
                DB::raw('IFNULL(SUM(p.payment),0) as revenue_f1')
            )
            ->where(DB::raw("DATE_FORMAT(p.date, '%Y-%m')"), '=', $month_year)

            ->groupBy('f1.parent_id', 'f1.customer_id', 'month');


        $f2Revenue = DB::table('tbl_referral_level as f1')
            ->leftJoin('tbl_referral_level as f2', 'f2.parent_id', '=', 'f1.customer_id')
            ->leftJoin('tbl_payment as p', 'p.customer_id', '=', 'f2.customer_id')
            ->select(
                'f1.parent_id as partner_id',
                'f1.customer_id as f1_id',
                DB::raw("DATE_FORMAT(p.date, '%Y-%m') as month"),
                DB::raw('IFNULL(SUM(p.payment),0) as revenue_f2')
            )
            ->whereRaw("DATE_FORMAT(p.date, '%Y-%m') = ?", [$month_year])
            ->groupBy('f1.parent_id', 'f1.customer_id', 'month');

        $tb_payment = DB::query()
            ->fromSub($f1Revenue, 'r1')
            ->leftJoinSub($f2Revenue, 'r2', function($join) {
                $join->on('r1.partner_id', '=', 'r2.partner_id')
                    ->on('r1.f1_id', '=', 'r2.f1_id')
                    ->on('r1.month', '=', 'r2.month');
            })
            ->select(
                'r1.partner_id',
                'r1.f1_id',
                'r1.month',
                DB::raw('(COALESCE(r1.revenue_f1,0) + COALESCE(r2.revenue_f2,0)) as f1_revenue')
            );


        $tb_f1_count_f2 = DB::table('tbl_clients')
            ->select(
                'tbl_referral_level.parent_id as f1_id',
                DB::raw('COUNT(tbl_clients.id) as total_f2'),
            )
            ->join('tbl_referral_level','tbl_referral_level.customer_id','=','tbl_clients.id')
            ->where('tbl_referral_level.level', '=',1)
            ->groupBy('tbl_referral_level.parent_id');


        $tb_f1 = DB::table('tbl_clients')
            ->join('tbl_referral_level','tbl_referral_level.customer_id','=','tbl_clients.id')
            ->leftJoinSub($tb_payment,'tb_payment',function ($join){
                $join->on('tb_payment.f1_id','=','tbl_clients.id');
            })
            ->leftJoinSub($tb_f1_count_f2,'tb_f1_count_f2',function ($join){
                $join->on('tb_f1_count_f2.f1_id','=','tbl_clients.id');
            })
            ->select(
                'tbl_referral_level.parent_id as partner_id',
                'tbl_clients.id as f1_id',
                'tbl_clients.fullname as f1_fullname',
                'tb_payment.month as month',
                DB::raw('COALESCE(tb_f1_count_f2.total_f2,0) as total_f2'),
                DB::raw('COALESCE(tb_payment.f1_revenue,0) as payment'),
                DB::raw("CASE WHEN COALESCE(tb_f1_count_f2.total_f2,0) <= 50 THEN 5 ELSE 2 END as partner_percent"),
                DB::raw("CASE WHEN COALESCE(tb_f1_count_f2.total_f2,0) <= 50
                      THEN COALESCE(tb_payment.f1_revenue,0) * 0.05
                      ELSE COALESCE(tb_payment.f1_revenue,0) * 0.02 END as partner_commission"),
                DB::raw("CASE WHEN COALESCE(tb_f1_count_f2.total_f2,0) > 50 THEN 3 ELSE 0 END as f1_percent"),
                DB::raw("CASE WHEN COALESCE(tb_f1_count_f2.total_f2,0) > 50
                      THEN COALESCE(tb_payment.f1_revenue,0) * 0.03 ELSE 0 END as f1_commission")
            )
            ->where('tbl_referral_level.level', 0);


        $query = Clients::where('tbl_clients.type_client', '=',2);
        $query->leftJoinSub($tb_f1,'tb_f1',function ($join){
            $join->on('tb_f1.partner_id','=','tbl_clients.id');
        });

        $query->select(
            'tbl_clients.id as partner_id',
            'tbl_clients.fullname as partner_name',
            'tb_f1.f1_id as f1_id',
            'tb_f1.f1_fullname as f1_fullname',
            'tb_f1.total_f2 as total_f2',
            DB::raw(''.$month_year.' as month'),
            'tb_f1.payment as payment',
            'tb_f1.partner_percent as partner_percent',
            'tb_f1.partner_commission as partner_commission',
            'tb_f1.f1_percent as f1_percent',
            'tb_f1.f1_commission as f1_commission',
        );
        $query->whereRaw("EXISTS (
            SELECT 1
            FROM tbl_referral_level
            WHERE tbl_referral_level.parent_id = tbl_clients.id
            AND tbl_referral_level.level = 0
        )");
        $total = $query->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getRevenuePartner(){
        $customer_id = $this->request->client->id ?? 0;
        $year = $this->request->input('year') ?? date('Y');

        $year_month = date('Y-m');
        $year_month_old = date('Y-m', strtotime($year_month.' -1 month'));

        $query = TransactionBill::where(function ($q) use ($year) {
            $q->whereYear('date', $year);
        });
        $query->where('status', '=', 1);
        $query->where('partner_id', '=', $customer_id);
        $total = $query->sum('grand_total');

        $query_month = TransactionBill::where(function ($q) use ($year_month) {
            $q->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$year_month]);
        });
        $query_month->where('status', '=', 1);
        $query_month->where('partner_id', '=', $customer_id);
        $totalMonth = $query_month->sum('grand_total');

        $arrId = (array_diff(getDataTreeReferralLevel($customer_id), [$customer_id]));
        $count_member = 0;
        if (!empty($arrId)){
            $count_member = ReferralLevel::whereIn('customer_id',$arrId)->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$year_month])->count();
        }

        $query_month_old = TransactionBill::where(function ($q) use ($year_month_old) {
            $q->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$year_month_old]);
        });
        $query_month_old->where('status', '=', 1);
        $query_month_old->where('partner_id', '=', $customer_id);
        $totalMonthOld = $query_month_old->sum('grand_total');

        $arrId = (array_diff(getDataTreeReferralLevel($customer_id), [$customer_id]));
        $count_member_old = 0;
        if (!empty($arrId)){
            $count_member_old = ReferralLevel::whereIn('customer_id',$arrId)->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$year_month_old])->count();
        }

        if ($totalMonth > 0) {
            $perentTotal = (($totalMonth - $totalMonthOld) / $totalMonth) * 100;
        } else {
            $perentTotal = 0;
        }

        if ($count_member > 0) {
            $perentCountMember = (($count_member - $count_member_old) / $count_member) * 100;
        } else {
            $perentCountMember = 0;
        }


        $data['result'] = true;
        $data['total'] = $total;
        $data['data_month'] = [
            'total' => $totalMonth,
            'count_member' => $count_member,
            'perentTotal' => $perentTotal,
            'perentCountMember' => $perentCountMember,
        ];
        $data['message'] = 'Lấy dữ liệu thành công';
        return response()->json($data);
    }

    public function getSyntheticRevenue(){
        $partner_id = $this->request->client->id ?? 0;
        $type = $this->request->input('type') ?? 'month';
        $year_search = $this->request->input('year_search') ?? date('Y');
        if ($type == 'month') {
            $month_search = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
            $query = Payment::select(DB::raw("COALESCE(SUM(payment),0) as total_payment"), DB::raw("DATE_FORMAT(date, '%m') as month"));
            $query->where(function ($q) use ($year_search, $month_search) {
                $q->whereYear('date', $year_search)
                    ->whereIn(DB::raw("DATE_FORMAT(date, '%m')"), $month_search);
            });
            $query->whereHas('transaction_bill', function ($query) use ($partner_id) {
                $query->where('partner_id', '=', $partner_id);
            });
            $query->where('status', '=', 2);
            $query->groupBy(DB::raw("DATE_FORMAT(date, '%m')"));
            $dtData = $query->get();

            $monthData = [];

            foreach ($dtData as $value) {
                $monthData[$value->month] = [
                    'total_payment' => $value->total_payment,
                    'month' => $value->month,
                    'value' => [$value->month],
                    'title' => 'Tháng '.$value->month.'/'.$year_search
                ];
            }
            foreach ($month_search as $month) {
                if (!isset($monthData[$month])) {
                    $monthData[$month] = [
                        'total_payment' => 0,
                        'month' => $month,
                        'value' => [$month],
                        'title' => 'Tháng '.$month.'/'.$year_search
                    ];
                }
            }
            ksort($monthData);
            $monthData = array_values($monthData);
        } else {
            $month_search = [
                'Q1' => ['01', '02', '03'],
                'Q2' => ['04', '05', '06'],
                'Q3' => ['07', '08', '09'],
                'Q4' => ['10', '11', '12']
            ];
            foreach ($month_search as $key => $value) {
                $query = Payment::select(DB::raw("COALESCE(SUM(payment),0) as total_payment"));
                $query->where(function ($q) use ($year_search, $value) {
                    $q->whereYear('date', $year_search)
                        ->whereIn(DB::raw("DATE_FORMAT(date, '%m')"), $value);
                });
                $query->whereHas('transaction_bill', function ($query) use ($partner_id) {
                    $query->where('partner_id', '=', $partner_id);
                });
                $query->where('status', '=', 2);
                $dtData = $query->first();
                $monthData[] = [
                    'total_payment' => $dtData->total_payment ?? 0,
                    'month' => $key,
                    'value' => $value,
                    'title' => 'Quý '.$key.'/'.$year_search
                ];
            }
        }

        $data['result'] = true;
        $data['data'] = $monthData;
        $data['message'] = 'Lấy dữ liệu thành công';
        return response()->json($data);
    }

    public function getSyntheticRevenueDetail(){
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page =$this->request->query('per_page');
        }
        $storageUrl = config('app.storage_url');
        $partner_id = $this->request->client->id ?? 0;
        $title = $this->request->input('title') ?? null;
        $month_search = $this->request->input('value_search') ?? [date('m')];
        $year_search = $this->request->input('year_search') ?? date('Y');
        $service_search = $this->request->input('service_search') ?? 0;
        $filter = $this->request->input('filter') ?? null;
        $date_start = $this->request->input('date_start') ?? null;
        $date_end = $this->request->input('date_end') ?? null;
        $orderBy = 'date desc';
        $query = Payment::select('id','date','customer_id','payment','reference_no','transaction_bill_id', DB::raw("DATE_FORMAT(date, '%m') as month"));
        $query->with(['customer' => function($q) use ($storageUrl){
            $q->select('id', 'fullname', 'phone', DB::raw("IF(avatar IS NOT NULL, CONCAT('$storageUrl/', avatar), NULL) as avatar"));
        }]);
        $query->with(['transaction_bill' => function($q) use ($storageUrl){
            $q->select('id', 'service_id', 'reference_no');
        }]);
        $query->where(function ($q) use ($year_search, $month_search) {
            $q->whereYear('date', $year_search)
                ->whereIn(DB::raw("DATE_FORMAT(date, '%m')"), $month_search);
        });
        $query->whereHas('transaction_bill', function ($query) use ($partner_id,$service_search) {
            $query->where('partner_id', '=', $partner_id);
            if (!empty($service_search)) {
                $query->where('service_id', '=', $service_search);
            }
        });

        if (!empty($date_start) && !empty($date_end)) {
            $date_start = to_sql_date($date_start) . ' 00:00:00';
            $date_end = to_sql_date($date_end) . ' 23:59:59';
            $query->whereBetween('date', [$date_start, $date_end]);
        }
        $query->when($filter, function ($q) use ($filter) {
            if ($filter == 'revenue_desc') {
                $q->orderByRaw('payment desc');
            } elseif ($filter == 'revenue_asc') {
                $q->orderByRaw('payment asc');
            } elseif ($filter == 'date_desc') {
                $q->orderByRaw('date desc');
            } elseif ($filter == 'date_asc') {
                $q->orderByRaw('date asc');
            }
        });
        $query->orderByRaw($orderBy);
        $query->where('status', '=', 2);
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);

        $service_ids = $dtData->pluck('transaction_bill.service_id')->filter()->unique()->toArray();
        $this->requestService = clone $this->request;
        $this->requestService->merge(['service_id' => $service_ids]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);

        $dtData->transform(function ($item) use ($services) {
            $service = $services->where('id', $item->transaction_bill->service_id)->first();
            $item->service = $service;
            return $item;
        });
        $collection = ReportResource::collection($dtData);
        $data['result'] = true;
        $data['title'] = $title;
        $data['data'] = $collection->response()->getData(true);
        $data['message'] = 'Lấy dữ liệu thành công';
        return response()->json($data);
    }

    public function getListReportRevenuePartnerDetail(){
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $month_search = $this->request->input('month_search') ?? date('m');
        $year_search = $this->request->input('year_search') ?? date('Y');
        $partner_search = $this->request->input('partner_search') ?? 0;
        $customer_search = $this->request->input('customer_search') ?? 0;
        $year_month = $year_search. '-' . $month_search;

        $f1Revenue = DB::table('tbl_referral_level as f1')
            ->join('tbl_payment as p', 'p.customer_id', '=', 'f1.customer_id')
            ->join('tbl_clients as cus', 'cus.id', '=', 'p.customer_id')
            ->select(
                'f1.parent_id as partner_id',
                'p.customer_id as f1_id',
                'p.customer_id as cus_id',
                'cus.fullname as cus_fullname',
                'cus.phone as cus_phone',
                'cus.avatar as cus_avatar',
                DB::raw('0 as customer_id'),
                'p.id as id',
                'p.date as date',
                'p.reference_no as reference_no',
                'p.payment as payment',
                'p.revenue_partner as revenue_partner',
                'p.percent_partner as percent_partner',
                'p.percent_f1 as percent_f1',
                'p.revenue_f1 as revenue_f1'
            )
            ->whereRaw("DATE_FORMAT(p.date, '%Y-%m') = ?", [$year_month])
            ->where('f1.level', '=',0)
            ->where('p.status', '=',2)
            ->where('f1.parent_id', '=',$partner_search)
            ->where('p.customer_id', '=',$customer_search);


        $f2Revenue = DB::table('tbl_referral_level as f1')
            ->where('f1.level', 0)
            ->leftJoin('tbl_referral_level as f2', function($join) {
                $join->on('f2.parent_id', '=', 'f1.customer_id')
                    ->where('f2.level', '=', 1);
            })
            ->join('tbl_payment as p', 'p.customer_id', '=', 'f2.customer_id')
            ->join('tbl_clients as cus', 'cus.id', '=', 'p.customer_id')
            ->select(
                'f1.parent_id as partner_id',
                'f1.customer_id as f1_id',
                'p.customer_id as cus_id',
                'cus.fullname as cus_fullname',
                'cus.phone as cus_phone',
                'cus.avatar as cus_avatar',
                'p.customer_id as customer_id',
                'p.id as id',
                'p.date as date',
                'p.reference_no as reference_no',
                'p.payment as payment',
                'p.revenue_partner as revenue_partner',
                'p.percent_partner as percent_partner',
                'p.percent_f1 as percent_f1',
                'p.revenue_f1 as revenue_f1'
            )
            ->whereRaw("DATE_FORMAT(p.date, '%Y-%m') = ?", [$year_month])
            ->where('p.status', '=',2)
            ->where('f1.parent_id', '=',$partner_search)
            ->where('f1.customer_id', '=',$customer_search);

        $storageUrl = config('app.storage_url');
        $tb_payment = $f1Revenue->union($f2Revenue);
        $query = DB::query()
            ->fromSub($tb_payment, 'u')
            ->select(
                'u.partner_id',
                'u.f1_id',
                'u.cus_id',
                'u.cus_fullname',
                'u.cus_phone',
                DB::raw("IF(cus_avatar IS NOT NULL, CONCAT('$storageUrl/', cus_avatar), NULL) as cus_avatar"),
                'u.customer_id',
                'u.id',
                'u.date',
                'u.reference_no',
                'u.payment',
                'u.revenue_partner',
                'u.percent_partner',
                'u.percent_f1',
                'u.revenue_f1',
            );
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        $query = DB::query()
            ->fromSub($tb_payment, 'u')
            ->select(
                'u.partner_id',
                'u.f1_id',
                'u.cus_id',
                'u.cus_fullname',
                'u.customer_id',
                'u.id',
                'u.date',
                'u.reference_no',
                'u.payment',
                'u.revenue_partner',
                'u.revenue_f1',
            );
        $total = $query->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);

    }

    public function getSyntheticMemberRose(){
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page =$this->request->query('per_page');
        }
        $partner_id = $this->request->client->id ?? 0;
        $type = $this->request->input('type') ?? 'member';
        $year_search = $this->request->input('year_search') ?? date('Y');
        $month_search = $this->request->input('month_search') ?? date('m');
        $year_month = $year_search. '-' . $month_search;
        $type_client = $this->request->client->type_client ?? 1;
        $dtData = Clients::find($partner_id);
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = 'Vui lòng đăng nhập để sử dụng dịch vụ';
            return response()->json($data);
        }
        $storageUrl = config('app.storage_url');
        $dtDataReport = [];
        if ($type == 'member') {
            $arrId = array_diff(getDataTreeReferralLevel($dtData->id), [$dtData->id]);
            $query = Clients::whereIn('id', $arrId);
            $query->select('id', 'fullname', 'phone', DB::raw("IF(tbl_clients.avatar IS NOT NULL, CONCAT('$storageUrl/', tbl_clients.avatar), NULL) as avatar"),'membership_level');
            $query->with(['referral_level' => function($q) {
                $q->select('id', 'customer_id', 'level');
            }]);
            $dtDataReport = $query->get();
            $dataLevelData = $this->fnbAdmin->getMemberShipLevel();
            $dataLevelData = $dataLevelData['data'] ?? [];
            $dataLevelData = collect($dataLevelData);
            $dtDataReport = $dtDataReport->map(function ($item) use ($dataLevelData) {
                $level = $dataLevelData->where('id', $item->membership_level)->first();
                $item->data_membership_level = $level;
                return $item;
            });
        } else {
            if ($type_client == 2) {
                $f1Revenue = DB::table('tbl_referral_level as f1')
                    ->join('tbl_payment as p', 'p.customer_id', '=', 'f1.customer_id')
                    ->join('tbl_clients as cus', 'cus.id', '=', 'p.customer_id')
                    ->select(
                        'f1.parent_id as partner_id',
                        'p.customer_id as f1_id',
                        'p.customer_id as cus_id',
                        'cus.fullname as cus_fullname',
                        'cus.phone as cus_phone',
                        'cus.avatar as cus_avatar',
                        DB::raw('0 as customer_id'),
                        'p.id as id',
                        'p.date as date',
                        'p.reference_no as reference_no',
                        'p.payment as payment',
                        'p.transaction_bill_id as transaction_bill_id',
                        'p.revenue_partner as revenue_partner',
                        'p.percent_partner as percent_partner',
                        'p.revenue_f1 as revenue_f1'
                    )
                    ->whereRaw("DATE_FORMAT(p.date, '%Y-%m') = ?", [$year_month])
                    ->where('f1.level', '=', 0)
                    ->where('p.status', '=', 2)
                    ->where('f1.parent_id', '=', $partner_id);


                $f2Revenue = DB::table('tbl_referral_level as f1')
                    ->where('f1.level', 0)
                    ->leftJoin('tbl_referral_level as f2', function ($join) {
                        $join->on('f2.parent_id', '=', 'f1.customer_id')
                            ->where('f2.level', '=', 1);
                    })
                    ->join('tbl_payment as p', 'p.customer_id', '=', 'f2.customer_id')
                    ->join('tbl_clients as cus', 'cus.id', '=', 'p.customer_id')
                    ->select(
                        'f1.parent_id as partner_id',
                        'f1.customer_id as f1_id',
                        'p.customer_id as cus_id',
                        'cus.fullname as cus_fullname',
                        'cus.phone as cus_phone',
                        'cus.avatar as cus_avatar',
                        'p.customer_id as customer_id',
                        'p.id as id',
                        'p.date as date',
                        'p.reference_no as reference_no',
                        'p.payment as payment',
                        'p.transaction_bill_id as transaction_bill_id',
                        'p.revenue_partner as revenue_partner',
                        'p.percent_partner as percent_partner',
                        'p.revenue_f1 as revenue_f1'
                    )
                    ->whereRaw("DATE_FORMAT(p.date, '%Y-%m') = ?", [$year_month])
                    ->where('p.status', '=', 2)
                    ->where('f1.parent_id', '=', $partner_id);

                $tb_payment = $f1Revenue->union($f2Revenue);
                $query = DB::query()
                    ->fromSub($tb_payment, 'u')
                    ->join('tbl_transaction_bill', 'tbl_transaction_bill.id', '=', 'u.transaction_bill_id')
                    ->select(
                        'u.partner_id',
                        'u.f1_id',
                        'u.cus_id',
                        'u.cus_fullname',
                        'u.cus_phone',
                        DB::raw("IF(cus_avatar IS NOT NULL, CONCAT('$storageUrl/', cus_avatar), NULL) as cus_avatar"),
                        'u.customer_id',
                        'u.id',
                        'u.date',
                        'u.reference_no',
                        'u.payment',
                        'u.revenue_partner',
                        'u.percent_partner',
                        'u.revenue_f1',
                        'tbl_transaction_bill.service_id',
                    );
                $totalPayment = $query->sum('payment');
                $totalRose = $query->sum('revenue_partner');
                $dtDataReport = $query->paginate($per_page, ['*'], '', $current_page);
            } else {
                //thành viên f1
                $tb_payment = DB::table('tbl_referral_level as f1')
                    ->where('f1.level', '=',1)
                    ->join('tbl_payment as p', 'p.customer_id', '=', 'f1.customer_id')
                    ->join('tbl_clients as cus', 'cus.id', '=', 'p.customer_id')
                    ->select(
                        'f1.parent_id as partner_id',
                        'f1.customer_id as f1_id',
                        'p.customer_id as cus_id',
                        'cus.fullname as cus_fullname',
                        'cus.phone as cus_phone',
                        'cus.avatar as cus_avatar',
                        'p.customer_id as customer_id',
                        'p.id as id',
                        'p.date as date',
                        'p.reference_no as reference_no',
                        'p.payment as payment',
                        'p.transaction_bill_id as transaction_bill_id',
                        'p.revenue_f1 as revenue_partner',
                        'p.percent_f1 as percent_partner',
                        'p.revenue_f1 as revenue_f1',
                    )
                    ->whereRaw("DATE_FORMAT(p.date, '%Y-%m') = ?", [$year_month])
                    ->where('p.status', '=', 2)
                    ->where('f1.parent_id', '=', $partner_id);
                $query = DB::query()
                    ->fromSub($tb_payment, 'u')
                    ->join('tbl_transaction_bill', 'tbl_transaction_bill.id', '=', 'u.transaction_bill_id')
                    ->select(
                        'u.partner_id',
                        'u.f1_id',
                        'u.cus_id',
                        'u.cus_fullname',
                        'u.cus_phone',
                        DB::raw("IF(cus_avatar IS NOT NULL, CONCAT('$storageUrl/', cus_avatar), NULL) as cus_avatar"),
                        'u.customer_id',
                        'u.id',
                        'u.date',
                        'u.reference_no',
                        'u.payment',
                        'u.revenue_partner',
                        'u.percent_partner',
                        'u.revenue_f1',
                        'tbl_transaction_bill.service_id',
                    );
                $totalPayment = $query->sum('payment');
                $totalRose = $query->sum('revenue_partner');
                $dtDataReport = $query->paginate($per_page, ['*'], '', $current_page);
            }

            $service_ids = $dtDataReport->pluck('service_id')->filter()->unique()->toArray();
            $this->requestService = clone $this->request;
            $this->requestService->merge(['service_id' => $service_ids]);
            $this->requestService->merge(['search' => null]);
            $responseService = $this->fnbService->getListDataByTransaction($this->requestService);
            $dataService = $responseService->getData(true);
            $services = collect($dataService['data']['data'] ?? []);

            $dtDataReport->transform(function ($item) use ($services) {
                $service = $services->where('id', $item->service_id)->first();
                $item->service = $service;
                $item->check_rose = true;
                return $item;
            });
            $dtDataReport = ReportResource::collection($dtDataReport);
            $dtDataReport = $dtDataReport->response()->getData(true);
        }

        $data['result'] = true;
        $data['data'] = $dtDataReport;
        $data['data_synthetic'] = [
            'total_payment' => $totalPayment ?? 0,
            'total_rose' => $totalRose ?? 0
        ];
        $data['message'] = 'Lấy dữ liệu thành công';
        return response()->json($data);
    }

    public function getSyntheticKpiUser(){
        $year = $this->request->input('year_search') ?? date('Y');
        $month = $this->request->input('month_search') ?? date('m');
        $year_month = $year. '-' . $month;
        $date_member = date($year. '-' . $month.'-t');

        $tb_payment = DB::table('tbl_payment')
            ->join('tbl_transaction_bill', 'tbl_transaction_bill.id', '=', 'tbl_payment.transaction_bill_id')
            ->join('tbl_clients', 'tbl_clients.id', '=', 'tbl_transaction_bill.partner_id')
            ->select(
                'tbl_clients.staff_id as staff_id',
                DB::raw('COALESCE(SUM(tbl_payment.payment),0) as payment'),
                DB::raw('0 as total_service'),
                DB::raw('0 as total_member'),
            )
            ->whereRaw("DATE_FORMAT(tbl_payment.date, '%Y-%m') = ?", [$year_month])
            ->where('tbl_clients.staff_id', '!=',0)
            ->where('tbl_payment.status', '=',2)
            ->groupBy('tbl_clients.staff_id');

        $responseService = $this->fnbReportService->getListRegisterServiceKPI($this->request);
        $dataService = $responseService->getData(true);
        $dataService = $dataService['data'] ?? [];
        if (empty($dataService)){
            $dataService[] = [
                'id' => 0,
                'customer_id' => 0,
                'month' => $year_month
            ];
        }

        $serviceQuery = DB::table(DB::raw('(' .
            collect($dataService)->map(function ($value) {
                return "(SELECT '{$value['id']}' as id, '{$value['customer_id']}' as customer_id, '{$value['month']}' as month)";
            })->implode(' UNION ALL ') . ') AS tb_service'))
            ->join('tbl_clients', 'tbl_clients.id', '=', 'tb_service.customer_id')
            ->select(
                'tbl_clients.staff_id as staff_id',
                DB::raw('0 as payment'),
                DB::raw('COUNT(tb_service.id) as total_service'),
                DB::raw('0 as total_member'),
            )
            ->where('tbl_clients.staff_id', '!=', 0)
            ->groupBy('tbl_clients.staff_id');

        $customerQuery = DB::table('tbl_clients')
            ->select(
                'tbl_clients.staff_id as staff_id',
                DB::raw('0 as payment'),
                DB::raw('0 as total_service'),
                DB::raw('COUNT(tbl_clients.id) as total_member'),
            )
            ->whereRaw("tbl_clients.date_active >= ?", [$date_member])
            ->where('tbl_clients.staff_id', '!=', 0)
            ->groupBy('tbl_clients.staff_id');


        $tb_services = $serviceQuery->unionAll($tb_payment);
        $tb_services = $customerQuery->unionAll($tb_services);

        $tb_services = DB::query()
            ->fromSub($tb_services, 'tb_services')
            ->select(
                'tb_services.staff_id',
                DB::raw('SUM(tb_services.payment) as payment'),
                DB::raw('SUM(tb_services.total_service) as total_service'),
                DB::raw('SUM(tb_services.total_member) as total_member'),
            )
            ->groupBy('tb_services.staff_id')
            ->get();

        return response()->json([
            'data' => $tb_services,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);

    }

    public function getListSyntheticRevenuePartner(){
        $storageUrl = config('app.storage_url');

        $date_start = $this->request->input('date_start_search') ?? date('01/m/Y');
        $date_end = $this->request->input('date_end_search') ?? date('t/m/Y');

        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $date_start = to_sql_date($date_start) . ' 00:00:00';
        $date_end = to_sql_date($date_end) . ' 23:59:59';

        $service_search = $this->request->input('service_search') ?? 0;
        $partner_search = $this->request->input('partner_search') ?? 0;
        $representative_search = $this->request->input('representative_search') ?? 0;
        $ares_search = $this->request->input('ares_search') ?? 0;

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $tb_payment = DB::table('tbl_transaction_bill')
            ->join('tbl_payment', 'tbl_payment.transaction_bill_id', '=', 'tbl_transaction_bill.id')
            ->select(
                'tbl_transaction_bill.partner_id as partner_id',
                'tbl_transaction_bill.service_id as service_id',
                'tbl_transaction_bill.date as date',
                'tbl_transaction_bill.id as transaction_bill_id',
                'tbl_transaction_bill.reference_no as reference_no_bill',
                'tbl_payment.reference_no as reference_no_payment',
                'tbl_payment.id as payment_id',
                DB::raw('tbl_payment.payment as total_payment'),
                DB::raw('(tbl_payment.revenue_partner + tbl_payment.revenue_f1) as total_rose')
            )
            ->where('tbl_payment.status', '=',2)
            ->whereBetween('tbl_payment.date', [$date_start, $date_end]);

        $query = Clients::where('tbl_clients.type_client', '=',2);
        $query->joinSub($tb_payment,'tb_payment',function ($join){
            $join->on('tb_payment.partner_id','=','tbl_clients.id');
        });
        $query->select(
            'tbl_clients.id as partner_id',
            'tbl_clients.fullname as partner_name',
            'tbl_clients.phone as partner_phone',
            'tbl_clients.province_id as province_id',
            'tbl_clients.wards_id as wards_id',
            DB::raw("IF(tbl_clients.avatar IS NOT NULL, CONCAT('$storageUrl/', tbl_clients.avatar), NULL) as partner_avatar"),
            'tbl_partner_representative_info.name as name_representative',
            'tbl_partner_representative_info.phone as phone_representative',
            DB::raw("IF(tbl_partner_representative_info.image IS NOT NULL, CONCAT('$storageUrl/', tbl_partner_representative_info.image), NULL) as avatar_representative"),
            'tb_payment.date as date',
            'tb_payment.service_id as service_id',
            'tb_payment.transaction_bill_id as transaction_bill_id',
            'tb_payment.reference_no_bill as reference_no_bill',
            'tb_payment.payment_id as payment_id',
            'tb_payment.reference_no_payment as reference_no_payment',
            'tb_payment.total_payment as total_payment',
            'tb_payment.total_rose as total_rose',
        );
        $query->leftJoin('tbl_partner_representative_info','tbl_partner_representative_info.customer_id','=','tbl_clients.id');
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $ListWard['data']);
                    } else {
                        $query->where('tbl_clients.id', 0);
                    }
                } else {
                    $query->where('tbl_clients.id', 0);
                }
            }
        }
        if(!empty($partner_search)){
            $query->where('tbl_clients.id','=',$partner_search);
        }
        if(!empty($representative_search)){
            $query->where('tbl_partner_representative_info.id','=',$representative_search);
        }
        if (!empty($service_search)){
            $query->where('tb_payment.service_id', '=',$service_search);
        }
        if (!empty($ares_search)){
            $dataAres = $this->fnbService->getWardsWhereAres($this->request, $ares_search);
            if (!empty($dataAres)) {
                $WardSearch = $dataAres->getData(true);
                if (!empty($WardSearch['result'])) {
                    if (!empty($WardSearch['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $WardSearch['data']);
                    }
                }
            }
        }
        $filtered = $query->count();
        $query->orderByRaw('partner_id asc ,service_id asc');
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
            }
        }

        $query = Clients::where('tbl_clients.type_client', '=',2);
        $query->joinSub($tb_payment,'tb_payment',function ($join){
            $join->on('tb_payment.partner_id','=','tbl_clients.id');
        });

        $query->select(
            'tbl_clients.id as partner_id',
            'tbl_clients.fullname as partner_name'
        );
        $total = $query->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListSyntheticCustomer(){
        $number_date_remind_payment_due = $this->fnbAdmin->get_option('number_date_remind_payment_due');
        $storageUrl = config('app.storage_url');

        $date_start = $this->request->input('date_start_search') ?? date('01/m/Y');
        $date_end = $this->request->input('date_end_search') ?? date('t/m/Y');

        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $type_report = $this->request->input('type_report') ?? 1;
        $date_start = to_sql_date($date_start) . ' 00:00:00';
        $date_end = to_sql_date($date_end) . ' 23:59:59';

        $ares_search = $this->request->input('ares_search') ?? 0;

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $query = Clients::where('tbl_clients.id', '!=',0);
        $query->select(
            'tbl_clients.id as id',
            'tbl_clients.code as code',
            'tbl_clients.fullname as fullname',
            'tbl_clients.phone as phone',
            'tbl_clients.email as email',
            'tbl_clients.created_at as created_at',
            'tbl_clients.date_active as date_active',
            'tbl_clients.province_id as province_id',
            'tbl_clients.membership_level as membership_level',
            'tbl_clients.active_limit_private as active_limit_private',
            'tbl_clients.radio_discount_private as radio_discount_private',
            'tbl_clients.wards_id as wards_id',
            DB::raw("IF(tbl_clients.avatar IS NOT NULL, CONCAT('$storageUrl/', tbl_clients.avatar), NULL) as avatar"),
        );
        if ($type_report == 1){
            $query->where('tbl_clients.active','=',1);
            $query->whereBetween('tbl_clients.created_at', [$date_start, $date_end]);
        } elseif ($type_report == 2){
            $query->where('tbl_clients.active','=',0);
            $query->whereBetween('tbl_clients.date_exec', [$date_start, $date_end]);
        } elseif ($type_report == 3){
            $query->whereRaw('DATE(DATE_SUB(date_active, INTERVAL ? DAY)) <= ?', [$number_date_remind_payment_due, date('Y-m-d')]);
            $query->whereRaw('DATE(date_active) >= ?', [date('Y-m-d')]);
            $query->whereBetween('tbl_clients.date_active', [$date_start, $date_end]);
        }
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $ListWard['data']);
                    } else {
                        $query->where('tbl_clients.id', 0);
                    }
                } else {
                    $query->where('tbl_clients.id', 0);
                }
            }
        }
        if (!empty($ares_search)){
            $dataAres = $this->fnbService->getWardsWhereAres($this->request, $ares_search);
            if (!empty($dataAres)) {
                $WardSearch = $dataAres->getData(true);
                if (!empty($WardSearch['result'])) {
                    if (!empty($WardSearch['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $WardSearch['data']);
                    }
                }
            }
        }
        $filtered = $query->count();
        $query->orderByRaw('id asc');
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
            }
        }

        $query = Clients::where('tbl_clients.id', '!=',0);
        if ($type_report == 1){
            $query->where('tbl_clients.active','=',1);
            $query->whereBetween('tbl_clients.created_at', [$date_start, $date_end]);
        } elseif ($type_report == 2){
            $query->where('tbl_clients.active','=',0);
            $query->whereBetween('tbl_clients.date_exec', [$date_start, $date_end]);
        } elseif ($type_report == 3){
            $query->whereRaw('DATE(DATE_SUB(date_active, INTERVAL ? DAY)) <= ?', [$number_date_remind_payment_due, date('Y-m-d')]);
            $query->whereRaw('DATE(date_active) >= ?', [date('Y-m-d')]);
            $query->whereBetween('tbl_clients.date_active', [$date_start, $date_end]);
        }
        $query->select(
            'tbl_clients.id as id',
            'tbl_clients.fullname as fullname'
        );
        $total = $query->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListSyntheticSpendingCustomer(){
        $storageUrl = config('app.storage_url');

        $date_start = $this->request->input('date_start_search') ?? date('01/m/Y');
        $date_end = $this->request->input('date_end_search') ?? date('t/m/Y');

        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $date_start = to_sql_date($date_start) . ' 00:00:00';
        $date_end = to_sql_date($date_end) . ' 23:59:59';

        $service_search = $this->request->input('service_search') ?? 0;
        $partner_search = $this->request->input('partner_search') ?? 0;
        $representative_search = $this->request->input('representative_search') ?? 0;
        $ares_search = $this->request->input('ares_search') ?? 0;
        $customer_search = $this->request->input('customer_search') ?? 0;

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $f1Revenue = DB::table('tbl_referral_level as f1')
            ->join('tbl_payment as p', 'p.customer_id', '=', 'f1.customer_id')
            ->join('tbl_transaction_bill', 'tbl_transaction_bill.id', '=', 'p.transaction_bill_id')
            ->select(
                'f1.parent_id as partner_id',
                'f1.customer_id as f1_id',
                'p.date as date',
                'p.reference_no as reference_no_payment',
                'p.id as payment_id',
                'tbl_transaction_bill.service_id as service_id',
                'tbl_transaction_bill.reference_no as reference_no_bill',
                'tbl_transaction_bill.id as transaction_bill_id',
                DB::raw('p.payment as payment'),
            )
            ->whereBetween('p.date', [$date_start, $date_end])
            ->where('f1.level', 0)
            ->where('p.status', '=',2);


        $f2Revenue = DB::table('tbl_referral_level as f1')
            ->where('f1.level', 0)
            ->leftJoin('tbl_referral_level as f2', function($join) {
                $join->on('f2.parent_id', '=', 'f1.customer_id')
                    ->where('f2.level', '=', 1);
            })
            ->join('tbl_payment as p', 'p.customer_id', '=', 'f2.customer_id')
            ->join('tbl_transaction_bill', 'tbl_transaction_bill.id', '=', 'p.transaction_bill_id')
            ->select(
                'f1.parent_id as partner_id',
                'f1.customer_id as f1_id',
                'p.date as date',
                'p.reference_no as reference_no_payment',
                'p.id as payment_id',
                'tbl_transaction_bill.service_id as service_id',
                'tbl_transaction_bill.reference_no as reference_no_bill',
                'tbl_transaction_bill.id as transaction_bill_id',
                DB::raw('p.payment as payment'),
            )
            ->whereBetween('p.date', [$date_start, $date_end])
            ->where('p.status', '=',2);
        $tb_payment = $f1Revenue->unionAll($f2Revenue);

        $query = Clients::where('tbl_clients.type_client', '=',2);
        $query->joinSub($tb_payment,'tb_payment',function ($join){
            $join->on('tb_payment.partner_id','=','tbl_clients.id');
        });
        $query->select(
            'tbl_clients.id as partner_id',
            'tbl_clients.fullname as partner_name',
            'tbl_clients.phone as partner_phone',
            'tbl_clients.province_id as province_id',
            'tbl_clients.wards_id as wards_id',
            DB::raw("IF(tbl_clients.avatar IS NOT NULL, CONCAT('$storageUrl/', tbl_clients.avatar), NULL) as partner_avatar"),
            'tbl_partner_representative_info.name as name_representative',
            'tbl_partner_representative_info.phone as phone_representative',
            DB::raw("IF(tbl_partner_representative_info.image IS NOT NULL, CONCAT('$storageUrl/', tbl_partner_representative_info.image), NULL) as avatar_representative"),
            'f1_customer.fullname as f1_name',
            'f1_customer.phone as f1_phone',
            DB::raw("IF(f1_customer.avatar IS NOT NULL, CONCAT('$storageUrl/', f1_customer.avatar), NULL) as f1_avatar"),
            'tb_payment.date as date',
            'tb_payment.service_id as service_id',
            'tb_payment.transaction_bill_id as transaction_bill_id',
            'tb_payment.reference_no_bill as reference_no_bill',
            'tb_payment.payment_id as payment_id',
            'tb_payment.reference_no_payment as reference_no_payment',
            'tb_payment.payment as total_payment',
        );
        $query->leftJoin('tbl_partner_representative_info','tbl_partner_representative_info.customer_id','=','tbl_clients.id');
        $query->join('tbl_clients as f1_customer','f1_customer.id','=','tb_payment.f1_id');
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $ListWard['data']);
                    } else {
                        $query->where('tbl_clients.id', 0);
                    }
                } else {
                    $query->where('tbl_clients.id', 0);
                }
            }
        }
        if (!empty($customer_search)){
            $query->where('tb_payment.f1_id', '=',$customer_search);
        }
        if(!empty($partner_search)){
            $query->where('tbl_clients.id','=',$partner_search);
        }
        if(!empty($representative_search)){
            $query->where('tbl_partner_representative_info.id','=',$representative_search);
        }
        if (!empty($service_search)){
            $query->where('tb_payment.service_id', '=',$service_search);
        }
        if (!empty($ares_search)){
            $dataAres = $this->fnbService->getWardsWhereAres($this->request, $ares_search);
            if (!empty($dataAres)) {
                $WardSearch = $dataAres->getData(true);
                if (!empty($WardSearch['result'])) {
                    if (!empty($WardSearch['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $WardSearch['data']);
                    }
                }
            }
        }
        $filtered = $query->count();
        $query->orderByRaw('partner_id asc ,f1_id asc');
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
            }
        }

        $query = Clients::where('tbl_clients.type_client', '=',2);
        $query->joinSub($tb_payment,'tb_payment',function ($join){
            $join->on('tb_payment.partner_id','=','tbl_clients.id');
        });

        $query->select(
            'tbl_clients.id as partner_id',
            'tbl_clients.fullname as partner_name'
        );
        $total = $query->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListSyntheticDiscountPartner(){
        $storageUrl = config('app.storage_url');

        $date_start = $this->request->input('date_start_search') ?? date('01/m/Y');
        $date_end = $this->request->input('date_end_search') ?? date('t/m/Y');

        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $date_start = to_sql_date($date_start) . ' 00:00:00';
        $date_end = to_sql_date($date_end) . ' 23:59:59';

        $service_search = $this->request->input('service_search') ?? 0;
        $partner_search = $this->request->input('partner_search') ?? 0;
        $representative_search = $this->request->input('representative_search') ?? 0;
        $ares_search = $this->request->input('ares_search') ?? 0;
        $membership_level_search = $this->request->input('membership_level_search') ?? 0;

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $tb_payment = DB::table('tbl_transaction_bill')
            ->join('tbl_payment', 'tbl_payment.transaction_bill_id', '=', 'tbl_transaction_bill.id')
            ->join('tbl_clients', 'tbl_clients.id', '=', 'tbl_payment.customer_id')
            ->select(
                'tbl_transaction_bill.partner_id as partner_id',
                'tbl_transaction_bill.service_id as service_id',
                'tbl_transaction_bill.date as date',
                'tbl_transaction_bill.id as transaction_bill_id',
                'tbl_transaction_bill.reference_no as reference_no_bill',
                'tbl_payment.reference_no as reference_no_payment',
                'tbl_payment.id as payment_id',
                'tbl_clients.id as customer_id',
                'tbl_clients.fullname as customer_name',
                'tbl_clients.phone as customer_phone',
                DB::raw("IF(tbl_clients.avatar IS NOT NULL, CONCAT('$storageUrl/', tbl_clients.avatar), NULL) as customer_avatar"),
                'tbl_transaction_bill.membership_level_id as membership_level_id',
                DB::raw('tbl_transaction_bill.total_discount as total_discount'),
            )
            ->where('tbl_payment.status', '=',2)
            ->whereBetween('tbl_payment.date', [$date_start, $date_end]);

        $query = Clients::where('tbl_clients.type_client', '=',2);
        $query->joinSub($tb_payment,'tb_payment',function ($join){
            $join->on('tb_payment.partner_id','=','tbl_clients.id');
        });
        $query->select(
            'tbl_clients.id as partner_id',
            'tbl_clients.fullname as partner_name',
            'tbl_clients.phone as partner_phone',
            'tbl_clients.province_id as province_id',
            'tbl_clients.wards_id as wards_id',
            DB::raw("IF(tbl_clients.avatar IS NOT NULL, CONCAT('$storageUrl/', tbl_clients.avatar), NULL) as partner_avatar"),
            'tbl_partner_representative_info.name as name_representative',
            'tbl_partner_representative_info.phone as phone_representative',
            DB::raw("IF(tbl_partner_representative_info.image IS NOT NULL, CONCAT('$storageUrl/', tbl_partner_representative_info.image), NULL) as avatar_representative"),
            'tb_payment.date as date',
            'tb_payment.service_id as service_id',
            'tb_payment.transaction_bill_id as transaction_bill_id',
            'tb_payment.reference_no_bill as reference_no_bill',
            'tb_payment.payment_id as payment_id',
            'tb_payment.reference_no_payment as reference_no_payment',
            'tb_payment.customer_name as customer_name',
            'tb_payment.customer_phone as customer_phone',
            'tb_payment.customer_id as customer_id',
            'tb_payment.customer_avatar as customer_avatar',
            'tb_payment.total_discount as total_discount',
            'tb_payment.membership_level_id as membership_level_id',
        );
        $query->leftJoin('tbl_partner_representative_info','tbl_partner_representative_info.customer_id','=','tbl_clients.id');
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $ListWard['data']);
                    } else {
                        $query->where('tbl_clients.id', 0);
                    }
                } else {
                    $query->where('tbl_clients.id', 0);
                }
            }
        }
        if(!empty($partner_search)){
            $query->where('tbl_clients.id','=',$partner_search);
        }
        if(!empty($representative_search)){
            $query->where('tbl_partner_representative_info.id','=',$representative_search);
        }
        if (!empty($service_search)){
            $query->where('tb_payment.service_id', '=',$service_search);
        }
        if (!empty($membership_level_search)){
            $query->where('tb_payment.membership_level_id', '=',$membership_level_search);
        }
        if (!empty($ares_search)){
            $dataAres = $this->fnbService->getWardsWhereAres($this->request, $ares_search);
            if (!empty($dataAres)) {
                $WardSearch = $dataAres->getData(true);
                if (!empty($WardSearch['result'])) {
                    if (!empty($WardSearch['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $WardSearch['data']);
                    }
                }
            }
        }
        $filtered = $query->count();
        $query->orderByRaw('partner_id asc ,customer_id asc');
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
            }
        }

        $query = Clients::where('tbl_clients.type_client', '=',2);
        $query->joinSub($tb_payment,'tb_payment',function ($join){
            $join->on('tb_payment.partner_id','=','tbl_clients.id');
        });

        $query->select(
            'tbl_clients.id as partner_id',
            'tbl_clients.fullname as partner_name'
        );
        $total = $query->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListSyntheticUpgradeMembership(){

        $storageUrl = config('app.storage_url');

        $date_start = $this->request->input('date_start_search') ?? date('01/m/Y');
        $date_end = $this->request->input('date_end_search') ?? date('t/m/Y');

        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $date_start = to_sql_date($date_start) . ' 00:00:00';
        $date_end = to_sql_date($date_end) . ' 23:59:59';

        $ares_search = $this->request->input('ares_search') ?? 0;
        $membership_level_search = $this->request->input('membership_level_search') ?? 0;

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $query = Clients::where('tbl_clients.id', '!=',0);
        $query->select(
            'tbl_clients.id as id',
            'tbl_clients.code as code',
            'tbl_clients.fullname as fullname',
            'tbl_clients.phone as phone',
            'tbl_clients.email as email',
            'tbl_clients.created_at as created_at',
            'tbl_clients.date_active as date_active',
            'tbl_clients.ranking_date as ranking_date',
            'tbl_clients.province_id as province_id',
            'tbl_clients.membership_level as membership_level',
            'tbl_clients.active_limit_private as active_limit_private',
            'tbl_clients.radio_discount_private as radio_discount_private',
            'tbl_clients.wards_id as wards_id',
            DB::raw("IF(tbl_clients.avatar IS NOT NULL, CONCAT('$storageUrl/', tbl_clients.avatar), NULL) as avatar"),
        );
        $query->where('tbl_clients.admin_membership','=',1);
        $query->where('tbl_clients.membership_level','!=',1);
        $query->whereBetween('tbl_clients.ranking_date', [$date_start, $date_end]);
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $ListWard['data']);
                    } else {
                        $query->where('tbl_clients.id', 0);
                    }
                } else {
                    $query->where('tbl_clients.id', 0);
                }
            }
        }
        if (!empty($ares_search)){
            $dataAres = $this->fnbService->getWardsWhereAres($this->request, $ares_search);
            if (!empty($dataAres)) {
                $WardSearch = $dataAres->getData(true);
                if (!empty($WardSearch['result'])) {
                    if (!empty($WardSearch['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $WardSearch['data']);
                    }
                }
            }
        }
        if (!empty($membership_level_search)){
            $query->where('tbl_clients.membership_level', '=',$membership_level_search);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
            }
        }

        $query = Clients::where('tbl_clients.id', '!=',0);
        $query->where('tbl_clients.admin_membership','=',1);
        $query->where('tbl_clients.membership_level','!=',1);
        $query->whereBetween('tbl_clients.ranking_date', [$date_start, $date_end]);
        $query->select(
            'tbl_clients.id as id',
            'tbl_clients.fullname as fullname'
        );
        $total = $query->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListSyntheticFeePartner(){
        $storageUrl = config('app.storage_url');

        $type_client = $this->request->input('type_client') ?? 1;
        $month_search = $this->request->input('month_search') ?? date('m');
        $year_search = $this->request->input('year_search') ?? date('Y');
        $date_start = $year_search . '-' . $month_search . '-01';
        $date_end = $year_search . '-' . $month_search . '-' . date('t', strtotime($date_start));


        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $query = Clients::where('tbl_clients.id', '!=',0);
        $query->where('tbl_clients.type_client', '=',$type_client);
        $query->select(
            'tbl_clients.province_id as province_id',
            'tbl_clients.wards_id as wards_id',
            DB::raw("COUNT(id) as total"),
        );
        $query->where(function ($q) use ($date_start, $date_end) {
            $q->where('tbl_clients.date_active', '>=', $date_start)
                ->orWhereBetween('tbl_clients.date_active', [$date_start, $date_end]);
        });
        $query->whereRaw('DATE(tbl_clients.created_at) <= ?',[$date_end]);
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $ListWard['data']);
                    } else {
                        $query->where('tbl_clients.id', 0);
                    }
                } else {
                    $query->where('tbl_clients.id', 0);
                }
            }
        }
        $query->groupBy('tbl_clients.province_id', 'tbl_clients.wards_id');
        $data = $query->get();
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListSyntheticRosePartner(){
        $storageUrl = config('app.storage_url');

        $date_start = $this->request->input('date_start_search') ?? date('01/m/Y');
        $date_end = $this->request->input('date_end_search') ?? date('t/m/Y');

        $date_start = to_sql_date($date_start) . ' 00:00:00';
        $date_end = to_sql_date($date_end) . ' 23:59:59';

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $tb_payment = DB::table('tbl_transaction_bill')
            ->join('tbl_payment', 'tbl_payment.transaction_bill_id', '=', 'tbl_transaction_bill.id')
            ->select(
                'tbl_transaction_bill.partner_id as partner_id',
                DB::raw('SUM(tbl_payment.payment) as total_payment'),
            )
            ->where('tbl_payment.status', '=',2)
            ->whereBetween('tbl_payment.date', [$date_start, $date_end])
            ->groupBy('tbl_transaction_bill.partner_id');

        $query = Clients::where('tbl_clients.id', '!=',0);
        $query->where('tbl_clients.type_client', '=',2);
        $query->select(
            'tbl_clients.province_id as province_id',
            'tbl_clients.wards_id as wards_id',
            DB::raw("COUNT(id) as total"),
            DB::raw("SUM(total_payment) as total_payment"),
        );
        $query->joinSub($tb_payment,'tb_payment',function ($join){
            $join->on('tb_payment.partner_id','=','tbl_clients.id');
        });
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $ListWard['data']);
                    } else {
                        $query->where('tbl_clients.id', 0);
                    }
                } else {
                    $query->where('tbl_clients.id', 0);
                }
            }
        }
        $query->groupBy('tbl_clients.province_id', 'tbl_clients.wards_id');
        $data = $query->get();

        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }
}
