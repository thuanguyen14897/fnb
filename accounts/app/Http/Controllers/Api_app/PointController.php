<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\HistoryPointResource;
use App\Models\HistoryPoint;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\ServiceService;
use App\Services\AdminService;
use Illuminate\Support\Facades\Validator;
use function Aws\map;

class PointController extends AuthController
{
    protected $fnbService;
    protected $fnbAdmin;
    use UploadFile;

    public function __construct(Request $request, ServiceService $ServiceService, AdminService $AdminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbService = $ServiceService;
        $this->fnbAdmin = $AdminService;
    }

    public function getListHistoryPoint(){
        $current_page = 1;
        $per_page = 10;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page =$this->request->query('per_page');
        }
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $year_month = $this->request->input('year_month') ?? date('Y-m');
        $dtHistoryPoint = HistoryPoint::where(function ($query) use ($customer_id,$year_month){
            $query->where('tbl_client_point_history.customer_id',$customer_id);
            if (!empty($year_month)){
                $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?",[$year_month]);
            }
        })
            ->orderByRaw('tbl_client_point_history.created_at desc')
            ->paginate($per_page, ['*'], '', $current_page);
        return HistoryPointResource::collection($dtHistoryPoint);
    }

    public function getListMonthPoint(){
        $customer_id = $this->request->client->id ?? 0;
        $query = HistoryPoint::where('id','!=',0);
        $query->where('customer_id', $customer_id);
        $query->select(DB::raw("DATE_FORMAT(created_at, '%m-%Y') as month_year"));
        $query->groupBy(DB::raw("DATE_FORMAT(created_at, '%m-%Y')"));
        $query->orderByRaw("DATE_FORMAT(created_at, '%m-%Y') desc");
        $dtData = $query->get();
        $data['result'] = true;
        $data['data'] = $dtData;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }

}
