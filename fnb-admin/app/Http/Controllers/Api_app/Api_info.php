<?php

namespace App\Http\Controllers\Api_app;

use App\Helpers\FilesHelpers;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\IconApp;
use App\Models\TransferAddress;
use App\Models\TransferAddressRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Google_Client;
use function Laravel\Prompts\table;
use DateTime;

class Api_info extends AuthController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->SaveSession = true;
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
            ]);
        })->get();
        $data = [];
        foreach ($dataField as $key => $value) {
            if ($value->name == 'rule_delete_account') {
                $value->value = str_replace('src="/storage', 'src="' . asset('/storage') . '', $value->value);
            }
            $data[$value->name] = $value->value;
        }
        return response()->json($data, 200);
    }

    public function getOption($field = ''){
        $data = get_option($field);
        return response()->json(['result' => $data]);
    }
}
