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
}
