<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Controllers\Http\Controllers\Controllers\Api_app\AuthController;
use App\Models\Banner;
use App\Models\IconApp;
use App\Models\TransferAddress;
use App\Models\TransferAddressRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'pusher',
                'cluster',
                'rule_delete_account',
                'hour_start_car',
                'hour_end_car',
                'hour_min_car',
                'hour_wait_status',
                'email_recruitment',
                'check_option',
                'token_key',
                'version_app',
                'note_version_app',
                'version_app_android',
                'time_cancel_trip',
                'onesignal_key_driver',
                'onesignal_id_driver',
                'display_talented',
                'display_driver',
                'time_cancel_driver',
                'time_cancel_driver_province',
                'contact_phone',
                'contact_email',
                'contact_address_head_office',
                'contact_phone_head_office',
                'contact_address_branch_office',
                'contact_phone_branch_office',
                'contact_data_place_google_map',
                'address_our_location',
                'link_contact_facebook',
                'link_contact_telegram',
                'link_contact_zalo',
                'content_short_footer',
                'copyright_footer',
                'money_unit',
                'link_messenger',
                'link_telegram',
                'link_facebook',
                'address_our_location_en',
                'address_our_location_zh',
                'content_short_footer_en',
                'content_short_footer_zh',
                'content_short_footer_ko',
                'content_short_footer_ja',
                'address_our_location_ko',
                'address_our_location_ja',
                'otp_default',
                'check_otp',
                'min_request_withdraw_money',
                'min_transfer_package',
                'fee_withdraw',
                'fee_transfer',
                'link_url_index',
                'button_text_index',
            ]); //ID của onesinal và key của onesinal
        })->get();
        $data = [];
        foreach ($dataField as $key => $value) {
            if ($value->name == 'rule_delete_account') {
                $value->value = str_replace('src="/storage', 'src="' . asset('/storage') . '', $value->value);
            }
            $data[$value->name] = $value->value;
        }
        $banner = Banner::where('active', 1)->first();
        if (!empty($banner)) {
            $data['banner'] = !empty($banner->image) ? asset('storage/' . $banner->image) : null;
        } else {
            $data['banner'] = null;
        }
        $iconAppTL = IconApp::where('active', 1)->where('type', 1)->first();
        if (!empty($iconAppTL)) {
            $data['icon_app_tl'] = !empty($iconAppTL->image) ? asset('storage/' . $iconAppTL->image) : null;
        } else {
            $data['icon_app_tl'] = null;
        }
        $iconAppTL = IconApp::where('active', 1)->where('type', 2)->first();
        if (!empty($banner)) {
            $data['icon_app_ct'] = !empty($iconAppTL->image) ? asset('storage/' . $iconAppTL->image) : null;
        } else {
            $data['icon_app_ct'] = null;
        }
        $iconAppTL = IconApp::where('active', 1)->where('type', 3)->first();
        if (!empty($banner)) {
            $data['icon_app_tx'] = !empty($iconAppTL->image) ? asset('storage/' . $iconAppTL->image) : null;
        } else {
            $data['icon_app_tx'] = null;
        }
        $dtTransferAddress = TransferAddress::where('active',1)->get();
        if (!empty($dtTransferAddress)){
            foreach ($dtTransferAddress as $key => $value){
                if (!empty($value->image)){
                    $value->image = asset('storage/'.$value->image);
                }
            }
        }
        $data['dtTransferAddress'] = $dtTransferAddress;

        $type_transfer_address = !empty(get_option('type_transfer_address')) ? explode(',',get_option('type_transfer_address')) : [];
        $data['dtTransferAddressRequest'] = TransferAddress::whereIn('Network',$type_transfer_address)->get();

        if ($_locale != $locale_default_vn) {
            $data['address_our_location'] = $data['address_our_location_'.$_locale];
            $data['content_short_footer'] = $data['content_short_footer_'.$_locale];
        }

        return response()->json($data, 200);
    }
}
