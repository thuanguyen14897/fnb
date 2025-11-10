<?php

namespace App\Http\Controllers;

use App\Models\SampleMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class SampleMessageController extends Controller
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getSampleMessage()
    {
        $dtSampleMessage = SampleMessage::orderByRaw('id desc');
        return Datatables::of($dtSampleMessage)
            ->addColumn('options', function ($sampleMessage) {
                $edit = "<a class='dt-modal' href='admin/sample_message/detail/$sampleMessage->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_sample_message') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/sample_message/delete/' . $sampleMessage->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_sample_message') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_sample_message');
        } else {
            $title = lang('dt_edit_sample_message');
        }
        $sampleMessage = SampleMessage::find($id);
        return view('admin.sample_message.detail', [
            'title' => $title,
            'id' => $id,
            'sampleMessage' => $sampleMessage,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $validator = Validator::make($this->request->all(),
            [
                'message' => 'required',
            ]
            , [
                'message.required' => 'Bạn chưa nhập ghi chú mẫu',
            ]);

        if ($validator->fails()) {
            $data['result'] = 0;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        if (!empty($id)) {
            $sampleMessage = SampleMessage::find($id);
        } else {
            $sampleMessage = new SampleMessage();
        }
        DB::beginTransaction();
        try {
            $sampleMessage->message = $this->request->message;
            $sampleMessage->type = 1;
            $sampleMessage->save();
            DB::commit();
            if ($sampleMessage) {
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function delete($id)
    {
        $sampleMessage = SampleMessage::find($id);
        try {
            $success = $sampleMessage->delete();
            if ($success) {
                $data['result'] = true;
                $data['message'] = lang('dt_success');
            } else {
                $data['result'] = false;
                $data['message'] = lang('dt_error');
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
