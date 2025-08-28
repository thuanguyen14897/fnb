<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ReviewResource;
use App\Http\Resources\Service as ServiceResource;
use App\Http\Resources\ServiceCollection;
use App\Models\CategoryService;
use App\Models\ReviewService;
use App\Models\ServiceDay;
use App\Models\Service;
use App\Models\ServiceImage;
use App\Traits\UploadFile;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceController extends AuthController
{
    use UploadFile;
    public $fnbCustomerService;
    public function __construct(Request $request,AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbCustomerService = $accountService;
    }

    public function getList(){
        $customer_search_value = $this->request->input('customer_search_value') ?? [];
        $group_category_service_search = $this->request->input('group_category_service_search') ?? 0;
        $category_service_search = $this->request->input('category_service_search') ?? 0;
        $customer_search = $this->request->input('customer_search') ?? 0;
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data",'id');
        $orderDir = $this->request->input('order.0.dir', 'desc');
        $query = Service::with('group_category_service')
            ->with('category_service')
            ->with('province')
            ->with('ward')
            ->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search,$customer_search_value) {
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
        if (!empty($customer_search)){
            $query->where('customer_id', $customer_search);
        }
        if (!empty($group_category_service_search)){
            $query->where('group_category_service_id', $group_category_service_search);
        }
        if (!empty($category_service_search)){
            $query->where('category_service_id', $category_service_search);
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)->get();
        if (!empty($data)){
            foreach ($data as $key => $value){
                $dtImage = !empty($value->image) ? env('STORAGE_URL').'/'.$value->image : null;
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

    public function getDetail(){
        $id = $this->request->input('id') ?? 0;
        $dtData = Service::with('group_category_service')
            ->with('category_service')
            ->with('other_amenities')
            ->with('province')
            ->with('ward')
            ->with('day')
            ->with('image_store')
            ->find($id);
        if (!empty($dtData)){
            $dtImage = !empty($dtData->image) ? env('STORAGE_URL').'/'.$dtData->image : null;
            $dtData->image = $dtImage;

            $image_store = !empty($dtData->image_store) ? $dtData->image_store : [];
            if (!empty($image_store)){
                foreach ($image_store as $k => $v){
                    $name = $v->image;
                    $dtImageStore = !empty($v->image) ? env('STORAGE_URL').'/'.$v->image : null;
                    $image_store[$k]['image'] = $dtImageStore;
                    $image_store[$k]['name'] = $name;
                }
            }

            $image_menu = !empty($dtData->image_menu) ? $dtData->image_menu : [];
            if (!empty($image_menu)){
                foreach ($image_menu as $k => $v){
                    $name = $v->image;
                    $dtImageMenu = !empty($v->image) ? env('STORAGE_URL').'/'.$v->image : null;
                    $image_menu[$k]['image'] = $dtImageMenu;
                    $image_menu[$k]['name'] = $name;
                }
            }
        }
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = 'Lấy thông tin thành công';
        return response()->json($data);
    }

    public function detail(){
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
            'name.unique' => 'Tên đã tồn tại',
            'group_category_service_id.required' => 'Vui lòng chọn nhóm danh mục',
            'customer_id.required' => 'Vui lòng chọn khách hàng',
            'category_service_id.required' => 'Vui lòng chọn danh mục dịch vụ',
            'province_id.required' => 'Vui lòng chọn tỉnh thành phố',
            'wards_id.required' => 'Vui lòng chọn phường xã',
            'phone_number.required' => 'Vui lòng nhập số điện thoại gian hàng',
        ];
        if (!empty($app)){
            unset($rules['customer_id']);
        } else {
            unset($rules['phone_number']);
        }
        $validator = Validator::make($this->request->all(), $rules, $messages);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        if (!empty($app)){
            $customer_id = !empty($this->request->client) ? $this->request->client->id : 0;
            if (empty($customer_id)){
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
            if (empty($dtPartner['representative'])){
                $responsePartner = $this->fnbCustomerService->detailRepresentativePartner($this->requestCustomer);
                $dataPartner = $responsePartner->getData(true);
                $data['result'] = $dataPartner['result'] ?? false;
                $data['message'] = $dataPartner['message'] ?? 'Lỗi khi thêm người đại diện';
                if ($data['result'] == false) {
                    return response()->json($data);
                }
            }
        } else {
            $customer_id = $this->request->input('customer_id');
        }
        if (empty($id)){
            $dtData = new Service();
        } else {
            $dtData = Service::find($id);
        }
        $other_amenities = $this->request->input('other_amenities');
        $arrOtherAmenities = [];
        if (!empty($other_amenities)) {
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
        if ($type_lunch_break == 0){
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
            $dtData->active = $this->request->input('active') ?? 1;
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
                if (!empty($day)){
                    foreach ($day as $key => $value){
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
                            $image_store = ServiceImage::where('service_id', $dtData->id)->where('image', $image['image'])->where('type',1)->first();
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
                            $image_menu= ServiceImage::where('service_id', $dtData->id)->where('image', $image['image'])->where('type',2)->first();
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
                            $path = $this->UploadFile($file, 'service/' . $dtData->id, 800, 600,false);
                            $image_store->image = $path;
                            $image_store->service_id = $dtData->id;
                            $image_store->type = 1;
                            $image_store->save();

                            if (!empty($app)) {
                                if ($key == 0) {
                                    $path = $this->UploadFile($file, 'service/' . $dtData->id,
                                        70, 70, false);
                                    $dtData->image = $path;
                                    $dtData->save();
                                }
                            }
                        }
                    }
                }

                if ($this->request->hasFile('image_menu')) {
                    if (is_array($this->request->file('image_menu'))) {
                        foreach ($this->request->file('image_menu') as $file) {
                            $image_menu = new ServiceImage();
                            $path = $this->UploadFile($file, 'service/' . $dtData->id, 800, 600,false);
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


                DB::commit();
                $data['result'] = true;
                if (empty($id)){
                    $data['message'] = 'Thêm mới thành công';
                } else {
                    $data['message'] = 'Cập nhập thành công';
                }
            } else {
                $data['result'] = false;
                if (empty($id)){
                    $data['message'] = 'Thêm mới thất bại';
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

    public function delete(){
        $id = $this->request->input('id') ?? 0;
        $dtData = Service::find($id);
        if (empty($dtData)){
            $data['result'] = false;
            $data['message'] = 'Không tồn tại data';
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
        $status = $this->request->input('status') ?? 0;
        $dtData = Service::find($id);
        DB::beginTransaction();
        try {
            $dtData->active = $status;
            $dtData->save();
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

    public function changeHot(){
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
        }
        catch (\Exception $exception){
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function getListData(){
        $current_page = 1;
        $per_page = 10;
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page =$this->request->query('per_page');
        }
        $hot = $this->request->input('hot') ?? -1;
        $search = $this->request->input('search') ?? null;
        $category_service_search = $this->request->input('category_service_search') ?? 0;
        //gian hàng liên quan
        $id = $this->request->input('id') ?? 0;
        //end
        $lat = !empty($this->request->input('lat')) ? $this->request->input('lat') : 0;
        $lon = !empty($this->request->input('lon')) ? $this->request->input('lon') : 0;
        if (!empty($lat) && !empty($lon)){
            $orderBy = 'distance asc';
        } else {
            $orderBy = 'id desc';
        }
        $query = Service::select('tbl_service.*',DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"))->with('category_service')
            ->with('group_category_service')
            ->with('province')
            ->with('ward')
            ->with('other_amenities')
            ->with('image_store')
            ->where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        if ($hot != -1){
            if ($hot == 1) {
                $query->where('hot', 1);
            } elseif ($hot == 0) {
                $query->where('hot', 0);
            }
        }
        if (!empty($id)){
            $query->where('id','!=',$id);
        }
        if (!empty($category_service_search)){
            $category_service_search = is_array($category_service_search) ? ($category_service_search): [$category_service_search];
            $query->whereIn('category_service_id', $category_service_search);
        }
        if (!empty($lat) && !empty($lon)){
            $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null)"),'!=',NULL);
            $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),'>=',0);
            $query->where(DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),10000)"),'<=',10);
        }
        $query->orderByRaw($orderBy);
        $dtData = $query->paginate($per_page, ['*'], '', $current_page);
        $customer_ids = $dtData->pluck('customer_id')->toArray();
        $this->requestCustomer = clone $this->request;
        $this->requestCustomer->merge(['customer_id' => $customer_ids]);
        $this->requestCustomer->merge(['search' => null]);
        $responseCustomer = $this->fnbCustomerService->getListData($this->requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);
        $customers = collect($dataCustomer['data']);
        $dtData->getCollection()->transform(function ($item) use ($customers) {
            $customer = $customers->where('id', $item->customer_id)->first();
            $item->customer = $customer;
            return $item;
        });
        $collection = new ServiceCollection($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getDetailData($id = 0){
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        $lat = !empty($this->request->input('lat')) ? $this->request->input('lat') : 0;
        $lon = !empty($this->request->input('lon')) ? $this->request->input('lon') : 0;
        $dtData = Service::select('tbl_service.*',DB::raw("IF($lat != 0 AND $lon != 0 AND latitude IS NOT NULL AND longitude IS NOT NULL,fnCalcDistanceKM($lat,latitude,$lon,longitude),null) as distance"))->with('category_service')
            ->with('group_category_service')
            ->with('province')
            ->with('ward')
            ->with('other_amenities')
            ->with('image_store')
            ->with('image_menu')
            ->with('day')
            ->with(['review' => function ($query) {
                $query->latest()->limit(5);
            }])
            ->find($id);
        if (empty($dtData)){
            return response()->json([
                'data' => [],
                'result' => true,
                'message' => 'Lấy thông tin thành công'
            ]);
        }
        //đánh giá
        $customer_id_reviews = count($dtData->review) > 0 ? $dtData->review->pluck('customer_id')->toArray() : [0];
        $this->requestCustomer = clone $this->request;
        $this->requestCustomer->merge(['customer_id' => $customer_id_reviews]);
        $this->requestCustomer->merge(['search' => null]);
        $responseCustomer = $this->fnbCustomerService->getListData($this->requestCustomer);
        $dataCustomer = $responseCustomer->getData(true);
        $customers = collect($dataCustomer['data']);
        if(count($customers) > 0) {
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
        $collection = ServiceResource::make($dtData);
        return response()->json([
            'data' => $collection->response()->getData(true),
            'result' => true,
            'message' => 'Lấy thông tin thành công'
        ]);
    }

    public function getListDataByTransaction(){
        if ($this->request->client == null) {
            $this->request->client = (object)['token' => Config::get('constant')['token_default']];
        }
        $service_id = $this->request->input('service_id') ?? [0];
        $query = Service::with('category_service')
            ->with('image_store')
            ->where('id','!=',0);
        if (!empty($service_id)){
            $service_id = is_array($service_id) ? ($service_id): [$service_id];
            $query->whereIn('id', $service_id);
        }
        $dtData = $query->get();
        $dtData->transform(function ($item) {
            $item->check_transaction = true;
            return $item;
        });
        $dtData = new ServiceCollection($dtData);
        return response()->json([
            'data' =>  $dtData->response()->getData(true),
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getListReview(){
        $per_page = 1;
        $current_page = 2;
        if ($this->request->input('current_page')) {
            $current_page = $this->request->input('current_page');
        }
        if ($this->request->input('per_page')) {
            $per_page =$this->request->input('per_page');
        }
        $service_id = !empty($this->request->input('service_id')) ? $this->request->input('service_id') : 0;
        $review = ReviewService::where('service_id',$service_id)
            ->orderByRaw('id desc')
            ->paginate($per_page, ['*'], '', $current_page);
        return ReviewResource::collection($review);
    }

    public function addService(){

        $validator = Validator::make($this->request->all(),
            [
                'name' => 'required',
                'group_category_service_id' => 'required',
                'category_service_id' => 'required',
                'province_id' => 'required',
                'wards_id' => 'required',
                'address' => 'required',
                'phone_number' => 'required',
            ]
            , [
                'name.required' => 'Vui lòng nhập tên gian hàng',
                'group_category_service_id.required' => 'Vui lòng chọn nhóm danh mục',
                'category_service_id.required' => 'Vui lòng chọn danh mục',
                'province_id.required' => 'Vui lòng chọn tỉnh thành phố',
                'wards_id.required' => 'Vui lòng chọn phường xã',
                'address.required' => 'Vui lòng nhập địa chỉ',
                'phone_number.required' => 'Vui lòng nhập số điện thoại gian hàng',
            ]);

        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all()[0];
            return response()->json($data);
        }
        $dataPost = $this->request->input();
        $service = new Service();
        $other_amenities = $dataPost['other_amenities'];
        $arrOtherAmenitiesCar = [];
        if (!empty($other_amenities_car)) {
            foreach (explode(',', $other_amenities_car) as $k => $v) {
                $arrOtherAmenitiesCar[] = [
                    'other_amenities_car_id' => $v,
                ];
            }
        }
        $arrHour = [];
        if (!empty($dataPost['hour_start'])) {
            $arrHour[] = [
                'hour_start' => optionHour("00:00", "23:30", $dataPost['hour_start']),
                'hour_end' => optionHour("00:00", "23:30", $dataPost['hour_end']),
                'type' => 1
            ];
        }
        if (!empty($dataPost['hour_start_new'])) {
            $arrHour[] = [
                'hour_start' => optionHour("00:00", "23:30", $dataPost['hour_start_new']),
                'hour_end' => optionHour("00:00", "23:30", $dataPost['hour_end_new']),
                'type' => 2
            ];
        }

        $fee_value = !empty($dataPost['fee_value']) ? $dataPost['fee_value'] : 0;
        $fee_id = !empty($dataPost['fee_id']) ? $dataPost['fee_id'] : 0;
        $car->name = $dataPost['name'];
        $car->number_car = $dataPost['number_car'];
        $car->year_manu = $dataPost['year_manu'];
        $car->company_car_id = $dataPost['company_car_id'];
        $car->type_car_id = $dataPost['type_car_id'];
        $car->model_car_id = $dataPost['model_car_id'];
        $car->type = 1;
        $car->customer_id = $customer_id;
        $car->color = !empty($dataPost['color']) ? $dataPost['color'] :null;
        $car->number_register_vehicle = !empty($dataPost['number_register_vehicle']) ? $dataPost['number_register_vehicle'] : null;
        $car->number_insurance = !empty($dataPost['number_insurance']) ? $dataPost['number_insurance'] : null;
        $car->number_registration = !empty($dataPost['number_registration']) ? $dataPost['number_registration'] :null;
        $car->registration_deadline = !empty($dataPost['registration_deadline']) ? to_sql_date($dataPost['registration_deadline']) : null;
        $car->name_owner = !empty($dataPost['name_owner']) ? $dataPost['name_owner'] : null;
        $car->rent_cost = number_unformat($dataPost['rent_cost']);
        $car->number_seat = ($dataPost['number_seat']);
        $car->limit_km = !empty($dataPost['limit_km']) ? 1 : 0;
        $car->total_km_day =  !empty($dataPost['limit_km']) ? number_unformat($dataPost['total_km_day']) : 0;
        $car->discount = !empty($dataPost['discount']) ? 1 : 0;
        $car->percent_discount =  !empty($dataPost['discount']) ? number_unformat($dataPost['percent_discount']) : 0;
        $car->fuel_consumption = ($dataPost['fuel_consumption']);
        $car->detail = htmlspecialchars($dataPost['detail'], ENT_QUOTES, 'UTF-8');
        $car->rules = get_option('rule_car_default');
        $car->province_id = ($dataPost['province_id']);
        $car->district_id = ($dataPost['district_id']);
        $car->wards_id = ($dataPost['wards_id']);
        $car->address = ($dataPost['address']);
        $car->book_car_flash = !empty($dataPost['book_car_flash']) ? 1 : 0;
        $car->from_book_car_flash = !empty($dataPost['book_car_flash']) ? ($dataPost['from_book_car_flash']) : 0;
        $car->to_book_car_flash = !empty($dataPost['book_car_flash']) ? ($dataPost['to_book_car_flash']) : 0;
        $car->delivery_car = !empty($dataPost['delivery_car']) ? 1 : 0;
        $car->km_delivery_car = !empty($dataPost['delivery_car']) ? ($dataPost['km_delivery_car']) : 0;
        $car->fee_km_delivery_car = !empty($dataPost['delivery_car']) ? number_unformat($dataPost['fee_km_delivery_car']) : 0;
        $car->free_km_delivery_car = !empty($dataPost['delivery_car']) ? ($dataPost['free_km_delivery_car']) : 0;
        $car->mortgage = !empty($dataPost['mortgage']) ? 1 : 0;
        $car->note_mortgage = !empty($dataPost['mortgage']) ? ($dataPost['note_mortgage']) : null;
        $car->transmission = $dataPost['transmission_id'];
        $car->type_fuel = $dataPost['type_fuel'];
        $car->latitude = $dataPost['latitude'];
        $car->longitude = $dataPost['longitude'];
        $car->name_location = !empty($dataPost['name_location']) ? $dataPost['name_location'] : null;
        $car->created_by = $customer_id;
        $car->type_create = 2;
        $car->status = Config::get('constant')['status_car_create'];
        $image_car_position = !empty($dataPost['image_car_position']) ? $dataPost['image_car_position'] : 0;
        DB::beginTransaction();
        try {
            $car->save();
            if ($car) {
                $car->other_amenities_car()->detach();
                if (!empty($arrOtherAmenitiesCar)) {
                    foreach ($arrOtherAmenitiesCar as $key => $value) {
                        $value['car_id'] = $car->id;
                        DB::table('tbl_other_amenities_car_car')->insert($value);;
                    }
                }

                if (!empty($car->image_car)) {
                    foreach ($car->image_car as $image) {
                        if (!in_array($image['name'], $dataPost['image_old'])) {
                            $this->deleteFile($image['name']);
                            $image_car = ImageCar::where('car_id', $car->id)->where('name', $image['name'])->first();
                            if (!empty($image_car)) {
                                $image_car->delete();
                            }
                        }
                    }
                }

                if (!empty($arrHour)) {
                    $car->car_hour()->delete();
                    foreach ($arrHour as $key => $value) {
                        $value['car_id'] = $car->id;
                        CarHour::create($value);
                    }
                }

                if ($this->request->hasFile('image')) {
                    if (is_array($this->request->file('image'))) {
                        foreach ($this->request->file('image') as $file) {
                            $image_car = new ImageCar();
                            $path = $this->UploadFile($file, 'car/' . $car->id, 800, 600);
                            $image_car->name = $path;
                            $image_car->car_id = $car->id;
                            $image_car->save();
                        }
                    }
                }
                $surcharge_car = SurchargeCar::where('type',1)->get();
                if (!empty($surcharge_car)) {
                    foreach ($surcharge_car as $key => $value) {
                        DB::table('tbl_surcharge_car_car')->insert([
                            'car_id' => $car->id,
                            'surcharge_car_id' => $value->id,
                            'value' => $value->min,
                            'type' => 1,
                        ]);
                    }
                }

                if(!empty($dataPost['limit_km'])){
                    if(!empty($fee_id)){
                        $car_id = $car->id;
                        $dtCheckSurcharge = Car::whereHas('surcharge_car',
                            function ($query) use ($car_id,$fee_id,$fee_value) {
                                $query->where('car_id', $car_id);
                                $query->where('surcharge_car_id', $fee_id);
                                $query->where('tbl_surcharge_car_car.type', 1);
                            })->get()->toArray();
                        if (empty($dtCheckSurcharge)) {
                            DB::table('tbl_surcharge_car_car')->insert([
                                'car_id' => $car_id,
                                'surcharge_car_id' => $fee_id,
                                'value' => $fee_value,
                                'type' => 1,
                            ]);
                        } else {
                            DB::table('tbl_surcharge_car_car')
                                ->where('car_id', $car_id)
                                ->where('surcharge_car_id', $fee_id)
                                ->where('type', 1)
                                ->update([
                                    'value' => $fee_value,
                                    'type' => 1,
                                ]);
                        }
                    }
                }


                if(!empty($dtClient)){
                    if($dtClient->type_client != 2){
                        $discountApp = DiscountApp::where('default',1)->first();
                        if (!empty($discountApp)){
                            $dtClient->discount_app_id = $discountApp->id;
                        } else {
                            $discountApp = DiscountApp::first();
                            $dtClient->discount_app_id = $discountApp->id;
                        }
                        $dtClient->type_client = 2;
                        $dtClient->save();
                    }
                }
                //image_car_position
                if (!empty($image_car_position)) {
                    $this->saveImagePosition($car->id, $this->request);
                }
                DB::commit();
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            } else {
                $data['result'] = false;
                $data['message'] = lang('dt_error');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }
}
