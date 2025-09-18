<?php

namespace App\Http\Controllers\Api_app;

use App\Models\Clients;
use App\Models\PartnerImage;
use App\Models\PartnerRepresentative;
use App\Models\TransactionPackage;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\ServiceService;
use App\Services\AdminService;
use Illuminate\Support\Facades\Validator;
use function Aws\map;

class ClientController extends AuthController
{
    protected $fnService;
    protected $fnbAdmin;
    use UploadFile;

    public function __construct(Request $request, ServiceService $ServiceService, AdminService $AdminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();

        $this->fnbService = $ServiceService;
        $this->fnbAdmin = $AdminService;
    }

    public function getListCustomer()
    {
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'asc');

        $type_client_search = $this->request->input('type_client_search') ?? 0;
        $active_search = $this->request->input('active_search') ?? -1;
        $date_search = $this->request->input('date_search') ?? null;
        $type_client = $this->request->input('type_client') ?? 1;
        $package_search = $this->request->input('package_search') ?? 0;


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
        ])->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
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

        $client = $query->find($id);
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
        DB::beginTransaction();
        try {
            $client->delete();
            DB::table('tbl_session_login')->where('id_client', $id)->delete();
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

    public function detailRepresentativePartner()
    {
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


}
