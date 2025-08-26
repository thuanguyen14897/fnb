<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\ContractTemplate;
use App\Models\GroupPermission;
//use App\Models\Permission;
//use App\Models\Department;
//use App\Models\Role;
use App\Models\Notification;
use App\Models\SettingCustomerClass;
use App\Models\SettingCustomerLeaderShip;
use App\Models\User;
use App\Models\Clients;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\DataTables;
use App\Helpers\FilesHelpers;
use App\Libraries\App;

class SettingsController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list(){
        $group = $this->request->input('group');
        if(empty($group)) {
            $group = 'info';
        }
        return view('admin.settings.list', [
            'group' => $group,
            'title' => lang('c_setting_' . $group)
        ]);
    }

    public function submit($group = 'info') {
        $listData = $this->request->input();
        $listDataFiles = $this->request->file();
        unset($listData['_token']);
        if(!empty($listData) || !empty($listDataFiles)) {
            if(!empty($listData)) {

                unset($listData['check_otp']);
                foreach ($listData as $key => $value) {
                    $arr = [
                    ];
                    if (in_array($key,$arr)){
                        $value = number_unformat($value);
                    }

                    $ktOptions = DB::table('tbl_options')->where('name', $key)->get()->first();
                    if (empty($ktOptions)) {
                        DB::table('tbl_options')->insert([
                            'name' => $key,
                            'value' => !empty($value) ? $value : ''
                        ]);
                    } else {
                        DB::table('tbl_options')->where('id', $ktOptions->id)->update([
                            'name' => $key,
                            'value' => !empty($value) ? $value : ''
                        ]);
                    }
                    if($key == 'contact_link_google_map') {
                        $keyAppend = 'contact_data_place_google_map';
                        $ktOptionsAppend = DB::table('tbl_options')->where('name', $keyAppend)->get()->first();
                        $valueAppend = extractCoordinates(get_option('contact_link_google_map'))['data_place'];
                        if (empty($ktOptionsAppend)) {
                            DB::table('tbl_options')->insert([
                                'name' => $keyAppend,
                                'value' => !empty($valueAppend) ? $valueAppend : ''
                            ]);
                        }
                        else {
                            DB::table('tbl_options')->where('id', $ktOptionsAppend->id)->update([
                                'name' => $keyAppend,
                                'value' => !empty($valueAppend) ? $valueAppend : ''
                            ]);
                        }
                    }
                }
            }

            if(!empty($listDataFiles)) {
                FilesHelpers::maybe_create_upload_path('upload/settings/');
                foreach ($listDataFiles as $key => $value) {
                    $paste_image = 'upload/settings/';
                    $paste_imageShort = 'upload/settings/';
                    $image_avatar = FilesHelpers::uploadFileData($value, $key, $paste_image, $paste_imageShort);
                    if (!empty($image_avatar)) {
                        $avatar = is_array($image_avatar) ? $image_avatar[0] : $image_avatar;
                        $ktOptions = DB::table('tbl_options')->where('name', $key)->get()->first();
                        if (empty($ktOptions)) {
                            DB::table('tbl_options')->insert([
                                'name' => $key,
                                'value' => !empty($avatar) ? $avatar : ''
                            ]);
                        } else {
                            DB::table('tbl_options')->where('id', $ktOptions->id)->update([
                                'name' => $key,
                                'value' => !empty($avatar) ? $avatar : ''
                            ]);
                        }
                    }
                }
            }
            $app = new App();
            $app->flushCache();

            return redirect('admin/settings?group=' . $group)->with('success', lang('dt_success'));
        }
        return redirect('admin/settings?group=' . $group)->with('error', lang('dt_error'));
    }

    public function download($id = 0)
    {
        $contract = ContractTemplate::find($id);
        $name_file = $contract->name_file;
        $fileNew = null;
        if (!empty($name_file)){
            $name_file = explode('___',$name_file);
            $fileNew = $name_file[1];
        }
        $file = public_path().'/storage/'.$contract->name_file;
        if(!empty($fileNew)) {
            return Response::download($file, $fileNew);
        }
    }

    public function changeStatus($id){
        $dtData = DB::table('tbl_setup_price_increase')->where('id',$id)->first();
        $status = $this->request->status == 0 ? 1 : 0;
        try {
            DB::table('tbl_setup_price_increase')->where('id',$id)->update([
                'status' => $status
            ]);
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function loadDataSetup()
    {
        $dtSetupPrice = DB::table('tbl_setup_price_increase')->get();
        $html = '';
        $counter = 0;
        $dtCategoryCar = DB::table('tbl_category_car')->get();
        if (!empty($dtSetupPrice)) {
            foreach ($dtSetupPrice as $key => $value) {
                $value = (array)$value;
                $option = '<option></option>';
                foreach ($dtCategoryCar as $k => $v) {
                    $option .= '<option ' . ($v->id == $value['category_car_id'] ? 'selected' : '') . ' value="' . $v->id . '">' . $v->name . '</option>';
                }
                $checked = $value['status'] == 1 ? 'checked' : '';
                $str = '<div><input type="checkbox" ' . $checked . ' name="active" class="active dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/settings/changeStatus/' . $value['id'] . '" data-status="' . $value['status'] . '"></div>';
                $html .= '<tr>
                    <td class="text-center">' . (++$key) . '</td>
                    <td>
                        <input type="time" name="hour_start[' . $counter . ']" id="hour_start' . $counter . '"
                               value="' . $value['hour_start'] . '"
                               class="name form-control">
                        <input type="hidden" name="counter_setup[]" value="' . $counter . '"
                               class="form-control counter_setup">
                        <input type="hidden" name="id_setup[' . $counter . ']" value="' . $value['id'] . '"
                               class="form-control">
                    </td>
                    <td>
                        <input type="time" name="hour_end[' . $counter . ']"
                               value="' . $value['hour_end'] . '" class="form-control hour_end">
                    </td>
                    <td>
                        <input type="text" name="coefficient[' . $counter . ']"
                               value="' . $value['coefficient'] . '" onchange="formatNumBerKeyChange(this)" class="form-control coefficient">
                    </td>
                    <td>
                        <select class="category_car_id select2 form-control" required name="category_car_id[' . $counter . ']">
                            ' . $option . '
                        </select>
                    </td>
                    <td class="text-center">
                        ' . $str . '
                    </td>
                    <td><div class="text-center"><i class="fa fa-remove btn btn-danger remove-row"></i></div></td>
                </tr>';
                $counter++;
            }
        }
        $data['html'] = $html;
        $data['counter'] = $counter;
        echo json_encode($data);
    }

    public function loadDataSetupHoliday()
    {
        $dtData = DB::table('tbl_setup_holiday_day')->get();
        $html = '';
        $counter = 0;
        $dtCategory = DB::table('tbl_holiday_list')->get();
        if (!empty($dtData)) {
            foreach ($dtData as $key => $value) {
                $value = (array)$value;
                $option = '<option></option>';
                foreach ($dtCategory as $k => $v) {
                    $option .= '<option ' . ($v->id == $value['holiday_list_id'] ? 'selected' : '') . ' value="' . $v->id . '">' . $v->name . '</option>';
                }
                $html .= '<tr>
                    <td class="text-center">' . (++$key) . '</td>
                    <td>
                          <select class="holiday_list_id select2 form-control" required name="holiday_list_id[' . $counter . ']">
                            ' . $option . '
                        </select>
                        <input type="hidden" name="counter_holiday[]" value="' . $counter . '"
                               class="form-control counter_holiday">
                        <input type="hidden" name="id_setup_holiday[' . $counter . ']" value="' . $value['id'] . '"
                               class="form-control">
                    </td>
                    <td>
                        <input type="text" name="hour[' . $counter . ']"
                               value="' . (_dthuan($value['date_start']).' - '._dthuan($value['date_end'])) . '" class="form-control date_search">
                    </td>
                    <td>
                        <input type="text" name="number_day[' . $counter . ']"
                               value="' . $value['number_day'] . '" onchange="formatNumBerKeyChange(this)" class="form-control number_day">
                    </td>
                    <td><div class="text-center"><i class="fa fa-remove btn btn-danger remove-row"></i></div></td>
                </tr>';
                $counter++;
            }
        }
        $data['html'] = $html;
        $data['counter'] = $counter;
        echo json_encode($data);
    }

    public function changeStatusDisplay($type = 1){
        $status = $this->request->status == 0 ? 1 : 0;
        try {
            if ($type == 1) {
                DB::table('tbl_options')->where('name', 'display_talented')->update([
                    'name' => 'display_talented',
                    'value' => $status
                ]);
            } else {
                DB::table('tbl_options')->where('name', 'display_driver')->update([
                    'name' => 'display_driver',
                    'value' => $status
                ]);
            }
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function changeStatusCheckOtp(){
        $status = $this->request->status == 0 ? 1 : 0;
        try {
            DB::table('tbl_options')->where('name', 'check_otp')->update([
                'name' => 'check_otp',
                'value' => $status
            ]);
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeTypeTransferAddress(){
        $transfer_address = !empty($this->request->input('transfer_address')) ? implode(',',$this->request->input('transfer_address')) : '';
        try {
            DB::table('tbl_options')->where('name', 'type_transfer_address')->update([
                'name' => 'type_transfer_address',
                'value' => $transfer_address
            ]);
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function loadCustomerClass()
    {
        $dtData = SettingCustomerClass::get()->toArray();
        $html = '';
        $counter = 0;
        if (!empty($dtData)) {
            foreach ($dtData as $key => $value) {
                $value = (array)$value;
                $imagePath = !empty($value['image']) ? asset('storage/'.$value['image']) : null;

                $html .= '<tr>
                    <td class="text-center">' . (++$key) . '</td>
                    <td class="text-center">
                          <a href="javascript:void(0);" class="image-popup" onclick="clickImageCustomerClass(this)" data-id="'.$counter.'" title="Screenshot-1">
                                        <img src="'.$imagePath.'" style="width:40px" class="thumb-img image_preview_'.$counter.'" alt="work-thumbnail">
                          </a>
                          <input type="file" name="image_customer_class['.$counter.']" class="image_customer_class" data-id="'.$counter.'" id="image_'.$counter.'" />
                    </td>
                    <td>
                        <div>
                        <label for="class_name">Tiếng việt</label>
                        <input type="text" name="class_name[]" class="class_name form-control" required value="'.$value['name'].'"></div>
                        <div>
                        <label for="class_name_en">Tiếng anh</label>
                        <input type="text" name="class_name_en[]" class="class_name_en form-control"  value="'.$value['name_en'].'"></div>
                         <div>
                        <label for="class_name_zh">Tiếng hàn</label>
                        <input type="text" name="class_name_zh[]" class="class_name_zh form-control"  value="'.$value['name_zh'].'"></div>
                        <input type="hidden" name="counter_customer_class[]" value="' . $counter . '"
                               class="form-control counter_customer_class">
                        <input type="hidden" name="id_setting_customer_class[' . $counter . ']" value="' . $value['id'] . '"
                               class="form-control">
                    </td>
                    <td>
                        <input type="text" name="total_start[' . $counter . ']"
                               value="' . formatMoney($value['total_start']) . '" class="form-control total_start" onchange="formatNumBerKeyChange(this)">
                    </td>
                    <td>
                        <input type="text" name="total_end[' . $counter . ']"
                               value="' . formatMoney($value['total_end']) . '" onchange="formatNumBerKeyChange(this)" class="form-control total_end">
                    </td>
                    <td>
                        <input type="text" name="percent[' . $counter . ']"
                               value="' . ($value['percent']) . '" onchange="formatNumBerKeyChange(this)" class="form-control percent_customer_class" min="0" max="100">
                    </td>
                </tr>';
                $counter++;
            }
        }
        $data['html'] = $html;
        $data['counter'] = $counter;
        echo json_encode($data);
    }

    public function loadCustomerLeaderShip()
    {
        $dtData = SettingCustomerLeaderShip::get();
        $html = '';
        $counter = 0;
        if (!empty($dtData)) {
            foreach ($dtData as $key => $value) {
                $imagePath = !empty($value->setting_customer_class) ? asset('storage/'.$value->setting_customer_class->image) : null;
                $html .= '<tr>
                    <td class="text-center">' . (++$key) . '</td>
                    <td class="text-center">
                        <div>'.loadHtmlReviewStarNew($value->star).'</div>
                    </td>
                    <td>
                        <input type="text" name="number_leader[' . $counter . ']"
                               value="' . formatMoney($value->number) . '" class="form-control number_leader" onchange="formatNumBerKeyChange(this)">
                    </td>
                     <td>
                        <div> <img src="'.$imagePath.'" style="width:40px" class="thumb-img" alt="work-thumbnail"> '.$value->setting_customer_class->name.'</div>
                        <input type="hidden" name="counter_customer_leader[]" value="' . $counter . '"
                               class="form-control counter_customer_leader">
                        <input type="hidden" name="id_setting_customer_leader[' . $counter . ']" value="' . $value->id . '"
                               class="form-control">
                    </td>
                    <td>
                        <input type="text" name="percent_leader[' . $counter . ']"
                               value="' . ($value->percent) . '" onchange="formatNumBerKeyChange(this)" class="form-control percent_leader" min="0" max="100">
                    </td>
                </tr>';
                $counter++;
            }
        }
        $data['html'] = $html;
        $data['counter'] = $counter;
        echo json_encode($data);
    }
}
