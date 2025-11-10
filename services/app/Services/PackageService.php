<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class PackageService
{
    protected $baseUrl;
    use RequestServiceTrait;
    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.fnb_account.base_url'), '/');
    }

    public function getListPackage($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/package/getListPackage",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ], $response->status());
            }

            $data = $response->json();
            return response()->json([
                'result' => ['result'],
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $data['total'], // tổng số user
                'recordsFiltered' => $data['filtered'], // tổng user sau khi lọc
                'data' => $data['data'], // danh sách user hiện tại
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDetail($request = []){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/package/getDetail",
                $request,
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
                'dtData' => $data['dtData'] ?? [],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function detail($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/package/detail",
                $request,
                [
                    'has_file' => true
                ]
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'] ?? false,
                'message' => $data['message']
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/package/delete",
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

    public function getListData($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/package/getListData",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                    'data' => []
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
