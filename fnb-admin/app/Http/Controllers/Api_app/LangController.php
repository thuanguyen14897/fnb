<?php

namespace App\Http\Controllers\Api_app;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use App\Models\Clients;

class LangController extends AuthController
{
    public function getData()
    {
        // $locale = $this->request->input('locale', 'vn');
        $locale = App::getLocale();
        // Đọc nội dung của file JSON tương ứng
        $localePath = resource_path("lang/{$locale}");

         // Kiểm tra nếu thư mục ngôn ngữ tồn tại
         if (File::exists($localePath) && File::isDirectory($localePath)) {
            // Lấy tất cả các file trong thư mục
            $files = File::allFiles($localePath);

            // Chỉ lấy các file JSON
            $jsonFiles = collect($files)->filter(function ($file) {
                return $file->getExtension() === 'json';
            });

            // Đọc nội dung của các file JSON và trả về dưới dạng mảng
            $data = [];
            foreach ($jsonFiles as $file) {
                $jsonContent = File::get($file);
                $data[$file->getFilenameWithoutExtension()] = json_decode($jsonContent, true);
            }

            return response()->json($data);
        }

        return response()->json(['error' => 'Language folder not found'], 404);
    }

    public function setLocale()
    {
        $data = [];
        $locale = $this->request->input('locale');
        if (empty($locale)) {
            $data['result'] = 0;
            $data['message'] = lang('language_not_found');
            return response()->json($data);
        }

        $client = $this->request->client;
        $client_id = $client->id;
        $option = [
            'locale' => $locale
        ];

        $up = Clients::where('id', $client_id)->update($option);
        if ($up) {
            $data['result'] = 1;
            $data['message'] = lang('change_lang_success');
            return response()->json($data);
        } else {
            $data['result'] = 0;
            $data['message'] = lang('change_lang_fail');
            return response()->json($data);
        }

        return response()->json($data);
    }
}
