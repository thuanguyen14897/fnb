<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\BlogCollection;
use App\Models\Blog;
use App\Http\Resources\Blog as BlogResource;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BlogController extends AuthController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getListBlog() {
        $current_page = 1;
        $per_page = 10;

        $_locale = $this->request->_locale;
        $locale_default_vn = config('constant.locale_default_vn');

        $id = !empty($this->request->input('id')) ? $this->request->input('id') : 0;
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        $per_page =$this->request->query('per_page_hot',3);
        $per_page_province =$this->request->query('per_page_province',3);
        $per_page_member =$this->request->query('per_page_member',3);
        $blog = Blog::select('*')
                ->when($_locale != config('constant.locale_default_vn'), function ($query) use ($_locale) {
                    $query->selectRaw(DB::raw("title_{$_locale} as title"));
                    $query->selectRaw(DB::raw("descption_{$_locale} as descption"));
                    $query->selectRaw(DB::raw("content_{$_locale} as content"));
                })
                ->selectRaw(DB::raw("'1' as 'check'"))
                ->where(function ($query) use ($id) {
                $query->where('active', 1);
                $query->where('type', 1);
                $query->where('type_blog', 1);
                if (!empty($id)){
                    $query->where('id','!=',$id);
                }
            })
            ->orderByRaw('id desc')->paginate($per_page, ['*'], '', $current_page);

        $blogHot = new BlogCollection($blog);
        $nextBlogHot = !empty($blogHot->hasMorePages()) ? 1 : 0;

        $blogProvince = Blog::where(function ($query) use ($id, $search, $_locale, $locale_default_vn) {
                $query->where('active', 1);
                $query->where('type', 1);
                $query->where('type_blog', 1);
                if (!empty($id)){
                    $query->where('id','!=',$id);
                }

                if (!empty($search)){
                    if ($_locale != $locale_default_vn) {
                        $query->where("title_{$_locale}", 'like', '%' . $search . '%');
                    } else {
                        $query->where('title', 'like', '%' . $search . '%');
                    }
                }
            })
            ->when($_locale != config('constant.locale_default_vn'), function ($query) use ($_locale) {
                $query->select('*');
                $query->selectRaw(DB::raw("title_{$_locale} as title"));
                $query->selectRaw(DB::raw("descption_{$_locale} as descption"));
                $query->selectRaw(DB::raw("content_{$_locale} as content"));
            })
            ->orderByRaw('id desc')
            ->paginate($per_page_province, ['*'], '', $current_page);
        $blogProvince = new BlogCollection($blogProvince);
        $nextBlogProvince = !empty($blogProvince->hasMorePages()) ? 1 : 0;

        $blogMember = Blog::where(function ($query) use ($id) {
                $query->where('active', 1);
                $query->where('type', 1);
                $query->where('type_blog', 2);
                if (!empty($id)){
                    $query->where('id','!=',$id);
                }
            })
            ->when($_locale != config('constant.locale_default_vn'), function ($query) use ($_locale) {
                $query->select('*');
                $query->selectRaw(DB::raw("title_{$_locale} as title"));
                $query->selectRaw(DB::raw("descption_{$_locale} as descption"));
                $query->selectRaw(DB::raw("content_{$_locale} as content"));
            })
            ->orderByRaw('id desc')
            ->paginate($per_page_member, ['*'], '', $current_page);
        $blogMember = new BlogCollection($blogMember);
        $nextBlogMember = !empty($blogMember->hasMorePages()) ? 1 : 0;

        $data['blogHot'] = [
            'data' => $blogHot,
            'next' => $nextBlogHot
        ];
        
        $data['blogProvince'] = [
            'data' => $blogProvince,
            'next' => $nextBlogProvince
        ];
        $data['blogMember'] = [
            'data' => $blogMember,
            'next' => $nextBlogMember
        ];
        return response()->json($data);
    }

    public function getListBlogNext()
    {
        $current_page = 1;
        $per_page = 10;
        $_locale = $this->request->_locale;
        $locale_default_vn = config('constant.locale_default_vn');

        if ($this->request->input('current_page')) {
            $current_page = $this->request->input('current_page');
        }
        if ($this->request->input('per_page')) {
            $per_page = $this->request->input('per_page');
        }
        $type = !empty($this->request->input('type')) ? $this->request->input('type') : 1; // 1 hot, 2 địa điểm,3 thành viên
        $id = !empty($this->request->input('id')) ? $this->request->input('id') : 0;
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $blog = Blog::where(function ($query) use ($id, $type, $search, $_locale, $locale_default_vn) {
                $query->where('active', 1);
                $query->where('type', 1);
                if ($type == 1){
                    $query->where('type_blog', 1);
                } elseif ($type == 2){
                    $query->where('type_blog', 1);
                    if (!empty($search)) {
                        if ($_locale != $locale_default_vn) {
                            $query->where("title_{$_locale}", 'like', '%' . $search . '%');
                        } else {
                            $query->where('title', 'like', '%' . $search . '%');
                        }
                    }
                } elseif ($type == 3){
                    $query->where('type_blog', 2);
                }
                if (!empty($id)){
                    $query->where('id','!=',$id);
                }
            })
            ->when($_locale != config('constant.locale_default_vn'), function ($query) use ($_locale) {
                $query->select('*');
                $query->selectRaw(DB::raw("title_{$_locale} as title"));
                $query->selectRaw(DB::raw("descption_{$_locale} as descption"));
                $query->selectRaw(DB::raw("content_{$_locale} as content"));
            })
            ->orderByRaw('id desc')
            ->paginate($per_page, ['*'], '', $current_page);
        $blog = new BlogCollection($blog);
        $nextBlog = !empty($blog->hasMorePages()) ? 1 : 0;

        $data['blog'] = [
            'data' => $blog,
            'next' => $nextBlog
        ];
        return response()->json($data);
    }

    public function getDetail($id){
        $_locale = $this->request->_locale;
        $blog = Blog::where(function ($query) {
            $query->where('active', 1);
            })
            ->where('id',$id)
            ->when($_locale != config('constant.locale_default_vn'), function ($query) use ($_locale) {
                $query->select('*');
                $query->selectRaw(DB::raw("title_{$_locale} as title"));
                $query->selectRaw(DB::raw("descption_{$_locale} as descption"));
                $query->selectRaw(DB::raw("content_{$_locale} as content"));
            })
            ->first();
        return BlogResource::make($blog);
    }

    public function getListBlogHomePage(){
        $current_page = 1;
        $per_page = 10;
        $_locale = $this->request->_locale;

        $id = !empty($this->request->input('id')) ? $this->request->input('id') : 0;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        $per_page =$this->request->query('per_page',3);
        $blog = Blog::where(function ($query) use ($id) {
                $query->where('active', 1);
                $query->where('type', 1);
                $query->where('homepage', 1);
                if (!empty($id)){
                    $query->where('id','!=',$id);
                }
            })
            ->when($_locale != config('constant.locale_default_vn'), function ($query) use ($_locale) {
                $query->select('*');
                $query->selectRaw(DB::raw("title_{$_locale} as title"));
                $query->selectRaw(DB::raw("descption_{$_locale} as descption"));
                $query->selectRaw(DB::raw("content_{$_locale} as content"));
            })
            ->orderByRaw('id desc')
            ->paginate($per_page, ['*'], '', $current_page);
        return new BlogCollection($blog);
    }
}
