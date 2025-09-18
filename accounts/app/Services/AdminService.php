<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class AdminService
{
    use RequestServiceTrait;
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.fnb_admin.base_url'), '/');
    }

    public function getOrderRef($ref = '')
    {
        $response = Http::get("{$this->baseUrl}/api/getOrderRef/{$ref}");
        $response = $response->json();
        return $response;
    }

    public function updateOrderRef($ref = '')
    {
        $response = Http::get("{$this->baseUrl}/api/updateOrderRef/{$ref}");
        $response = $response->json();
        return $response;
    }

    public function get_option($field = '')
    {
        $response = Http::get("{$this->baseUrl}/api/getOption/{$field}");
        $response = $response->json();
        return !empty($response['result']) ? $response['result'] : null;
    }

    public function getSetting($field = 0,$type = 'number_unformat')
    {
        $response = Http::get("{$this->baseUrl}/api/getSetting/{$field}/{$type}");
        $response = $response->json();
        return !empty($response['result']) ? $response['result'] : null;
    }

    public function send_zalo($request = [])
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/send_zalo",
                $request,
                ['token' => 'fnb']
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getWardUser($request = [])
    {
        try {
            $response = $this->sendRequestToService(
                'GET',
                "{$this->baseUrl}/api/category/getListWardToUser",
                $request,
            );
            $data = $response->json();
            return $data;
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getMemberShipLevel($id = '') {
        $response = Http::get("{$this->baseUrl}/api/category/getListMemberShip/{$id}");
        $response = $response->json();
        return $response;
    }

    public function requestPaymentPay2s($request = [])
    {
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/pay2s/requestPaymentPay2s",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'] ?? false,
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
