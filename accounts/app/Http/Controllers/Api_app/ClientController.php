<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Clients;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends AuthController
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getListCustomer(){
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data",'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $type_client_search = $this->request->input('type_client_search') ?? 0;
        $active_search = $this->request->input('active_search') ?? -1;
        $date_search = $this->request->input('date_search') ?? null;
        $type_client = $this->request->input('type_client') ?? 1;

        $query = Clients::where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }
        if (($type_client_search)){
            $query->where('type_client', $type_client_search);
        }
        if ($active_search != -1 && $active_search != ''){
            $query->where('active', $active_search);
        }
        $query->where('type_client',$type_client);
        if (!empty($date_search)){
            $date_search = explode(' - ',$date_search);
            $start_date = to_sql_date($date_search[0].' 00:00:00',true);
            $end_date = to_sql_date($date_search[1].' 23:59:59',true);
            $query->whereBetween('tbl_clients.created_at', [$start_date, $end_date]);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtImage = !empty($value->avatar) ? env('STORAGE_URL').'/'.$value->avatar : null;
                $data[$key]['avatar'] = $dtImage;
            }
        }
        $total = Clients::count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function countAll(){
        $type_client_search = $this->request->input('type_client_search') ?? 0;
        $active_search = $this->request->input('active_search') ?? -1;
        $date_search = $this->request->input('date_search') ?? null;

        if (!empty($date_search)){
            $date_search = explode(' - ',$date_search);
            $start_date = to_sql_date($date_search[0].' 00:00:00',true);
            $end_date = to_sql_date($date_search[1].' 23:59:59',true);
        } else {
            $start_date = null;
            $end_date = null;
        }

        $arrType = [
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
        ];

        $query = Clients::where('id','!=',0);

        if (($type_client_search)){
            $query->where('type_client', $type_client_search);
        }
        if ($active_search != -1 && $active_search != ''){
            $query->where('active', $active_search);
        }
        if (!empty($date_search)){
            $query->whereBetween('tbl_clients.created_at', [$start_date, $end_date]);
        }
        $totalAll = $query->count();

        foreach ($arrType as $key => $value){
            $type_client = $value['id'];
            $query = Clients::where('id','!=',0);
            $query->where('type_client', $type_client);
            if ($active_search != -1 && $active_search != ''){
                $query->where('active', $active_search);
            }
            if (!empty($date_search)){
                $query->whereBetween('tbl_clients.created_at', [$start_date, $end_date]);
            }
            $total = $query->count();
            $arrType[$key]['total'] = $total;
        }

        return response()->json([
            'total' => $totalAll,
            'arrType' => $arrType,
            'result' => true,
            'message' => 'Thành công'
        ]);
    }

    public function getDetailCustomer(){
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        if (!empty($client)){
            $dtImage = !empty($client->avatar) ? env('STORAGE_URL').'/'.$client->avatar : null;
            $client->avatar = $dtImage;
        }
        $data['result'] = true;
        $data['client'] = $client;
        $data['message'] = 'Lấy thông tin khách hàng thành công';
        return response()->json($data);
    }

    public function detail(){
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        if (empty($client)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại khách hàng';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $client->fullname = $this->request->fullname;
            $client->phone = $this->request->phone;
            $client->email = $this->request->email;
            $client->active = $this->request->active;
            $client->type_client = $this->request->type_client;
            $client->number_cccd = $this->request->number_cccd;
            $client->issued_cccd = $this->request->issued_cccd;
            if (!empty($this->request->date_cccd)) {
                $client->date_cccd = to_sql_date($this->request->date_cccd);
            }
            if (!empty($this->request->date_passport)) {
                $client->date_passport = to_sql_date($this->request->date_passport);
            }
            $client->number_passport = $this->request->number_passport;
            $client->issued_passport = $this->request->issued_passport;
            if (!empty($this->request->password)) {
                $client->password = encrypt($this->request->password);
            }
            $client->save();
            if ($client) {
                if ($this->request->hasFile('avatar')) {
                    if (!empty($client->avatar)) {
                        $this->deleteFile($client->avatar);
                    }
                    $path = $this->UploadFile($this->request->file('avatar'), 'clients/' . $client->id, 70, 70, false);
                    $client->avatar = $path;
                    $client->save();
                }
                DB::commit();
                $data['result'] = true;
                $data['message'] = 'Cập nhập thành công';
            } else {
                $data['result'] = false;
                $data['message'] = 'Cập nhập thất bại';
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function deleteCustomer(){
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        if (empty($client)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại khách hàng';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $client->delete();
            DB::table('tbl_session_login')->where('id_client', $id)->delete();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('c_delete_true');
            return response()->json($data);
        }  catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function active(){
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        DB::beginTransaction();
        try {
            $client->active = $client->active == 0 ? 1 : 0;
            $client->save();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        }
        catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListData(){
        $search = $this->request->input('search') ?? null;
        $limit = $this->request->input('limit') ?? 50;
        $id = $this->request->input('customer_id') ?? [];
        $query = Clients::select('id','fullname','phone','avatar','email')
            ->where('active', 1)
        ->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('fullname', 'like', "%$search%");
                $q->orWhere('phone', 'like', "%$search%");
            });
        }
        if (!empty($id)) {
            $query->whereIn('id', $id);
        }
        $data = $query->limit($limit)->get();
        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtImage = !empty($value->avatar) ? env('STORAGE_URL').'/'.$value->avatar : null;
                $data[$key]['avatar'] = $dtImage;
            }
        }
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }
}
