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

    public function getListReportRevenuePartner($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/report/getListReportRevenuePartner",
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

    public function getListReportRevenuePartnerDetail($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/report/getListReportRevenuePartnerDetail",
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
                ], $response->status());
            }

            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
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

    public function getSyntheticKpiUser($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/report/getSyntheticKpiUser",
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

    public function getListSyntheticRevenuePartner($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/report/getListSyntheticRevenuePartner",
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

    public function getListSyntheticCustomer($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/report/getListSyntheticCustomer",
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

    public function getListSyntheticSpendingCustomer($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/report/getListSyntheticSpendingCustomer",
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

    public function getListSyntheticDiscountPartner($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/report/getListSyntheticDiscountPartner",
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

    public function getListSyntheticUpgradeMembership($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/report/getListSyntheticUpgradeMembership",
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
                    'status' => $response->status(),
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
