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

    public function getListBlog(){
        $current_page = 1;
        $per_page = 10;
        $id = !empty($this->request->input('id')) ? $this->request->input('id') : 0;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('per_page')) {
            $per_page =$this->request->query('per_page');
        }
        $blog = Blog::where(function ($query) use ($id) {
                $query->where('active', 1);
                if (!empty($id)){
                    $query->where('id','!=',$id);
                }
            })
            ->orderByRaw('id desc')
            ->paginate($per_page, ['*'], '', $current_page);
        return new BlogCollection($blog);
    }

    public function getDetail($id){
        $blog = Blog::where(function ($query) {
                $query->where('active', 1);
            })
            ->where('id',$id)
            ->first();
        if (empty($blog)){
            return response()->json(['data' => (object)[]]);
        } else {
            return BlogResource::make($blog);
        }
    }
}
