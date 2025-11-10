<?php

namespace App\Services;

use App\Traits\RequestServiceTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class AccountService
{
    protected $baseUrl;
    use RequestServiceTrait;
    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.fnb_account.base_url'), '/');
    }

    public function getListCustomer($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getListCustomer",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
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

    public function getDetailCustomer($request = []){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getDetailCustomer",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
                ], $response->status());
            }
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
                'client' => $data['client'] ?? [],
                'referral' => $data['referral'] ?? [],
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function detailCustomer($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/detail",
                $request,
                [
                    'has_file' => true
                ]
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
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

    public function countAll($request){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/countAll",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'total' => 0,
                    'arrType' => [],
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
                ]);
            }

            $data = $response->json();

            return response()->json([
                'total' => $data['total'],
                'arrType' => $data['arrType'],
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

    public function deleteCustomer($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/deleteCustomer",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
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

    public function active($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/active",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
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

    public function getListData($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getListData",
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

    public function getListDataRepresentative($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getListDataRepresentative",
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

    public function detailRepresentativePartner($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/detailRepresentativePartner",
                $request,
                [
                    'has_file' => true
                ]
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
                'result' => $data['result'],
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

    public function cronUpgradeMemberShip($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/cronUpgradeMemberShip",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'count' => 0,
                    'message' => $response->json()['error'] ?? ( $response->json()['message'] ?? 'Unknown error'),
                ], $response->status());
            }

            $data = $response->json();
            return response()->json([
                'count' => $data['count'] ?? 0,
                'message' => $data['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getReportReferral($request)
    {
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getReportReferral",
                $request,
            );
            if (!$response->successful()) {
                return response()->json([
                    'result' => false,
                    'status' => $response->status(),
                    'message' => $response->json()['error'] ?? 'Unknown error',
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

    public function getParentReferralByCustomer($request = [], $id = 0){
        try {
            if(!empty($id)) {
                $request->merge(['id' => $id]);
            }
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getParentReferralByCustomer",
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

    public function getListLogUpgradeClient($request){
        try {
            $response = $this->sendRequestToService(
                'get',
                "{$this->baseUrl}/api/customer/getListLogUpgradeClient",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data['data'] ?? [],
                'result' => $data['result'],
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

    public function updateLogUpgradeClient($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/updateLogUpgradeClient",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'result' => $data['result'],
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

    public function updateDateActive($request){
        try {
            $response = $this->sendRequestToService(
                'POST',
                "{$this->baseUrl}/api/customer/updateDateActive",
                $request,
            );
            $data = $response->json();
            return response()->json([
                'data' => $data,
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
