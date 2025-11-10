<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Clients;
use App\Models\CustomerPackage;
use App\Models\CustomerPackageHistory;
use App\Models\HistoryCustomerMemberShipLevel;
use App\Models\Package;
use App\Models\PartnerImage;
use App\Models\PartnerRepresentative;
use App\Models\Payment;
use App\Models\ReferralLevel;
use App\Models\TransactionPackage;
use App\Services\NotiService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\ServiceService;
use App\Services\AdminService;
use Illuminate\Support\Facades\Validator;
use function Aws\map;

class ClientController extends AuthController
{
    protected $fnbService;
    protected $fnbAdmin;
    protected $fnbNoti;
    use UploadFile;

    public function __construct(Request $request, ServiceService $ServiceService, AdminService $AdminService,NotiService $NotiService)
    {
        parent::__construct($request);
        DB::enableQueryLog();

        $this->fnbService = $ServiceService;
        $this->fnbAdmin = $AdminService;
        $this->fnbNoti = $NotiService;
    }

    public function getListCustomer()
    {
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        if ($length == -1){
            $length = 100000;
        }

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $type_client_search = $this->request->input('type_client_search') ?? 0;
        $active_search = $this->request->input('active_search') ?? -1;
        $date_search = $this->request->input('date_search') ?? null;
        $type_client = $this->request->input('type_client') ?? 1;
        $package_search = $this->request->input('package_search') ?? 0;
        $staff_search = $this->request->input('staff_search') ?? 0;


        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $aresPer = $this->request->input('aresPer') ?? 0;//tạm không dùng nưữa
            $user_id = $this->request->input('user_id') ?? 0;
        }

