<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\MemberShipLevel;
use App\Services\TransactionBillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\GroupCategoryService;
use App\Services\OtherAmenitisService;
use App\Services\CategoryService;
use App\Services\AccountService;
use App\Services\ServiceService;
use App\Services\PackageService;

class CategoryController extends Controller
{
    protected $fnbGroupCategoryService;
    protected $fnbCategoryService;
    protected $fnbOtherAmenitisService;
    protected $fnbCustomerService;
    protected $fnbServiceService;
    protected $fnbPackageService;
    protected $fnbTransactionBillService;

    public function __construct(
        Request $request,
        GroupCategoryService $groupCategoryService,
        OtherAmenitisService $otherAmenitisService,
        AccountService $customerService,
        CategoryService $categoryService,
        ServiceService $serviceService,
        PackageService $packageService,
        TransactionBillService $transactionBillService,
    ) {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbGroupCategoryService = $groupCategoryService;
        $this->fnbCategoryService = $categoryService;
        $this->fnbOtherAmenitisService = $otherAmenitisService;
        $this->fnbCustomerService = $customerService;
        $this->fnbServiceService = $serviceService;
        $this->fnbPackageService = $packageService;
        $this->fnbTransactionBillService = $transactionBillService;
    }

    public function searchTransaction()
    {
        $search = $this->request->input('term');
        $dtTransaction = Transaction::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->where('reference_no', 'like', '%' . $search . '%');
            }
        })->limit(50)->get();
        $results = [];
        foreach ($dtTransaction as $key => $value) {
            $results[] = [
                'id' => $value->id,
                'text' => $value->reference_no
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchTransferMoney()
    {
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $dtTransferMoney = TransferMoney::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->where('reference_no', 'like', '%' . $search . '%');
            }
        })->limit(50)->get();
        $results = [];
        foreach ($dtTransferMoney as $key => $value) {
            $results[] = [
                'id' => $value->id,
                'text' => $value->reference_no
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchRequestWithdrawMoney()
    {
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $dtRequestWithdrawMoney = RequestWithdrawMoney::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->where('reference_no', 'like', '%' . $search . '%');
            }
        })->limit(50)->get();
        $results = [];
        foreach ($dtRequestWithdrawMoney as $key => $value) {
            $results[] = [
                'id' => $value->id,
                'text' => $value->reference_no
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchTransactionAll()
    {
        $search = $this->request->input('term');

        $tb_transaction_river = DB::table('tbl_transaction_driver')
            ->select('tbl_transaction_driver.id as id',
                'reference_no', DB::raw('1 as type'))
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('reference_no', 'like', '%' . $search . '%');
                }
            })
            ->limit(30);

        $dtTransaction = Transaction::select(
            'tbl_transaction.id as id',
            'reference_no',
            DB::raw('2 as type')
        )
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('reference_no', 'like', '%' . $search . '%');
                }
            })
            ->limit(30)->unionAll($tb_transaction_river);
        $result = DB::query()
            ->fromSub($dtTransaction, 'tb_transaction')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('reference_no', 'like', '%' . $search . '%');
                }
            })
            ->limit(60)->get();
        $results = [];
        $arrItems = [];
        $arrItemsDriver = [];
        foreach ($result as $key => $value) {
            if ($value->type == 1) {
                $arrItemsDriver[] = [
                    'id' => $value->id,
                    'text' => $value->reference_no
                ];
            } else {
                $arrItems[] = [
                    'id' => $value->id,
                    'text' => $value->reference_no
                ];
            }
        }
        $results[] = [
            'text' => 'Giao dịch đặt tài xế',
            'children' => $arrItemsDriver,
        ];
        $results[] = [
            'text' => 'Giao dịch đặt xe',
            'children' => $arrItems
        ];
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchBlog()
    {
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $type = !empty($params['type']) ? $params['type'] : 2;
        $dtData = Blog::where(function ($query) use ($search, $type) {
            if (!empty($type) && $type != -1) {
                $query->where('type', $type);
            }
            $query->where('active', 1);
            if (!empty($search)) {
                $query->where('title', 'like', '%' . $search . '%');
                $query->orWhere('detail', 'like', '%' . $search . '%');
            }
        })->limit(50)->get();
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value->id,
                'text' => $value->title,
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchGroupCategoryService()
    {
        $search = $this->request->input('term');
        $this->request->merge(['search' => $search]);
        $this->request->merge(['limit' => 50]);
        $response = $this->fnbGroupCategoryService->getListData($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value['id'],
                'text' => $value['name'],
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchCategoryService($id = 0)
    {
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $group_category_service_id = !empty($params['group_category_service_id']) ? $params['group_category_service_id'] : 0;
        $this->request->merge(['search' => $search]);
        $this->request->merge(['limit' => 50]);
        $this->request->merge(['id' => $id]);
        $this->request->merge(['group_category_service_id' => $group_category_service_id]);
        $response = $this->fnbCategoryService->getListData($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value['id'],
                'text' => $value['name'],
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchOtherAmenities()
    {
        $search = $this->request->input('term');
        $this->request->merge(['search' => $search]);
        $this->request->merge(['limit' => 50]);
        $response = $this->fnbOtherAmenitisService->getListData($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value['id'],
                'text' => $value['name'],
                'image' => $value['image'],
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchCustomer()
    {
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $type_client = $params['type_client'] ?? null;
        $this->request->merge(['search' => $search]);
        $this->request->merge(['type_client' => $type_client]);
        $this->request->merge(['limit' => 50]);
        $response = $this->fnbCustomerService->getListData($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value['id'],
                'text' => $value['fullname'] . ' (' . $value['phone'] . ')',
                'phone' => $value['phone'],
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchRepresentativer()
    {
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $this->request->merge(['search' => $search]);
        $this->request->merge(['limit' => 50]);
        $response = $this->fnbCustomerService->getListDataRepresentative($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value['id'],
                'text' => $value['name'] . ' (' . $value['phone'] . ')',
                'phone' => $value['phone'],
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchService()
    {
        $search = $this->request->input('term');
        $this->request->merge(['search' => $search]);
        $this->request->merge(['current_page' => 1]);
        $this->request->merge(['per_page' => 50]);
        $response = $this->fnbServiceService->getListData($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']['data']) ?? [];
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value['id'],
                'text' => $value['name'],
                'category' => json_encode($value['category_service'])
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchPackage()
    {
        $params = $this->request->input('paramsCus');
        $search = $this->request->input('term');
        $type = $params['type'] ?? 1;
        $this->request->merge(['search' => $search]);
        $this->request->merge(['type' => $type]);
        $this->request->merge(['admin' => 1]);
        $response = $this->fnbPackageService->getListData($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']) ?? [];
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value['id'],
                'text' => $value['name'],
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function getListMemberShip()
    {
        $search = $this->request->input('term');
        $params = $this->request->input('paramsCus');
        $dtData = MemberShipLevel::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->where('name', 'like', '%' . $search . '%');
            }
        })->limit(50)->get();
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value->id,
                'text' => $value->name,
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

    public function searchTransactionBill()
    {
        $search = $this->request->input('term');
        $this->request->merge(['search' => $search]);
        $this->request->merge(['cron' => 1]);
        $this->request->merge(['status_search' => -1]);
        $this->request->merge(['current_page' => 1]);
        $this->request->merge(['per_page' => 50]);
        $response = $this->fnbTransactionBillService->getListData($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = ($data['data']['data']) ?? [];
        $results = [];
        foreach ($dtData as $key => $value) {
            $results[] = [
                'id' => $value['id'],
                'text' => $value['reference_no'],
            ];
        }
        $data = [
            'items' => $results
        ];
        return response()->json($data);
    }

}
