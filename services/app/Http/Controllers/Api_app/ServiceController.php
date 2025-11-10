<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ReviewResource;
use App\Http\Resources\Service as ServiceResource;
use App\Http\Resources\ServiceCollection;
use App\Models\CategoryService;
use App\Models\Province;
use App\Models\ReviewService;
use App\Models\ServiceDay;
use App\Models\Service;
use App\Models\ServiceFavourite;
use App\Models\ServiceImage;
use App\Models\Ward;
use App\Services\NotiService;
use App\Services\PackageService;
use App\Services\TransactionBillService;
use App\Services\TransactionService;
use App\Traits\UploadFile;
use App\Services\AccountService;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\New_;

class ServiceController extends AuthController
{
    use UploadFile;

    public $fnbCustomerService;
    public $fnbAdminService;
    public $fnbNoti;
    public $fnbTransactionBillService;
    public $fnbTransactionService;
    public $fnbPackageService;

    public function __construct(Request $request, AccountService $accountService, AdminService $adminService,NotiService $notiService,TransactionBillService $transactionBillService,TransactionService $transactionService,PackageService $packageService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbCustomerService = $accountService;
        $this->fnbAdminService = $adminService;
        $this->fnbNoti = $notiService;
        $this->fnbTransactionBillService = $transactionBillService;
        $this->fnbTransactionService = $transactionService;
        $this->fnbPackageService = $packageService;
    }