        $query = Clients::with([
            'customer_package' => function ($q) {
                $q->select('id', 'package_id', 'customer_id', 'name');
                $q->with([
                    'package' => function ($qr) {
                        $qr->select('id', 'name', 'check_default');
                    }
                ]);
            }
        ])
            ->with([
                'referral_level' => function ($q) {
                    $q->select('id', 'parent_id', 'customer_id', 'referral_code');
                    $q->with([
                        'parent' => function ($qr) {
                            $qr->select('id', 'fullname', 'email','phone','avatar','type_client');
                        }
                    ]);
                }
            ])
            ->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('referral_code', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
                $q->orWhereHas('referral_level', function ($qr) use ($search) {
                    $qr->where('referral_code', 'like', "%$search%");
                });
            });
        }
        if (!empty($package_search)) {
            $query->whereHas('customer_package', function ($q) use ($package_search) {
                $q->where('package_id', $package_search);
            });
        }
        if ($this->request->input('ares_search')) {
            $dataAres = $this->fnbService->getWardsWhereAres($this->request, $this->request->input('ares_search'));
            if (!empty($dataAres)) {
                $WardSearch = $dataAres->getData(true);
                if (!empty($WardSearch['result'])) {
                    if (!empty($WardSearch['data'])) {
                        $query->whereIn('wards_id', $WardSearch['data']);
                    }
                }
            }
        }
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereIn('wards_id', $ListWard['data']);
                    } else {
                        $query->where('tbl_clients.id', 0);
                    }
                } else {
                    $query->where('tbl_clients.id', 0);
                }
            }
        }

        if (($type_client_search)) {
            $query->where('type_client', $type_client_search);
        }
        if ($active_search != -1 && $active_search != '') {
            $query->where('active', $active_search);
        }
        if (!empty($staff_search)){
            $query->where('staff_id', $staff_search);
        }
        $query->where('type_client', $type_client);

        if (!empty($date_search)) {
            $date_search = explode(' - ', $date_search);
            $start_date = to_sql_date($date_search[0] . ' 00:00:00', true);
            $end_date = to_sql_date($date_search[1] . ' 23:59:59', true);
            $query->whereBetween('tbl_clients.created_at', [$start_date, $end_date]);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtImage = !empty($value->avatar) ? env('STORAGE_URL') . '/' . $value->avatar : null;
                $data[$key]['avatar'] = $dtImage;
                $count_member = count(array_diff(getDataTreeReferralLevel($value->id), [$value->id]));
                $data[$key]['count_member'] = $count_member;
            }
        }
        $query = Clients::where('id', '!=', 0);
        $query->where('type_client', $type_client);
        $total = $query->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function countAll()
    {
        $type_client_search = $this->request->input('type_client_search') ?? 0;
        $active_search = $this->request->input('active_search') ?? -1;
        $date_search = $this->request->input('date_search') ?? null;

        if (!empty($date_search)) {
            $date_search = explode(' - ', $date_search);
            $start_date = to_sql_date($date_search[0] . ' 00:00:00', true);
            $end_date = to_sql_date($date_search[1] . ' 23:59:59', true);
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

        $query = Clients::where('id', '!=', 0);

        if (($type_client_search)) {
            $query->where('type_client', $type_client_search);
        }
        if ($active_search != -1 && $active_search != '') {
            $query->where('active', $active_search);
        }
        if (!empty($date_search)) {
            $query->whereBetween('tbl_clients.created_at', [$start_date, $end_date]);
        }
        $totalAll = $query->count();

        foreach ($arrType as $key => $value) {
            $type_client = $value['id'];
            $query = Clients::where('id', '!=', 0);
            $query->where('type_client', $type_client);
            if ($active_search != -1 && $active_search != '') {
                $query->where('active', $active_search);
            }
            if (!empty($date_search)) {
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

    public function getDetailCustomer()
    {
        $id = $this->request->input('id') ?? 0;
        $noti = $this->request->input('noti') ?? 0;

        $ares_permission = (int)($this->request->input('ares_permission') ?? 0);
        $query = Clients::with(['representative', 'image_cccd', 'image_kd']);
        if ($ares_permission) {
            $user_id = $this->request->input('user_id');
            if (!empty($user_id)) {
                $ListWard = $this->fnbAdmin->getWardUser($this->request);
                $hasResult = !empty($ListWard['result']);
                $wards = !empty($ListWard['data']) ? $ListWard['data'] : [];
                if ($hasResult && !empty($wards)) {
                    $query->whereIn('wards_id', $wards);
                } else {
                    $query->where('tbl_clients.id', 0);
                }
            } else {
                $query->where('tbl_clients.id', 0);
            }
        }
        $query->with([
            'referral_level' => function ($q) {
                $q->select('id', 'parent_id', 'customer_id', 'referral_code');
                $q->with([
                    'parent' => function ($qr) {
                        $qr->select('id', 'fullname', 'email','phone','avatar','type_client');
                    }
                ]);
            }
        ]);
        $client = $query->find($id);
        if (empty($client)) {
            $data['result'] = false;
            $data['client'] = null;
            $data['message'] = 'Lấy thông tin khách hàng thành công';
            return response()->json($data);
        }
        if (!empty($client)) {
            $dtImage = !empty($client->avatar) ? env('STORAGE_URL') . '/' . $client->avatar : null;
            $client->avatar = $dtImage;

            $client->image_cccd->map(function ($item) {
                return $item->image_new = !empty($item->image) ? env('STORAGE_URL') . '/' . $item->image : null;;
            });
            $client->image_kd->map(function ($item) {
                return $item->image_new = !empty($item->image) ? env('STORAGE_URL') . '/' . $item->image : null;;
            });
        }
        $data['result'] = true;
        $data['client'] = $client;
        if (!empty($client->representative)){
            $dtImage = !empty($client->representative->image) ? env('STORAGE_URL') . '/' . $client->representative->image : null;
            $client->representative->image = $dtImage;
            $dtImageQr = !empty($client->representative->account_image) ? env('STORAGE_URL') . '/' . $client->representative->account_image : null;
            $client->representative->account_image = $dtImageQr;
        }
        if (!empty($client->province_id)) {
            if ($this->request->client == null) {
                $this->request->client = (object)['token' => Config::get('constant')['token_default']];
            }
            $this->request->merge([
                'id' => $client->province_id,
            ]);
            $data_province = $this->fnbService->getProvice($this->request);
            if (!empty($data_province->getData(true)['result'])) {
                $isProvince = $data_province->getData(true)['data'];
                if (!empty($isProvince[0])) {
                    $client->province = $isProvince[0];
                }
            }
            if (!empty($client->wards_id)) {
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
                if (!empty($data_wards->getData(true)['result'])) {
                    $isWards = $data_wards->getData(true)['data'];
                    if (!empty($isWards[0])) {
                        $client->wards = $isWards[0];
                    }
                }
            }
        }

        if (!empty($client->staff_id)){

            $this->requestStaff = new Request();
            $this->requestStaff->merge(['id' => $client->staff_id]);
            $dtStaff = $this->fnbAdmin->getListStaff($this->requestStaff);
            $dtStaff = $dtStaff['data'][0] ?? [];
            if (!empty($dtStaff)){
                $client->staff = $dtStaff;
            }
        }

        if (!empty($noti)){
            $arr_object_id = [];
            $dtCustomer = Clients::select(
                'tbl_clients.fullname as name',
                'tbl_clients.id as object_id',
                'tbl_player_id.player_id as player_id',
                DB::raw("'owen' as 'object_type'")
            )
                ->leftJoin('tbl_player_id', function ($join) {
                    $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                    $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                })
                ->where('tbl_clients.id', $id)
                ->get()->toArray();
            if (!empty($dtCustomer)) {
                $arr_object_id = array_merge($arr_object_id, $dtCustomer);
            }
            $arr_object_id = array_values($arr_object_id);
            $client->arr_object_id = $arr_object_id;
        }

        $storageUrl = config('app.storage_url');
        $arrId = getDataTreeReferralLevel($id);
        $dataReferralLevel = ReferralLevel::select('id','customer_id','parent_id','referral_code')
            ->with(['customer' => function ($query) use($storageUrl) {
                $query->select('id', 'fullname', 'code','phone','email','type_client', DB::raw("IF(avatar IS NOT NULL, CONCAT('$storageUrl/', avatar), NULL) as avatar"));
            }])->whereIn('customer_id',$arrId)->get();
        $newElement = new ReferralLevel();
        $newElement->customer_id = $id;
        $newElement->parent_id = 0;
        $newElement->referral_code = null;
        $dataReferralLevel->prepend($newElement);
        $newElement->load(['customer' => function ($query) use($storageUrl) {
            $query->select('id', 'fullname', 'code','phone','email','type_client', DB::raw("IF(avatar IS NOT NULL, CONCAT('$storageUrl/', avatar), NULL) as avatar"));
        }]);
        $dtReferralLevelValue = get_parent_id_referral_level_new($dataReferralLevel);
        $dtReferralLevel = collect(Arr::pluck($dtReferralLevelValue,'level'));
        $level = $dtReferralLevel->max();
        $countMember = ($dtReferralLevel->count()) - 1;
        $data['referral'] = [
            'level' => $level,
            'count_member' => $countMember,
            'data' => $dtReferralLevelValue
        ];

        $data['message'] = 'Lấy thông tin khách hàng thành công';
        return response()->json($data);
    }

    public function detail()
    {
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        if (empty($client)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn tại khách hàng';
            return response()->json($data);
        }
        $ClientRules = [];
        if (filled($this->request->email)) {
            $ClientRules['email'] = 'unique:tbl_clients,email,' . $id;
        }
        if (filled($this->request->phone)) {
            $ClientRules['phone'] = 'unique:tbl_clients,phone,' . $id;
        }
        $clientMessages = [
            'email.unique' => 'Email người đã tồn tại',
            'phone.unique' => 'Số điện thoại đã tồn tại',
        ];
        $validatorClient = Validator::make($this->request->all(), $ClientRules, $clientMessages);
        if ($validatorClient->fails()) {
            $data['result'] = false;
            $data['message'] = $validatorClient->errors()->all()[0];
            echo json_encode($data);
            die();
        }

        $membership_level = $client->membership_level;

        $staff_id_old = $client->staff_id;

        DB::beginTransaction();
        try {
            $client->fullname = $this->request->fullname;
            $client->phone = $this->request->phone ?? null;
            $client->email = $this->request->email ?? null;
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

            $client->province_id = $this->request->province_id ?? 0;
            $client->wards_id = $this->request->wards_id ?? 0;

            $client->active_limit_private = $this->request->active_limit_private ?? 0;
            if ($client->active_limit_private == 1) {
                $client->invoice_limit_private = $this->request->invoice_limit_private;
                $client->radio_discount_private = $this->request->radio_discount_private;
            } else {
                $client->radio_discount_private = null;
                $client->invoice_limit_private = null;
            }
            $client->membership_level = $this->request->membership_level ?? 0;
            $client->staff_id = $this->request->staff_id ?? 0;
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
                if ($membership_level != $this->request->membership_level){
                    $dataLevelData = $this->fnbAdmin->getMemberShipLevel($membership_level);
                    $dtDataMemberShipOld = null;
                    if (!empty($dataLevelData['result'])) {
                        $dtDataMemberShipOld = $dataLevelData['data'][0] ?? null;
                    }
                    $history = new HistoryCustomerMemberShipLevel();
                    $history->customer_id = $client->id;
                    $history->membership_level_id = $membership_level;
                    $history->name = $dtDataMemberShipOld['name'] ?? null;
                    $history->radio_discount = $dtDataMemberShipOld['radio_discount'] ?? 0;
                    $history->invoice_limit = $dtDataMemberShipOld['invoice_limit'] ?? 0;
                    $history->created_by = !empty($this->request->client) ? $this->request->client->id : 0;
                    $history->save();

                    $client->ranking_date = date('Y-m-d H:i:s');
                    $client->admin_membership = 1;
                    $client->save();
                }

                //update lại nhân viên cskh
                if ($client->type_client == 2){
                    if ($staff_id_old != $this->request->staff_id){
                        $arrId = array_diff(getDataTreeReferralLevel($client->id,'all'));
                        $dtData = Clients::whereIn('id',$arrId)->get();
                        if (!empty($dtData)){
                            foreach ($dtData as $item){
                                $item->staff_id = $this->request->staff_id;
                                $item->save();
                            }
                        }
                    }
                }

                DB::commit();
                $data['result'] = true;
                $data['message'] = 'Cập nhật thành công';
            } else {
                $data['result'] = false;
                $data['message'] = 'Cập nhật thất bại';
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function deleteCustomer()
    {
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        if (empty($client)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn tại khách hàng';
            return response()->json($data);
        }
        if (count($client->referral_level_child) > 0){
            $data['result'] = false;
            $data['message'] = 'Thành viên có thành viên cấp dưới, không thể xóa';
            return response()->json($data);
        }
        $dtReferal = ReferralLevel::where('customer_id',$id)->first();
        DB::beginTransaction();
        try {
            $client->delete();
            $client->referral_level()->delete();
            DB::table('tbl_session_login')->where('id_client', $id)->delete();

            //cập nhập lại stt khi xóa
            if(!empty($dtReferal)){
                $dtData = DB::table('tbl_referral_level')
                    ->where('parent_id','=',$dtReferal->parent_id)
                    ->orderBy('created_at','asc')
                    ->get();
                $stt = 1;
                $arrUpdate = [];
                if (!empty($dtData)){
                    foreach ($dtData as $key => $value){
                        $arrUpdate[] = [
                            'id' => $value->id,
                            'stt' => $stt
                        ];
                        $stt ++;
                    }
                }
                if (!empty($arrUpdate)){
                    $index = 'id';
                    ReferralLevel::batchUpdate($arrUpdate,$index);
                }
            }

            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('c_delete_true');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function active()
    {
        $id = $this->request->input('id') ?? 0;
        $client = Clients::find($id);
        DB::beginTransaction();
        try {
            $client->active = $client->active == 0 ? 1 : 0;
            $client->date_exec = date('Y-m-d H:i:s');
            $client->save();
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListData()
    {
        $type_client = $this->request->input('type_client') ?? null;
        $search = $this->request->input('search') ?? null;
        $limit = $this->request->input('limit') ?? 50;
        $id = $this->request->input('customer_id') ?? [];
        $query = Clients::with('representative')
            ->select('id', 'fullname', 'phone', 'avatar', 'email','type_client')
            ->where('active', 1)
            ->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%");
                $q->orWhere('phone', 'like', "%$search%");
            });
        }
        if (!empty($id)) {
            $query->whereIn('id', $id);
        }
        if (!empty($type_client)){
            $query->where('type_client', $type_client);
        }
        $data = $query->limit($limit)->get();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtImage = !empty($value->avatar) ? env('STORAGE_URL') . '/' . $value->avatar : null;
                $data[$key]['avatar'] = $dtImage;
            }
        }
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListDataRepresentative()
    {
        $search = $this->request->input('search') ?? null;
        $limit = $this->request->input('limit') ?? 50;
        $id = $this->request->input('customer_id') ?? [];
        $query = PartnerRepresentative::select('id', 'name', 'phone', 'image', 'email')
            ->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
                $q->orWhere('phone', 'like', "%$search%");
                $q->orWhere('mst', 'like', "%$search%");
                $q->orWhere('email', 'like', "%$search%");
            });
        }
        if (!empty($id)) {
            $query->whereIn('id', $id);
        }
        $data = $query->limit($limit)->get();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtImage = !empty($value->avatar) ? env('STORAGE_URL') . '/' . $value->image : null;
                $data[$key]['image'] = $dtImage;
            }
        }
        return response()->json([
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function detailRepresentativePartner()
    {
        $id = $this->request->input('id') ?? 0;

        $partnerRules = [
            'name_representative' => 'required',
            'phone_representative' => 'required',
//            'email_representative' => 'required|unique:tbl_partner_representative_info,email,' . $id,
//            'mst_representative' => 'required|unique:tbl_partner_representative_info,mst,' . $id,
            'birthday_representative' => 'required',
            'number_cccd_representative' => 'required',
            'date_cccd_representative' => 'required',
            'date_end_cccd_representative' => 'required',
            'issued_cccd_representative' => 'required',
            'type_representative' => 'required',
            'staff_id_representative' => 'required',
        ];
        $partnerMessages = [
            'name_representative.required' => 'Vui lòng nhập tên người đại diện',
            'phone_representative.required' => 'Vui lòng nhập số điện thoại người đại diện',
//            'email_representative.required' => 'Vui lòng nhập email người đại diện',
//            'email_representative.unique' => 'Email người đại diện đã tồn tại',
//            'mst_representative.required' => 'Vui lòng nhập mã số thuế',
//            'mst_representative.unique' => 'Mã số thuế đã tồn tại',
            'birthday_representative.required' => 'Vui lòng nhập ngày sinh',
            'number_cccd_representative.required' => 'Vui lòng nhập số cccd',
            'date_cccd_representative.required' => 'Vui lòng nhập ngày cấp cccd',
            'date_end_cccd_representative.required' => 'Vui lòng nhập ngày hết hạn cccd',
            'issued_cccd_representative.required' => 'Vui lòng nhập nơi cấp cccd',
            'type_representative.required' => 'Vui lòng chọn loại hình kinh doanh',
            'staff_id_representative.required' => 'Vui lòng chọn nhân viên phụ trách',
        ];
        $validatorPartner = Validator::make($this->request->all(), $partnerRules, $partnerMessages);
        if ($validatorPartner->fails()) {
            $data['result'] = false;
            $data['message'] = $validatorPartner->errors()->all()[0];
            echo json_encode($data);
            die();
        }
        if (empty($id)) {
            $dtData = new PartnerRepresentative();
        } else {
            $dtData = PartnerRepresentative::find($id);
        }
        DB::beginTransaction();
        $image_cccd_old = !empty($this->request->input('image_cccd_old')) ? is_array($this->request->input('image_cccd_old')) ? $this->request->input('image_cccd_old') : json_decode($this->request->input('image_cccd_old')) : [];
        $image_kd_old = !empty($this->request->input('image_kd_old')) ? is_array($this->request->input('image_kd_old')) ? $this->request->input('image_kd_old') : json_decode($this->request->input('image_kd_old')) : [];
        $customer_id = $this->request->partner_id;

        $account_name = $this->request->input('account_name_representative') ?? null;
        $account_number = $this->request->input('account_number_representative') ?? null;
        $account_bank = $this->request->input('account_bank_representative') ?? null;
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
            $dtData->account_name = $account_name;
            $dtData->account_number = $account_number;
            $dtData->account_bank = $account_bank;

            $dtData->save();
            if ($dtData) {
                if (!empty($dtData->image_kd)) {
                    foreach ($dtData->image_kd as $image) {
                        if (!in_array($image['image'], $image_kd_old)) {
                            $this->deleteFile($image['image']);
                            $image_kd = PartnerImage::where('partner_representative', $dtData->id)->where('image',
                                $image['image'])->where('type', 2)->first();
                            if (!empty($image_kd)) {
                                $image_kd->delete();
                            }
                        }
                    }
                }

                if ($this->request->hasFile('image_kd')) {
                    if (is_array($this->request->file('image_kd'))) {
                        foreach ($this->request->file('image_kd') as $file) {
                            $image_kd = new PartnerImage();
                            $path = $this->UploadFile($file, 'partner/' . $dtData->id, 800, 600, false);
                            $image_kd->image = $path;
                            $image_kd->partner_representative = $dtData->id;
                            $image_kd->customer_id = $customer_id;
                            $image_kd->type = 2;
                            $image_kd->save();
                        }
                    }
                }
                if ($this->request->hasFile('image_avatar')) {
                    if (!empty($dtData->image)) {
                        $this->deleteFile($dtData->image);
                    }
                    $path = $this->UploadFile($this->request->file('image_avatar'), 'partner/' . $dtData->id, 70, 70, false);
                    $dtData->image = $path;
                    $dtData->save();
                }

                if ($this->request->hasFile('image_cccd_before')) {
                    $image_cccd_before = PartnerImage::where('partner_representative', $dtData->id)->where('type', 1)->where('type_cccd',1)->first();
                    if (!empty($image_cccd_before)) {
                        $image_cccd_before->delete();
                    }
                    $image_cccd_before = new PartnerImage();
                    $path = $this->UploadFile($this->request->file('image_cccd_before'), 'partner/' . $dtData->id, 800, 600, false);
                    $image_cccd_before->image = $path;
                    $image_cccd_before->partner_representative = $dtData->id;
                    $image_cccd_before->customer_id = $customer_id;
                    $image_cccd_before->type = 1;
                    $image_cccd_before->type_cccd = 1;
                    $image_cccd_before->save();
                }

                if ($this->request->hasFile('image_cccd_after')) {
                    $image_cccd_after = PartnerImage::where('partner_representative', $dtData->id)->where('type', 1)->where('type_cccd',2)->first();
                    if (!empty($image_cccd_after)) {
                        $image_cccd_after->delete();
                    }
                    $image_cccd_after = new PartnerImage();
                    $path = $this->UploadFile($this->request->file('image_cccd_after'), 'partner/' . $dtData->id, 800, 600, false);
                    $image_cccd_after->image = $path;
                    $image_cccd_after->partner_representative = $dtData->id;
                    $image_cccd_after->customer_id = $customer_id;
                    $image_cccd_after->type = 1;
                    $image_cccd_after->type_cccd = 2;
                    $image_cccd_after->save();
                }

                if ($this->request->hasFile('account_image')) {
                    if (!empty($dtData->account_image)) {
                        $this->deleteFile($dtData->account_image);
                    }
                    $path = $this->UploadFile($this->request->file('account_image'), 'partner/' . $dtData->id, 70, 70, false);
                    $dtData->account_image = $path;
                    $dtData->save();
                }

                $client = Clients::find($customer_id);
                $client->staff_id = $this->request->staff_id_representative;
                $client->save();

                DB::commit();
                $data['result'] = true;
                if (empty($id)) {
                    $data['message'] = 'Thêm thành công';
                } else {
                    $data['message'] = 'Cập nhật thành công';
                }
            } else {
                $data['result'] = false;
                if (empty($id)) {
                    $data['message'] = 'Thêm thất bại';
                } else {
                    $data['message'] = 'Cập nhật thất bại';
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

    public function requestPaymentPay2sOld()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $dtData = TransactionPackage::where('customer_id', $customer_id)->where('status', 1)->first();
        if (!empty($dtData)) {
            $this->request->merge(['payment_mode_id' => 0]);
            $this->request->merge(['transaction_package_id' => $dtData->id]);
            $this->request->merge(['amount' => $dtData->grand_total]);
            $this->request->merge(['orderId' => $dtData->id]);
            $this->request->merge(['orderInfo' => str_replace('-', '', $dtData->reference_no)]);
            $this->request->merge(['orderType' => 'pay2s']);
            $this->request->merge([
                'bankAccounts' => [
                    [
                        'account_number' => $this->fnbAdmin->get_option('account_number'),
                        'bank_id' => $this->fnbAdmin->get_option('account_bank_sort'),
                    ]
                ]
            ]);
            $this->request->merge(['requestType' => 'pay2s']);
            $response = $this->fnbAdmin->requestPaymentPay2s($this->request);
            $data = $response->getData(true);
            $dtDataPayment = $data['data'] ?? [];
            $dtData['payUrl'] = $dtDataPayment['payUrl'] ?? null;
        }
        return response()->json([
            'data' => $dtData ?? [],
            'result' => true,
            'message' => 'Lấy thông tin giao dịch thanh toán gói thành công'
        ]);
    }

    public function requestPaymentPay2s()
    {
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $dtData = TransactionPackage::where('customer_id', $customer_id)->where('status', 1)->first();
        if (!empty($dtData)) {
            $dataNew = [
                'bankShortName' => $this->fnbAdmin->get_option('account_bank_sort'),
                'accountNumber' => $this->fnbAdmin->get_option('account_number'),
                'accountName' => $this->fnbAdmin->get_option('account_name'),
                'amount' => $dtData['grand_total'],
                'memo' => $dtData['reference_no'],
            ];
            $result = createQrBank($dataNew);
            $image = null;
            if ($result->status == true) {
                $image = $result->data;
            }
            $dtData->payUrl = $image;
        }
        return response()->json([
            'data' => $dtData ?? [],
            'result' => true,
            'message' => 'Lấy thông tin giao dịch thanh toán gói thành công'
        ]);
    }

    public function updateTypeClient(){
        $id = $this->request->input('id') ?? 0;
        $delete = $this->request->input('delete') ?? 0;
        $type_client = $this->request->input('type_client') ?? 1;
        $client = Clients::find($id);
        if (empty($client)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn tại khách hàng';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $client->type_client = $type_client;
            $client->save();
            if ($type_client == 1 && !empty($delete)){
                $representative = $client->representative;
                if (!empty($representative)) {
                    $representative->delete();
                    if (!empty($representative->image)) {
                        $this->deleteFile($representative->image);
                    }
                    if (!empty($representative->account_image)) {
                        $this->deleteFile($representative->account_image);
                    }
                    $representative->image_cccd()->delete();
                    $representative->image_kd()->delete();
                }
            }
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }


    public function updateBankPartnerRepresentative(){
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (empty($customer_id)){
            $data['result'] = false;
            $data['message'] = 'Vui lòng đăng nhập để sử dụng dịch vụ!';
            return response()->json($data);
        }
        $dtClient = Clients::find($customer_id);
        $account_name = $this->request->input('account_name') ?? null;
        $account_number = $this->request->input('account_number') ?? null;
        $account_bank = $this->request->input('account_bank') ?? null;

        $representative = $dtClient->representative;
        if (empty($representative)){
            $data['result'] = false;
            $data['message'] = 'Khách hàng chưa có người đại diện, vui lòng liên hệ quản trị viên để được hỗ trợ!';
            return response()->json($data);
        }
        $dtData = PartnerRepresentative::find($representative->id);
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = 'Khách hàng chưa có người đại diện, vui lòng liên hệ quản trị viên để được hỗ trợ!';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $dtData->account_name = $account_name;
            $dtData->account_number = $account_number;
            $dtData->account_bank = $account_bank;
            $dtData->save();

            if ($this->request->hasFile('account_image')) {
                if (!empty($dtData->account_image)) {
                    $this->deleteFile($dtData->account_image);
                }
                $path = $this->UploadFile($this->request->file('account_image'), 'partner/' . $dtData->id, 70, 70, false);
                $dtData->account_image = $path;
                $dtData->save();
            }

            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('Thêm thông tin ngân hàng thành công!');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function cronUpgradeMemberShip(){
        $time_start = $this->fnbAdmin->get_option('hour_start_membership');
        $time_end = date('H:i',strtotime($time_start.' +2 hour'));
        $start = [''.date('Y').'-01-01',''.date('Y').'-04-01',''.date('Y').'-07-01',''.date('Y').'-10-01'];
        $date_check = date('Y-m-d');
        $date_new = date('Y-m-d',strtotime($date_check.' -1 day'));
        $run = false;
        if (in_array($date_check, $start)){
            $date = $date_new;
            $run = true;
        } else {
            $date = date('Y-m-d');
        }
        $total_minimum_cost_quarter = $this->fnbAdmin->get_option('total_minimum_cost_quarter');
        $arrDate = getQuarterInfoFromDate($date);
        $date_start = $arrDate['start_date'] ?? $date;
        $end_date = $arrDate['end_date'] ?? $date;

        $start_date = $date_start.' 00:00:00';
        $end_date = $end_date.' 23:59:59';

        $listMembershipExpense = $this->fnbAdmin->getLisMembershipExpense($this->request);
        $listMembershipExpense = $listMembershipExpense->getData(true);
        $listMembershipExpense = $listMembershipExpense['data']['data'] ?? [];

        $listMembershipPurchases = $this->fnbAdmin->getLisMembershipPurchases($this->request);
        $listMembershipPurchases = $listMembershipPurchases->getData(true);
        $listMembershipPurchases = $listMembershipPurchases['data']['data'] ?? [];

        $listMembershipLongTerm = $this->fnbAdmin->getLisMembershipLongTerm($this->request);
        $listMembershipLongTerm = $listMembershipLongTerm->getData(true);
        $listMembershipLongTerm = $listMembershipLongTerm['data']['data'] ?? [];

        $listMembershipLevel= $this->fnbAdmin->getMemberShipLevel();
        $listMembershipLevel = $listMembershipLevel['data'] ?? [];
        $count = 0;
        $limit = 50;
        if (!empty($run)){
            if (strtotime(date('H:i')) >= strtotime($time_start) && strtotime(date('H:i')) <= strtotime($time_end)) {
                //chỉ lấy những user nếu hạng thành viên lớn hơn 1 thì lấy user đăng ký sau 45 ngày còn lại lấy user đăng ký sau 1 ngày
                $dtClients = Clients::where(function ($query) use($date){
                    $query->where('active', 1);
                    $query->where(function ($q) use($date){
                        $q->whereNull('date_run_membership');
                        $q->orWhere('date_run_membership', '!=', $date);
                    });
                    $query->where(function ($q) use ($date) {
                        $q->where(function ($sub) use ($date) {
                            $sub->where('membership_level', '>', 1)
                                ->whereRaw('TIMESTAMPDIFF(DAY, created_at, ?) > 45', [$date]);
                        })
                            ->orWhere(function ($sub) use ($date) {
                                $sub->where('membership_level', '<=', 1)
                                    ->whereRaw('TIMESTAMPDIFF(DAY, created_at, ?) >= 0', [$date]);
                            });
                    });

                })->limit($limit)->get();
                if (!empty($dtClients)){
                    foreach ($dtClients as $key => $value){
                        $dtClient = $value;
                        $membership_level = $dtClient->membership_level;
                        $dataLevelData = $this->fnbAdmin->getMemberShipLevel($membership_level);
                        $dtDataMemberShipOld = null;
                        if (!empty($dataLevelData['result'])) {
                            $dtDataMemberShipOld = $dataLevelData['data'][0] ?? null;
                        }
                        $customer_id = $value->id;

                        $arr_object_id = [];
                        $dtPartner = Clients::select(
                            'tbl_clients.fullname as name',
                            'tbl_clients.id as object_id',
                            'tbl_player_id.player_id as player_id',
                            DB::raw("'customer' as 'object_type'")
                        )
                            ->leftJoin('tbl_player_id', function ($join) {
                                $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                                $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                            })
                            ->where('tbl_clients.id', $customer_id)
                            ->get()->toArray();
                        if (!empty($dtPartner)) {
                            $arr_object_id = array_merge($arr_object_id, $dtPartner);
                        }

                        $arr_object_id = array_values($arr_object_id);

                        $point = 0;
                        $pointPayment = 0;
                        $pointCountPurchase = 0;
                        $pointCountLongTerm = 0;

                        $totalPayment = Payment::where(function ($q) use($customer_id, $start_date, $end_date){
                            $q->where('customer_id', $customer_id);
                            $q->where('status', 2);
                            if (!empty($start_date) && !empty($end_date)){
                                $q->whereBetween('date', [$start_date, $end_date]);
                            }
                        })->sum('payment');

                        if (!empty($listMembershipExpense)){
                            foreach ($listMembershipExpense as $item){
                                if(empty($item['money_end'])){
                                    if ($totalPayment >= $item['money_start']){
                                        $point += $item['point'] ?? 0;
                                        $pointPayment = $item['point'] ?? 0;
                                    }
                                } else {
                                    if ($totalPayment >= $item['money_start'] && $totalPayment < $item['money_end']){
                                        $point += $item['point'] ?? 0;
                                        $pointPayment = $item['point'] ?? 0;
                                    }
                                }
                            }
                        }

                        $countPayment = Payment::where(function ($q) use($customer_id, $start_date, $end_date){
                            $q->where('customer_id', $customer_id);
                            $q->where('status', 2);
                            if (!empty($start_date) && !empty($end_date)){
                                $q->whereBetween('date', [$start_date, $end_date]);
                            }
                        })->count();
                        if (!empty($listMembershipPurchases)){
                            foreach ($listMembershipPurchases as $item){
                                if(empty($item['number_purchases_end'])){
                                    if ($countPayment >= $item['number_purchases_start']){
                                        $point += $item['point'] ?? 0;
                                        $pointCountPurchase = $item['point'] ?? 0;
                                    }
                                } else {
                                    if ($countPayment >= $item['number_purchases_start'] && $countPayment < $item['number_purchases_end']){
                                        $point += $item['point'] ?? 0;
                                        $pointCountPurchase = $item['point'] ?? 0;
                                    }
                                }
                            }
                        }

                        $totalPointReferral = DB::table('tbl_client_point_month')->where(function ($q) use($customer_id, $date_start, $end_date){
                            $q->where('customer_id', $customer_id);
                            if (!empty($date_start) && !empty($end_date)){
                                $end_date_new = date('Y-m-d', strtotime($end_date));
                                $date_start_new = date('Y-m-d', strtotime($date_start));
                                $q->whereBetween(
                                    DB::raw("STR_TO_DATE(CONCAT(year,'-',LPAD(month,2,'0'),'-01'), '%Y-%m-%d')"),
                                    [$date_start_new, $end_date_new]
                                );
                            }
                        })->sum('point');
                        $point += $totalPointReferral;

                        $totalLongTerm = DB::table('tbl_clients')
                            ->selectRaw('TIMESTAMPDIFF(DAY, created_at, ?) as diff_days', [$date])
                            ->where('id', $customer_id)
                            ->first();
                        $totalLongTerm = !empty($totalLongTerm->diff_days) ? (int)($totalLongTerm->diff_days / 30) : 0;

                        if (!empty($listMembershipLongTerm)){
                            foreach ($listMembershipLongTerm as $item){
                                if(empty($item['month_end'])){
                                    if ($totalLongTerm >= $item['month_start']){
                                        $point += $item['point'] ?? 0;
                                        $pointCountLongTerm = $item['point'] ?? 0;
                                    }
                                } else {
                                    if ($totalLongTerm >= $item['month_start'] && $totalLongTerm < $item['month_end']){
                                        $point += $item['point'] ?? 0;
                                        $pointCountLongTerm = $item['point'] ?? 0;
                                    }
                                }
                            }
                        }

                        DB::beginTransaction();
                        try {
                            //lưu lịch sử tính điểm
                            DB::table('tbl_history_calculate_upgrade_point')->insert([
                                'date_start' => $date_start,
                                'date_end' => $end_date,
                                'customer_id' => $customer_id,
                                'totalPayment' => $totalPayment,
                                'countPayment' => $countPayment,
                                'totalLongTerm' => $totalLongTerm,
                                'pointPayment' => $pointPayment,
                                'pointCountPurchase' => $pointCountPurchase,
                                'pointCountLongTerm' => $pointCountLongTerm,
                                'point' => $point,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                            //nếu mức chi tiêu trong quý mà ít hơn mức tối thiểu quy định thì hạ bậc
                            if ($totalPayment < $total_minimum_cost_quarter){
                                if ($membership_level != 0){
                                    if ($membership_level > 1){
                                        $membership_level = $membership_level - 1;
                                    }

                                    if (!empty($pointPayment)){
                                        changePoint(0, 'point_payment',$this->request->input('staff_status') ?? 0,$customer_id,$pointPayment,$date);
                                    }

                                    if (!empty($pointCountPurchase)){
                                        changePoint(0, 'point_purchase',$this->request->input('staff_status') ?? 0,$customer_id,$pointCountPurchase,$date);
                                    }

                                    if (!empty($pointCountLongTerm)){
                                        changePoint(0, 'point_long_term',$this->request->input('staff_status') ?? 0,$customer_id,$pointCountLongTerm,$date);
                                    }

                                    $dtClient->membership_level = $membership_level;
                                    $dtClient->ranking_date = date('Y-m-d H:i:s');
                                    $dtClient->date_run_membership = $date;
                                    $dtClient->save();

                                    //noti thông báo rớt hạng
//                                    $dataLevelData = $this->fnbAdmin->getMemberShipLevel($membership_level);
//                                    $dtDataMemberShip = null;
//                                    if (!empty($dataLevelData['result'])) {
//                                        $dtDataMemberShip = $dataLevelData['data'][0] ?? null;
//                                    }
//                                    $this->requestNoti = clone $this->request;
//                                    $this->requestNoti->merge(['type_noti' => 'noti_upgrade_membership']);
//                                    $this->requestNoti->merge(['type_check' => 1]);
//                                    $this->requestNoti->merge(['arr_object_id' => $arr_object_id]);
//                                    $this->requestNoti->merge(['dtData' => $dtDataMemberShip]);
//                                    $this->requestNoti->merge(['customer_id' => $customer_id]);
//                                    $this->requestNoti->merge(['type' => 'staff']);
//                                    $this->requestNoti->merge(['staff_id' => $this->request->input('staff_status') ?? 0]);
//                                    $this->fnbNoti->addNoti($this->requestNoti);
                                    //end

                                    //luu lịch sử thăng , hạ hạng
                                    DB::table('tbl_log_upgrade_client')->insert([
                                        'customer_id' => $customer_id,
                                        'date_start' => $date_start,
                                        'date_end' => $end_date,
                                        'type' => 1,
                                        'created_at' => date('Y-m-d H:i:s'),
                                    ]);

                                    $point_reset = $point;
                                    if (!empty($point_reset)){
                                        changePoint(0, 'reset_point',$this->request->input('staff_status') ?? 0,$customer_id,$point_reset,$date);
                                    }

                                    $historyMembership = new HistoryCustomerMemberShipLevel();
                                    $historyMembership->customer_id = $customer_id;
                                    $historyMembership->membership_level_id = $membership_level;
                                    $historyMembership->name = $dtDataMemberShipOld['name'] ?? null;
                                    $historyMembership->radio_discount = $dtDataMemberShipOld['radio_discount'] ?? 0;
                                    $historyMembership->invoice_limit = $dtDataMemberShipOld['invoice_limit'] ?? 0;
                                    $historyMembership->total_payment = $totalPayment;
                                    $historyMembership->check_minimum_cost_quarter = 1;
                                    $historyMembership->save();
                                }
                            } else {
                                $membership_level_id = $membership_level;
                                if (!empty($listMembershipLevel)){
                                    foreach ($listMembershipLevel as $item){
                                        if(empty($item['point_end'])){
                                            if ($point >= $item['point_start']){
                                                $membership_level_id = $item['id'];
                                            }
                                        } else {
                                            if ($point >= $item['point_start'] && $point < $item['point_end']){
                                                $membership_level_id = $item['id'];
                                            }
                                        }
                                    }
                                }

                                if (!empty($pointPayment)){
                                    changePoint(0, 'point_payment',$this->request->input('staff_status') ?? 0,$customer_id,$pointPayment,$date);
                                }

                                if (!empty($pointCountPurchase)){
                                    changePoint(0, 'point_purchase',$this->request->input('staff_status') ?? 0,$customer_id,$pointCountPurchase,$date);
                                }

                                if (!empty($pointCountLongTerm)){
                                    changePoint(0, 'point_long_term',$this->request->input('staff_status') ?? 0,$customer_id,$pointCountLongTerm,$date);
                                }

                                $dtClient->membership_level = $membership_level_id;
                                $dtClient->ranking_date = date('Y-m-d H:i:s');
                                $dtClient->date_run_membership = date('Y-m-d');
                                $dtClient->save();

                                //noti thông báo
                                if ($membership_level > $membership_level_id){
                                    $type_check = 1;
                                } else if ($membership_level < $membership_level_id){
                                    $type_check = 2;
                                } else {
                                    $type_check = 3;
                                }
                                //luu lịch sử thăng , hạ hạng
                                DB::table('tbl_log_upgrade_client')->insert([
                                    'customer_id' => $customer_id,
                                    'date_start' => $date_start,
                                    'date_end' => $end_date,
                                    'type' => $type_check,
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);


                                if ($type_check == 2) {
                                    $dataLevelData = $this->fnbAdmin->getMemberShipLevel($membership_level_id);
                                    $dtDataMemberShip = null;
                                    if (!empty($dataLevelData['result'])) {
                                        $dtDataMemberShip = $dataLevelData['data'][0] ?? null;
                                    }
                                    $this->requestNoti = clone $this->request;
                                    $this->requestNoti->merge(['type_noti' => 'noti_upgrade_membership']);
                                    $this->requestNoti->merge(['type_check' => $type_check]);
                                    $this->requestNoti->merge(['arr_object_id' => $arr_object_id]);
                                    $this->requestNoti->merge(['dtData' => $dtDataMemberShip]);
                                    $this->requestNoti->merge(['customer_id' => $customer_id]);
                                    $this->requestNoti->merge(['type' => 'staff']);
                                    $this->requestNoti->merge(['staff_id' => $this->request->input('staff_status') ?? 0]);
                                    $this->fnbNoti->addNoti($this->requestNoti);
                                }
                                //end

                                $point_reset = $point;
                                if (!empty($point_reset)){
                                    changePoint(0, 'reset_point',$this->request->input('staff_status') ?? 0,$customer_id,$point_reset,$date);
                                }

                                $historyMembership = new HistoryCustomerMemberShipLevel();
                                $historyMembership->customer_id = $customer_id;
                                $historyMembership->membership_level_id = $membership_level;
                                $historyMembership->name = $dtDataMemberShipOld['name'] ?? null;
                                $historyMembership->radio_discount = $dtDataMemberShipOld['radio_discount'] ?? 0;
                                $historyMembership->invoice_limit = $dtDataMemberShipOld['invoice_limit'] ?? 0;
                                $historyMembership->total_payment = $totalPayment;
                                $historyMembership->save();
                            }
                            DB::commit();
                            $count ++;
                        } catch (\Exception $exception) {
                            DB::rollBack();
                        }
                    }
                }
            }
        }

        return response()->json([
            'count' => $count,
            'message' => 'Thành công!'
        ]);
    }

    public function getListLogUpgradeClient(){
        $time_start = $this->fnbAdmin->get_option('hour_start_membership');
        $date_number_send_noti_upgrade = $this->fnbAdmin->get_option('date_number_send_noti_upgrade');
        $time_end = date('H:i',strtotime($time_start.' +2 hour'));
        $dtData = [];
        $limit = 50;
        if (strtotime(date('H:i')) >= strtotime($time_start) && strtotime(date('H:i')) <= strtotime($time_end)) {
            $dtData = DB::table('tbl_log_upgrade_client')
                ->where('type', '!=', 2)
                ->where('noti', '=', 0)
                ->whereRaw('DATE(DATE_ADD(created_at, INTERVAL ? DAY)) <= ?', [$date_number_send_noti_upgrade, date('Y-m-d')])
                ->limit($limit)
                ->get();
            if (!empty($dtData)) {
                foreach ($dtData as $key => $item) {
                    $customer_id = $item->customer_id;
                    $arr_object_id = [];
                    $dtCustomer = Clients::select(
                        'tbl_clients.fullname as name',
                        'tbl_clients.id as object_id',
                        'tbl_player_id.player_id as player_id',
                        DB::raw("'customer' as 'object_type'")
                    )
                        ->leftJoin('tbl_player_id', function ($join) {
                            $join->on('tbl_player_id.object_id', '=', 'tbl_clients.id');
                            $join->on('tbl_player_id.object_type', '=', DB::raw("'customer'"));
                        })
                        ->where('tbl_clients.id', $customer_id)
                        ->get()->toArray();
                    if (!empty($dtCustomer)) {
                        $arr_object_id = array_merge($arr_object_id, $dtCustomer);
                    }
                    $arr_object_id = array_values($arr_object_id);
                    $dtData[$key]->arr_object_id = $arr_object_id;
                }
            }
        }
        return response()->json([
            'result' => true,
            'data'  => $dtData,
            'message' => 'Lấy danh sách thành công!'
        ]);
    }

    public function updateLogUpgradeClient(){
        $id = $this->request->input('id') ?? 0;
        $dtData = DB::table('tbl_log_upgrade_client')->where('id', $id)->first();
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = 'Dữ liệu không tồn tại!';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            DB::table('tbl_log_upgrade_client')->where('id', $id)->update([
                'noti' => 1
            ]);
            DB::commit();
            $data['result'] = true;
            $data['message'] = 'Cập nhật thành công!';
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getReportReferral()
    {
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $partner_search = $this->request->input('partner_search') ?? null;
        $customer_search = $this->request->input('customer_search') ?? null;
        $representative_search = $this->request->input('representative_search') ?? null;
        $type_client = $this->request->input('type_client') ?? 1;
        $date_search = $this->request->input('date_search') ?? null;
        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }
        $storageUrl = config('app.storage_url');
        $query = Clients::select(
//            DB::raw('ROW_NUMBER() OVER (PARTITION BY tbl_referral_level.parent_id ORDER BY tbl_referral_level.created_at ASC) as stt'),
            'tbl_referral_level.stt as stt',
            'tbl_clients.id',
            'tbl_clients.fullname',
            'tbl_clients.phone',
            'tbl_clients.type_client',
            'tbl_clients.province_id',
            'tbl_clients.wards_id',
            DB::raw("IF(tbl_clients.avatar IS NOT NULL, CONCAT('$storageUrl/', tbl_clients.avatar), NULL) as avatar"),
            'tbl_partner_representative_info.name as name_representative',
            'tbl_partner_representative_info.phone as phone_representative',
            DB::raw("IF(tbl_partner_representative_info.image IS NOT NULL, CONCAT('$storageUrl/', tbl_partner_representative_info.image), NULL) as avatar_representative"),
            'tbl_referral_level.created_at',
            'customer.id as customer_id',
            'customer.fullname as customer_fullname',
            'customer.phone as customer_phone',
            'customer.type_client as customer_type_client',
            DB::raw("IF(customer.avatar IS NOT NULL, CONCAT('$storageUrl/', customer.avatar), NULL) as customer_avatar"),
            'cp.id as package_id',
            'cp.name as package_name',
            'p.id as pkg_id',
            'p.name as pkg_name',
            'p.check_default'
        );
        $query->leftJoin('tbl_partner_representative_info','tbl_partner_representative_info.customer_id','=','tbl_clients.id');
        $query->join('tbl_referral_level','tbl_referral_level.parent_id','=','tbl_clients.id');
        $query->join('tbl_clients as customer', function($join) {
            $join->on('customer.id','=','tbl_referral_level.customer_id');
        });
        $query->leftJoin('tbl_customer_package as cp','cp.customer_id','=','customer.id');
        $query->leftJoin('tbl_package as p','p.id','=','cp.package_id');
        $query->where('tbl_clients.id', '!=', 0);
        if ($type_client == 2){
            $query->where('tbl_referral_level.level','=',0);
            $query->where('tbl_clients.type_client', 2);
        } else {
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('tbl_referral_level.level','=',1);
                    $sub->whereIn('tbl_clients.type_client', [1,2]);
                });
                $q->orWhere(function ($sub) {
                    $sub->where('tbl_referral_level.level','=',0);
                    $sub->where('tbl_clients.type_client', 1);
                });
            });
        }
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }
        if ($this->request->input('ares_search')) {
            $dataAres = $this->fnbService->getWardsWhereAres($this->request, $this->request->input('ares_search'));
            if (!empty($dataAres)) {
                $WardSearch = $dataAres->getData(true);
                if (!empty($WardSearch['result'])) {
                    if (!empty($WardSearch['data'])) {
                        $query->whereIn('tbl_clients.wards_id', $WardSearch['data']);
                    }
                }
            }
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

        if (!empty($partner_search)){
            $query->where('tbl_clients.id', $partner_search);
        }
        if (!empty($customer_search)){
            $query->where('tbl_clients.id', $customer_search);
        }
        if (!empty($representative_search)){
            $query->where('tbl_partner_representative_info.id', $representative_search);
        }

        if (!empty($date_search)) {
            $date_search = explode(' - ', $date_search);
            $start_date = to_sql_date($date_search[0] . ' 00:00:00', true);
            $end_date = to_sql_date($date_search[1] . ' 23:59:59', true);
            $query->whereBetween('tbl_referral_level.created_at', [$start_date, $end_date]);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();

        $query = Clients::select('id');
        $query->join('tbl_referral_level','tbl_referral_level.parent_id','=','tbl_clients.id');
        $query->join('tbl_clients as customer','customer.id','=','tbl_referral_level.customer_id');
        if ($type_client == 2){
            $query->where('tbl_clients.type_client', 2);
            $query->where('tbl_referral_level.level','=',0);
        } else {
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('tbl_referral_level.level','=',1);
                    $sub->whereIn('tbl_clients.type_client', [1,2]);
                });
                $q->orWhere(function ($sub) {
                    $sub->where('tbl_referral_level.level','=',0);
                    $sub->where('tbl_clients.type_client', 1);
                });
            });
        }
        $total = $query->count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getParentReferralByCustomer(){
        $customer_id = $this->request->input('customer_id') ?? 0;
        $customer_id = is_array($customer_id) ? $customer_id : [$customer_id];
        $storageUrl = config('app.storage_url');
        $dtData = ReferralLevel::with(['parent' => function($query) use ($storageUrl) {
            $query->select('id', 'fullname', 'code','phone','email','type_client', DB::raw("IF(avatar IS NOT NULL, CONCAT('$storageUrl/', avatar), NULL) as avatar"));
            $query->with(['representative' => function($q) use ($storageUrl) {
                $q->select('id', 'customer_id', 'name', 'phone', DB::raw("IF(image IS NOT NULL, CONCAT('$storageUrl/', image), NULL) as image"));
            }]);
        }])
            ->whereIn('customer_id', $customer_id)
            ->get();
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }

    public function addCustomerPackage(){
        $customer_id = $this->request->input('customer_id') ?? 0;
        $dtPackage = Package::where('check_default', 1)->where('type',2)->first();
        if (!empty($dtPackage)) {
            DB::beginTransaction();
            try {
                $dtClient = Clients::find($customer_id);

                $customerPackageOld = $dtClient->customer_package ?? null;
                if (!empty($customerPackageOld)){
                    $customerPackageHistory = new CustomerPackageHistory();
                    $customerPackageHistory->transaction_package_id = $customerPackageOld->transaction_package_id;
                    $customerPackageHistory->package_id = $customerPackageOld->package_id;
                    $customerPackageHistory->customer_id = $customerPackageOld->customer_id;
                    $customerPackageHistory->name = $customerPackageOld->name;
                    $customerPackageHistory->total = $customerPackageOld->total;
                    $customerPackageHistory->percent = $customerPackageOld->percent;
                    $customerPackageHistory->grand_total = $customerPackageOld->grand_total;
                    $customerPackageHistory->number_day = $customerPackageOld->number_day;
                    $customerPackageHistory->check_default = $customerPackageOld->check_default;
                    $customerPackageHistory->save();
                }

                $dtClient->customer_package()->delete();

                $customerPackage = new CustomerPackage();
                $customerPackage->transaction_package_id = 0;
                $customerPackage->package_id = $dtPackage->id;
                $customerPackage->customer_id = $customer_id;
                $customerPackage->name = $dtPackage->name;
                $customerPackage->total = $dtPackage->total;
                $customerPackage->percent = $dtPackage->percent;
                $customerPackage->grand_total = $dtPackage->total - ($dtPackage->total * $dtPackage->percent / 100);
                $customerPackage->number_day = $dtPackage->number_day;
                $customerPackage->check_default = $dtPackage->check_default;
                $customerPackage->save();

                $number_day = $dtPackage->number_day ?? 0;
                $data_active_new = date('Y-m-d',strtotime("+$number_day day",strtotime($dtClient->date_active)));
                $dtClient->date_active = $data_active_new;
                $dtClient->save();

                $data['result'] = true;
                $data['message'] = lang('dt_success');
                DB::commit();
                return response()->json($data);
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

    public function updateDateActive(){
        $customer_id = $this->request->input('id') ?? 0;
        $date_active = $this->request->input('date_active') ?? null;
        if (empty($date_active)){
            $data['result'] = false;
            $data['message'] = 'Ngày hết hạn sử dụng không được để trống!';
            return response()->json($data);
        }
        $dtClient = Clients::find($customer_id);
        if (empty($dtClient)){
            $data['result'] = false;
            $data['message'] = 'Khách hàng không tồn tại!';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $dtClient->date_active = to_sql_date($date_active);
            $dtClient->save();

            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('Cập nhật ngày kích hoạt thành công!');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

}
