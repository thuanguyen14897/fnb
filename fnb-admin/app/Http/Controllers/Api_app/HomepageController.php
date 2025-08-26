<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\ProvinceResource;
use App\Models\CategoryCard;
use App\Models\CategoryPreferential;
use App\Models\CountryCurrencyHomepage;
use App\Models\Province;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomepageController extends AuthController
{
    use UploadFile;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getData()
    {
        $_locale = $this->request->_locale;
        $locale_default_vn = config('constant.locale_default_vn');

        $homePage = !empty(get_option('homepage')) ? json_decode(get_option('homepage')) : [];
        if (!empty($homePage)) {
            if ($_locale != config('constant.locale_default_vn')) {
                $homePage->section_1->title = $homePage->section_1->{"title_{$_locale}"};
                $homePage->section_1->content = $homePage->section_1->{"content_{$_locale}"};


                $homePage->section_2->title = $homePage->section_2->{"title_{$_locale}"};
                $homePage->section_2->content = $homePage->section_2->{"content_{$_locale}"};
                for($i = 1; $i <= 4; $i++) {
                    $homePage->section_2->{"image_{$i}"}->title = $homePage->section_2->{"image_{$i}"}->{"title_{$_locale}"};
                    $homePage->section_2->{"image_{$i}"}->content = $homePage->section_2->{"image_{$i}"}->{"content_{$_locale}"};
                }

                $homePage->section_3->title = $homePage->section_3->{"title_{$_locale}"};
                $homePage->section_3->content = $homePage->section_3->{"content_{$_locale}"};
                for($i = 1; $i <= 5; $i++) {
                    $homePage->section_3->{"image_{$i}"}->title = $homePage->section_3->{"image_{$i}"}->{"title_{$_locale}"};
                    $homePage->section_3->{"image_{$i}"}->content = $homePage->section_3->{"image_{$i}"}->{"content_{$_locale}"};
                }

                $homePage->section_4->title = $homePage->section_4->{"title_{$_locale}"};
                $homePage->section_4->content = $homePage->section_4->{"content_{$_locale}"};
            }

            unset($homePage->section_1->title_en);
            unset($homePage->section_1->content_en);
            unset($homePage->section_1->title_zh);
            unset($homePage->section_1->content_zh);
            unset($homePage->section_1->title_ko);
            unset($homePage->section_1->content_ko);
            unset($homePage->section_1->title_ja);
            unset($homePage->section_1->content_ja);

            unset($homePage->section_2->title_en);
            unset($homePage->section_2->content_en);
            unset($homePage->section_2->title_zh);
            unset($homePage->section_2->content_zh);
            unset($homePage->section_2->title_ko);
            unset($homePage->section_2->content_ko);
            unset($homePage->section_2->title_ja);
            unset($homePage->section_2->content_ja);

            unset($homePage->section_3->title_en);
            unset($homePage->section_3->content_en);
            unset($homePage->section_3->title_zh);
            unset($homePage->section_3->content_zh);
            unset($homePage->section_3->title_ko);
            unset($homePage->section_3->content_ko);
            unset($homePage->section_3->title_ja);
            unset($homePage->section_3->content_ja);

            unset($homePage->section_4->title_en);
            unset($homePage->section_4->content_en);
            unset($homePage->section_4->title_zh);
            unset($homePage->section_4->content_zh);
            unset($homePage->section_4->title_ko);
            unset($homePage->section_4->content_ko);
            unset($homePage->section_4->title_ja);
            unset($homePage->section_4->content_ja);
        }

        $data['homePage'] = $homePage;
        $data['base']['base'] = asset('storage/');

        $dtCountryCurrencyHomepage = CountryCurrencyHomepage::all()->pluck('country_currency_code')->toArray();
        $data['countryCurrency'] = show_rate_usd_to_vnd($dtCountryCurrencyHomepage);

        $data['categoryPreferential'] = CategoryPreferential::where('active',1)->select('*')->when($_locale != $locale_default_vn, function ($query) use ($_locale) {
            $query->selectRaw(DB::raw("title_{$_locale} as title"));
            $query->selectRaw(DB::raw("detail_{$_locale} as detail"));
        })->get();

        $data['categoryCard'] = CategoryCard::with(['card_preferential' => function($query) use ($_locale, $locale_default_vn) {
            if ($_locale != $locale_default_vn) {
                // $query->select('tbl_card_preferential.id', 'tbl_card_preferential.title', 'tbl_card_preferential.detail');
                $query->select('tbl_category_preferential.*');
                $query->selectRaw(DB::raw("title_{$_locale} as title"));
                $query->selectRaw(DB::raw("detail_{$_locale} as detail"));
            }
        }])->where('active',1)->select('*')->when($_locale != $locale_default_vn, function ($query) use ($_locale) {
            $query->selectRaw(DB::raw("name_{$_locale} as name"));
            $query->selectRaw(DB::raw("content_{$_locale} as content"));
        })->orderByRaw('order_by asc')->get();
        return response()->json($data);
    }

    public function getDataPrivilege(){
        $_locale = $this->request->_locale;
        $locale_default_vn = config('constant.locale_default_vn');

        $privilege = !empty(get_option('privilege')) ? json_decode(get_option('privilege')) : [];
        if (!empty($privilege)) {
            if ($_locale != $locale_default_vn) {
                $privilege->section_1->title = $privilege->section_1->{"title_{$_locale}"};
                $privilege->section_1->content = $privilege->section_1->{"content_{$_locale}"};

                $privilege->section_2->title = $privilege->section_2->{"title_{$_locale}"};
                $privilege->section_2->content = $privilege->section_2->{"content_{$_locale}"};

                $privilege->document_use_card = $privilege->{"document_use_card_{$_locale}"};
            }

            unset($privilege->section_1->title_en);
            unset($privilege->section_1->content_en);
            unset($privilege->section_1->title_zh);
            unset($privilege->section_1->content_zh);
            unset($privilege->section_1->title_ko);
            unset($privilege->section_1->content_ko);
            unset($privilege->section_1->title_ja);
            unset($privilege->section_1->content_ja);

            unset($privilege->section_2->title_en);
            unset($privilege->section_2->content_en);
            unset($privilege->section_2->title_zh);
            unset($privilege->section_2->content_zh);
            unset($privilege->section_2->title_ko);
            unset($privilege->section_2->content_ko);
            unset($privilege->section_2->title_ja);
            unset($privilege->section_2->content_ja);

            unset($privilege->document_use_card_en);
            unset($privilege->document_use_card_zh);
            unset($privilege->document_use_card_ko);
            unset($privilege->document_use_card_ja);
        }


        $data['privilege'] = $privilege;
        $data['categoryPreferential'] = CategoryPreferential::where('active',1)->select('*')->when($_locale != $locale_default_vn, function ($query) use ($_locale) {
            $query->selectRaw(DB::raw("title_{$_locale} as title"));
            $query->selectRaw(DB::raw("detail_{$_locale} as detail"));
        })->get();

        $data['categoryCard'] = CategoryCard::with(['card_preferential' => function($query) use ($_locale, $locale_default_vn) {
            if ($_locale != $locale_default_vn) {
                $query->select('tbl_category_preferential.*');
                $query->selectRaw(DB::raw("title_{$_locale} as title"));
                $query->selectRaw(DB::raw("detail_{$_locale} as detail"));
            }
        }])->where('active',1)->select('*')
        ->when($_locale != $locale_default_vn, function ($query) use ($_locale) {
            $query->selectRaw(DB::raw("name_{$_locale} as name"));
            $query->selectRaw(DB::raw("content_{$_locale} as content"));
        })->orderByRaw('order_by asc')->get();

        $data['base']['base'] = asset('storage/');
        return response()->json($data);
    }

    public function getDataWhitePaper(){
        $_locale = $this->request->_locale;
        $locale_default_vn = config('constant.locale_default_vn');

        $white_paper = !empty(get_option('white_paper')) ? json_decode(get_option('white_paper')) : [];
        if (!empty($white_paper)){
            if ($_locale != config('constant.locale_default_vn')) {
                $white_paper->document_paper = $white_paper->{"document_paper_{$_locale}"};
            }
        }
        $white_paper->document_paper = str_replace('src="/storage', 'src="'.asset('/storage').'', $white_paper->document_paper);
        $white_paper->document_paper_en = str_replace('src="/storage', 'src="'.asset('/storage').'', $white_paper->document_paper_en);
        $white_paper->document_paper_zh = str_replace('src="/storage', 'src="'.asset('/storage').'', $white_paper->document_paper_zh);
        $white_paper->document_paper_ko = str_replace('src="/storage', 'src="'.asset('/storage').'', $white_paper->document_paper_ko);
        $white_paper->document_paper_ja = str_replace('src="/storage', 'src="'.asset('/storage').'', $white_paper->document_paper_ja);
        unset($white_paper->document_paper_en);
        unset($white_paper->document_paper_zh);
        unset($white_paper->document_paper_ko);
        unset($white_paper->document_paper_ja);
        $data['white_paper'] = $white_paper;

        $data['base']['base'] = asset('storage/');
        return response()->json($data);
    }

    public function getDataPageNotFound(){
        $_locale = $this->request->_locale;
        $locale_default_vn = config('constant.locale_default_vn');

        $page_not_found = !empty(get_option('page_not_found')) ? json_decode(get_option('page_not_found')) : [];
        if (!empty($page_not_found)){
            if ($_locale != config('constant.locale_default_vn')) {
                $page_not_found->document_paper = $page_not_found->{"document_paper_{$_locale}"};
            }
        }
        $page_not_found->document_paper = str_replace('src="/storage', 'src="'.asset('/storage').'', $page_not_found->document_paper);
        $page_not_found->document_paper_en = str_replace('src="/storage', 'src="'.asset('/storage').'', $page_not_found->document_paper_en);
        $page_not_found->document_paper_zh = str_replace('src="/storage', 'src="'.asset('/storage').'', $page_not_found->document_paper_zh);
        $page_not_found->document_paper_ko = str_replace('src="/storage', 'src="'.asset('/storage').'', $page_not_found->document_paper_ko);
        $page_not_found->document_paper_ja = str_replace('src="/storage', 'src="'.asset('/storage').'', $page_not_found->document_paper_ja);

        unset($page_not_found->document_paper_en);
        unset($page_not_found->document_paper_zh);
        unset($page_not_found->document_paper_ko);
        unset($page_not_found->document_paper_ja);
        $data['page_not_found'] = $page_not_found;

        $data['base']['base'] = asset('storage/');
        return response()->json($data);
    }
}
