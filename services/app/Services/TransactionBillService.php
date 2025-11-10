<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class TransactionBillService
{
    protected $baseUrl;
    use RequestServiceTrait;
    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.fnb_account.base_url'), '/');
    }

    public function getList($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/transaction_bill/getList",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'customer_ids' => []
                ], $response->status());
            }

            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $data['total'], // tổng số user
                'recordsFiltered' => $data['filtered'], // tổng user sau khi lọc
                'data' => $data['data'], // danh sách user hiện tại
                'customer_ids' => array_column($data['data'], 'customer_id'),
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
                "{$this->baseUrl}/api/service/getDetail",
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
                "{$this->baseUrl}/api/service/detail",
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

    public function changeStatus($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/transaction_bill/changeStatus",
                $request
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
                "{$this->baseUrl}/api/transaction_bill/delete",
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
                "{$this->baseUrl}/api/transaction_bill/getListData",
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

    public function countAll($request){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/transaction_bill/countAll",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'follow' => 0,
                    'arr' => [],
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
                ]);
            }

            $data = $response->json();

            return response()->json([
                'follow' => $data['follow'],
                'arr' => $data['arr'],
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

    public function countTransaction($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/transaction/countTransaction",
                $request,
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

    public function getListDataTransaction($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/transaction/getListData",
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

    public function getListDetailTransaction($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/transaction_bill/getListDataDetail/{$request->id}",
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

    public function checkService($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/transaction_bill/checkService",
                $request
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
}
