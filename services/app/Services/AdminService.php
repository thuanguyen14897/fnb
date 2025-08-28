<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AdminService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.fnb_admin.base_url'), '/');
    }

    public function getOrderRef($ref = '')
    {
        $response = Http::get("{$this->baseUrl}/api/getOrderRef/{$ref}");
        $response = $response->json();
        return $response;
    }

    public function updateOrderRef($ref = '')
    {
        $response = Http::get("{$this->baseUrl}/api/updateOrderRef/{$ref}");
        $response = $response->json();
        return $response;
    }

    public function get_option($field = '')
    {
        $response = Http::get("{$this->baseUrl}/api/getOption/{$field}");
        $response = $response->json();
        return !empty($response['result']) ? $response['result'] : null;
    }
}
