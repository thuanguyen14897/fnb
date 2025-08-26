<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class BlogController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->type = 1;
        if (!empty($this->request->query('type'))){
            $this->type = $this->request->query('type');
        }
    }

    public function get_list()
    {
        if (!has_permission('blog','view')){
            access_denied();
        }
        if ($this->type == 1) {
            $title = lang('dt_blog');
        } else {
            $title = lang('dt_blog_new');
        }
        return view('admin.blog.list',[
            'title' => $title,
            'type' => $this->type
        ]);
    }

    public function getBlog()
    {
        $type = $this->request->input('type') ?? 1;
        $dtData = Blog::query();
        $dtData->where('type',$type);
        return Datatables::of($dtData)
            ->addColumn('options', function ($data) use ($type) {
                $edit = "<a href='admin/blog/detail/$data->id'><i class='fa fa-pencil'></i> " . ($type == 1 ? lang('dt_edit_blog') : lang('dt_edit_blog_new')) . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                <button href=\'admin/blog/delete/'.$data->id.'\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
            "><i class="fa fa-remove width-icon-actions"></i> ' . ($type == 1 ? lang('dt_delete_blog') : lang('dt_delete_blog_new')) .'</a>';
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
            ->editColumn('image', function ($data) {
                $dtImage = !empty($data->image) ? asset('storage/'.$data->image) : null;
                return loadImage($dtImage, '110px', 'img-rounded');
            })
            ->editColumn('descption', function ($data) {
                return '<div>'.$data->descption.'</div>';
            })
            ->editColumn('active', function ($data) {
                $checked = $data->active == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="active" class="active dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/blog/changeStatus/'.$data->id.'" data-status="'.$data->active.'">';
                return $str;
            })
            ->editColumn('homepage', function ($data) {
                $checked = $data->homepage == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="homepage" class="homepage dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/blog/changeHomePage/'.$data->id.'" data-status="'.$data->homepage.'">';
                return $str;
            })
            ->editColumn('hot', function ($data) {
                $checked = $data->hot == 1 ? 'checked' : '';
                $str = '<input type="checkbox" '.$checked.' name="hot" class="hot dt-active"  data-plugin="switchery" data-color="#5fbeaa" data-href="admin/blog/changeHot/'.$data->id.'" data-status="'.$data->hot.'">';
                return $str;
            })
            ->editColumn('type', function ($data) {
                if ($data->type == 1){
                    $htmlType = '<div class="label label-default">Tin khuyến mãi</div>';
                } else {
                    $htmlType = '<div class="label label-primary">Tin thường</div>';
                }
                return '<div>'.$htmlType.'</div>';
            })
            ->editColumn('type_blog', function ($data) {
                if ($data->type_blog == 1){
                    $htmlTypeBlog = '<div class="label label-default">Loại điểm đến</div>';
                } else {
                    $htmlTypeBlog = '<div class="label label-primary">Loại thành viên</div>';
                }
                return '<div>'.$htmlTypeBlog.'</div>';
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options','image','active','descption','homepage','type','type_blog','hot'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)){
            if ($this->type == 1) {
                $title = lang('dt_add_blog');
            } else {
                $title = lang('dt_add_blog_new');
            }
            if (!has_permission('blog','add')){
                access_denied();
            }
        } else {
            if (!has_permission('blog','edit')){
                access_denied();
            }
            if($this->type == 1) {
                $title = lang('dt_edit_blog');
            } else {
                $title = lang('dt_edit_blog_new');
            }
        }
        $blog = Blog::find($id);
        $dtType = [
            [
                'id' => 1,
                'name' => 'Tin khuyến mãi'
            ],
            [
                'id' => 2,
                'name' => 'Tin thường'
            ]
        ];
        $dtTypeBlog = [
            [
                'id' => 1,
                'name' => 'Loại điểm đến'
            ],
            [
                'id' => 2,
                'name' => 'Loại thành viên'
            ]
        ];
        return view('admin.blog.detail', [
            'title' => $title,
            'id' => $id,
            'blog' => $blog,
            'dtType' => $dtType,
            'dtTypeBlog' => $dtTypeBlog,
            'type' => $this->type,
        ]);
    }

    public function submit($id = 0)
    {
        $data = [];
        $message = [
            'title.required' => 'Vui lòng nhập tên tiêu đề',
            'image.required' => 'Vui lòng chọn hình ảnh',
            'descption.required' => 'Vui lòng nhập mô tả',

            'title_en.required' => 'Vui lòng nhập tên tiêu đề tiếng anh',
            'descption_en.required' => 'Vui lòng nhập mô tả tiếng anh',
            'title_zh.required' => 'Vui lòng nhập tên tiêu đề tiếng trung',
            'descption_zh.required' => 'Vui lòng nhập mô tả tiếng trung',
            'title_ko.required' => 'Vui lòng nhập tên tiêu đề tiếng hàn',
            'descption_ko.required' => 'Vui lòng nhập mô tả tiếng hàn',
            'title_ja.required' => 'Vui lòng nhập tên tiêu đề tiếng nhật',
            'descption_ja.required' => 'Vui lòng nhập mô tả tiếng nhật',
        ];
        if (!empty($id)) {
            $blog = Blog::find($id);
            $validator = Validator::make($this->request->all(),
                [
                    'title' => 'required',
                    'descption' => 'required',
                    'title_en' => 'required',
                    'descption_en' => 'required',
                    'title_zh' => 'required',
                    'descption_zh' => 'required',
                    'title_ko' => 'required',
                    'descption_ko' => 'required',
                    'title_ja' => 'required',
                    'descption_ja' => 'required',
                ],$message);
        } else {
            $blog = new Blog();
            $validator = Validator::make($this->request->all(),
                [
                    'image' => 'required',
                    'title' => 'required',
                    'descption' => 'required',
                    'title_en' => 'required',
                    'descption_en' => 'required',
                    'title_zh' => 'required',
                    'descption_zh' => 'required',
                    'title_ko' => 'required',
                    'descption_ko' => 'required',
                    'title_ja' => 'required',
                    'descption_ja' => 'required',
                ],$message);
        }

        if ($validator->fails()) {
            $data['result'] = false;
            $data['message'] = $validator->errors()->all();
            return response()->json($data);
        }
        DB::beginTransaction();
        try {
            $blog->title = $this->request->input('title');
            $blog->descption = $this->request->input('descption');
            $blog->content = $this->request->input('content');
            $blog->type = $this->request->input('type');
            $blog->type_blog = $this->request->input('type_blog');
            $blog->title_en = $this->request->input('title_en');
            $blog->descption_en = $this->request->input('descption_en');
            $blog->content_en = $this->request->input('content_en');
            $blog->title_zh = $this->request->input('title_zh');
            $blog->descption_zh = $this->request->input('descption_zh');
            $blog->content_zh = $this->request->input('content_zh');
            $blog->title_ko = $this->request->input('title_ko');
            $blog->descption_ko = $this->request->input('descption_ko');
            $blog->content_ko = $this->request->input('content_ko');
            $blog->title_ja = $this->request->input('title_ja');
            $blog->descption_ja = $this->request->input('descption_ja');
            $blog->content_ja = $this->request->input('content_ja');
            $blog->save();
            DB::commit();
            if ($blog) {
                if ($this->request->hasFile('image')) {
                    if (!empty($blog->image)){
                        $this->deleteFile($blog->image);
                    }
                    $path = $this->UploadFile($this->request->file('image'),'blog/'.$blog->id,600,500,false);
                    $blog->image = $path;
                    $blog->save();
                }
                $data['result'] = true;
                $data['type'] = $blog->type;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            } else {
                $data['result'] = false;
                $data['type'] = $blog->type;
                $data['message'] = lang('dt_error');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }

    public function changeStatus($id){
        if (!has_permission('blog','edit')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $blog = Blog::find($id);
        try {
            $blog->active = $this->request->input('status') == 0 ? 1 : 0;
            $blog->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeHomePage($id){
        if (!has_permission('blog','edit')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $blog = Blog::find($id);
        try {
            $blog->homepage = $this->request->input('status') == 0 ? 1 : 0;
            $blog->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function changeHot($id){
        if (!has_permission('blog','edit')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $blog = Blog::find($id);
        try {
            $blog->hot = $this->request->input('status') == 0 ? 1 : 0;
            $blog->save();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function delete($id){
        if (!has_permission('blog','delete')){
            $data['result'] = false;
            $data['message'] = lang('dt_access');
            return response()->json($data);
        }
        $blog = Blog::find($id);
        try {
            $blog->delete();
            if (!empty($blog->image)) {
                $this->deleteFile($blog->image);
            }
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        } catch (\Exception $exception){
            $data['result'] = false;
            $data['message'] = $exception;
            return response()->json($data);
        }
    }
}
