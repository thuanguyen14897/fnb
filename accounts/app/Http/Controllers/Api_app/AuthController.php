<?php

namespace App\Http\Controllers\Api_app;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class AuthController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
