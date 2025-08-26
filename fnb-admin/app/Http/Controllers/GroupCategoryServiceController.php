<?php

namespace App\Http\Controllers;

use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\GroupCategoryService;
use Yajra\DataTables\CollectionDataTable;

class GroupCategoryServiceController extends Controller
{
    protected $fnbGroupCategoryService;
    use UploadFile;
    public function __construct(Request $request,GroupCategoryService $groupCategoryService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbGroupCategoryService = $groupCategoryService;
    }

    public function get_list(){
        if (!has_permission('group_category_service','view')) {
            access_denied();
        }
        return view('admin.group_category_service.list',[]);
    }

    public function getListGroupCategoryService()
    {
        $response = $this->fnbGroupCategoryService->getListGroupCategoryService($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $edit = "<a class='dt-modal' href='admin/group_category_service/detail/$id'><i class='fa fa-pencil'></i> " . lang('Sửa nhóm danh mục') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/group_category_service/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('Xóa nhóm danh mục') . '</a>';
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
                $str = '<div>' . $dtData['name'] . '</div>';
                return $str;
            })
            ->editColumn('active', function ($dtData) {
                $id = $dtData['id'];
                $classes = $dtData['active'] == 1 ? "btn-info" : "btn-danger";
                $content = $dtData['active'] == 1 ? "Hoạt động" : "Không hoạt động";
                $str = "<a class='dt-update text-center btn btn-xs $classes' href='admin/group_category_service/active/$id'>$content</a>";
                return $str;
            })
            ->editColumn('icon', function ($dtData) {
                $dtImage = !empty($dtData['icon']) ? $dtData['icon'] : null;
                return loadImageNew($dtImage,'40px','img-rounded','',false);
            })
            ->editColumn('image', function ($dtData) {
                $dtImage = !empty($dtData['image']) ? $dtData['image'] : null;
                return loadImageNew($dtImage,'75px','img-rounded','',false);
            })
            ->editColumn('background', function ($dtData) {
                $dtImage = !empty($dtData['background']) ? $dtData['background'] : null;
                return loadImageNew($dtImage,'150px','img-rounded','',false);
            })
            ->editColumn('index', function ($dtData) {
                $str = '<div class="text-center">' . $dtData['index'] . '</div>';
                return $str;
            })
            ->rawColumns(['options', 'active', 'icon', 'name','id','image','background','index'])
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
            if (!has_permission('group_category_service', 'add')) {
                access_denied(true, lang('Không có quyền thêm'));
            }
            $title = lang('Thêm mới nhóm danh mục dịch vụ');
        } else {
            if (!has_permission('group_category_service', 'edit')) {
                access_denied(true, lang('Không có quyền sửa'));
            }
            $title = lang('Sửa nhóm danh mục dịch vụ');
            $response = $this->fnbGroupCategoryService->getDetail($this->request);
            $data = $response->getData(true);
            $dtData = $data['dtData'] ?? [];
        }
        return view('admin.group_category_service.detail',[
            'id' => $id,
            'title' => $title,
            'dtData' => $dtData ?? [],
        ]);
    }

    public function detail($id = 0) {
        $this->request->merge(['id' => $id]);
        $response = $this->fnbGroupCategoryService->detail($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        if (!has_permission('group_category_service', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbGroupCategoryService->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function active($id = 0){
        if (!has_permission('group_category_service', 'edit')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbGroupCategoryService->active($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }
}
