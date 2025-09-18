<?php
namespace App\Http\Controllers\Api_app;

use App\Services\AdminService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\QuestionOften;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class QuestionOftenController extends AuthController
{
    use UploadFile;
    protected $fnbAdmin;
    public function __construct(Request $request, AdminService $adminService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbAdmin = $adminService;
    }

    public function getList() {
        $search = $this->request->input('search.value');
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        if($length < 0) {
            $length = PHP_INT_MAX;
        }

        $orderColumnIndex = $this->request->input('order.0.column');
        $orderBy = $this->request->input("columns.$orderColumnIndex.data",'id');
        if($orderBy == 'DT_RowIndex') {
            $orderBy = 'order_by';
        }
        $orderDir = $this->request->input('order.0.dir', 'asc');
        $query = QuestionOften::where('id','!=',0);
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('question', 'like', "%$search%");
                $q->where('content_reply', 'like', "%$search%");
            });
        }
        $filtered = $query->count();
        $query->orderBy($orderBy, $orderDir);
        $data = $query->skip($start)->take($length)
            ->get();

        $total = QuestionOften::count();

        return response()->json([
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data,
            'result' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function getDetail(){
        $id = $this->request->input('id') ?? 0;
        $dtData = QuestionOften::find($id);
        $data['result'] = true;
        $data['dtData'] = $dtData;
        $data['message'] = 'Lấy câu hỏi thường gặp thành công';
        return response()->json($data);
    }

    public function detail($id = 0){
        if(empty($id)) {
            $id = $this->request->input('id') ?? 0;
        }
        $validator = Validator::make($this->request->all(),
            [
                'question' => 'required',
                'question' => 'required'
            ],
            [
                'question.required' => 'Bạn chưa nhập câu hỏi',
                'content_reply.required' => 'Bạn chưa nhập trả lời',
            ]);
        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            echo json_encode($data);
            die();
        }
        if (empty($id)){
            $dtData = new QuestionOften();
        } else {
            $dtData = QuestionOften::find($id);
        }


        DB::beginTransaction();
        try {
            $dtData->question = $this->request->question;
            $dtData->content_reply = $this->request->content_reply;
            $dtData->save();
            if ($dtData) {
                DB::commit();
                $data['result'] = true;
                if (empty($id)){
                    $data['message'] = 'Thêm mới thành công';
                } else {
                    $data['message'] = 'Cập nhật thành công';
                }
            } else {
                $data['result'] = false;
                if (empty($id)){
                    $data['message'] = 'Thêm mới thất bại';
                } else {
                    $data['message'] = 'Cập nhật thất bại';
                }
            }
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
    public function delete() {
        try {
            $id = $this->request->input('id') ?? 0;
            DB::beginTransaction();
            $success = QuestionOften::find($id)->delete();
            if(!empty($success)) {
                DB::commit();
                if (!empty($success)) {
                    $data['result'] = true;
                    $data['message'] = lang('Xóa dữ liệu thành công');
                    return response()->json($data);
                }
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = lang('Xóa dữ liệu không thành công');
            return response()->json($data);
        }
    }
    public function ChangeStatus() {
        try {
            $id = $this->request->input('id') ?? 0;
            $status = $this->request->input('status') ?? 0;
            $QuestionOften = QuestionOften::find($id);
            $QuestionOften->active = $status;
            $success = $QuestionOften->save();
            if(!empty($success)) {
                $data['result'] = true;
                $data['message'] = lang('Đổi trạng thái thành công');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = lang('Đổi trạng thái không thành công');
            return response()->json($data);
        }
    }

    public function order_by() {
        try {
            $list_order_by = $this->request->input('list_order_by');
            if (!empty($list_order_by)) {
                foreach ($list_order_by as $id => $order_by) {
                    $questionOften = QuestionOften::find($id);
                    $questionOften->order_by = $order_by;
                    $questionOften->save();
                }
                $data['result'] = 1;
                $data['message'] = lang('Sắp xếp thành công');
                return response()->json($data);
            }
            $data['result'] = 0;
            $data['message'] = lang('Sắp xếp không thành công');
            return response()->json($data);
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = lang('Sắp xếp không thành công');
            return response()->json($data);
        }
    }
}
