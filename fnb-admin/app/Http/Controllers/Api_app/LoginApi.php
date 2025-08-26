<?php

namespace App\Http\Controllers\Api_app;

use App\Helpers\FilesHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewCarCollection;
use App\Http\Resources\ReviewCarResource;
use App\Http\Resources\ReviewCustomerCollection;
use App\Models\Car;
use App\Models\ClientBusiness;
use App\Models\Clients;
use App\Models\DiscountApp;
use App\Models\Driver;
use App\Models\DrivingLiscense;
use App\Models\DrivingLiscenseDriver;
use App\Models\Notification;
use App\Models\ReferralLevel;
use App\Models\ReviewCar;
use App\Models\ReviewCustomer;
use App\Models\Transaction;
use App\Models\TransactionDriver;
use App\Models\TransactionDriverCancel;
use App\Models\TransactionNotDriver;
use App\Models\User;
use App\Traits\UploadFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Google_Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use function Laravel\Prompts\table;
use DateTime;
use GuzzleHttp;

//use App\Helpers\FilesHelpers;

class LoginApi extends AuthController
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->SaveSession = true;
        $this->ActiveServer = true;
    }

    //(1)
    public function sign_up_to_google()
    {
        $dataResult = [
            'result' => false
        ];
        $id_google = $this->request->input('id_google');
        $idToken = $this->request->input('idToken');
        $player_id = $this->request->input('player_id');
        $object_type = $this->request->input('object_type');
        $referral_code = $this->request->input('referral_code', null);
        $payload = false;
        if (!empty($this->ActiveServer)) {
            $info = $this->getUserByTokenApple($idToken);
            if ($info['sub'] == $id_google) {
                $payload = true;
            }
//            $clientGoogle = new Google_Client(['client_id' => $id_google]);
//            $payload = $clientGoogle->verifyIdToken($idToken);
        }
        if (!empty($payload) && !empty($id_google) && !empty($idToken)) {
            $data_client = DB::table('tbl_clients')->where([
                'sign_up_with' => 'google',
                'id_sign_up' => $id_google,
            ])->get()->first();
            if (!empty($data_client)) {
                if ($data_client->active == 0) {
                    $dataResult['message'] = lang('tai_khoan_cua_ban_dang_tam_khoa_vui_long_lien_he_hotline_smb_de_duoc_xu_ly');
                    return response()->json($dataResult);
                }
                $dataResult['result'] = true;
                $dataResult['id'] = $data_client->id;
                $dataResult['event'] = 'login';
                $dataResult['message'] = lang('c_login_success');
                $dataResult['token'] = $this->Create_Token([
                    'password' => $data_client->password,
                    'id' => $data_client->id,
//                    'player_id' => !empty($player_id) ? $player_id : null,
                    'sign_up_with' => 'google',
                    'id_sign_up' => $id_google,
                ]);
                if (!empty($player_id)) {
                    DB::table('tbl_player_id')->where('player_id', $player_id)->delete();

                    DB::table('tbl_player_id')->insert([
                        'object_id' => $data_client->id,
                        'object_type' => $object_type,
                        'player_id' => $player_id,
                    ]);
                }
                if (!empty($dataResult['token'])) {
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['result'] = true;
                $dataResult['event'] = 'sign_up';
                $dataResult['message'] = lang('c_login_fail');
                $dataResult['email'] = $info['email'];
                $dataResult['fullname'] = $info['name'];
                $dataResult['idToken'] = $idToken;
                $dataResult['id_apple'] = $id_google;
                $dataResult['player_id'] = $player_id;
                $dataResult['object_type'] = $object_type;
                $dataResult['sign_up_with'] = 'google';
                $dataResult['id_sign_up'] = $id_google;
                $dataResult['referral_code'] = $referral_code;
                $dataResult['avatar'] = !empty($info['picture']) ? $info['picture'] : null;
                $dataResultNew = $this->sign_up_apple($dataResult);
                return $dataResultNew;
            }
        } else {
            $dataResult['message'] = lang('c_login_fail');
            return response()->json($dataResult, 500);
        }
    }

    //(1)
    public function sign_up_to_apple()
    {
        $dataResult = [
            'result' => false
        ];
        $id_apple = $this->request->input('id_apple');
        $idToken = $this->request->input('idToken');
        $player_id = $this->request->input('player_id');
        $object_type = $this->request->input('object_type');
        $fullname = $this->request->input('fullname');
        $payload = true;
        if (!empty($this->ActiveServer)) {
            $info = $this->getUserByTokenApple($idToken);
            if ($info['sub'] == $id_apple) {
                $payload = true;
            }
        }
        if (!empty($payload) && !empty($idToken) && !empty($id_apple)) {
            $data_client = DB::table('tbl_clientss')->where([
                'sign_up_with' => 'apple',
                'id_sign_up' => $id_apple,
            ])->get()->first();
            if (!empty($data_client)) {
                $dataResult['result'] = true;
                $dataResult['id'] = $data_client->id;
                $dataResult['event'] = 'login';
                $dataResult['message'] = lang('c_login_success');
                $dataResult['token'] = $this->Create_Token([
                    'password' => $data_client->password,
                    'id' => $data_client->id,
//                    'player_id' => !empty($player_id) ? $player_id : null,
                    'sign_up_with' => 'apple',
                    'id_sign_up' => $id_apple,
                ]);
                if (!empty($player_id)) {
                    DB::table('tbl_player_id')->where('player_id', $player_id)->delete();

                    DB::table('tbl_player_id')->insert([
                        'object_id' => $data_client->id,
                        'object_type' => $object_type,
                        'player_id' => $player_id,
                    ]);
                }
                if (!empty($dataResult['token'])) {
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['result'] = true;
                $dataResult['event'] = 'sign_up';
                $dataResult['message'] = lang('c_login_fail');
                $dataResult['email'] = $info['email'];
                $dataResult['fullname'] = !empty($fullname) ? $fullname : 'NoName';
                $dataResult['idToken'] = $idToken;
                $dataResult['id_apple'] = $id_apple;
                $dataResult['player_id'] = $player_id;
                $dataResult['object_type'] = $object_type;
                $dataResult['sign_up_with'] = 'apple';
                $dataResult['id_sign_up'] = $id_apple;
                $dataResultNew = $this->sign_up_apple($dataResult);
                return $dataResultNew;
            }
        } else {
            $dataResult['message'] = lang('c_login_fail');
            return response()->json($dataResult, 500);
        }
    }

    public function sign_up_to_facebook_khong_dung()
    {
        $dataResult = [
            'result' => false
        ];
        $id_facebook = $this->request->input('id_facebook');
        $idToken = $this->request->input('idToken');
        $player_id = $this->request->input('player_id');
        $payload = true;
        if (!empty($this->ActiveServer)) {
            $curl = 'https://graph.facebook.com/v13.0/me?fields=id,name&access_token=' . $idToken;
            $ktSuccess = json_decode(GetCurlData($curl));
            if ($ktSuccess->id) {
                $payload = true;
            }
        }
        if (!empty($payload) && $ktSuccess->id == $id_facebook) {
            $data_client = DB::table('tbl_clients')->where([
                'sign_up_with' => 'facebook',
                'id_sign_up' => $id_facebook,
            ])->get()->first();
            if (!empty($data_client)) {
                $dataResult['result'] = true;
                $dataResult['event'] = 'login';
                $dataResult['message'] = lang('c_login_success');
                $dataResult['token'] = $this->Create_Token([
                    'password' => $data_client->password,
                    'id' => $data_client->id,
                    'player_id' => !empty($player_id) ? $player_id : null,
                    'sign_up_with' => 'facebook',
                    'id_sign_up' => $id_facebook,
                ]);

                if (!empty($dataResult['token'])) {
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['result'] = true;
                $dataResult['event'] = 'sign_up';
                $dataResult['message'] = lang('c_login_fail');
                return response()->json($dataResult, 200);
            }
        } else {
            $dataResult['message'] = lang('c_login_fail');
            return response()->json($dataResult, 500);
        }
    }

    //(3)đăng ký
    public function sign_up_apple($dataPost = array())
    {
        $dataResult = [
            'result' => false,
        ];
        if ($dataPost) {
            $data = $dataPost;
            if (empty($data['fullname'])) {
                $dataResult['message'] = lang('c_pls_input_fullname');
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
            $referral_code = !empty($data['referral_code']) ? $data['referral_code'] : null;
            try {
                $dataInsert = [
                    'code' => getReference('client'),
                    'phone' => !empty($data['phone']) ? $data['phone'] : null,
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
                        'sign_up_with' => $data['sign_up_with'],
                        'id_sign_up' => $data['id_sign_up'],
                    ])->get()->first();

                    if (!empty($data_client_with)) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_error_sign_up_lk');
                        return response()->json($dataResult);
                    }
                }

//                $discountApp = DiscountApp::where('default', 1)->first();
//                if (!empty($discountApp)) {
//                    $dataInsert['discount_app_id'] = $discountApp->id;
//                } else {
//                    $discountApp = DiscountApp::first();
//                    $dataInsert['discount_app_id'] = $discountApp->id;
//                }

                $id = DB::table('tbl_clients')->insertGetId($dataInsert);
                updateReference('client');
                if (!empty($id)) {
                    $getClient = Clients::find($id);
                    $getClient->referral_code = generateRandomString($id);
                    $getClient->save();
                }
                if (!empty($_FILES['avatar'])) {
                    FilesHelpers::maybe_create_upload_path('upload/clients/');
                    $paste_image = 'upload/clients/' . $id . '/';
                    $paste_imageShort = 'upload/clients/' . $id . '/';
                    $image_avatar = FilesHelpers::uploadFileData($this->request->file('avatar'), 'avatar', $paste_image,
                        $paste_imageShort);
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

                $dataResult['result'] = true;
                $dataResult['id'] = $id;
                $dataResult['message'] = lang('c_login_success');
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

                if (!empty($referral_code)) {
                    $dtCustomer = Clients::where('referral_code',$referral_code)->first();
                    $referralLevel = new ReferralLevel();
                    $referralLevel->customer_id = $id;
                    $referralLevel->parent_id = $dtCustomer->id;
                    $referralLevel->referral_code = $dtCustomer->referral_code;
                    $referralLevel->level = 0;
                    $referralLevel->save();
                    Notification::notiRegisterByReferralCode($id, Config::get('constant')['noti_register_by_referral'],
                        $id);
                }

                //gui mail
                $dataMail = [
                    'fullname' => !empty($data['fullname']) ? $data['fullname'] : null,
                    'email' => !empty($data['email']) ? $data['email'] : null,
                    'phone' => !empty($data['phone']) ? $data['phone'] : null,
                    'date_create' => date('Y-m-d H:i:s'),
                ];
                $emailCc = 'thuannguyen.fososoft@gmail.com';
                $emailCc = null;
                if (!empty($emailCc)) {
                    Mail::send('admin.email-template.new_customer_register', $dataMail,
                        function ($message) use ($emailCc) {
                            $message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
                            $message->to($emailCc, 'Đăng ký người dùng');
                            $message->subject('Đăng ký người dùng');
                        });
                }

                if (!empty($dataResult['token'])) {
                    return response()->json($dataResult);
                }
            } catch (\Exception $exception) {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_sign_up_fail');
                return response()->json($dataResult);
            }

        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    public function login_facebook()
    {
        $data = [];
        $validator = Validator::make(
            $this->request->all(),
            [
                'id_token' => 'required',
            ],
        );

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            return response()->json($data);
        } else {
            $sign_up_with = 'facebook';
            //            $id_facebook = $this->request->input('id_facebook');
            //            $type = $this->request->input('type');
            $player_id = $this->request->input('player_id');
            $id_token = $this->request->input('id_token');
            $referral_code = $this->request->input('referral_code', null);
            //            $object_type = $this->request->input('object_type');
            $object_type = 'customer';

            $FACEBOOK_CLIENT_ID = get_option('facebook_app_id');
            $FACEBOOK_CLIENT_SECRET = get_option('facebook_secret');
            $PUBLIC_URL_WEBSITE = get_option('redirect_uri_facebook');
            $graph_facebook = "https://graph.facebook.com/oauth/access_token?client_id=$FACEBOOK_CLIENT_ID&client_secret=$FACEBOOK_CLIENT_SECRET&redirect_uri=$PUBLIC_URL_WEBSITE/api/auth/callback/facebook&code=$id_token";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $graph_facebook);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $token_facebook = json_decode($response);
            //            $token_facebook->access_token = 'EAAKCTvHU9XkBADQgZCSOD66HIlwU9bQK8gnMumUiXmQKBYDRRwB69JkI13rKNKZCYZBj3WVXZCZBn4dnNAJNTWpyTRivxVKwMMCoKUWKVav9EysjkKKMoSpZBhG1YLdFJgk2sF0P04vIDBDtY0veHei3LgNoUMAVBLHeUICqiqm6xj9ZAP1ZCXZAB';
            if (!empty($token_facebook->access_token)) {
                $access_token = $token_facebook->access_token;
                $url_info_facebook = 'https://graph.facebook.com/' . get_option('version_sdk_facebook') . '/me?fields=id,email,name,picture';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url_info_facebook);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer $access_token",
                    "Content-Type: application/json"
                ]);

                $response = curl_exec($ch);
                curl_close($ch);
                $info_facebook = json_decode($response);
            } elseif (!empty($token_facebook->error)) {
                $data['result'] = 0;
                $data['message'] = $token_facebook->error->message;
                return response()->json($data, 401);
            }

            if (empty($info_facebook)) {
                $data['result'] = 0;
                $data['message'] = lang('not_find_data_facebook');
                return response()->json($data);
            }

            $id_facebook = $info_facebook->id;
            //            $email = $info_facebook->email;
            //            $name = $info_facebook->name;

            $info = [
                'id_facebook' => $id_facebook,
                'email' => $info_facebook->email,
                'name' => $info_facebook->name,
                'picture' => !empty($info_facebook->picture) ? $info_facebook->picture->data->url : null,
            ];

            if (!empty($id_facebook)) {
                $data_client = DB::table('tbl_clients')->where([
                    'sign_up_with' => 'facebook',
                    'id_sign_up' => $id_facebook,
                ])->get()->first();
                if (!empty($data_client)) {
                    if ($data_client->active == 0) {
                        $dataResult['message'] = lang('tai_khoan_cua_ban_dang_tam_khoa_vui_long_lien_he_hotline_smb_de_duoc_xu_ly');
                        return response()->json($dataResult);
                    }
                    $dataResult['result'] = true;
                    $dataResult['id'] = $data_client->id;
                    $dataResult['event'] = 'login';
                    $dataResult['message'] = lang('c_login_success');
                    $dataResult['token'] = $this->Create_Token([
                        'password' => $data_client->password,
                        'id' => $data_client->id,
                        //                    'player_id' => !empty($player_id) ? $player_id : null,
                        'sign_up_with' => $sign_up_with,
                        'id_sign_up' => $id_facebook,
                    ]);
                    if (!empty($player_id)) {
                        DB::table('tbl_player_id')->where('player_id', $player_id)->delete();

                        DB::table('tbl_player_id')->insert([
                            'object_id' => $data_client->id,
                            'object_type' => $object_type,
                            'player_id' => $player_id,
                        ]);
                    }
                    if (!empty($dataResult['token'])) {
                        $dataResult['message'] = lang('c_login_success');
                        return response()->json($dataResult);
                    }
                } else {
                    $dataResult['result'] = true;
                    $dataResult['event'] = 'sign_up';
                    $dataResult['message'] = lang('c_login_fail');
                    $dataResult['email'] = $info['email'];
                    $dataResult['fullname'] = $info['name'];
                    //                    $dataResult['idToken'] = $idToken;
                    //                    $dataResult['id_apple'] = $id_facebook;
                    $dataResult['player_id'] = $player_id;
                    $dataResult['object_type'] = $object_type;
                    $dataResult['sign_up_with'] = $sign_up_with;
                    $dataResult['id_sign_up'] = $id_facebook;
                    $dataResult['referral_code'] = $referral_code;
                    $dataResult['avatar'] = !empty($info['picture']) ? $info['picture'] : null;
                    $dataResultNew = $this->sign_up_apple($dataResult);
                    return $dataResultNew;
                }
            } else {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_login_fail');
                return response()->json($dataResult, 500);
            }

        }
        return response()->json($data);
    }

    public function sign_up()
    {
        $check_otp = get_option('check_otp');
        $otp_default = get_option('otp_default');
        $dataResult = [
            'result' => false,
        ];
        if ($this->request->input()) {
            $data = $this->request->input();
            if (empty($data['username'])) {
                $dataResult['message'] = lang('vui_long_nhap_username');
                return response()->json($dataResult);
            }

            if (empty($data['email'])) {
                $dataResult['message'] = lang('vui_long_nhap_dia_chi_email');
                return response()->json($dataResult);
            }

            if (empty($data['fullname'])) {
                $dataResult['message'] = lang('c_pls_input_fullname');
                return response()->json($dataResult);
            }
            if (empty($data['phone']) && empty($data['email'])) {
                $dataResult['message'] = lang('c_pls_input_phone_sign_up');
                return response()->json($dataResult);
            }

            $referral_code = !empty($data['referral_code']) ? $data['referral_code'] : null;

            if (!empty($referral_code)){
                $checkExists = Clients::where('referral_code',$referral_code)->first();
                if (empty($checkExists)){
                    $dataResult['result'] = false;
                    $dataResult['message'] = lang('referral_code_khong_ton_tai');
                    return response()->json($dataResult);
                }
            }

            if (empty($data['password']) && empty($data['id_sign_up'])) {
                $dataResult['message'] = lang('c_pls_input_password');
                return response()->json($dataResult);
            }

            if (!empty($data['email'])) {
                $kt_email = DB::table('tbl_clients')
                    ->where('email', $data['email'])
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
                if ($check_otp == 1) {
                    $ktKeyCode = DB::table('tbl_otp_client')
                        ->where('email', $data['email'])
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
                    if ($data['key_code'] != $otp_default) {
                        $dataResult['result'] = false;
                        $dataResult['message'] = lang('c_code_otp_fail');
                        return response()->json($dataResult);
                    }
                }

            }
            try {
                $dataInsert = [
                    'code' => getReference('client'),
                    'phone' => !empty($data['phone']) ? $data['phone'] : null,
                    'referral_code' => !empty($data['username']) ? $data['username'] : null,
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
                updateReference('client');
                if (!empty($_FILES['avatar'])) {
                    FilesHelpers::maybe_create_upload_path('upload/clients/');
                    $paste_image = 'upload/clients/' . $id . '/';
                    $paste_imageShort = 'upload/clients/' . $id . '/';
                    $image_avatar = FilesHelpers::uploadFileData($this->request->file('avatar'), 'avatar', $paste_image,
                        $paste_imageShort);
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

                if (!empty($data['email'])) {
                    DB::table('tbl_otp_client')->where('email', $data['email'])->delete();
                }

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

                if (!empty($referral_code)) {
                    $dtCustomer = Clients::where('referral_code',$referral_code)->first();
                    if (!empty($dtCustomer)) {
                        $referralLevel = new ReferralLevel();
                        $referralLevel->customer_id = $id;
                        $referralLevel->parent_id = $dtCustomer->id;
                        $referralLevel->referral_code = $dtCustomer->referral_code;
                        $referralLevel->level = 0;
                        $referralLevel->save();
                        Notification::notiRegisterByReferralCode($id,
                            Config::get('constant')['noti_register_by_referral'],
                            $id);
                    }
                }


                //gui mail
                $dataMail = [
                    'fullname' => !empty($data['fullname']) ? $data['fullname'] : null,
                    'email' => !empty($data['email']) ? $data['email'] : null,
                    'phone' => !empty($data['phone']) ? $data['phone'] : null,
                    'date_create' => date('Y-m-d H:i:s'),
                ];
                $emailCc = 'thuannguyen.fososoft@gmail.com';
                $emailCc = null;
                if (!empty($emailCc)) {
                    Mail::send('admin.email-template.new_customer_register', $dataMail,
                        function ($message) use ($emailCc) {
                            $message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
                            $message->to($emailCc, 'Đăng ký người dùng');
                            $message->subject('Đăng ký người dùng');
                        });
                }

                if (!empty($dataResult['token'])) {
                    return response()->json($dataResult);
                }
            } catch (\Exception $exception) {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_sign_up_fail');
                return response()->json($dataResult);
            }
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    //đăng nhập
    public function login()
    {
        $dataResult = [
            'result' => false,
        ];
        if ($this->request->input()) {
            $data = $this->request->input();
            if (empty($data['phone']) && empty($data['email'])) {
                $dataResult['message'] = lang('c_pls_input_phone_login');
                return response()->json($dataResult);
            }
            if (empty($data['password'])) {
                $dataResult['message'] = lang('c_pls_input_password');
                return response()->json($dataResult);
            }
            if (!empty($data['phone'])) {
                $ktLogin = DB::table('tbl_clients')
                    ->where('phone', $data['phone'])->first();
                if (empty($ktLogin)) {
                    $dataResult['message'] = lang('c_not_find_phone');
                    return response()->json($dataResult);
                }
            } else {
                $ktLogin = DB::table('tbl_clients')
                    ->where('referral_code', $data['email'])->first();
                if (empty($ktLogin)) {
                    $dataResult['message'] = lang('c_not_find_username');
                    return response()->json($dataResult);
                }
            }
            if (!empty($ktLogin)) {
                if ($ktLogin->active == 0) {
                    $dataResult['message'] = lang('tai_khoan_cua_ban_dang_tam_khoa_vui_long_lien_he_hotline_smb_de_duoc_xu_ly');
                    return response()->json($dataResult);
                }
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
                $dataResult['message'] = lang('c_phone_or_password_not');
                return response()->json($dataResult);
            }
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    public function check_otp_forgot_password()
    {
        if ($this->request->input()) {
            $data = $this->request->input();
            if (!empty($data['email'])) {
                $kt_email = DB::table('tbl_clients')
                    ->where('email', $data['email'])
                    ->first();
                if (empty($kt_email)) {
                    $dataResult['message'] = lang('dia_chi_email_nay_chua_dang_ky_tai_khoan');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            }

            if (empty($data['key_code'])) {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('khong_ton_tai_otp');
                return response()->json($dataResult);
            } else {
                $ktKeyCode = DB::table('tbl_otp_client')
                    ->where('email', $data['email'])
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
            $dataResult['result'] = true;
            $dataResult['message'] = 'Nhập OTP thành công!';
            return response()->json($dataResult);
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    public function check_otp_forgot()
    {
        if ($this->request->input()) {
            $data = $this->request->input();
            if (empty($data['email']) || empty($data['key_code'])) {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('da_co_loi_xay_ra_vui_long_thu_lai');
                return response()->json($dataResult);
            }

            if (!empty($data['email'])) {
                $kt_email = DB::table('tbl_clients')
                    ->where('referral_code', $data['email'])
                    ->first();
                if (empty($kt_email)) {
                    $dataResult['message'] = lang('c_not_find_username');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            }

            if (empty($data['key_code'])) {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('khong_ton_tai_otp');
                return response()->json($dataResult);
            } else {
                $ktKeyCode = DB::table('tbl_otp_client')
                    ->where('email', $data['email'])
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

            $dataResult['result'] = true;
            $dataResult['message'] = lang('c_otp_success');
            return response()->json($dataResult);
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    public function check_otp_and_update_password()
    {
        if ($this->request->input()) {
            $data = $this->request->input();
            if (empty($data['email']) || empty($data['key_code']) || empty($data['password'])) {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('da_co_loi_xay_ra_vui_long_thu_lai');
                return response()->json($dataResult);
            }

            if (!empty($data['email'])) {
                $kt_email = DB::table('tbl_clients')
                    ->where('email', $data['email'])
                    ->first();
                if (empty($kt_email)) {
                    $dataResult['message'] = lang('c_not_find_email');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            }

            if (empty($data['key_code'])) {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('khong_ton_tai_otp');
                return response()->json($dataResult);
            } else {
                $ktKeyCode = DB::table('tbl_otp_client')
                    ->where('email', $data['email'])
//                    ->whereRaw('DATE_FORMAT(date_end, "%Y-%m-%d %H:%i:%s") > "'.date('Y-m-d H:i:s').'"')
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

            $client = Clients::find($kt_email->id);
            $client->password = encrypt($data['password']);
            $client->save();

            $dataResult['result'] = true;
            $dataResult['message'] = lang('cap_nhat_mat_khau_thanh_cong');
            return response()->json($dataResult);
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    public function create_otp_forgot()
    {
        $email = $this->request->input('email');
        if (!empty($email)) {
            if (!empty($data['email'])) {
                $kt_email = DB::table('tbl_clients')
                    ->where('email', $data['email'])
                    ->first();
                if (empty($kt_email)) {
                    $dataResult['message'] = lang('email_nay_chua_dang_ky_tai_khoan');
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            }
            $date = date('Y-m-d H:i:s');

            DB::table('tbl_otp_client')->where('email', $email)->delete();
            $key_code = $this->createKeyCode();
            $dateEnd = strtotime('+' . get_option('time_otp') . ' minute', strtotime($date));
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
                $data['time'] = get_option('time_otp') * 60;
                $dataMail = [
                    'code' => $key_code,
                ];
                $emailCc = $email;
                Mail::send('admin.email-template.send_otp', $dataMail, function ($message) use ($emailCc) {
                    $message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
                    $message->to($emailCc, 'Email OTP');
                    $message->subject('Email OTP');
                });
                return response()->json($data);
            } else {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_send_otp_fail');
                return response()->json($dataResult);
            }
        } else {
            $dataResult['result'] = false;
            $dataResult['message'] = lang('vui_long_nhap_dia_chi_email_da_dang_ky');
            return response()->json($dataResult);
        }
    }

    public function forgot_password()
    {
        if ($this->request->input()) {
            $data = $this->request->input();
            if (!empty($data['email'])) {
                $kt_email = DB::table('tbl_clients')
                    ->where('email', $data['email'])
                    ->first();
                if (empty($kt_email)) {
                    $dataResult['message'] = lang('email_nay_chua_dang_ky_tai_khoan');
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
                $client = Clients::find($kt_email->id);
                $client->password = encrypt($data['password']);
                $client->save();
                if ($client) {
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
                $dataResult['message'] = $exception;
                return response()->json($dataResult);
            }
        } else {
            $dataResult['message'] = lang('c_pls_input');
            return response()->json($dataResult);
        }
    }

    //(2)tạo + gửi OTP
    public function create_otp_sign_up()
    {
        $check_otp = get_option('check_otp');
        $otp_default = get_option('otp_default');
        $referral_code = $this->request->input('referral_code');
        $validator = Validator::make($this->request->all(),
            [
                'username' => 'required|unique:tbl_clients,referral_code',
                'email' => 'required|unique:tbl_clients,email',
                'fullname' => 'required',
                'password' => 'required',
            ],
            [
                'username.required' => lang('vui_long_nhap_username'),
                'username.unique' => lang('username_da_duoc_su_dung'),
                'email.required' => lang('vui_long_nhap_dia_chi_email'),
                'email.unique' => lang('dia_chi_email_da_duoc_su_dung'),
                'fullname.required' => lang('vui_long_nhap_ho_va_ten'),
                'password.required' => lang('vui_long_nhap_mat_khau'),
            ]);

        $errors = [];
        if (!empty($referral_code)){
            $checkExists = Clients::where('referral_code',$referral_code)->first();
            if (empty($checkExists)){
                $errors = [
                    'referral_code' => [lang('referral_code_khong_ton_tai')]
                ];
            }
        }
        if ($validator->fails()) {
            $errors = array_merge($errors,$validator->errors()->getMessages());
        }
        if (!empty($errors)){
            $data['result'] = false;
            $data['message'] = $errors;
            return response()->json($data);
        }
        $email = $this->request->input('email');
        if (!empty($email)) {
            if ($check_otp == 1) {
                $date = date('Y-m-d H:i:s');
                DB::table('tbl_otp_client')->where('email', $email)->delete();
                $key_code = $this->createKeyCode();
                $dateEnd = strtotime('+' . get_option('time_otp') . ' minute', strtotime($date));
                $dateEnd = date('Y-m-d H:i:s', $dateEnd);
                $idOTP = DB::table('tbl_otp_client')->insertGetId([
                    'email' => $email,
                    'key_code' => $key_code,
                    'date_end' => $dateEnd
                ]);
            } else {
                $key_code = $otp_default;
                $idOTP = true;
            }
            if (!empty($idOTP)) {
                $data['message'] = lang('c_send_otp_true');
                $data['result'] = true;
                $data['key_code'] = $key_code;
                $data['time'] = get_option('time_otp') * 60;
                $dataMail = [
                    'code' => $key_code,
                ];
                if ($check_otp == 1) {
                    $emailCc = $email;
                    Mail::send('admin.email-template.send_otp', $dataMail, function ($message) use ($emailCc) {
                        $message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
                        $message->to($emailCc, 'SMB OTP');
                        $message->subject('SMB OTP');
                    });
                }
                return response()->json($data);
            } else {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_send_otp_fail');
                return response()->json($dataResult);
            }
        }
        $data['result'] = false;
        $data['message'] = lang('c_send_otp_fail');
        return response()->json($data);
    }

    public function create_otp_sign_up_old_vs1()
    {
        $phone = $this->request->input('phone');
        $event = !empty($this->request->input('event')) ? $this->request->input('event') : 'register';
        if (!empty($phone)) {
            $date = date('Y-m-d H:i:s');

            $countSendSms = DB::table('tbl_send_sms')->where('phone', $phone)
                ->where('status', 1)
                ->where('event', 'change_password')
                ->where(DB::raw('DATE_FORMAT(date_send,"%Y-%m-%d")'), '=', date('Y-m-d'))->count();
            if ($event == 'change_password') {
                $ktPhoneClient = DB::table('tbl_clients')
                    ->where('phone', $phone)
                    ->get()->first();
                if (empty($ktPhoneClient)) {
                    $dataResult['message'] = 'Số điện thoại này chưa đăng ký tài khoản!';
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
                if ($countSendSms >= get_option('limit_otp_change_pass')) {
                    $dataResult['message'] = 'Số điện thoại này đã vượt quá số lần gửi OTP trong 1 ngày!';
                    $dataResult['result'] = false;
                    return response()->json($dataResult);
                }
            } elseif ($event == 'register') {
                $ktPhoneClient = DB::table('tbl_clients')
                    ->where('phone', $phone)
                    ->get()->first();
                if (!empty($ktPhoneClient)) {
                    $dataResult['message'] = lang('c_phone_isset_pls_input_phone_other');
                    $dataResult['result'] = false;
                    $dataResult['isset'] = true;
                    return response()->json($dataResult);
                }
            }

            DB::table('tbl_otp_client')->where('phone', $phone)->delete();
            $key_code = $this->createKeyCode();
            $dateEnd = strtotime('+' . get_option('time_otp') . ' minute', strtotime($date));
            $dateEnd = date('Y-m-d H:i:s', $dateEnd);
            $idOTP = DB::table('tbl_otp_client')->insertGetId([
                'phone' => $phone,
                'key_code' => $key_code,
                'date_end' => $dateEnd
            ]);
            if (!empty($idOTP)) {
                $dataResult['message'] = lang('c_send_otp_true');
                $dataResult['result'] = true;
                $dataResult['key_code'] = $key_code;
                $dataResult['time'] = get_option('time_otp') * 60;
                $content_sms = '';
                if ($event == 'register') {
                    $content_sms = get_option('content_otp_register');
                } elseif ($event == 'change_password') {
                    $content_sms = get_option('content_otp_change_pass');
                }
                $content_sms = str_replace('{code}', $key_code, $content_sms);
                send_sms($phone, $content_sms, $event);
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

    public function create_otp_sign_up_old()
    {
        $phone = $this->request->input('phone');
        if (!empty($phone)) {
            $ktPhoneClient = DB::table('tbl_clients')
                ->where('phone', $phone)
                ->get()->first();
            if (!empty($ktPhoneClient)) {
                $dataResult['message'] = lang('c_phone_isset_pls_input_phone_other');
                $dataResult['result'] = false;
                $dataResult['isset'] = true;
                return response()->json($dataResult);
            }

            $ktOTPClient = DB::table('tbl_otp_client')
                ->where('phone', $phone)
                ->whereRaw('DATE_FORMAT(date_end, "%Y-%m-%d %H:%i:%s") > "' . date('Y-m-d H:i:s') . '"')
                ->get()->first();
            $date = date('Y-m-d H:i:s');
            if (empty($ktOTPClient)) {
                DB::table('tbl_otp_client')->where('phone', $phone)->delete();

                $key_code = $this->createKeyCode();
                $dateEnd = strtotime('+' . get_option('time_otp') . ' minute', strtotime($date));
                $dateEnd = date('Y-m-d H:i:s', $dateEnd);
                $idOTP = DB::table('tbl_otp_client')->insertGetId([
                    'phone' => $phone,
                    'key_code' => $key_code,
                    'date_end' => $dateEnd
                ]);
                if (!empty($idOTP)) {
                    $dataResult['message'] = lang('c_send_otp_true');
                    $dataResult['result'] = true;
                    $dataResult['key_code'] = $key_code;
                    $dataResult['time'] = get_option('time_otp') * 60;
                    return response()->json($dataResult);
                }
            } else {
                $dateS = new DateTime($date);
                $dateE = new DateTime($ktOTPClient->date_end);
                $dataResult['result'] = true;
                $dataResult['key_code'] = $ktOTPClient->key_code;
                $dataResult['time'] = ($dateS->diff($dateE)->format("%i") * 60) + $dateS->diff($dateE)->format("%s");
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
            $dt = Carbon::now();
            if (!empty($data) || !empty($_FILES['avatar'])) {
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

//                if (isset($data['password'])) {
//                    $dataUpdate['password'] = encrypt($data['password']);
//                    if (!empty($dataClient) && !empty($dataClient['password'])) {
//                        if (decrypt($dataClient['password']) != $data['password_old']) {
//                            $dataResult['result'] = false;
//                            $dataResult['message'] = 'Mật khẩu cũ không đúng!';
//                            return response()->json($dataResult);
//                        }
//                    }
//                }
                if (!empty($_FILES['avatar'])) {
                    FilesHelpers::maybe_create_upload_path('upload/clients/');
                    $paste_image = 'upload/clients/' . $id . '/';
                    $paste_imageShort = 'upload/clients/' . $id . '/';
                    $image_avatar = FilesHelpers::uploadFileData($this->request->file('avatar'), 'avatar', $paste_image,
                        $paste_imageShort);
                    if (!empty($image_avatar)) {
                        $avatar = is_array($image_avatar) ? $image_avatar[0] : $image_avatar;
                        if (!empty($avatar)) {
                            $dataUpdate['avatar'] = $avatar;
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
                        $dataUpdate['avatar'] = $paste_img . $time . '.jpg';
                    }
                }

                if (!empty($dataUpdate)) {
                    $affected = Clients::find($id)
                        ->update($dataUpdate);
                    if (!empty($affected)) {
                        $dataResult['result'] = true;
                        $dataResult['message'] = lang('c_update_account_success');
                        if (!empty($dataUpdate['password'])) {
                            $dataResult['token'] = $this->Create_Token([
                                'password' => $dataUpdate['password'],
                                'id' => $id,
                                'player_id' => !empty($data['player_id']) ? $data['player_id'] : null,
                            ]);
                            if (!empty($dataResult['token'])) {
                                DB::table('tbl_session_login')->where('token', $token)->delete();
                            }
                        }
                        return response()->json($dataResult);
                    }
                }
            }
            $dataResult['result'] = false;
            $dataResult['message'] = lang('c_update_account_fail');
            return response()->json($dataResult);
        } else {
            $dataResult['message'] = lang('c_account_not_isset');
            return response()->json($dataResult);
        }
    }


    //Gửi otp khi đổi mật khẩu

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
                $dateEnd = strtotime('+' . get_option('time_otp') . ' minute', strtotime($date));
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
                    $data['time'] = get_option('time_otp') * 60;
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

    public function create_otp_update_password_old()
    {
        $token = $this->request->bearerToken();
        $data = $this->request->input();
        $id = !empty($this->request->client) ? $this->request->client->id : 0;
        if (!empty($id) && !empty($data['password'])) {
            $dataClient = Clients::find($id);
            if (is_null($dataClient->sign_up_with) && !empty($dataClient->email)) {
                if (decrypt($dataClient->password) == ($data['password'])) {
                    $email = $dataClient->email;
                    $date = date('Y-m-d H:i:s');
                    DB::table('tbl_otp_client')->where('email', $email)->delete();
                    $key_code = $this->createKeyCode();
                    $dateEnd = strtotime('+' . get_option('time_otp') . ' minute', strtotime($date));
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
                        $data['time'] = get_option('time_otp') * 60;
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
                    $dataResult['message'] = lang('c_old_password_is_incorrect');
                    return response()->json($dataResult);
                }
            } else {
                $dataResult['result'] = false;
                $dataResult['message'] = lang('c_not_find_email');
                return response()->json($dataResult);
            }
        } else {
            $dataResult['result'] = false;
            $dataResult['message'] = lang('c_not_find_password');
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
                'id', 'code', 'fullname', 'phone', 'email', 'prefix_phone', 'sign_up_with', 'address', 'birthday',
                'gender',
                'created_at', 'point', 'account_balance', 'customer_alepay_id', 'password', 'verify_phone',
                'number_cccd', 'issued_cccd', 'date_cccd', 'number_passport', 'issued_passport', 'date_passport',
                'referral_code', 'locale', 'active'
            )->selectRaw('CONCAT("' . url('/') . '/", avatar) as avatar')
                ->with(['customer_class' => function($q){
                    $q->with(['category_card' => function($instance){
                        $instance->select('id','code','name','total','number_night','percent');
                        $instance->selectRaw('CONCAT("' . asset('storage/') . '/", image) as image');
                        $instance->selectRaw('CONCAT("' . asset('storage/') . '/", avatar) as avatar');
                    }]);
                }])
                ->with(['class_customer' => function($q){
                        $q->select('tbl_class_customer.id','tbl_class_customer.name','tbl_class_customer.class_id','tbl_class_customer.customer_id','tbl_class_customer.balance','tbl_class_customer.percent');
                        $q->selectRaw('CONCAT("' . asset('storage/') . '/", tbl_setting_customer_class.image) as image');
                        $q->join('tbl_setting_customer_class', 'tbl_setting_customer_class.id', '=', 'tbl_class_customer.class_id');
                    }])
                ->with('reward')
                ->with('reward_day_profit')
                ->with('reward_day_commission')
                ->with('reward_day_ranking_bonus')
                ->with('reward_day_leadership_bonus')
                ->with('leadership_customer')
                ->where('id', $id)->first();
            if (!empty($data)) {
                if (!empty($data->password)) {
                    $data->password = true;
                } elseif (!empty($data->password)) {
                    $data->password = false;
                }
                if (empty($data->avatar)) {
                    $data->avatar = url('/') . '/admin/assets/images/avatar.jpg';
                }

                $data->locale = $data->locale ?? config('constant.locale_default');
                $max_withdraw = get_option('coefficient_withdraw') * $data->transaction->where('status', 1)->sum('total');
                $date_customer_class = !empty($data->customer_class) ? $data->customer_class->created_at : null;
                if (!empty($date_customer_class)) {
                    $total_withdraw = !empty($data->request_withdraw_money) ? $data->request_withdraw_money->whereIn('status',[0,1])->sum('total') : 0;
                    $total_transfer_package = !empty($data->transfer_package) ? $data->transfer_package->whereIn('status',[0,1])->sum('total') : 0;
                    $total_withdraw += $total_transfer_package;
                } else {
                    $total_withdraw = 0;
                }
                $data->max_withdraw = $max_withdraw;
                $data->total_withdraw = $total_withdraw;
                $max_total_withdraw_warning = (get_option('percent_withdraw_limit_warning') * $max_withdraw) / 100;
                $data->max_total_withdraw_warning = $max_total_withdraw_warning;
                $content_warning_mail = get_option('content_warning_mail');
                $email_company = get_option('phone_company');
                $phone_company = get_option('phone_company');
                $content_warning_mail = str_replace('{name_customer}', $data->fullname, $content_warning_mail);
                $content_warning_mail = str_replace('{email_company}', $email_company, $content_warning_mail);
                $content_warning_mail = str_replace('{phone_company}', $phone_company, $content_warning_mail);
                $data->content_warning_mail = $content_warning_mail;
                unset($data->transfer_money);
                $dataResult['result'] = true;
                $dataResult['info'] = $data;
                $dataResult['info']['total_transaction'] = $data->transaction->where('status', 1)->sum('total');
                $dataResult['info']['customer_class'] = $data->customer_class;
                $dataResult['info']['class_customer'] = $data->class_customer;
                $dataResult['info']['reward'] = !empty($data->reward) ? $data->reward->balance : 0;
                $dataResult['info']['reward_day_profit'] = !empty($data->reward_day_profit) ? $data->reward_day_profit->balance : 0;
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
        $dateCreate = @date("Y-m-d H:i:s");
        $player_id = $data['player_id'] = !empty($data['player_id']) ? $data['player_id'] : null;
        $sign_up_with = $data['sign_up_with'] = !empty($data['sign_up_with']) ? $data['sign_up_with'] : null;
        $id_sign_up = $data['id_sign_up'] = !empty($data['id_sign_up']) ? $data['id_sign_up'] : null;
        $project = 'SMB';

        if (empty($data['password']) && empty($sign_up_with)) {
            return null;
        }

        $token = base64_encode($data['id']
            . '|||' . $data['password']
            . '|||' . $dateCreate
            . '|||' . $data['player_id']
            . '|||' . $sign_up_with
            . '|||' . $id_sign_up
            . '|||' . $project
        );

        if (!empty($this->SaveSession)) {
            $user_agent = $this->request->server('HTTP_USER_AGENT');
//            $ip_login = $this->request->server('REMOTE_ADDR');

            $ktToken = DB::table('tbl_session_login')
                ->where('id_client', $data['id'])
                ->where('token', $token)
                ->where('user_agent', $user_agent)
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
                        'user_agent' => $user_agent,
//                        'ip_login' => $ip_login,
                    ]);
            }
        }
        return $token;
    }

    private function createKeyCode()
    {
        $keyCode = rand(1000, 9999);
        return $keyCode;
    }

    //lấy thôn tin khách hàng từ token apple
    protected function getUserByTokenApple($token)
    {
        $claims = explode('.', $token)[1];
        return json_decode(base64_decode($claims), true);
    }

    public function lockAccount()
    {
        $token = $this->request->bearerToken();
        $id = $this->Info_To_Token($token);
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
