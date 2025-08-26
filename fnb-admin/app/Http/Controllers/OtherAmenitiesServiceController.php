<?php

namespace App\Http\Controllers;

use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\OtherAmenitisService;
use Yajra\DataTables\CollectionDataTable;

class OtherAmenitiesServiceController extends Controller
{
    protected $fnbOtherAmenitiesService;
    use UploadFile;
    public function __construct(Request $request,OtherAmenitisService $otherAmenitisService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbOtherAmenitiesService = $otherAmenitisService;
    }

    public function get_list(){
        if (!has_permission('other_amenities_service','view')) {
            access_denied();
        }
        return view('admin.other_amenities_service.list',[]);
    }

    public function getList()
    {
        $response = $this->fnbOtherAmenitiesService->getList($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $edit = "<a class='dt-modal' href='admin/other_amenities_service/detail/$id'><i class='fa fa-pencil'></i> " . lang('Sửa tiện nghi') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/other_amenities_service/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('Xóa tiện nghi') . '</a>';
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
            ->editColumn('image', function ($dtData) {
                $dtImage = !empty($dtData['image']) ? $dtData['image'] : null;
                return loadImageNew($dtImage,'50px','img-rounded','',false);
            })
            ->rawColumns(['options', 'image', 'name','id'])
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
            if (!has_permission('other_amenities_service', 'add')) {
                access_denied(true, lang('Không có quyền thêm'));
            }
            $title = lang('Thêm mới tiện nghi');
        } else {
            if (!has_permission('other_amenities_service', 'edit')) {
                access_denied(true, lang('Không có quyền sửa'));
            }
            $title = lang('Sửa tiện nghi');
            $response = $this->fnbOtherAmenitiesService->getDetail($this->request);
            $data = $response->getData(true);
            $dtData = $data['dtData'] ?? [];
        }
        return view('admin.other_amenities_service.detail',[
            'id' => $id,
            'title' => $title,
            'dtData' => $dtData ?? [],
        ]);
    }

    public function detail($id = 0) {
        $this->request->merge(['id' => $id]);
        $response = $this->fnbOtherAmenitiesService->detail($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id = 0){
        if (!has_permission('other_amenities_service', 'delete')) {
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $this->request->merge(['id' => $id]);
        $response = $this->fnbOtherAmenitiesService->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }
}
