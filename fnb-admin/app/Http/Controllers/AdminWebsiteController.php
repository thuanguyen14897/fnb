<?php

namespace App\Http\Controllers;

use App\Models\CountryCurrency;
use App\Models\CountryCurrencyHomepage;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWebsiteController extends Controller
{
    use UploadFile;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->limit  = 6;
        $this->limit_section  = 6;
    }

    public function homepage(){
        $title = lang('Trang chủ App');
        $homePage = !empty(get_option('homepage')) ? json_decode(get_option('homepage')) : [];
        return view('admin.admin_website.homepage',[
            'title' => $title,
            'limit' => $this->limit,
            'limit_section' => $this->limit_section,
            'homePage' => $homePage,
        ]);
    }

    public function submit_homepage(){
        $dataPost = $this->request->input();
        $data_json_old = !empty(get_option('homepage')) ? json_decode(get_option('homepage')) : [];
        $data_json = [];

        for ($i = 1;$i <= $this->limit;$i++){
            $title_section_2_image = !empty($dataPost['title_section_2_image_'.$i.'']) ? $dataPost['title_section_2_image_'.$i.''] : null;
            $detail_section_2_image = !empty($dataPost['detail_section_2_image_'.$i.'']) ? $dataPost['detail_section_2_image_'.$i.''] : null;
            $content_section_2_image = !empty($dataPost['content_section_2_image_'.$i.'']) ? $dataPost['content_section_2_image_'.$i.''] : null;
            $data_json['section_2']['image_'.$i.'']['title'] = $title_section_2_image;
            $data_json['section_2']['image_'.$i.'']['detail'] = $detail_section_2_image;
            $data_json['section_2']['image_'.$i.'']['content'] = $content_section_2_image;
            $key_image = 'image_'.$i;
            if ($this->request->hasFile('section_2_image_'.$i.'')){
                if (!empty($data_json_old->section_2->$key_image->image)){
                    $this->deleteFile($data_json_old->section_2->$key_image->image);
                }
                if ($i%2 == 0){
                    $path = $this->UploadFile($this->request->file('section_2_image_'.$i.''), 'homepage', 400, 300,false);
                } else {
                    $path = $this->UploadFile($this->request->file('section_2_image_'.$i.''), 'homepage', 400, 500,false);
                }
                $data_json['section_2']['image_'.$i.'']['image'] = $path;
            } else {
                $data_json['section_2']['image_'.$i.'']['image'] = $data_json_old->section_2->$key_image->image ?? null;
            }
        }
        $data_json = json_encode($data_json);
        DB::table('tbl_options')->where('name', 'homepage')->update([
            'name' => 'homepage',
            'value' => $data_json
        ]);
        alert_float('success','Thành công');
        return redirect('admin/admin_website/homepage');
    }
}
