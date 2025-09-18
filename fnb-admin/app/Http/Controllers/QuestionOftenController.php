<?php

namespace App\Http\Controllers;

use App\Models\Ares;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\DataTables;
use App\Models\Department;
use App\Services\QuestionOftenService;
use App\Helpers\AppHelper;
use App\Http\Requests\AresRequest;
use Illuminate\Support\Facades\Validator;

class QuestionOftenController extends Controller
{
    protected $fnbQuestionOften;
    public function __construct(Request $request, QuestionOftenService $fnbQuestionOften)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->per_page = 10;
        $this->fnbQuestionOften = $fnbQuestionOften;
    }

    public function get_list()
    {
        if (!has_permission('question_often','view')){
            access_denied();
        }
        return view('admin.question_often.list');
    }

    public function getList()
    {
        $search = $this->request->input('search.value') ?? null;
        $response = $this->fnbQuestionOften->getList($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false){
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('options', function ($dtData) {
                $id = $dtData['id'];
                $edit = "<a class='dt-modal' href='admin/question_often/detail/$id'><i class='fa fa-pencil'></i> " . lang('c_edit_question_often') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/question_often/delete/' . $id. '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('c_delete_question_often') . '</a>';
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
            ->addColumn('id', function ($dtData) {
                return $dtData['id'];
            })
            ->addColumn('stt', function ($row) use (&$start) {
                return (++$start);
            })
            ->addColumn('order_by', function ($dtData) {
                return $dtData['order_by'];
            })
            ->editColumn('question', function ($dtData) {
                $str = '<div>' . $dtData['question'] . '</div>';
                return $str;
            })
            ->editColumn('content_reply', function ($dtData) {
                $str = '<div>' . $dtData['content_reply'] . '</div>';
                return $str;
            })
            ->editColumn('active', function ($dtData) {
                $checked = $dtData['active'] == 1 ? 'checked' : '';
                $status = $dtData['active'] == 1 ? '0' : '1';
                $str = '<div class="text-center"><input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/question_often/changeStatus/'.$dtData['id'].'" data-status="'.$status.'"></div>';
                return $str;
            })
            ->rawColumns(['options', 'active', 'question', 'content_reply', 'id', 'row'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function getDetail($id = '0')
    {
        if(empty($id)) {
            $id = $this->request->input('id');
        }
        $this->request->merge([
            'id' => $id,
        ]);
        if (empty($id)) {
            $title = lang('c_add_question_often');
            if (!has_permission('question_often','add')){
                access_denied(true);
            }
        } else {
            if (!has_permission('question_often','edit')){
                access_denied(true);
            }
            $title = lang('c_edit_question_often');

            $data = $this->fnbQuestionOften->getDetail($this->request);
            $question_often = $data->getData()->dtData;
        }
        return view('admin.question_often.detail', [
            'title' => $title,
            'id' => $id,
            'question_often' => $question_often ?? [],
        ]);
    }

    public function detail($id = 0)
    {
        $response = $this->fnbQuestionOften->detail($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function delete($id){
        if (!has_permission('question_often','delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }

        if(empty($id)) {
            $id = $this->request->input('id');
        }
        $this->request->merge([
            'id' => $id,
        ]);
        $response = $this->fnbQuestionOften->delete($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function changeStatus($id){
        if (!has_permission('question_often','edit')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }

        if(empty($id)) {
            $id = $this->request->input('id');
        }
        $this->request->merge([
            'id' => $id,
        ]);
        $response = $this->fnbQuestionOften->ChangeStatus($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

    public function order_by(){
        if (!has_permission('question_often', 'edit')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $response = $this->fnbQuestionOften->OrderBy($this->request);
        $dataRes = $response->getData(true);
        $data = $dataRes['data'];
        return response()->json($data);
    }

}
