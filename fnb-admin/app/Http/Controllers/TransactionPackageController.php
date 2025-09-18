<?php

namespace App\Http\Controllers;

use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\TransactionPackageService;
use Yajra\DataTables\CollectionDataTable;

class TransactionPackageController extends Controller
{
    protected $fnbTransactionPackageService;
    use UploadFile;
    public function __construct(Request $request,TransactionPackageService $fnbTransactionPackageService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbTransactionPackageService = $fnbTransactionPackageService;
    }

    public function get_list(){
        if (!has_permission('transaction_package','view') && !has_permission('transaction_package','viewown')) {
            access_denied();
        }
        return view('admin.transaction_package.list',[]);
    }

    public function getListTransactionPackage()
    {
        if (!has_permission('transaction_package','view') && has_permission('transaction_package','viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }
        $response = $this->fnbTransactionPackageService->getListTransactionPackage($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/transaction_package/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('Xóa giao dịch mua gói') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('reference_no', function ($dtData) {
                $str = '<div>' . $dtData['reference_no'] . '</div>';
                return $str;
            })
            ->editColumn('date', function ($dtData) {
                $str = '<div>' . _dt($dtData['date']) . '</div>';
                return $str;
            })
            ->editColumn('customer', function ($dtData) {
                $customer = $dtData['customer'] ?? [];
                $url = !empty($customer['avatar_new']) ? $customer['avatar_new'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div>'.(!empty($customer['fullname']) ? $customer['fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($customer['phone']) ? $customer['phone'] : 'Chưa có sdt').'</div>';
            })
            ->editColumn('package', function ($dtData) {
                $str = '<div class="text-left">' . ($dtData['package']['name'] ?? '') . '</div>';
                return $str;
            })
            ->editColumn('number_day', function ($dtData) {
                $str = '<div class="text-center">' . $dtData['number_day'] . '</div>';
                return $str;
            })
            ->editColumn('grand_total', function ($dtData) {
                $str = '<div class="text-center">' . formatMoney($dtData['grand_total']) . '</div>';
                return $str;
            })
            ->editColumn('status', function ($dtData) {
                if ($dtData['status'] == 1){
                    $htmlStatus = '<div class="dt-update label label-danger" data-type="payment" style="cursor: pointer;" href="admin/transaction_package/changeStatus/'.$dtData['id'].'">Chờ thanh toán</div>';
                } else {
                    $htmlStatus = '<div class="label label-success">Đã thanh toán</div>';
                }
                return '<div class="text-center">'.$htmlStatus.'</div>';
            })
            ->rawColumns(['options', 'reference_no', 'number_day', 'date','id','customer','package','grand_total','status'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function delete($id = 0){
        if (!has_permission('transaction_package', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbTransactionPackageService->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function changeStatus($id = 0)
    {
        if (!has_permission('transaction_package', 'approve')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $this->request->merge(['staff_status' => Config::get('constant')['user_admin']]);
        $response = $this->fnbTransactionPackageService->changeStatus($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

}
