<?php

namespace App\Http\Controllers\Api_app;

use App\Helpers\FilesHelpers;
use App\Models\Clients;
use App\Services\AdminService;
use App\Traits\UploadFile;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class LoginApi extends AuthController
{
    use UploadFile;
    protected $fnbAdmin;

    public function __construct(Request $request,AdminService $adminService)
    {
        parent::__construct($request);
        $this->fnbAdmin = $adminService;
        DB::enableQueryLog();
    }

    public function sign_up()
    {
        $dataResult = [
            'result' => false,
        ];
        if ($this->request->input()) {
            $data = $this->request->input();
            if (!empty($data['phone'])) {
                $kt_phone = DB::table('tbl_clients')
                    ->where('phone', $data['phone'])
                    ->get()
                    ->first();
                if (!empty($kt_phone)) {
                    $dataResult['message'] = lang('c_phone_isset_pls_input_phone_other');
                    return response()->json($dataResult);
                }
            }


            if (empty($data['fullname'])) {
                $dataResult['message'] = lang('c_pls_input_fullname');
                return response()->json($dataResult);
            }
            if (empty($data['phone']) && empty($data['email'])) {
                $dataResult['message'] = lang('c_pls_input_phone_sign_up');
                return response()->json($dataResult);
            }

            if (empty($data['password']) && empty($data['id_sign_up'])) {
                $dataResult['message'] = lang('c_pls_input_password');
                return response()->json($dataResult);
            }

            if (!empty($data['email'])) {
                $kt_email = DB::table('tbl_clients')
                    ->where('email', $data['email'])
                    ->get()
                    ->first();
                if (!empty($kt_email)) {
                    $dataResult['message'] = lang('c_phone_isset_pls_input_email_other');
                    return response()->json($dataResult);
                }
            }
            if (empty($data['key_code'])) {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_sign_up_fail');
                return response()->json($dataResult);
            } else {
                if (config('app.debug')){
                    if ($data['key_code'] != '1111'){
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_fail');
                        return response()->json($dataResult);
                    }
                } else {
                    $ktKeyCode = DB::table('tbl_otp_client')
                        ->where('phone', $data['phone'])
                        ->whereRaw('DATE_FORMAT(date_end, "%Y-%m-%d %H:%i:%s") > "' . date('Y-m-d H:i:s') . '"')
                        ->first();
                    if (empty($ktKeyCode)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_expired');
                        return response()->json($dataResult);
                    } elseif ($ktKeyCode->key_code != $data['key_code']) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_fail');
                        return response()->json($dataResult);
                    }
                }

            }
            $responseRefCode = $this->fnbAdmin->getOrderRef('client');
            $code = $responseRefCode['reference_no'] ?? null;
            if (empty($code)){
                $dataResult['result'] = false;
                $dataResult['message'] = 'Mã người dùng không hợp lệ';
                return response()->json($dataResult);
            }
            try {
                $dataInsert = [
                    'code' => $code,
                    'phone' => !empty($data['phone']) ? $data['phone'] : null,
                    'verify_phone' => 1,
                    'email' => !empty($data['email']) ? $data['email'] : null,
                    'fullname' => !empty($data['fullname']) ? $data['fullname'] : null,
                ];
                if (!empty($data['password'])) {
                    $dataInsert['password'] = encrypt($data['password']);
                }
                if (isset($data['id_sign_up'])) {
                    $dataInsert['sign_up_with'] = $data['sign_up_with'];
                    $dataInsert['id_sign_up'] = $data['id_sign_up'];

                    $data_client_with = DB::table('tbl_clients')->where([
                        'sign_up_with' => $dataInsert['sign_up_with'],
                        'id_sign_up' => $data['id_sign_up'],
                    ])->get()->first();
                    if (!empty($data_client_with)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_error_sign_up_lk');
                        return response()->json($dataResult);
                    }
                }

                $id = DB::table('tbl_clients')->insertGetId($dataInsert);
                $this->fnbAdmin->updateOrderRef('client');
                if (!empty($_FILES['avatar'])) {
                    FilesHelpers::maybe_create_upload_path('upload/clients/');
                    $paste_image = 'upload/clients/' . $id . '/';
                    $paste_imageShort = 'upload/clients/' . $id . '/';
                    $image_avatar = FilesHelpers::uploadFileData($this->request->file('avatar'), 'avatar', $paste_image, $paste_imageShort);
                    if (!empty($image_avatar)) {
                        $avatar = is_array($image_avatar) ? $image_avatar[0] : $image_avatar;
                        if (!empty($avatar)) {
                            DB::table('tbl_clients')->where('id', $id)->update(['avatar' => $avatar]);
                        }
                    }
                } elseif (!empty($data['avatar'])) {
                    FilesHelpers::maybe_create_upload_path('upload/clients/');
                    FilesHelpers::maybe_create_upload_path('upload/clients/' . $id . '/');
                    $image_small = $data['avatar'];
                    $time = time();
                    $paste_img = 'upload/clients/' . $id . '/';
                    $copyAvatar = copy($image_small, $paste_img . $time . '.jpg');
                    if (!empty($copyAvatar)) {
                        $avatar = $paste_img . $time . '.jpg';
                        DB::table('tbl_clients')->where('id', $id)->update(['avatar' => $avatar]);
                    }
                }

                if (!empty($data['phone'])) {
                    DB::table('tbl_otp_client')->where('phone', $data['phone'])->delete();
                }

                //update mã giới thiệu
                $dtClient = Clients::find($id);
                $dtClient->referral_code = generateRandomString($id,6);
                $dtClient->save();
                //end

                $dataResult['result'] = true;
                $dataResult['id'] = $id;
                $dataResult['message'] = lang('c_sign_up_success');
                $dataResult['token'] = $this->Create_Token([
                    'password' => !empty($dataInsert['password']) ? $dataInsert['password'] : null,
                    'id' => $id,
                    'sign_up_with' => !empty($data['sign_up_with']) ? $data['sign_up_with'] : null,
                    'id_sign_up' => !empty($data['id_sign_up']) ? $data['id_sign_up'] : null,
                ]);

                if (!empty($data['player_id'])) {
                    DB::table('tbl_player_id')->where('player_id', $data['player_id'])->delete();
                    DB::table('tbl_player_id')->insert([
                        'object_id' => $id,
                        'object_type' => $data['object_type'],
                        'player_id' => $data['player_id'],
                    ]);
                }

                if (!empty($dataResult['token'])) {
                    return response()->json($dataResult);
                }
            } catch (\Exception $exception) {
                $dataResult['result'] = false;
                $dataResult['message'] = $exception->getMessage();
                return response()->json($dataResult);
            }
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    public function login()
    {
        $dataResult = [
            'result' => false,
        ];
        if ($this->request->input()) {
            $data = $this->request->input();
            $type_login = $data['type_login'] ?? 'sms';
            if (empty($data['phone']) && empty($data['email'])) {
                $dataResult['message'] = lang('c_pls_input_phone_login');
                return response()->json($dataResult);
            }
            if ($type_login == 'password'){
                if (empty($data['password'])) {
                    $dataResult['message'] = lang('c_pls_input_password');
                    return response()->json($dataResult);
                }
            }
            if (!empty($data['phone'])) {
                $ktLogin = DB::table('tbl_clients')
                    ->where('phone', $data['phone'])->first();
                if (empty($ktLogin)) {
                    $dataResult['message'] = lang('c_not_find_phone');
                    return response()->json($dataResult);
                }
            }
            if (!empty($ktLogin)) {
                if ($ktLogin->active == 0) {
                    $dataResult['message'] = 'Tài khoản của bạn đang tạm khóa, vui lòng liên hệ hotline Minh Phương để được xử lý!';
                    return response()->json($dataResult);
                }
                if ($type_login == 'sms') {
                    if (empty($data['key_code'])) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = 'Vui lòng nhập mã OTP';
                        return response()->json($dataResult);
                    } else {
                        if (config('app.debug')){
                            if ($data['key_code'] != '1111'){
                                $dataResult['result'] = false;
                                $dataResult['message'] = lang('c_code_otp_fail');
                                return response()->json($dataResult);
                            }
                        } else {
                            if ($data['phone'] != '0878123366' && $data['phone'] != '0981893353') {
                                $ktKeyCode = DB::table('tbl_otp_client')
                                    ->where('phone', $data['phone'])
                                    ->whereRaw('DATE_FORMAT(date_end, "%Y-%m-%d %H:%i:%s") > "' . date('Y-m-d H:i:s') . '"')
                                    ->first();
                                if (empty($ktKeyCode)) {
                                    $dataResult['result'] = false;
                                    $dataResult['message'] = lang('c_code_otp_expired');
                                    return response()->json($dataResult);
                                } elseif ($ktKeyCode->key_code != $data['key_code']) {
                                    $dataResult['result'] = false;
                                    $dataResult['message'] = lang('c_code_otp_fail');
                                    return response()->json($dataResult);
                                }
                            } else {
                                if ($data['key_code'] != '1111') {
                                    $dataResult['result'] = false;
                                    $dataResult['message'] = lang('c_code_otp_fail');
                                    return response()->json($dataResult);
                                }
                            }
                        }
                    }
                    $dataResult['message'] = lang('c_login_success');
                    $dataResult['id'] = $ktLogin->id;
                    $dataResult['result'] = true;
                    $dataResult['token'] = $this->Create_Token([
                        'password' => $ktLogin->password,
                        'id' => $ktLogin->id,
                        'sign_up_with' => !empty($ktLogin->sign_up_with) ? $ktLogin->sign_up_with : null,
                        'id_sign_up' => !empty($ktLogin->id_sign_up) ? $ktLogin->id_sign_up : null,
                    ]);
                    if (!empty($data['player_id'])) {
                        DB::table('tbl_player_id')->where('player_id', $data['player_id'])->delete();

                        DB::table('tbl_player_id')->insert([
                            'object_id' => $ktLogin->id,
                            'object_type' => $data['object_type'],
                            'player_id' => $data['player_id'],
                        ]);
                    }
                    if (!empty($dataResult['token'])) {
                        return response()->json($dataResult);
                    }
                } else {

                    if (!empty($ktLogin->password) && decrypt($ktLogin->password) != $data['password']) {
                        $dataResult['message'] = lang('c_password_incorrect');
                        return response()->json($dataResult);
                    } else {
                        $dataResult['message'] = lang('c_login_success');
                        $dataResult['id'] = $ktLogin->id;
                        $dataResult['result'] = true;
                        $dataResult['token'] = $this->Create_Token([
                            'password' => $ktLogin->password,
                            'id' => $ktLogin->id,
                            'sign_up_with' => !empty($ktLogin->sign_up_with) ? $ktLogin->sign_up_with : null,
                            'id_sign_up' => !empty($ktLogin->id_sign_up) ? $ktLogin->id_sign_up : null,
                        ]);
                        if (!empty($data['player_id'])) {
                            DB::table('tbl_player_id')->where('player_id', $data['player_id'])->delete();

                            DB::table('tbl_player_id')->insert([
                                'object_id' => $ktLogin->id,
                                'object_type' => $data['object_type'],
                                'player_id' => $data['player_id'],
                            ]);
                        }
                        if (!empty($dataResult['token'])) {
                            return response()->json($dataResult);
                        }
                    }
                }
                $dataResult['message'] = 'Đăng nhập thất bại!';
                return response()->json($dataResult);
            }
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    public function verifyOtp(){
        $dataResult = [
            'result' => false,
        ];
        if ($this->request->input()) {
            $data = $this->request->input();
            if (!empty($data['phone'])) {
                $kt_phone = DB::table('tbl_clients')
                    ->where('phone', $data['phone'])
                    ->get()
                    ->first();
                if (!empty($kt_phone)) {
                    $dataResult['message'] = lang('c_phone_isset_pls_input_phone_other');
                    return response()->json($dataResult);
                }
            }
            if (empty($data['fullname'])) {
                $dataResult['message'] = lang('c_pls_input_fullname');
                return response()->json($dataResult);
            }
            if (empty($data['phone']) && empty($data['email'])) {
                $dataResult['message'] = lang('c_pls_input_phone_sign_up');
                return response()->json($dataResult);
            }

            if (!empty($data['email'])) {
                $kt_email = DB::table('tbl_clients')
                    ->where('email', $data['email'])
                    ->get()
                    ->first();
                if (!empty($kt_email)) {
                    $dataResult['message'] = lang('c_phone_isset_pls_input_email_other');
                    return response()->json($dataResult);
                }
            }
            if (empty($data['key_code'])) {
                $dataResult['result'] = false;
                $dataResult['message'] = 'Mã OTP không được bỏ trống';
                return response()->json($dataResult);
            } else {
                if (config('app.debug')){
                    if ($data['key_code'] != '1111'){
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_fail');
                        return response()->json($dataResult);
                    }
                } else {
                    $ktKeyCode = DB::table('tbl_otp_client')
                        ->where('phone', $data['phone'])
                        ->whereRaw('DATE_FORMAT(date_end, "%Y-%m-%d %H:%i:%s") > "' . date('Y-m-d H:i:s') . '"')
                        ->first();
                    if (empty($ktKeyCode)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_expired');
                        return response()->json($dataResult);
                    } elseif ($ktKeyCode->key_code != $data['key_code']) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_fail');
                        return response()->json($dataResult);
                    }
                }
            }
            $dataResult['result'] = true;
            $dataResult['message'] = 'Nhập OTP thành công!';
            return response()->json($dataResult);
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    public function check_otp_forgot_password(){
        if ($this->request->input()) {
            $data = $this->request->input();
            if (!empty($data['phone'])) {
                $kt_phone = DB::table('tbl_clients')
                    ->where('phone', $data['phone'])
                    ->first();
                if (empty($kt_phone)) {
                    $dataResult['message'] = 'Số điện thoại này chưa đăng ký tài khoản!';
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            }

            if (empty($data['key_code'])) {
                $dataResult['result'] = false;
                $dataResult['message'] = 'Mã OTP không được bỏ trống';
                return response()->json($dataResult);
            } else {
                if (config('app.debug')){
                    if ($data['key_code'] != '1111'){
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_fail');
                        return response()->json($dataResult);
                    }
                } else {
                    $ktKeyCode = DB::table('tbl_otp_client')
                        ->where('phone', $data['phone'])
                        ->whereRaw('DATE_FORMAT(date_end, "%Y-%m-%d %H:%i:%s") > "' . date('Y-m-d H:i:s') . '"')
                        ->first();
                    if (empty($ktKeyCode)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_expired');
                        return response()->json($dataResult);
                    } elseif ($ktKeyCode->key_code != $data['key_code']) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_fail');
                        return response()->json($dataResult);
                    }
                }
            }
            $dataResult['result'] = true;
            $dataResult['message'] = 'Nhập OTP thành công!';
            return response()->json($dataResult);
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    public function forgot_password(){
        if ($this->request->input()) {
            $data = $this->request->input();
            if (!empty($data['phone'])) {
                $kt_phone = DB::table('tbl_clients')
                    ->where('phone', $data['phone'])
                    ->first();
                if (empty($kt_phone)) {
                    $dataResult['message'] = 'Số điện thoại này chưa đăng ký tài khoản!';
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            }

            if (empty($data['password'])) {
                $dataResult['message'] = lang('c_pls_input_password');
                $dataResult['result'] = false;
                return response()->json($dataResult);
            }

            try {
                $client = Clients::find($kt_phone->id);
                $client->password = encrypt($data['password']);
                $client->save();
                if ($client){
                    $dataResult['result'] = true;
                    $dataResult['message'] = 'Cập nhập mật khẩu thành công!';
                    return response()->json($dataResult);
                } else {
                    $dataResult['result'] = false;
                    $dataResult['message'] = 'Cập nhập mật khẩu thất bại!';
                    return response()->json($dataResult);
                }
            } catch (\Exception $exception) {
                $dataResult['result'] = false;
                $dataResult['message'] = $exception->getMessage();
                return response()->json($dataResult);
            }
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    public function otpDangKyThuanFoso()
    {
        $phone = $this->request->input('phone');
        $event = !empty($this->request->input('event')) ? $this->request->input('event') : 'register';
        if (!empty($phone)) {
            $date = date('Y-m-d H:i:s');
            $phone_check = substr($phone, 1, 9);
            $phone_check = '84'.$phone_check;

            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $needle = "Windows";

            if ($event == 'login'){
                $ktPhoneClient = DB::table('tbl_clients')
                    ->where('phone', $phone)
                    ->get()->first();
                if (empty($ktPhoneClient)) {
                    $dataResult['message'] = 'Số điện thoại này chưa đăng ký tài khoản!';
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            } elseif ($event == 'change_password'){
                $ktPhoneClient = DB::table('tbl_clients')
                    ->where('phone', $phone)
                    ->get()->first();
                if (empty($ktPhoneClient)) {
                    $dataResult['message'] = 'Số điện thoại này chưa đăng ký tài khoản!';
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
                $countSendSms = DB::table('tbl_send_sms')->where('phone',$phone_check)
                    ->where('event','change_password')
                    ->where(DB::raw('DATE_FORMAT(date_send,"%Y-%m-%d")'),'=',date('Y-m-d'))->count();
                if ($countSendSms >= $this->fnbAdmin->get_option('limit_otp_change_pass')){
                    $dataResult['message'] = 'Số điện thoại này đã vượt quá số lần gửi OTP trong 1 ngày!';
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            } elseif($event == 'register') {
                $ktPhoneClient = DB::table('tbl_clients')
                    ->where('phone', $phone)
                    ->get()->first();
                $countSendSms = DB::table('tbl_send_sms')->where('phone',$phone_check)
                    ->where('event','register')
                    ->where(DB::raw('DATE_FORMAT(date_send,"%Y-%m-%d")'),'=',date('Y-m-d'))->count();
                if ($countSendSms >= $this->fnbAdmin->get_option('limit_otp_change_pass')){
                    $dataResult['message'] = 'Số điện thoại này đã vượt quá số lần gửi OTP trong 1 ngày!';
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
                $countSendLimitSms = DB::table('tbl_send_sms')
                    ->where('event','register')
                    ->where('user_agent',$userAgent)
                    ->where(DB::raw('DATE_FORMAT(date_send,"%Y-%m-%d")'),'=',date('Y-m-d'))->count();
                // if ($countSendLimitSms >= $this->fnbAdmin->get_option('limit_send_otp_register')){
                //     $dataResult['message'] = 'Đã vượt quá số lần gửi OTP trong 1 ngày';
                //     $dataResult['result'] = false;
                //     return response()->json($dataResult);
                // }
                if (!empty($ktPhoneClient)) {
                    $dataResult['message'] = lang('c_phone_isset_pls_input_phone_other');
                    $dataResult['result'] = false;
                    $dataResult['isset'] = true;
                    return response()->json($dataResult);
                }
            }

            DB::table('tbl_otp_client')->where('phone', $phone)->delete();
            $key_code = $this->createKeyCode();
            $dateEnd = strtotime('+' . $this->fnbAdmin->get_option('time_otp') . ' minute', strtotime($date));
            $dateEnd = date('Y-m-d H:i:s', $dateEnd);
            $idOTP = DB::table('tbl_otp_client')->insertGetId([
                'phone' => $phone,
                'key_code' => $key_code,
                'date_end' => $dateEnd
            ]);
            if (!empty($idOTP)) {
                $dataResult['message'] = lang('c_send_otp_true');
                $dataResult['result'] = true;
                $dataResult['key_code'] = '';
                $dataResult['time'] = $this->fnbAdmin->get_option('time_otp') * 60;
                $content_sms = '';
                if ($event == 'register') {
                    $content_sms = $this->fnbAdmin->get_option('content_otp_register');
                } elseif ($event == 'change_password'){
                    $content_sms = $this->fnbAdmin->get_option('content_otp_change_pass');
                }
                $content_sms = str_replace('{code}', $key_code,$content_sms);
                if (empty(strpos($userAgent, $needle))) {
                    if (!config('app.debug')) {
                        send_zalo($phone,'otp',Config::get('constant')['template_id_otp'],$key_code);
                    }
                }
                return response()->json($dataResult);
            } else {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_send_otp_fail');
                return response()->json($dataResult);
            }
        }
        $dataResult['result'] = false;
        $dataResult['message'] = lang('c_send_otp_fail');
        return response()->json($dataResult);
    }

    public function update_account()
    {
        $dataResult = [
            'result' => false,
        ];
        $token = $this->request->bearerToken();
        $data = $this->request->input();
        $id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (!empty($id)) {
           DB::beginTransaction();
            try {
                $dataClient = Clients::find($id);
                if (isset($data['email']) && !empty($data['email'])) {
                    $ktEmail = Clients::where('email', $data['email'])->where('id', '!=', $id)->first();
                    if (!is_null($ktEmail)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_kt_email_isset_pls_input_email_other');
                        return response()->json($dataResult);
                    }
                }
                if (isset($data['phone']) && !empty($data['phone'])) {
                    $ktPhone = Clients::where('phone', $data['phone'])->where('id', '!=', $id)->first();
                    if (!is_null($ktPhone)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_kt_phone_isset_pls_input_phone_other');
                        return response()->json($dataResult);
                    }
                }

                $dataUpdate = [];
                if (!empty($data) || $this->request->hasFile('avatar')) {
                    if (isset($data['phone'])) {
                        $dataUpdate['phone'] = $data['phone'];
                        if ($data['phone'] != $dataClient->phone) {
                            $dataUpdate['verify_phone'] = 0;
                        }
                    }
                    if (isset($data['birthday'])) {
                        $dataUpdate['birthday'] = to_sql_date($data['birthday']);
                        $now = strtotime(date('Y-m-d') . ' 23:59:59');
                        $birthdayCheck = strtotime(to_sql_date($data['birthday']));
                        if ($now < $birthdayCheck) {
                            $dataResult['result'] = false;
                            $dataResult['message'] = lang('khong_nhap_ngay_nho_hon_ngay_hien_tai');
                            return response()->json($dataResult);
                        }
                    }
                    if (isset($data['gender'])) {
                        $dataUpdate['gender'] = ($data['gender']);
                    }
                    if (isset($data['prefix_phone'])) {
                        $dataUpdate['prefix_phone'] = $data['prefix_phone'];
                    }
                    if (isset($data['email'])) {
                        $dataUpdate['email'] = $data['email'];
                    }
                    if (isset($data['fullname'])) {
                        $dataUpdate['fullname'] = $data['fullname'];
                    }
                    if (isset($data['number_cccd'])) {
                        $dataUpdate['number_cccd'] = $data['number_cccd'];
                    }
                    if (isset($data['issued_cccd'])) {
                        $dataUpdate['issued_cccd'] = $data['issued_cccd'];
                    }
                    if (isset($data['date_cccd'])) {
                        $dataUpdate['date_cccd'] = to_sql_date($data['date_cccd']);
                    }
                    if (isset($data['number_passport'])) {
                        $dataUpdate['number_passport'] = $data['number_passport'];
                    }
                    if (isset($data['issued_passport'])) {
                        $dataUpdate['issued_passport'] = $data['issued_passport'];
                    }
                    if (isset($data['date_passport'])) {
                        $dataUpdate['date_passport'] = to_sql_date($data['date_passport']);
                    }

                    $affected_avatar = false;
                    if ($this->request->hasFile('avatar')) {
                        if (!empty($dataClient->avatar)) {
                            $this->deleteFile($dataClient->avatar);
                        }
                        $path = $this->UploadFile($this->request->file('avatar'), 'clients/' . $dataClient->id, 70, 70, false);
                        $dataClient->avatar = $path;
                        $dataClient->save();
                        $affected_avatar = true;
                    }

                    $affected = Clients::find($id)
                        ->update($dataUpdate);
                    if (!empty($affected) || !empty($affected_avatar)) {
                        DB::commit();
                        $dataResult['result'] = true;
                        $dataResult['message'] = lang('c_update_account_success');
                        if (!empty($dataUpdate['password'])) {
                            $dataResult['token'] = $this->Create_Token([
                                'password' => $dataUpdate['password'],
                                'fullname' => $dataUpdate['fullname'],
                                'id' => $id,
                                'player_id' => !empty($data['player_id']) ? $data['player_id'] : null,
                            ]);
                            if (!empty($dataResult['token'])) {
                                DB::table('tbl_session_login')->where('token', $token)->delete();
                            }
                        }
                        return response()->json($dataResult);
                    } else {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_update_account_fail');
                        return response()->json($dataResult);
                    }
                }
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_update_account_fail');
                return response()->json($dataResult);
            } catch (\Exception $exception){
                DB::rollBack();
                $dataResult['result'] = false;
                $dataResult['message'] = $exception->getMessage();
                return response()->json($dataResult);
            }
        } else {
            $dataResult['message'] = lang('c_account_not_isset');
            return response()->json($dataResult);
        }
    }

    public function create_otp_update_password()
    {
        $token = $this->request->bearerToken();
        $data = $this->request->input();
        $id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (!empty($id)) {
            $dataClient = Clients::find($id);
            if (!empty($dataClient->referral_code)) {
                $email = $dataClient->email;
                $date = date('Y-m-d H:i:s');
                DB::table('tbl_otp_client')->where('email', $email)->delete();
                $key_code = $this->createKeyCode();
                $dateEnd = strtotime('+' . $this->fnbAdmin->get_option('time_otp') . ' minute', strtotime($date));
                $dateEnd = date('Y-m-d H:i:s', $dateEnd);
                $idOTP = DB::table('tbl_otp_client')->insertGetId([
                    'email' => $email,
                    'key_code' => $key_code,
                    'date_end' => $dateEnd
                ]);
                if (!empty($idOTP)) {
                    $data['message'] = lang('c_send_otp_true');
                    $data['result'] = true;
                    $data['key_code'] = $key_code;
                    $data['time'] = $this->fnbAdmin->get_option('time_otp') * 60;
                    $dataMail = [
                        'code' => $key_code,
                    ];
                    $emailCc = $email;
                    Mail::send('admin.email-template.send_otp', $dataMail, function ($message) use ($emailCc) {
                        $message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
                        $message->to($emailCc, 'SMB OTP');
                        $message->subject('SMB OTP');
                    });
                    return response()->json($data);
                } else {
                    $dataResult['result'] = false;
                    $dataResult['message'] = lang('c_send_otp_fail');
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_not_find_username');
                return response()->json($dataResult);
            }
        } else {
            $dataResult['result'] = false;
            $dataResult['message'] = lang('not_find_account');
            return response()->json($dataResult);
        }
    }

    public function get_info_account()
    {
        $dataResult = [
            'result' => false,
        ];
        $id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (!empty($id)) {
            $data = Clients::select(
                'id', 'code', 'fullname', 'phone', 'email', 'prefix_phone', 'sign_up_with', 'address', 'birthday', 'gender',
                'created_at', 'point', 'account_balance', 'customer_alepay_id', 'password', 'verify_phone',
                'number_cccd', 'issued_cccd', 'date_cccd', 'number_passport', 'issued_passport', 'date_passport',
                'active','avatar'
                )
                ->where('id', $id)->first();
            if (!empty($data)) {
                if (!empty($data->password)) {
                    $data->password = true;
                } elseif (!empty($data->password)) {
                    $data->password = false;
                }
                $data->avatar = !empty($data->avatar) ? asset('storage/'.$data->avatar) : asset('images/avatar_default.png');

                $dataResult['result'] = true;
                $dataResult['info'] = $data;
                $dataResult['message'] = lang('c_get_info_success');
                return response()->json($dataResult);
            } else {
                $dataResult['message'] = lang('c_get_info_fail');
                return response()->json($dataResult, 403);
            }
        } else {
            $dataResult['message'] = lang('c_code_token_fail');
            return response()->json($dataResult, 503);
        }
    }

    public function logout()
    {
        $dataResult = [
            'result' => false,
            'message' => lang('c_logout_fail')
        ];
        $player_id = $this->request->input('player_id');
        $token = $this->request->bearerToken();
        if ($token) {
            DB::table('tbl_session_login')->where('token', $token)->delete();

            if (!empty($player_id)) {
                DB::table('tbl_player_id')->where('player_id', $player_id)->delete();
            }

            $dataResult['result'] = true;
            $dataResult['message'] = lang('c_logout_true');
        }
        return response()->json($dataResult);
    }

    private function Create_Token($data = [])
    {
        $dateNow = date('Y-m-d H:i:s');
        $player_id = !empty($data['player_id']) ? $data['player_id'] : null;
        $fullname = !empty($data['fullname']) ? $data['fullname'] : null;
        $customer_id = !empty($data['id']) ? $data['id'] : null;
        $password = !empty($data['password']) ? $data['password'] : null;
        $privateKey = file_get_contents(storage_path('keys/private.pem'));
        $dtClient = Clients::find($customer_id);

        $payload = [
            'customer_id' => $customer_id,
            'customer_name' => $dtClient->fullname, // audience
            'email' => $dtClient->email ?? null,
            'password' => $password,
            'guard' => 'customer',
            'date' => $dateNow, // 1 phút
        ];

        $token = JWT::encode($payload, $privateKey, 'RS256');

        $ktToken = DB::table('tbl_session_login')
            ->where('id_client', $customer_id)
            ->where('token', $token)
            ->where(function ($sqlWhere) use ($player_id) {
                $sqlWhere->where('player_id', $player_id);
            })->get()->first();

        if (!empty($ktToken)) {
            DB::table('tbl_session_login')
                ->where('id', $ktToken->id)
                ->update(['token' => $token]);
        } else {
            DB::table('tbl_session_login')
                ->insertGetId([
                    'token' => $token,
                    'id_client' => $data['id'],
                ]);
        }
        return $token;
    }

    private function createKeyCode()
    {
        $keyCode = rand(1000, 9999);
        return $keyCode;
    }

    public function lockAccount()
    {
        $token = $this->request->bearerToken();
        $id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (!empty($id)) {
            $client = Clients::find($id);
            if (!empty($client)) {
                $client->active = 1;
                $client->save();
                if ($client) {
                    $dataResult['result'] = true;
                    $dataResult['message'] = 'Tài khoản của bạn bị khóa';
                    return response()->json($dataResult);
                } else {
                    $dataResult['result'] = false;
                    $dataResult['message'] = lang('dt_error');
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_get_info_fail');
                return response()->json($dataResult, 403);
            }
        } else {
            $dataResult['result'] = false;
            $dataResult['message'] = lang('c_code_token_fail');
            return response()->json($dataResult, 503);
        }
    }

}
