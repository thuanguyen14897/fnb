<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

trait RequestServiceTrait
{
    public function sendRequestToService(string $method, string $url, Request $request, array $options = [])
    {
        $token = $options['token'] ?? $request->client->token ?? Config::get('constant')['token_default'];
        $hasFile = $options['has_file'] ?? false;

        $http = Http::withHeaders([
            'Accept' => 'application/json',
        ]);

        if ($token) {
            $http = $http->withToken($token);
        }

        $method = strtolower($method);

        if ($hasFile && $request->files->count() > 0) {
            $multipart = [];

            // Xử lý fields thông thường
            foreach ($request->except(array_keys($request->files->all())) as $key => $value) {
                if (is_null($value)) {
                    $value = '';
                } elseif (is_bool($value)) {
                    $value = $value ? '1' : '0';
                } elseif (is_array($value)) {
                    $value = json_encode($value);
                }

                $multipart[] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }

            // Xử lý file upload
            foreach ($request->files as $name => $file) {
                if (is_array($file)) {
                    // Trường hợp file là mảng (nhiều file cùng name)
                    foreach ($file as $index => $f) {
                        if ($f->isValid()) {
                            $multipart[] = [
                                'name' => $name.'[]',
                                'contents' => fopen($f->getRealPath(), 'r'),
                                'filename' => $f->getClientOriginalName(),
                            ];
                        }
                    }
                } else {
                    // Trường hợp file đơn
                    if ($file->isValid()) {
                        $multipart[] = [
                            'name' => $name,
                            'contents' => fopen($file->getRealPath(), 'r'),
                            'filename' => $file->getClientOriginalName(),
                        ];
                    }
                }
            }

            // Gửi đi với multipart
            return $http->asMultipart()->post($url, $multipart);
        }

        if ($method === 'get') {
            return $http->get($url, $request->all());
        }
        return $http->post($url, $request->all());
    }
}
