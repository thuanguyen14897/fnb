<?php

namespace App\Http\Controllers\Api_app;

use App\Libraries\Socket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SocketController extends AuthController
{

    public function __construct(Request $request)
    {
        $this->opts = [];
        parent::__construct($request);
        DB::enableQueryLog();
        $opts = [];
        $this->opts = $opts;
        $this->socket = new Socket($opts);
    }

    public function login_socket(){
        $user_id = $this->request->input('user_id');
        $user_name = $this->request->input('user_name');
        $db_name = config('database.connections.mysql.database');
        if ($user_id && $user_name && $db_name) {
            $result = $this->socket->login([
                'user_id' => $user_id,
                'user_name' => $user_name,
                'db_name' => $db_name
            ]);
            if (isset($result) && !empty($result['result'])) {
                $data = [
                    'status' => true,
                    'message' => 'Login successful',
                    'data' => $result['result']
                ];
            } else {
                $data = [
                    'status' => false,
                    'message' => 'Login failed',
                    'data' => null
                ];
            }
            echo json_encode($data);die();
        } else {
            $data = [
                'status' => false,
                'message' => 'Invalid input data',
                'data' => null
            ];
            echo json_encode($data);
        }
    }
}
