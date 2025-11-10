<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Support\Facades\Http;

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

    public function getListDataKPI($request = [])
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/category/getListDataKPI",
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
