<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Clients;
use App\Models\PartnerImage;
use App\Models\PartnerRepresentative;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\ServiceService;
use Illuminate\Support\Facades\Validator;

class ClientController extends AuthController
{
    protected $fnService;
    use UploadFile;
    public function __construct(Request $request,ServiceService $ServiceService)
    {
        parent::__construct($request);
        DB::enableQueryLog();

        $this->fnbService = $ServiceService;
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
        $client = Clients::with('representative')
            ->with('image_cccd')
            ->with('image_kd')
            ->find($id);
        if (!empty($client)){
            $dtImage = !empty($client->avatar) ? env('STORAGE_URL').'/'.$client->avatar : null;
            $client->avatar = $dtImage;

            $client->image_cccd->map(function ($item){
                return $item->image_new = !empty($item->image) ? env('STORAGE_URL').'/'.$item->image : null;;
            });
            $client->image_kd->map(function ($item){
                return $item->image_new = !empty($item->image) ? env('STORAGE_URL').'/'.$item->image : null;;
            });
        }
        $data['result'] = true;
        $data['client'] = $client;
        if(!empty($client->province_id)) {
            if ($this->request->client == null) {
                $this->request->client = (object)['token' => Config::get('constant')['token_default']];
            }
            $this->request->merge([
                'id' => $client->province_id,
            ]);
            $data_province = $this->fnbService->getProvice($this->request);
            if(!empty($data_province->getData(true)['result'])) {
                $isProvince = $data_province->getData(true)['data'];
                if(!empty($isProvince[0])) {
                    $client->province = $isProvince[0];
                }
            }
            if(!empty($client->wards_id)) {
                $this->request->merge([
                    'id' => $client->wards_id,
                ]);
                $this->request->merge([
                    'province_id' => $client->province_id,
                ]);
                if ($this->request->client == null) {
                    $this->request->client = (object)['token' => Config::get('constant')['token_default']];
                }
                $data_wards = $this->fnbService->getWards($this->request);
                if(!empty($data_wards->getData(true)['result'])) {
                    $isWards = $data_wards->getData(true)['data'];
                    if(!empty($isWards[0])) {
                        $client->wards = $isWards[0];
                    }
                }
            }
        }

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

            $client->province_id = $this->request->province_id;
            $client->wards_id = $this->request->wards_id;

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
        $query = Clients::with('representative')
            ->select('id','fullname','phone','avatar','email')
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

    public function detailRepresentativePartner(){
        $id = $this->request->input('id') ?? 0;
        $partnerRules = [
            'name_representative' => 'required',
            'phone_representative' => 'required',
            'email_representative' => 'required|unique:tbl_partner_representative_info,email,' . $id,
            'mst_representative' => 'required|unique:tbl_partner_representative_info,mst,' . $id,
            'birthday_representative' => 'required',
            'number_cccd_representative' => 'required',
            'date_cccd_representative' => 'required',
            'date_end_cccd_representative' => 'required',
            'issued_cccd_representative' => 'required',
            'type_representative' => 'required',
        ];
        $partnerMessages = [
            'name_representative.required' => 'Vui lòng nhập tên người đại diện',
            'phone_representative.required' => 'Vui lòng nhập số điện thoại người đại diện',
            'email_representative.required' => 'Vui lòng nhập email người đại diện',
            'email_representative.unique' => 'Email người đại diện đã tồn tại',
            'mst_representative.required' => 'Vui lòng nhập mã số thuế',
            'mst_representative.unique' => 'Mã số thuế đã tồn tại',
            'birthday_representative.required' => 'Vui lòng nhập ngày sinh',
            'number_cccd_representative.required' => 'Vui lòng nhập số cccd',
            'date_cccd_representative.required' => 'Vui lòng nhập ngày cấp cccd',
            'date_end_cccd_representative.required' => 'Vui lòng nhập ngày hết hạn cccd',
            'issued_cccd_representative.required' => 'Vui lòng nhập nơi cấp cccd',
            'type_representative.required' => 'Vui lòng chọn loại hình kinh doanh',
        ];
        $validatorPartner = Validator::make($this->request->all(), $partnerRules, $partnerMessages);
        if ($validatorPartner->fails()) {
            $data['result'] = false;
            $data['message'] = $validatorPartner->errors()->all()[0];
            echo json_encode($data);
            die();
        }
        if (empty($id)){
            $dtData = new PartnerRepresentative();
        } else {
            $dtData = PartnerRepresentative::find($id);
        }
        DB::beginTransaction();
        $image_cccd_old = !empty($this->request->input('image_cccd_old')) ? is_array($this->request->input('image_cccd_old')) ? $this->request->input('image_cccd_old') : json_decode($this->request->input('image_cccd_old')) : [];
        $image_kd_old = !empty($this->request->input('image_kd_old')) ? is_array($this->request->input('image_kd_old')) ? $this->request->input('image_kd_old') : json_decode($this->request->input('image_kd_old')) : [];
        $customer_id = $this->request->partner_id;
        try {
            $dtData->name = $this->request->name_representative;
            $dtData->phone = $this->request->phone_representative;
            $dtData->email = $this->request->email_representative;
            $dtData->mst = $this->request->mst_representative;
            if (!empty($this->request->birthday_representative)) {
                $dtData->birthday = to_sql_date($this->request->birthday_representative);
            }
            $dtData->number_cccd = $this->request->number_cccd_representative;
            $dtData->issued_cccd = $this->request->issued_cccd_representative;
            if (!empty($this->request->date_cccd_representative)) {
                $dtData->date_cccd = to_sql_date($this->request->date_cccd_representative);
            }
            if (!empty($this->request->date_end_cccd_representative)) {
                $dtData->date_end_cccd = to_sql_date($this->request->date_end_cccd_representative);
            }
            $dtData->type = $this->request->type_representative;
            $dtData->customer_id = $customer_id;

            $dtData->save();
            if ($dtData) {
                if (!empty($dtData->image_cccd)) {
                    foreach ($dtData->image_cccd as $image) {
                        if (!in_array($image['image'], $image_cccd_old)) {
                            $this->deleteFile($image['image']);
                            $image_cccd = PartnerImage::where('partner_representative', $dtData->id)->where('image', $image['image'])->where('type',1)->first();
                            if (!empty($image_cccd)) {
                                $image_cccd->delete();
                            }
                        }
                    }
                }

                if (!empty($dtData->image_kd)) {
                    foreach ($dtData->image_kd as $image) {
                        if (!in_array($image['image'], $image_kd_old)) {
                            $this->deleteFile($image['image']);
                            $image_kd = PartnerImage::where('partner_representative', $dtData->id)->where('image', $image['image'])->where('type',2)->first();
                            if (!empty($image_kd)) {
                                $image_kd->delete();
                            }
                        }
                    }
                }

                if ($this->request->hasFile('image_cccd')) {
                    if (is_array($this->request->file('image_cccd'))) {
                        foreach ($this->request->file('image_cccd') as $file) {
                            $image_cccd = new PartnerImage();
                            $path = $this->UploadFile($file, 'partner/' . $dtData->id, 800, 600,false);
                            $image_cccd->image = $path;
                            $image_cccd->partner_representative = $dtData->id;
                            $image_cccd->customer_id = $customer_id;
                            $image_cccd->type = 1;
                            $image_cccd->save();
                        }
                    }
                }

                if ($this->request->hasFile('image_kd')) {
                    if (is_array($this->request->file('image_kd'))) {
                        foreach ($this->request->file('image_kd') as $file) {
                            $image_kd= new PartnerImage();
                            $path = $this->UploadFile($file, 'partner/' . $dtData->id, 800, 600,false);
                            $image_kd->image = $path;
                            $image_kd->partner_representative = $dtData->id;
                            $image_kd->customer_id = $customer_id;
                            $image_kd->type = 2;
                            $image_kd->save();
                        }
                    }
                }
                DB::commit();
                $data['result'] = true;
                if (empty($id)){
                    $data['message'] = 'Thêm thành công';
                } else {
                    $data['message'] = 'Cập nhập thành công';
                }
            } else {
                $data['result'] = false;
                if (empty($id)){
                    $data['message'] = 'Thêm thất bại';
                } else {
                    $data['message'] = 'Cập nhập thất bại';
                }
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
