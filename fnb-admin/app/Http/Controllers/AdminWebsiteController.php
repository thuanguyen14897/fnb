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
        $this->limit  = 4;
        $this->limit_section  = 5;
    }

    public function homepage(){
        $title = lang('dt_homepage');
        $homePage = !empty(get_option('homepage')) ? json_decode(get_option('homepage')) : [];
        $dtCountryCurrent = CountryCurrency::all();
        $dtCountryCurrentHomepage = CountryCurrencyHomepage::all();
        return view('admin.admin_website.homepage',[
            'title' => $title,
            'limit' => $this->limit,
            'limit_section' => $this->limit_section,
            'homePage' => $homePage,
            'dtCountryCurrent' => $dtCountryCurrent,
            'dtCountryCurrentHomepage' => $dtCountryCurrentHomepage,
        ]);
    }

    public function submit_homepage(){
        $dataPost = $this->request->input();
        $data_json_old = !empty(get_option('homepage')) ? json_decode(get_option('homepage')) : [];
        $title_section1 = !empty($dataPost['title_section1']) ? $dataPost['title_section1'] : null;
        $content_section1 = !empty($dataPost['content_section1']) ? $dataPost['content_section1'] : null;
        $country_currency = !empty($dataPost['country_currency']) ? $dataPost['country_currency'] : [];
        $title_section2 = !empty($dataPost['title_section2']) ? $dataPost['title_section2'] : null;
        $content_section2 = !empty($dataPost['content_section2']) ? $dataPost['content_section2'] : null;
        $title_section3 = !empty($dataPost['title_section3']) ? $dataPost['title_section3'] : null;
        $content_section3 = !empty($dataPost['content_section3']) ? $dataPost['content_section3'] : null;
        $title_section4 = !empty($dataPost['title_section4']) ? $dataPost['title_section4'] : null;
        $content_section4 = !empty($dataPost['content_section4']) ? $dataPost['content_section4'] : null;

        $title_section1_en = !empty($dataPost['title_section1_en']) ? $dataPost['title_section1_en'] : null;
        $content_section1_en = !empty($dataPost['content_section1_en']) ? $dataPost['content_section1_en'] : null;
        $title_section2_en = !empty($dataPost['title_section2_en']) ? $dataPost['title_section2_en'] : null;
        $content_section2_en = !empty($dataPost['content_section2_en']) ? $dataPost['content_section2_en'] : null;
        $title_section3_en = !empty($dataPost['title_section3_en']) ? $dataPost['title_section3_en'] : null;
        $content_section3_en = !empty($dataPost['content_section3_en']) ? $dataPost['content_section3_en'] : null;
        $title_section4_en = !empty($dataPost['title_section4_en']) ? $dataPost['title_section4_en'] : null;
        $content_section4_en = !empty($dataPost['content_section4_en']) ? $dataPost['content_section4_en'] : null;

        $title_section1_zh = !empty($dataPost['title_section1_zh']) ? $dataPost['title_section1_zh'] : null;
        $content_section1_zh = !empty($dataPost['content_section1_zh']) ? $dataPost['content_section1_zh'] : null;
        $title_section2_zh = !empty($dataPost['title_section2_zh']) ? $dataPost['title_section2_zh'] : null;
        $content_section2_zh = !empty($dataPost['content_section2_zh']) ? $dataPost['content_section2_zh'] : null;
        $title_section3_zh = !empty($dataPost['title_section3_zh']) ? $dataPost['title_section3_zh'] : null;
        $content_section3_zh = !empty($dataPost['content_section3_zh']) ? $dataPost['content_section3_zh'] : null;
        $title_section4_zh = !empty($dataPost['title_section4_zh']) ? $dataPost['title_section4_zh'] : null;
        $content_section4_zh = !empty($dataPost['content_section4_zh']) ? $dataPost['content_section4_zh'] : null;

        $title_section1_ko = !empty($dataPost['title_section1_ko']) ? $dataPost['title_section1_ko'] : null;
        $content_section1_ko = !empty($dataPost['content_section1_ko']) ? $dataPost['content_section1_ko'] : null;
        $title_section2_ko = !empty($dataPost['title_section2_ko']) ? $dataPost['title_section2_ko'] : null;
        $content_section2_ko = !empty($dataPost['content_section2_ko']) ? $dataPost['content_section2_ko'] : null;
        $title_section3_ko = !empty($dataPost['title_section3_ko']) ? $dataPost['title_section3_ko'] : null;
        $content_section3_ko = !empty($dataPost['content_section3_ko']) ? $dataPost['content_section3_ko'] : null;
        $title_section4_ko = !empty($dataPost['title_section4_ko']) ? $dataPost['title_section4_ko'] : null;
        $content_section4_ko = !empty($dataPost['content_section4_ko']) ? $dataPost['content_section4_ko'] : null;

        $title_section1_ja = !empty($dataPost['title_section1_ja']) ? $dataPost['title_section1_ja'] : null;
        $content_section1_ja = !empty($dataPost['content_section1_ja']) ? $dataPost['content_section1_ja'] : null;
        $title_section2_ja = !empty($dataPost['title_section2_ja']) ? $dataPost['title_section2_ja'] : null;
        $content_section2_ja = !empty($dataPost['content_section2_ja']) ? $dataPost['content_section2_ja'] : null;
        $title_section3_ja = !empty($dataPost['title_section3_ja']) ? $dataPost['title_section3_ja'] : null;
        $content_section3_ja = !empty($dataPost['content_section3_ja']) ? $dataPost['content_section3_ja'] : null;
        $title_section4_ja = !empty($dataPost['title_section4_ja']) ? $dataPost['title_section4_ja'] : null;
        $content_section4_ja = !empty($dataPost['content_section4_ja']) ? $dataPost['content_section4_ja'] : null;

        $data_json = [];
        $data_json['section_1']['title'] = $title_section1;
        $data_json['section_1']['content'] = $content_section1;

        $data_json['section_1']['title_en'] = $title_section1_en;
        $data_json['section_1']['content_en'] = $content_section1_en;

        $data_json['section_1']['title_zh'] = $title_section1_zh;
        $data_json['section_1']['content_zh'] = $content_section1_zh;

        $data_json['section_1']['title_ko'] = $title_section1_ko;
        $data_json['section_1']['content_ko'] = $content_section1_ko;

        $data_json['section_1']['title_ja'] = $title_section1_ja;
        $data_json['section_1']['content_ja'] = $content_section1_ja;

        DB::table('tbl_homepage_country_currency')->delete();
        if (!empty($country_currency)){
            foreach ($country_currency as $key => $value) {
                DB::table('tbl_homepage_country_currency')->insert([
                    'country_currency_id' => $value,
                    'country_currency_code' => CountryCurrency::find($value)->code,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        $data_json['section_2']['title'] = $title_section2;
        $data_json['section_2']['content'] = $content_section2;
        $data_json['section_3']['title'] = $title_section3;
        $data_json['section_3']['content'] = $content_section3;
        $data_json['section_4']['title'] = $title_section4;
        $data_json['section_4']['content'] = $content_section4;

        $data_json['section_2']['title_en'] = $title_section2_en;
        $data_json['section_2']['content_en'] = $content_section2_en;
        $data_json['section_3']['title_en'] = $title_section3_en;
        $data_json['section_3']['content_en'] = $content_section3_en;
        $data_json['section_4']['title_en'] = $title_section4_en;
        $data_json['section_4']['content_en'] = $content_section4_en;

        $data_json['section_2']['title_zh'] = $title_section2_zh;
        $data_json['section_2']['content_zh'] = $content_section2_zh;
        $data_json['section_3']['title_zh'] = $title_section3_zh;
        $data_json['section_3']['content_zh'] = $content_section3_zh;
        $data_json['section_4']['title_zh'] = $title_section4_zh;
        $data_json['section_4']['content_zh'] = $content_section4_zh;

        $data_json['section_2']['title_ko'] = $title_section2_ko;
        $data_json['section_2']['content_ko'] = $content_section2_ko;
        $data_json['section_3']['title_ko'] = $title_section3_ko;
        $data_json['section_3']['content_ko'] = $content_section3_ko;
        $data_json['section_4']['title_ko'] = $title_section4_ko;
        $data_json['section_4']['content_ko'] = $content_section4_ko;

        $data_json['section_2']['title_ja'] = $title_section2_ja;
        $data_json['section_2']['content_ja'] = $content_section2_ja;
        $data_json['section_3']['title_ja'] = $title_section3_ja;
        $data_json['section_3']['content_ja'] = $content_section3_ja;
        $data_json['section_4']['title_ja'] = $title_section4_ja;
        $data_json['section_4']['content_ja'] = $content_section4_ja;

        for ($i = 1;$i <= $this->limit;$i++){
            $title_section_2_image = !empty($dataPost['title_section_2_image_'.$i.'']) ? $dataPost['title_section_2_image_'.$i.''] : null;
            $content_section_2_image = !empty($dataPost['content_section_2_image_'.$i.'']) ? $dataPost['content_section_2_image_'.$i.''] : null;

            $title_section_2_image_en = !empty($dataPost['title_section_2_image_en_'.$i.'']) ? $dataPost['title_section_2_image_en_'.$i.''] : null;
            $content_section_2_image_en = !empty($dataPost['content_section_2_image_en_'.$i.'']) ? $dataPost['content_section_2_image_en_'.$i.''] : null;

            $title_section_2_image_zh = !empty($dataPost['title_section_2_image_zh_'.$i.'']) ? $dataPost['title_section_2_image_zh_'.$i.''] : null;
            $content_section_2_image_zh = !empty($dataPost['content_section_2_image_zh_'.$i.'']) ? $dataPost['content_section_2_image_zh_'.$i.''] : null;

            $title_section_2_image_ko = !empty($dataPost['title_section_2_image_ko_'.$i.'']) ? $dataPost['title_section_2_image_ko_'.$i.''] : null;
            $content_section_2_image_ko = !empty($dataPost['content_section_2_image_ko_'.$i.'']) ? $dataPost['content_section_2_image_ko_'.$i.''] : null;

            $title_section_2_image_ja = !empty($dataPost['title_section_2_image_ja_'.$i.'']) ? $dataPost['title_section_2_image_ja_'.$i.''] : null;
            $content_section_2_image_ja = !empty($dataPost['content_section_2_image_ja_'.$i.'']) ? $dataPost['content_section_2_image_ja_'.$i.''] : null;

            $type_section_2 = $dataPost['type_link_'.$i.''];
            $blog_id_section_2 = !empty($dataPost['blog_id_'.$i.'']) ? $dataPost['blog_id_'.$i.''] : 0;
            $href_id_section_2 = !empty($dataPost['href_id_'.$i.'']) ? $dataPost['href_id_'.$i.''] : null;
            if ($type_section_2 == 0){
                $href_id_section_2 = null;
            } else {
                $blog_id_section_2 = 0;
            }
            $data_json['section_2']['image_'.$i.'']['title'] = $title_section_2_image;
            $data_json['section_2']['image_'.$i.'']['content'] = $content_section_2_image;

            $data_json['section_2']['image_'.$i.'']['title_en'] = $title_section_2_image_en;
            $data_json['section_2']['image_'.$i.'']['content_en'] = $content_section_2_image_en;
            $data_json['section_2']['image_'.$i.'']['title_zh'] = $title_section_2_image_zh;
            $data_json['section_2']['image_'.$i.'']['content_zh'] = $content_section_2_image_zh;
            $data_json['section_2']['image_'.$i.'']['title_ko'] = $title_section_2_image_ko;
            $data_json['section_2']['image_'.$i.'']['content_ko'] = $content_section_2_image_ko;
            $data_json['section_2']['image_'.$i.'']['title_ja'] = $title_section_2_image_ja;
            $data_json['section_2']['image_'.$i.'']['content_ja'] = $content_section_2_image_ja;

            $data_json['section_2']['image_'.$i.'']['type'] = $type_section_2;
            $data_json['section_2']['image_'.$i.'']['blog_id'] = $blog_id_section_2;
            $data_json['section_2']['image_'.$i.'']['href_id'] = $href_id_section_2;
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
        for ($i = 1;$i <= $this->limit_section;$i++){
            $title_section_3_image = !empty($dataPost['title_section_3_image_'.$i.'']) ? $dataPost['title_section_3_image_'.$i.''] : null;
            $content_section_3_image = !empty($dataPost['content_section_3_image_'.$i.'']) ? $dataPost['content_section_3_image_'.$i.''] : null;

            $title_section_3_image_en = !empty($dataPost['title_section_3_image_en_'.$i.'']) ? $dataPost['title_section_3_image_en_'.$i.''] : null;
            $content_section_3_image_en = !empty($dataPost['content_section_3_image_en_'.$i.'']) ? $dataPost['content_section_3_image_en_'.$i.''] : null;

            $title_section_3_image_zh = !empty($dataPost['title_section_3_image_zh_'.$i.'']) ? $dataPost['title_section_3_image_zh_'.$i.''] : null;
            $content_section_3_image_zh = !empty($dataPost['content_section_3_image_zh_'.$i.'']) ? $dataPost['content_section_3_image_zh_'.$i.''] : null;

            $title_section_3_image_ko = !empty($dataPost['title_section_3_image_ko_'.$i.'']) ? $dataPost['title_section_3_image_ko_'.$i.''] : null;
            $content_section_3_image_ko = !empty($dataPost['content_section_3_image_ko_'.$i.'']) ? $dataPost['content_section_3_image_ko_'.$i.''] : null;

            $title_section_3_image_ja = !empty($dataPost['title_section_3_image_ja_'.$i.'']) ? $dataPost['title_section_3_image_ja_'.$i.''] : null;
            $content_section_3_image_ja = !empty($dataPost['content_section_3_image_ja_'.$i.'']) ? $dataPost['content_section_3_image_ja_'.$i.''] : null;

            $type_section_3 = $dataPost['type_link_section_3_'.$i.''];
            $blog_id_section_3 = !empty($dataPost['blog_id_section_3_'.$i.'']) ? $dataPost['blog_id_section_3_'.$i.''] : 0;
            $href_id_section_3 = !empty($dataPost['href_id_section_3_'.$i.'']) ? $dataPost['href_id_section_3_'.$i.''] : null;
            if ($type_section_3 == 0){
                $href_id_section_3 = null;
            } else {
                $blog_id_section_3 = 0;
            }
            $data_json['section_3']['image_'.$i.'']['title'] = $title_section_3_image;
            $data_json['section_3']['image_'.$i.'']['content'] = $content_section_3_image;

            $data_json['section_3']['image_'.$i.'']['title_en'] = $title_section_3_image_en;
            $data_json['section_3']['image_'.$i.'']['content_en'] = $content_section_3_image_en;
            $data_json['section_3']['image_'.$i.'']['title_zh'] = $title_section_3_image_zh;
            $data_json['section_3']['image_'.$i.'']['content_zh'] = $content_section_3_image_zh;
            $data_json['section_3']['image_'.$i.'']['title_ko'] = $title_section_3_image_ko;
            $data_json['section_3']['image_'.$i.'']['content_ko'] = $content_section_3_image_ko;
            $data_json['section_3']['image_'.$i.'']['title_ja'] = $title_section_3_image_ja;
            $data_json['section_3']['image_'.$i.'']['content_ja'] = $content_section_3_image_ja;

            $data_json['section_3']['image_'.$i.'']['type'] = $type_section_3;
            $data_json['section_3']['image_'.$i.'']['blog_id'] = $blog_id_section_3;
            $data_json['section_3']['image_'.$i.'']['href_id'] = $href_id_section_3;
            $key_image = 'image_'.$i;
            if ($this->request->hasFile('section_3_image_'.$i.'')){
                if (!empty($data_json_old->section_3->$key_image->image)){
                    $this->deleteFile($data_json_old->section_3->$key_image->image);
                }
                $path = $this->UploadFile($this->request->file('section_3_image_'.$i.''), 'homepage', 395, 385,false);
                $data_json['section_3']['image_'.$i.'']['image'] = $path;
            } else {
                $data_json['section_3']['image_'.$i.'']['image'] = $data_json_old->section_3->$key_image->image ?? null;
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

    public function privilege(){
        $title = lang('dt_privilege');
        $privilege = !empty(get_option('privilege')) ? json_decode(get_option('privilege')) : [];
        return view('admin.admin_website.privilege',[
            'title' => $title,
            'privilege' => $privilege
        ]);
    }

    public function submit_privilege(){
        $dataPost = $this->request->input();
        $title_section1 = !empty($dataPost['title_section1']) ? $dataPost['title_section1'] : null;
        $content_section1 = !empty($dataPost['content_section1']) ? $dataPost['content_section1'] : null;
        $title_section2 = !empty($dataPost['title_section2']) ? $dataPost['title_section2'] : null;
        $content_section2 = !empty($dataPost['content_section2']) ? $dataPost['content_section2'] : null;
        $document_use_card = !empty($dataPost['document_use_card']) ? $dataPost['document_use_card'] : null;

        $title_section1_en = !empty($dataPost['title_section1_en']) ? $dataPost['title_section1_en'] : null;
        $content_section1_en = !empty($dataPost['content_section1_en']) ? $dataPost['content_section1_en'] : null;
        $title_section2_en = !empty($dataPost['title_section2_en']) ? $dataPost['title_section2_en'] : null;
        $content_section2_en = !empty($dataPost['content_section2_en']) ? $dataPost['content_section2_en'] : null;
        $document_use_card_en= !empty($dataPost['document_use_card_en']) ? $dataPost['document_use_card_en'] : null;

        $title_section1_zh = !empty($dataPost['title_section1_zh']) ? $dataPost['title_section1_zh'] : null;
        $content_section1_zh = !empty($dataPost['content_section1_zh']) ? $dataPost['content_section1_zh'] : null;
        $title_section2_zh = !empty($dataPost['title_section2_zh']) ? $dataPost['title_section2_zh'] : null;
        $content_section2_zh = !empty($dataPost['content_section2_zh']) ? $dataPost['content_section2_zh'] : null;
        $document_use_card_zh = !empty($dataPost['document_use_card_zh']) ? $dataPost['document_use_card_zh'] : null;

        $title_section1_ko = !empty($dataPost['title_section1_ko']) ? $dataPost['title_section1_ko'] : null;
        $content_section1_ko = !empty($dataPost['content_section1_ko']) ? $dataPost['content_section1_ko'] : null;
        $title_section2_ko = !empty($dataPost['title_section2_ko']) ? $dataPost['title_section2_ko'] : null;
        $content_section2_ko = !empty($dataPost['content_section2_ko']) ? $dataPost['content_section2_ko'] : null;
        $document_use_card_ko = !empty($dataPost['document_use_card_ko']) ? $dataPost['document_use_card_ko'] : null;

        $title_section1_ja = !empty($dataPost['title_section1_ja']) ? $dataPost['title_section1_ja'] : null;
        $content_section1_ja = !empty($dataPost['content_section1_ja']) ? $dataPost['content_section1_ja'] : null;
        $title_section2_ja = !empty($dataPost['title_section2_ja']) ? $dataPost['title_section2_ja'] : null;
        $content_section2_ja = !empty($dataPost['content_section2_ja']) ? $dataPost['content_section2_ja'] : null;
        $document_use_card_ja = !empty($dataPost['document_use_card_ja']) ? $dataPost['document_use_card_ja'] : null;

        $data_json = [];
        $data_json['section_1']['title'] = $title_section1;
        $data_json['section_1']['content'] = $content_section1;
        $data_json['section_2']['title'] = $title_section2;
        $data_json['section_2']['content'] = $content_section2;
        $data_json['document_use_card'] = $document_use_card;

        $data_json['section_1']['title_en'] = $title_section1_en;
        $data_json['section_1']['content_en'] = $content_section1_en;
        $data_json['section_2']['title_en'] = $title_section2_en;
        $data_json['section_2']['content_en'] = $content_section2_en;
        $data_json['document_use_card_en'] = $document_use_card_en;

        $data_json['section_1']['title_zh'] = $title_section1_zh;
        $data_json['section_1']['content_zh'] = $content_section1_zh;
        $data_json['section_2']['title_zh'] = $title_section2_zh;
        $data_json['section_2']['content_zh'] = $content_section2_zh;
        $data_json['document_use_card_zh'] = $document_use_card_zh;

        $data_json['section_1']['title_ko'] = $title_section1_ko;
        $data_json['section_1']['content_ko'] = $content_section1_ko;
        $data_json['section_2']['title_ko'] = $title_section2_ko;
        $data_json['section_2']['content_ko'] = $content_section2_ko;
        $data_json['document_use_card_ko'] = $document_use_card_ko;

        $data_json['section_1']['title_ja'] = $title_section1_ja;
        $data_json['section_1']['content_ja'] = $content_section1_ja;
        $data_json['section_2']['title_ja'] = $title_section2_ja;
        $data_json['section_2']['content_ja'] = $content_section2_ja;
        $data_json['document_use_card_ja'] = $document_use_card_ja;

        $data_json = json_encode($data_json);
        DB::table('tbl_options')->where('name', 'privilege')->update([
            'name' => 'privilege',
            'value' => $data_json
        ]);
        alert_float('success','Thành công');
        return redirect('admin/admin_website/privilege');
    }

    public function white_paper(){
        $title = lang('dt_white_paper');
        $white_paper = !empty(get_option('white_paper')) ? json_decode(get_option('white_paper')) : [];
        return view('admin.admin_website.white_paper',[
            'title' => $title,
            'white_paper' => $white_paper
        ]);
    }

    public function submit_white_paper(){
        $dataPost = $this->request->input();
        $document_paper = !empty($dataPost['document_paper']) ? $dataPost['document_paper'] : null;
        $document_paper_en = !empty($dataPost['document_paper_en']) ? $dataPost['document_paper_en'] : null;
        $document_paper_zh = !empty($dataPost['document_paper_zh']) ? $dataPost['document_paper_zh'] : null;
        $document_paper_ko = !empty($dataPost['document_paper_ko']) ? $dataPost['document_paper_ko'] : null;
        $document_paper_ja = !empty($dataPost['document_paper_ja']) ? $dataPost['document_paper_ja'] : null;

        $data_json = [];
        $data_json['document_paper'] = $document_paper;
        $data_json['document_paper_en'] = $document_paper_en;
        $data_json['document_paper_zh'] = $document_paper_zh;
        $data_json['document_paper_ko'] = $document_paper_ko;
        $data_json['document_paper_ja'] = $document_paper_ja;

        $data_json = json_encode($data_json);
        DB::table('tbl_options')->where('name', 'white_paper')->update([
            'name' => 'white_paper',
            'value' => $data_json
        ]);
        alert_float('success','Thành công');
        return redirect('admin/admin_website/white_paper');
    }

    public function page_not_found(){
        $title = lang('dt_page_not_found');
        $page_not_found = !empty(get_option('page_not_found')) ? json_decode(get_option('page_not_found')) : [];
        return view('admin.admin_website.page_not_found',[
            'title' => $title,
            'page_not_found' => $page_not_found
        ]);
    }

    public function submit_page_not_found(){
        $dataPost = $this->request->input();
        $document_paper = !empty($dataPost['document_paper']) ? $dataPost['document_paper'] : null;
        $document_paper_en = !empty($dataPost['document_paper_en']) ? $dataPost['document_paper_en'] : null;
        $document_paper_zh = !empty($dataPost['document_paper_zh']) ? $dataPost['document_paper_zh'] : null;
        $document_paper_ko = !empty($dataPost['document_paper_ko']) ? $dataPost['document_paper_ko'] : null;
        $document_paper_ja = !empty($dataPost['document_paper_ja']) ? $dataPost['document_paper_ja'] : null;

        $data_json = [];
        $data_json['document_paper'] = $document_paper;
        $data_json['document_paper_en'] = $document_paper_en;
        $data_json['document_paper_zh'] = $document_paper_zh;
        $data_json['document_paper_ko'] = $document_paper_ko;
        $data_json['document_paper_ja'] = $document_paper_ja;

        $data_json = json_encode($data_json);
        DB::table('tbl_options')->where('name', 'page_not_found')->update([
            'name' => 'page_not_found',
            'value' => $data_json
        ]);
        alert_float('success','Thành công');
        return redirect('admin/admin_website/page_not_found');
    }
}
