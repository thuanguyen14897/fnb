<?php

namespace App\Http\Controllers\Api_app;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class OrderRefController extends AuthController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getOrderRef($ref = ''){
        $data = getReference($ref);
        return response()->json(['reference_no' => $data]);
    }

    public function updateOrderRef($ref = ''){
        $data = updateReference($ref);
        return response()->json(['result' => $data]);
    }

}
