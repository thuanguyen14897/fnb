<?php

namespace App\Http\Controllers\Api_app;

use App\Helpers\FilesHelpers;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\SampleMessage;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Google_Client;
use function Laravel\Prompts\table;
use DateTime;

class Api_info extends AuthController
{
    protected $fnbCustomerService;
    public function __construct(Request $request,AccountService $accountService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->SaveSession = true;
        $this->fnbCustomerService = $accountService;
    }

    //(1)
    public function get_info_settings()
    {
        $_locale = $this->request->_locale;
        $locale_default_vn = config('constant.locale_default_vn');

        $dataField = DB::table('tbl_options')->where(function ($field) {
            $field->whereIn('name', [
                'onesignal_id',
                'onesignal_key',
                'google_api_key',
                'rule_delete_account',
                'version_app',
                'note_version_app',
                'version_app_android',
                'contact_phone',
                'contact_email',
                'link_messenger',
                'link_telegram',
                'link_facebook',
                'intro_one',
                'intro_two',
                'intro_three',
                'percent',
                'policy_terms',
                'url_socket',
                'account_bank_sort',
                'account_number',
                'account_name',
                'account_bank',
                'title_package',
                'content_package',
                'rule_register_partner',
                'terms_guide',
                'is_apple',
                'total_member',
                'phone_company',
                'link_contact_facebook',
                'link_contact_zalo',
            ]);
        })->get();
        $data = [];
        $total_member = 0;
        foreach ($dataField as $key => $value) {
            if ($value->name == 'rule_delete_account') {
                $value->value = str_replace('src="/storage', 'src="' . asset('/storage') . '', $value->value);
            }
            $data[$value->name] = $value->value;
            if ($value->name == 'total_member') {
                $total_member += $value->value ?? 0;
            }
        }
        $data['db_name'] = config('database.connections.mysql.database');
        $data['dtTypeRepresentative'] = getListTypeBusiness();
        $data['dtDay'] = getListDay();
        $data['dtSampleMessage'] = SampleMessage::get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->message,
            ];
        })->toArray();
        $response = $this->fnbCustomerService->countAll($this->request);
        $dataRes = $response->getData(true);
        $total_member  += $dataRes['total'] ?? 0;
        $data['total_member'] = $total_member;
        return response()->json($data, 200);
    }

    public function getOption($field = ''){
        $data = get_option($field);
        return response()->json(['result' => $data]);
    }

    public function getSetting($value = 0,$type = 'number_unformat'){
        $data = null;
        if ($type == 'number_unformat') {
            $data = number_unformat($value);
        }
        return response()->json(['result' => $data]);
    }

    public function send_zalo(){
        $dtObject = $this->request->input('dtObject');
        $event = $this->request->input('event');
        $template_id = $this->request->input('template_id');
        $code = $this->request->input('code');
        $phone_zalo = $this->request->input('phone_zalo');
        $result = send_zalo($dtObject,$event,$template_id,$code,$phone_zalo);
        return response()->json([
            'result' => $result,
            'message' => 'Gửi zalo zns thành công'
        ]);
    }
}
