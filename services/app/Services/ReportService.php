<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class ReportService
{
    protected $baseUrl;
    use RequestServiceTrait;
    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.fnb_account.base_url'), '/');
    }

    public function getListSyntheticFeePartner($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/report/getListSyntheticFeePartner",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                    'data' => [],
                ], $response->status());
            }

            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'data' => $data['data'],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getListSyntheticRosePartner($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/report/getListSyntheticRosePartner",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                    'data' => [],
                ], $response->status());
            }

            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'data' => $data['data'],
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