    public function getList()
    {
        $customer_search_value = $this->request->input('customer_search_value') ?? [];
        $group_category_service_search = $this->request->input('group_category_service_search') ?? 0;
        $category_service_search = $this->request->input('category_service_search') ?? 0;
        $customer_search = $this->request->input('customer_search') ?? 0;
        $customer_id = $this->request->input('customer_id') ?? 0;
        $customer_favourite = $this->request->input('customer_favourite') ?? 0;
        $favourite = $this->request->input('favourite') ?? 0;
        $province_search = $this->request->input('province_search') ?? 0;
        $ward_search = $this->request->input('ward_search') ?? 0;
        $search = $this->request->input('search.value');
        $status_search = $this->request->input('status_search');
        $status_search = isset($status_search) ? $status_search : -1;
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        if ($length == -1){
            $length = 100000;
        }
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'desc');

        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }
        $query = Service::with('group_category_service')
            ->with('category_service')
            ->with('province')
            ->with('ward')
            ->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search, $customer_search_value) {
                $q->where('name', 'like', "%$search%");
                $q->orWhereHas('group_category_service', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%$search%");
                });
                $q->orWhereHas('category_service', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%$search%");
                });
                $q->orWhereIn('customer_id', $customer_search_value);
            });
        }
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $this->requestWard = clone $this->request;
                $ListWard = $this->fnbAdminService->getWardUser($this->requestWard);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereIn('tbl_service.wards_id', $ListWard['data']);
                    } else {
                        $query->where('tbl_service.id', 0);
                    }
                } else {
                    $query->where('tbl_service.id', 0);
                }
            }
        }
        if (!empty($customer_search)) {
            $query->where('customer_id', $customer_search);
        }
        if (!empty($customer_id)) {
            $query->where('customer_id', $customer_id);
        }
        if (!empty($favourite)) {
            $query->WhereHas('favourite', function ($q) use ($customer_favourite) {
                $q->where('customer_id', $customer_favourite);
            });
        }
        if (!empty($province_search)) {
            $query->where('province_id', $province_search);
        }
        if (!empty($ward_search)) {
            $query->where('wards_id', $ward_search);
        }
        if ($status_search != -1) {
            $query->where('active', $status_search);
        }
        if (!empty($group_category_service_search)) {
            $query->where('group_category_service_id', $group_category_service_search);
        }
        if (!empty($category_service_search)) {
            $query->where('category_service_id', $category_service_search);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $dtImage = !empty($value->image) ? env('STORAGE_URL') . '/' . $value->image : null;
                $data[$key]['image'] = $dtImage;
            }
        }
        $total = Service::count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getDetail()
    {
        $id = $this->request->input('id') ?? 0;
        $ares_permission = $this->request->input('ares_permission') ?? 0;
        if (!empty($ares_permission)) {
            $user_id = $this->request->input('user_id') ?? 0;
        }
        $query = Service::with(['group_category_service', 'category_service', 'other_amenities', 'province', 'ward', 'day', 'image_store', 'review']);
        if (!empty($ares_permission)) {
            if (!empty($user_id)) {
                $this->requestWard = clone $this->request;
                $ListWard = $this->fnbAdminService->getWardUser($this->requestWard);
                if (!empty($ListWard['result'])) {
                    if (!empty($ListWard['data'])) {
                        $query->whereIn('tbl_service.wards_id', $ListWard['data']);
                    } else {
                        $query->where('tbl_service.id', 0);
                    }
                } else {
                    $query->where('tbl_service.id', 0);
                }
            }
        }
        $dtData = $query->find($id);
        if (!empty($dtData)) {
            $dtImage = !empty($dtData->image) ? env('STORAGE_URL') . '/' . $dtData->image : null;
            $dtData->image = $dtImage;

            $image_store = !empty($dtData->image_store) ? $dtData->image_store : [];
            if (!empty($image_store)) {
                foreach ($image_store as $k => $v) {
                    $name = $v->image;
                    $dtImageStore = !empty($v->image) ? env('STORAGE_URL') . '/' . $v->image : null;
                    $image_store[$k]['image'] = $dtImageStore;
                    $image_store[$k]['name'] = $name;
                }
            }

            $image_menu = !empty($dtData->image_menu) ? $dtData->image_menu : [];
            if (!empty($image_menu)) {
                foreach ($image_menu as $k => $v) {
                    $name = $v->image;
                    $dtImageMenu = !empty($v->image) ? env('STORAGE_URL') . '/' . $v->image : null;
                    $image_menu[$k]['image'] = $dtImageMenu;
                    $image_menu[$k]['name'] = $name;
                }
            }

            $other_amenities = !empty($dtData->other_amenities) ? $dtData->other_amenities : [];
            if (!empty($other_amenities)) {
                foreach ($other_amenities as $k => $v) {
                    $dtImage = !empty($v->image) ? env('STORAGE_URL') . '/' . $v->image : null;
                    $other_amenities[$k]['image'] = $dtImage;
                }
            }
            $category_service = !empty($dtData->category_service) ? $dtData->category_service : null;
            if (!empty($category_service)) {
                $dtIcon = !empty($category_service->icon) ? env('STORAGE_URL') . '/' . $category_service->icon : null;
                $category_service->icon = $dtIcon;
            }

            $dtData->total_star = $dtData->review->avg('star');
        }
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }

    public function detail()
    {

        $app = $this->request->input('app') ?? 0;
        $id = $this->request->input('id') ?? 0;
        $rules = [
            'name' => 'required|unique:tbl_service,name,' . $id,
            'group_category_service_id' => 'required',
            'customer_id' => 'required',
            'category_service_id' => 'required',
            'province_id' => 'required',
            'wards_id' => 'required',
            'phone_number' => 'required',
        ];
        $messages = [
            'name.required' => 'Vui lòng nhập tên gian hàng',
            'name.unique' => 'Tên gian hàng đã tồn tại',
            'group_category_service_id.required' => 'Vui lòng chọn nhóm danh mục',
            'customer_id.required' => 'Vui lòng chọn khách hàng',
            'category_service_id.required' => 'Vui lòng chọn danh mục dịch vụ',
            'province_id.required' => 'Vui lòng chọn tỉnh thành phố',
            'wards_id.required' => 'Vui lòng chọn phường xã',
            'phone_number.required' => 'Vui lòng nhập số điện thoại gian hàng',
        ];
        if (!empty($app)) {
            unset($rules['customer_id']);
            unset($rules['phone_number']);
        } else {
            unset($rules['customer_id']);
            unset($rules['phone_number']);
        }
        $validator = Validator::make($this->request->all(), $rules, $messages);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }

        if ($this->request->hasFile('image_store')) {
            if (count($this->request->file('image_store')) > 6) {
                if (empty($customer_id)) {
                    $data['result'] = false;
                    $data['message'] = 'Chỉ được upload tối đã 5 hình cửa hàng';
                    return response()->json($data);
                }
            }
        }
        if ($this->request->hasFile('image_menu')) {
            if (count($this->request->file('image_menu')) > 10) {
                if (empty($customer_id)) {
                    $data['result'] = false;
                    $data['message'] = 'Chỉ được upload tối đã 10 hình menu';
                    return response()->json($data);
                }
            }
        }

        $step = $this->request->input('step') ?? 1;
        if (!empty($app)) {
            $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
            if (empty($customer_id)) {
                $data['result'] = false;
                $data['message'] = 'Vui lòng đăng nhập trước !';
                return response()->json($data);
            }
            $customer_ids = [$customer_id];
            $this->requestCustomer = clone $this->request;
            $this->requestCustomer->merge(['customer_id' => $customer_ids]);
            $this->requestCustomer->merge(['search' => null]);
            $this->requestCustomer->merge(['partner_id' => $customer_id]);
            $responseCustomer = $this->fnbCustomerService->getListData($this->requestCustomer);
            $dataCustomer = $responseCustomer->getData(true);
            $customers = collect($dataCustomer['data']);
            $dtPartner = $customers->where('id', $customer_id)->first();
            if (empty($dtPartner['representative'])) {
                if ($step >= 3) {
                    $this->requestCustomer->merge(['id' => 0]);
                    $responsePartner = $this->fnbCustomerService->detailRepresentativePartner($this->requestCustomer);
                    $dataPartner = $responsePartner->getData(true);
                    $data['result'] = $dataPartner['result'] ?? false;
                    $data['message'] = $dataPartner['message'] ?? 'Lỗi khi thêm người đại diện';
                    if ($data['result'] == false) {
                        return response()->json($data);
                    }
                }
            }
        } else {
            $customer_id = $this->request->input('customer_id') ?? 0;
            $customer_ids = [$customer_id];
            $this->requestCustomer = clone $this->request;
            $this->requestCustomer->merge(['customer_id' => $customer_ids]);
            $this->requestCustomer->merge(['search' => null]);
            $this->requestCustomer->merge(['partner_id' => $customer_id]);
            $responseCustomer = $this->fnbCustomerService->getListData($this->requestCustomer);
            $dataCustomer = $responseCustomer->getData(true);
            $customers = collect($dataCustomer['data']);
            $dtPartner = $customers->where('id', $customer_id)->first();
        }
        if (empty($id)) {
            $dtData = new Service();
        } else {
            $dtData = Service::find($id);
        }
        $other_amenities = $this->request->input('other_amenities');
        $arrOtherAmenities = [];
        if (!empty($other_amenities)) {
            $other_amenities = trim($other_amenities, ',');
            foreach (explode(',', $other_amenities) as $k => $v) {
                $arrOtherAmenities[] = [
                    'other_amenities_service_id' => $v,
                ];
            }
        }
        $day = !empty($this->request->input('day')) ? is_array($this->request->input('day')) ? $this->request->input('day') : json_decode($this->request->input('day')) : [];
        $type_lunch_break = !empty($this->request->input('type_lunch_break')) ? 1 : 0;
        $hour_start_lunch_break = $this->request->input('hour_start_lunch_break') ?? null;
        $hour_end_lunch_break = $this->request->input('hour_end_lunch_break') ?? null;
        if ($type_lunch_break == 0) {
            $hour_start_lunch_break = null;
            $hour_end_lunch_break = null;
        }
        $image_store_old = !empty($this->request->input('image_store_old')) ? is_array($this->request->input('image_store_old')) ? $this->request->input('image_store_old') : json_decode($this->request->input('image_store_old')) : [];
        $image_menu_old = !empty($this->request->input('image_menu_old')) ? is_array($this->request->input('image_menu_old')) ? $this->request->input('image_menu_old') : json_decode($this->request->input('image_menu_old')) : [];
        DB::beginTransaction();
        try {
            $created_by = !empty($this->request->user) ? $this->request->user->id : $customer_id;
            $type_create = !empty($this->request->user) ? 1 : 2;
            $dtData->name = $this->request->input('name');
            $dtData->group_category_service_id = $this->request->input('group_category_service_id');
            $dtData->customer_id = $customer_id;
            $dtData->category_service_id = $this->request->input('category_service_id');
            $dtData->province_id = $this->request->input('province_id');
            $dtData->wards_id = $this->request->input('wards_id');
            $dtData->created_by = $created_by;
            $dtData->type_create = $type_create;
            $dtData->address = $this->request->input('address') ?? null;
            $dtData->price = !empty($this->request->input('price')) ? ($this->request->input('price')) : 0;
            $dtData->latitude = $this->request->input('latitude') ?? null;
            $dtData->longitude = $this->request->input('longitude') ?? null;
            $dtData->name_location = $this->request->input('name_location') ?? null;
            $dtData->detail = $this->request->input('detail') ?? null;
            $dtData->rules = $this->request->input('rules') ?? null;
            $dtData->html_percent = $this->request->input('html_percent') ?? null;
            if ($app == 1) {
                if (!empty($this->request->input('save'))) {
                    $dtData->active = 0;
                } else {
                    $dtData->active = 4;
                }
                $dtData->step = $this->request->input('step') ?? 1;
            } else {
                if (empty($id)) {
                    $dtData->active = 0;
                }
            }
            $dtData->type_lunch_break = $type_lunch_break;
            $dtData->hour_start_lunch_break = $hour_start_lunch_break;
            $dtData->hour_end_lunch_break = $hour_end_lunch_break;
            $dtData->hour_start = $this->request->input('hour_start') ?? null;
            $dtData->hour_end = $this->request->input('hour_end') ?? null;
            $dtData->fanpage_facebook = $this->request->input('fanpage_facebook') ?? null;
            $dtData->link_website = $this->request->input('link_website') ?? null;
            $dtData->phone_number = $this->request->input('phone_number') ?? null;
            $dtData->name_wifi = $this->request->input('name_wifi') ?? null;
            $dtData->pass_wifi = $this->request->input('pass_wifi') ?? null;
            $dtData->app = $app;
            $dtData->save();
            if ($dtData) {
                $dtData->day()->delete();
                if (!empty($day)) {
                    foreach ($day as $key => $value) {
                        $serviceDay = new ServiceDay();
                        $serviceDay->service_id = $dtData->id;
                        $serviceDay->day = $value;
                        $serviceDay->save();
                    }
                }

                if (!empty($dtData->image_store)) {
                    foreach ($dtData->image_store as $image) {
                        if (!in_array($image['image'], $image_store_old)) {
                            $this->deleteFile($image['image']);
                            $image_store = ServiceImage::where('service_id', $dtData->id)->where('image',
                                $image['image'])->where('type', 1)->first();
                            if (!empty($image_store)) {
                                $image_store->delete();
                            }
                        }
                    }
                }

                if (!empty($dtData->image_menu)) {
                    foreach ($dtData->image_menu as $image) {
                        if (!in_array($image['image'], $image_menu_old)) {
                            $this->deleteFile($image['image']);
                            $image_menu = ServiceImage::where('service_id', $dtData->id)->where('image',
                                $image['image'])->where('type', 2)->first();
                            if (!empty($image_menu)) {
                                $image_menu->delete();
                            }
                        }
                    }
                }

                if ($this->request->hasFile('image_store')) {
                    if (is_array($this->request->file('image_store'))) {
                        foreach ($this->request->file('image_store') as $key => $file) {
                            $image_store = new ServiceImage();
                            $path = $this->UploadFile($file, 'service/' . $dtData->id, 800, 600, false);
                            $image_store->image = $path;
                            $image_store->service_id = $dtData->id;
                            $image_store->type = 1;
                            $image_store->save();
                        }
                    }
                }
                if ($this->request->hasFile('image_menu')) {
                    if (is_array($this->request->file('image_menu'))) {
                        foreach ($this->request->file('image_menu') as $file) {
                            $image_menu = new ServiceImage();
                            $path = $this->UploadFile($file, 'service/' . $dtData->id, 800, 600, false);
                            $image_menu->image = $path;
                            $image_menu->service_id = $dtData->id;
                            $image_menu->type = 2;
                            $image_menu->save();
                        }
                    }
                }

                if ($this->request->hasFile('image')) {
                    if (!empty($dtData->image)) {
                        $this->deleteFile($dtData->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'), 'service/' . $dtData->id, 70, 70, false);
                    $dtData->image = $path;
                    $dtData->save();
                }
                $dtData->other_amenities()->detach();
                if (!empty($arrOtherAmenities)) {
                    foreach ($arrOtherAmenities as $key => $value) {
                        $value['service_id'] = $dtData->id;
                        DB::table('tbl_other_amenities_service_service')->insert($value);;
                    }
                }

                //update thành đối tác
                if (!empty($dtPartner)) {
                    if ($dtPartner['type_client'] != 2){
                        $this->requestUpdateCustomer = new Request();
                        $this->requestUpdateCustomer->merge(['id' => $dtPartner['id']]);
                        $this->requestUpdateCustomer->merge(['type_client' => 2]);
                        $responsePartnerUpdate = $this->fnbCustomerService->updateTypeClient($this->requestUpdateCustomer);
                        $dataPartnerUpdate = $responsePartnerUpdate->getData(true);
                        $data['result'] = $dataPartnerUpdate['result'] ?? false;
                        $data['message'] = $dataPartnerUpdate['message'] ?? 'Lỗi khi cập nhập trạng thái khách hàng';
                        if ($data['result'] == false) {
                            return response()->json($data);
                        }
                    }
                }

                DB::commit();
                $data['result'] = true;
                $data['id'] = $dtData->id;
                if (empty($id)) {
                    $data['message'] = 'Thêm mới thành công';
                } else {
                    $data['message'] = 'Cập nhật thành công';
                }
            } else {
                $data['result'] = false;
                $data['id'] = 0;
                if (empty($id)) {
                    $data['message'] = 'Thêm mới thất bại';
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

    public function delete()
    {
        $id = $this->request->input('id') ?? 0;
        $admin = $this->request->input('admin') ?? 0;
        $dtData = Service::find($id);
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn tại data';
            return response()->json($data);
        }
        if (!empty($admin)){
            $partner_id = $dtData->customer_id;
        } else {
            $partner_id = !empty($this->request->client) ? $this->request->client->id : 0;
            if (empty($partner_id)){
                $data['result'] = false;
                $data['message'] = 'Vui lòng đăng nhập để sử dụng tính năng !';
                return response()->json($data);
            }
        }

        $this->request->merge(['service_id' => $id]);
        $dtCheck = $this->fnbTransactionBillService->checkService($this->request);
        $dtCheck = $dtCheck->getData(true);
        $dtCheck = $dtCheck['data'] ?? [];
        if (!empty($dtCheck['result']) && !empty($dtCheck['data'])){
            $data['result'] = false;
            $data['message'] = 'Gian hàng đã tồn tại hóa đơn không thể xóa!';
            return response()->json($data);
        }
        $dtCheck = $this->fnbTransactionService->checkService($this->request);
        $dtCheck = $dtCheck->getData(true);
        $dtCheck = $dtCheck['data'] ?? [];
        if (!empty($dtCheck['result']) && !empty($dtCheck['data'])){
            $data['result'] = false;
            $data['message'] = 'Gian hàng đã tồn tại chuyến đi không thể xóa!';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $dtData->delete();
            if (!empty($dtData->image)) {
                $this->deleteFile($dtData->image);
            }
            if (!empty($dtData->image_store)) {
                foreach ($dtData->image_store as $image) {
                    $this->deleteFile($image['image']);
                    ServiceImage::find($image['id'])->delete();
                }
            }
            if (!empty($dtData->image_menu)) {
                foreach ($dtData->image_menu as $image) {
                    $this->deleteFile($image['image']);
                    ServiceImage::find($image['id'])->delete();
                }
            }
            $dtData->other_amenities()->detach();
            $dtData->day()->delete();
            DB::commit();

            $checkService = Service::where('customer_id', $partner_id)->first();
            if (empty($checkService)){
                $this->requestCustomer = clone $this->request;
                $this->requestCustomer->merge(['customer_id' => [$partner_id]]);
                $this->requestCustomer->merge(['search' => null]);
                $responseCustomer = $this->fnbCustomerService->getListData($this->requestCustomer);
                $dataCustomer = $responseCustomer->getData(true);
                $customers = collect($dataCustomer['data']);
                $dtPartner = $customers->where('id', $partner_id)->first();
                if (!empty($dtPartner)){
                    if($dtPartner['type_client'] == 2){
                        $this->requestUpdateCustomer = new Request();
                        $this->requestUpdateCustomer->merge(['id' => $dtPartner['id']]);
                        $this->requestUpdateCustomer->merge(['type_client' => 1]);
                        $this->requestUpdateCustomer->merge(['delete' => true]);
                        $this->fnbCustomerService->updateTypeClient($this->requestUpdateCustomer);
                    }
                }
            }

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
        $status = $this->request->input('status') ?? 0;
        $dtData = Service::find($id);
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại gian hàng!';
            return response()->json($data);
        }
        $customer_id = $dtData->customer_id;
        $this->requestCustomer = clone $this->request;
        $this->requestCustomer->merge(['id' => $customer_id]);
        $this->requestCustomer->merge(['noti' => true]);
        $responseCustomer = $this->fnbCustomerService->getDetailCustomer($this->requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);
        $client = $dataCustomer['client'] ?? [];
        $arr_object_id = [];
        if(!empty($client)){
            $arr_object_id = $client['arr_object_id'] ?? [];
        }
        $checkService = Service::where('customer_id', $customer_id)->whereIn('active', [1,3])->first();
        $statusCheck = $dtData->active;
        DB::beginTransaction();
        try {
            $dtData->active = $status;
            $dtData->save();

            if (empty($checkService) && $statusCheck == 0){
                if ($status == 1) {
                    $this->requestCustomerPackage = new Request();
                    $this->requestCustomerPackage->merge(['customer_id' => $customer_id]);
                    $responseCustomerPackage = $this->fnbCustomerService->addCustomerPackage($this->requestCustomerPackage);
                    $dataCustomerPackage = $responseCustomerPackage->getData(true);
                    $data['result'] = $dataCustomerPackage['result'] ?? false;
                    $data['message'] = $dataCustomerPackage['message'] ?? 'Lỗi khi thêm người đại diện';
                    if ($data['result'] == false) {
                        return response()->json($data);
                    }
                }
            }

            $this->requestNoti = clone $this->request;
            $this->requestNoti->merge(['arr_object_id' => $arr_object_id]);
            $this->requestNoti->merge(['dtData' => $dtData]);
            $this->requestNoti->merge(['customer_id' => $customer_id]);
            $this->requestNoti->merge(['type' => 'staff']);
            $this->requestNoti->merge(['staff_id' => $this->request->input('staff_status')]);
            $this->requestNoti->merge(['type_noti' => 'change_status_service']);
            $this->fnbNoti->addNoti($this->requestNoti);

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

    public function changeHot()
    {
        $id = $this->request->input('id') ?? 0;
        $dtData = Service::find($id);
        DB::beginTransaction();
        try {
            $dtData->hot = $dtData->hot == 0 ? 1 : 0;
            $dtData->save();
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
        $current_page = 1;
        $per_page = 10;
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page = $this->request->query('per_page');
        }
        $hot = $this->request->input('hot') ?? -1;
        $search = $this->request->input('search') ?? null;
        $category_service_search = $this->request->input('category_service_search') ?? 0;
        //gian hàng liên quan
        $id = $this->request->input('id') ?? 0;
        //end
        $lat = !empty($this->request->input('lat')) ? $this->request->input('lat') : 0;
        $lon = !empty($this->request->input('lon')) ? $this->request->input('lon') : 0;

        $favourite = $this->request->input('favourite') ?? 0;
        $customer_id = $this->request->client->id ?? 0;

        $partner = $this->request->input('partner') ?? 0;

        $ward_id = $this->request->input('ward_id') ?? 0;
        $province_id = $this->request->input('province_id') ?? 0;

        $google_api_key = $this->fnbAdminService->get_option('google_api_key');

        $checkWard = false;
        $checkProvince = false;
        if (empty($lat) && empty($lon)) {
            $dtWard = Ward::where('Id', $ward_id)->first();
            if (!empty($dtWard)) {
                $lat = $dtWard->lat ?? 0;
                $lon = $dtWard->lon ?? 0;
                $checkWard = true;
            } else {
                $dtProvince = Province::where('Id', $province_id)->first();
                if (!empty($dtProvince)) {
                    $lat = $dtProvince->lat ?? 0;
                    $lon = $dtProvince->lon ?? 0;
                    $checkProvince = true;
                }
            }
        }

        if (!empty($lat) && !empty($lon)) {
            $orderBy = 'distance asc';
        } else {
            $orderBy = 'id desc';
        }
        $query = Service::select('tbl_service.*',
            DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"))->with('category_service')
            ->with('group_category_service')
            ->with('province')
            ->with('ward')
            ->with('other_amenities')
            ->with('image_store')
            ->with('image_menu')
            ->with('favourite')
            ->with('day')
            ->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        if ($hot != -1) {
            if ($hot == 1) {
                $query->where('hot', 1);
            } elseif ($hot == 0) {
                $query->where('hot', 0);
            }
        }
        if (empty($partner)) {
            if ($customer_id != 22) {
                $query->where('active', 1);
            }
        }
        if (!empty($id)) {
            $query->where('id', '!=', $id);
        }
        if (!empty($favourite)) {
            $query->WhereHas('favourite', function ($q) use ($customer_id) {
                $q->where('customer_id', $customer_id);
            });
        }
        if (!empty($partner)){
            $query->where('customer_id', $customer_id);
        }
        if (!empty($category_service_search)) {
            $category_service_search = is_array($category_service_search) ? ($category_service_search) : explode(',',$category_service_search);
            $query->whereIn('category_service_id', $category_service_search);
        }
        if (!empty($lat) && !empty($lon)) {
            $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null)"),
                '!=', null);
            if (empty($favourite) && empty($partner)) {
                $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),
                    '>=', 0);
                $query->where(function ($q) use ($lat, $lon, $ward_id, $province_id, $checkWard, $checkProvince) {
                    $q->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),
                        '<=', 50);
                    if (!empty($checkWard)) {
                        $q->orWhere('wards_id', $ward_id);
                    }
                    if (!empty($checkProvince)) {
                        $q->orWhere('province_id', $province_id);
                    }
                });
            }
        }
        $query->orderByRaw($orderBy);
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);
        $customer_ids = $dtData->pluck('customer_id')->toArray();

        $arrLatLng = $dtData->map(function ($item) {
            return [
                'lat' => $item->latitude,
                'lng' => $item->longitude,
                'service_id' => $item->id,
            ];
        })->toArray();

        $this->requestCustomer = clone $this->request;
        $this->requestCustomer->merge(['customer_id' => $customer_ids]);
        $this->requestCustomer->merge(['search' => null]);
        $responseCustomer = $this->fnbCustomerService->getListData($this->requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);
        $customers = collect($dataCustomer['data']);


        $distances = getDistancesToMultipleDestinations($lat, $lon, $arrLatLng, $google_api_key);

        $dtData->getCollection()->transform(function ($item) use ($customers, $distances, $lat, $lon,$customer_id) {
            $duration_text = $item->distance > 0 ? round(($item->distance / 40) * 60) : 0;
            $dtDataInstance = $distances[$item->id] ?? [];
            $customer = $customers->where('id', $item->customer_id)->first();
            $item->customer = $customer;
            $item->distance = $customer_id == 22 ? ['distance_km' => $item->distance,'duration_text' => $duration_text] : $dtDataInstance;
            $item->location_address = [
                'lat' => $lat,
                'lon' => $lon,
            ];
            return $item;
        });
        $collection = new ServiceCollection($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getDetailData($id = 0)
    {
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        $customer_id = $this->request->client->id ?? 0;
        $lat = !empty($this->request->input('lat')) ? $this->request->input('lat') : 0;
        $lon = !empty($this->request->input('lon')) ? $this->request->input('lon') : 0;

        $ward_id = $this->request->input('ward_id') ?? 0;
        $province_id = $this->request->input('province_id') ?? 0;

        if (empty($lat) && empty($lon)) {
            $dtWard = Ward::where('Id', $ward_id)->first();
            if (!empty($dtWard)) {
                $lat = $dtWard->lat ?? 0;
                $lon = $dtWard->lon ?? 0;
            } else {
                $dtProvince = Province::where('Id', $province_id)->first();
                if (!empty($dtProvince)) {
                    $lat = $dtProvince->lat ?? 0;
                    $lon = $dtProvince->lon ?? 0;
                }
            }
        }

        $dtData = Service::select('tbl_service.*',
            DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"))->with('category_service')
            ->with('group_category_service')
            ->with('province')
            ->with('ward')
            ->with('other_amenities')
            ->with('image_store')
            ->with('image_menu')
            ->with('day')
            ->with('favourite')
            ->with([
                'review' => function ($query) {
                    $query->latest()->limit(5);
                }
            ])
            ->find($id);
        if (empty($dtData)) {
            return response()->json([
                'data' => [],
                'result' => true,
                'message' => 'Lấy thông tin thành công'
            ]);
        }

        $arrLatLng[] = [
            'lat' => $dtData->latitude,
            'lng' => $dtData->longitude,
            'service_id' => $dtData->id,
        ];

        //đánh giá
        $customer_id_reviews = count($dtData->review) > 0 ? $dtData->review->pluck('customer_id')->toArray() : [0];
        $this->requestCustomer = clone $this->request;
        $this->requestCustomer->merge(['customer_id' => $customer_id_reviews]);
        $this->requestCustomer->merge(['search' => null]);
        $responseCustomer = $this->fnbCustomerService->getListData($this->requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);
        $customers = collect($dataCustomer['data']);
        if (count($customers) > 0) {
            $dtData->review->transform(function ($item) use ($customers) {
                $customer = $customers->where('id', $item->customer_id)->first();
                $item->customer = $customer;
                return $item;
            });
        }
        //end

        $customer_ids = $dtData->customer_id ? [$dtData->customer_id] : [];
        $this->requestCustomer = clone $this->request;
        $this->requestCustomer->merge(['customer_id' => $customer_ids]);
        $this->requestCustomer->merge(['search' => null]);
        $responseCustomer = $this->fnbCustomerService->getListData($this->requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);
        $customers = collect($dataCustomer['data']);
        $customer = $customers->where('id', $dtData->customer_id)->first();
        $dtData->customer = $customer;
        $dtData->check_detail = true;

        $distances = getDistancesToMultipleDestinations($lat, $lon, $arrLatLng,
            $this->fnbAdminService->get_option('google_api_key'));
        if ($customer_id == 22){
            $duration_text = $dtData->distance > 0 ? round(($dtData->distance / 40) * 60) : 0;
            $dtData->distance = ['distance_km' => $dtData->distance,'duration_text' => $duration_text];
        } else {
            $dtData->distance = $distances[$dtData->id] ?? [];
        }
        $dtData->location_address = [
            'lat' => $lat,
            'lon' => $lon,
        ];

        $collection = ServiceResource::make($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy thông tin thành công'
        ]);
    }

    public function getListDataByTransaction()
    {
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        $search = $this->request->input('search') ?? null;
        $partner_id = $this->request->input('partner_id') ?? 0;
        $service_id = $this->request->input('service_id') ?? [];
        $query = Service::with('category_service')
            ->with('image_store')
            ->with('group_category_service')
            ->where('id', '!=', 0);
        if (!empty($service_id)) {
            $service_id = is_array($service_id) ? ($service_id) : [$service_id];
            $query->whereIn('id', $service_id);
        }
        if (!empty($partner_id)){
            $query->where('customer_id', $partner_id);
//            $query->where('active', '=',1);
        }
        if (!empty($search)){
            $query->where('name','like',"%$search%");
        }
        $dtData = $query->get();
        $dtData->transform(function ($item) {
            $item->check_transaction = true;
            return $item;
        });
        $dtData = new ServiceCollection($dtData);
        return response()->json([
            'data' => $dtData->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListReview()
    {
        $per_page = 1;
        $current_page = 2;
        if ($this->request->input('current_page')) {
            $current_page = $this->request->input('current_page');
        }
        if ($this->request->input('per_page')) {
            $per_page = $this->request->input('per_page');
        }
        $service_id = !empty($this->request->input('service_id')) ? $this->request->input('service_id') : 0;
        $review = ReviewService::where('service_id', $service_id)
            ->orderByRaw('id desc')
            ->paginate($per_page, ['*'], '', $current_page);
        return ReviewResource::collection($review);
    }

    public function getReviewService()
    {
        $service_id = $this->request->input('service_id') ?? 0;
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data", 'id');
        $orderDir = $this->request->input('order.0.dir', 'desc');
        $query = ReviewService::with('detail')
            ->with('service')
            ->where('id', '!=', 0);
        if (!empty($search)) {
            $query->where(function ($q) use ($search,) {
                $q->where('content', 'like', "%$search%");
                $q->orWhereHas('service', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%$search%");
                });
            });
        }
        if (!empty($service_id)) {
            $query->where('service_id', $service_id);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
            }
        }
        $total = ReviewService::count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function changeFavouriteService()
    {
        $data = [];
        $service_id = !empty($this->request->input('service_id')) ? $this->request->input('service_id') : 0;
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        $status = $this->request->input('status') ?? 0;
        if (empty($customer_id)) {
            $data['result'] = false;
            $data['message'] = 'Vui lòng đăng nhập để sử dụng tính năng này!';
            return response()->json($data);
        }
        $dtData = Service::find($service_id);
        if (empty($dtData)) {
            $data['result'] = false;
            $data['message'] = 'Không tồn gian hàng!';
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            if ($status == 0) {
                $success = DB::table('tbl_favourite_service')->where([
                    'service_id' => $service_id,
                    'customer_id' => $customer_id,
                ])->delete();
                $success = true;
            } else {
                DB::table('tbl_favourite_service')->where([
                    'service_id' => $service_id,
                    'customer_id' => $customer_id,
                ])->delete();
                $success = DB::table('tbl_favourite_service')->insertGetId([
                    'service_id' => $service_id,
                    'customer_id' => $customer_id,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            DB::commit();
            if ($success) {
                $data['result'] = true;
                $data['message'] = 'Thành công';
            } else {
                $data['result'] = false;
                $data['message'] = 'Thất bại';
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function checkServiceRegister(){
        $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (empty($customer_id)) {
            $data['result'] = false;
            $data['message'] = 'Vui lòng đăng nhập để sử dụng tính năng này!';
            return response()->json($data);
        }
        $dtData = Service::with('category_service')
            ->with('group_category_service')
            ->with('province')
            ->with('ward')
            ->with('other_amenities')
            ->with('image_store')
            ->with('image_menu')
            ->with('day')
            ->with('favourite')
            ->where('customer_id',$customer_id)->where('active','=',4)->first();
        if (empty($dtData)){
            return response()->json([
                'data' => null,
                'result' => true,
                'message' => 'Lấy danh sách thành công'
            ]);
        } else {
            $collection = ServiceResource::make($dtData);
            return response()->json([
                'data' => $collection->response()->getData(true),
                'result' => true,
                'message' => 'Lấy danh sách thành công'
            ]);
        }
    }

    public function checkServicePartner(){
        $customer_id = $this->request->input('customer_id') ?? 0;
        if (empty($customer_id)) {
            $data['result'] = false;
            $data['message'] = 'Vui lòng đăng nhập để sử dụng tính năng này!';
            return response()->json($data);
        }
        $dtData = Service::with('category_service')
            ->with('group_category_service')
            ->with('province')
            ->with('ward')
            ->with('other_amenities')
            ->with('image_store')
            ->with('image_menu')
            ->with('day')
            ->with('favourite')
            ->where('customer_id',$customer_id)
            ->first();
        if (empty($dtData)){
            return response()->json([
                'data' => null,
                'result' => true,
                'message' => 'Lấy danh sách thành công'
            ]);
        } else {
            $collection = ServiceResource::make($dtData);
            return response()->json([
                'data' => $collection->response()->getData(true),
                'result' => true,
                'message' => 'Lấy danh sách thành công'
            ]);
        }
    }
}
