<?php

namespace App\Http\Controllers;

use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PackageService;
use Yajra\DataTables\CollectionDataTable;

class PackageController extends Controller
{
    protected $fnbPackageService;
    use UploadFile;
    public function __construct(Request $request,PackageService $fnbPackageService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbPackageService = $fnbPackageService;
    }

    public function get_list(){
        if (!has_permission('package','view')) {
            access_denied();
        }
        return view('admin.package.list',[]);
    }

    public function getListPackage()
    {
        $response = $this->fnbPackageService->getListPackage($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $edit = "<a class='dt-modal' href='admin/package/detail/$id'><i class='fa fa-pencil'></i> " . lang('Sửa gói') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/package/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('Xóa gói') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu " role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('name', function ($dtData) {
                $html = '';
                if ($dtData['check_default'] == 1){
                    $html = '<span class="label label-default">Mặc định</span>';
                }
                $str = '<div>' . $dtData['name'] . '</div><div>'.$html.'</div>';
                return $str;
            })
            ->editColumn('number_day', function ($dtData) {
                $str = '<div class="text-center">' . $dtData['number_day'] . '</div>';
                return $str;
            })
            ->editColumn('total', function ($dtData) {
                $str = '<div class="text-center">' . formatMoney($dtData['total']) . '</div>';
                return $str;
            })
            ->editColumn('percent', function ($dtData) {
                $str = '<div class="text-center">' . $dtData['percent'] . '</div>';
                return $str;
            })
            ->editColumn('note', function ($dtData) {
                $str = '<div class="text-left">' . $dtData['note'] . '</div>';
                return $str;
            })
            ->editColumn('type', function ($dtData) {
                $str = '<div class="'.($dtData['type'] == 1 ? 'label label-default' : 'label label-primary').'">' . getListTypePackage($dtData['type']) . '</div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->rawColumns(['options', 'total', 'number_day', 'name','id','percent','note','type'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function get_detail($id = 0) {
        $this->request->merge(['id' => $id]);
        if (empty($id)){
            if (!has_permission('package', 'add')) {
                access_denied(true, lang('Không có quyền thêm'));
            }
            $title = lang('Thêm mới gói thành viên');
        } else {
            if (!has_permission('package', 'edit')) {
                access_denied(true, lang('Không có quyền sửa'));
            }
            $title = lang('Sửa gói thành viên');
            $response = $this->fnbPackageService->getDetail($this->request);
            $data = $response->getData(true);
            $dtData = $data['dtData'] ?? [];
        }
        return view('admin.package.detail',[
            'id' => $id,
            'title' => $title,
            'dtData' => $dtData ?? [],
        ]);
    }

    public function detail($id = 0) {
        $total = number_unformat($this->request->input('total',0));
        $this->request->merge(['id' => $id]);
        $this->request->merge(['total' => $total]);
        $response = $this->fnbPackageService->detail($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        if (!has_permission('package', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbPackageService->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

}
