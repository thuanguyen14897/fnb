<?php

namespace App\Http\Controllers\Api_app;

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
        $homePage = !empty(get_option('homepage')) ? json_decode(get_option('homepage')) : [];
        if (!empty($homePage)) {
            for($i = 1; $i <= 6; $i++) {
                $homePage->section_2->{"image_{$i}"}->title = $homePage->section_2->{"image_{$i}"}->{"title"};
                $homePage->section_2->{"image_{$i}"}->content = $homePage->section_2->{"image_{$i}"}->{"content"};
                $homePage->section_2->{"image_{$i}"}->image = asset('storage/'.$homePage->section_2->{"image_{$i}"}->{"image"});
            }
        }

        $data['homePage'] = $homePage;
        $data['base']['base'] = asset('storage/');

        return response()->json($data);
    }
}
