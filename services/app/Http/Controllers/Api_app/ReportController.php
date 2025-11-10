<?php

namespace App\Http\Controllers\Api_app;

use App\Traits\UploadFile;
use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\AdminService;
use Illuminate\Support\Facades\Validator;
use function Aws\map;

class ReportController extends AuthController
{
    protected $fnbAdmin;
    use UploadFile;

    public function __construct(Request $request, AdminService $AdminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbAdmin = $AdminService;
    }

    public function getListRegisterServiceKPI()
    {
        $year = $this->request->input('year_search') ?? date('Y');
        $month = $this->request->input('month_search') ?? date('m');
        $year_month = $year. '-' . $month;
        $tb_service = DB::table('tbl_service')
            ->select(
                'tbl_service.id as id',
                'tbl_service.customer_id as customer_id',
                DB::raw("DATE_FORMAT(tbl_service.created_at, '%Y-%m') as month"),
            )
            ->whereRaw("DATE_FORMAT(tbl_service.created_at, '%Y-%m') = ?", [$year_month])
            ->where('tbl_service.active', '=',1)
            ->get();

        return response()->json([
            'data' => $tb_service,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }
}
