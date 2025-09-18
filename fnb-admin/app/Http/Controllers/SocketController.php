<?php

namespace App\Http\Controllers;

use App\Libraries\Socket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocketController extends Controller
{
    public function __construct(Request $request)
    {
        $this->opts = [];
        parent::__construct($request);
        DB::enableQueryLog();
        $opts = [
        ];
        $this->opts = $opts;
        $this->socket = new Socket($opts);
    }

    public function login_socket(){
        $user_id = $this->request->input('user_id');
        $user_name = $this->request->input('user_name');
        $db_name = $this->request->input('db_name');
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

    public function sendNotification(){
        $channels = $this->request->input('channels');
        $event = $this->request->input('event');
        $data = $this->request->input('data');
        $db_name = $this->request->input('db_name');
        if ($channels && $event && $db_name) {
            $result = $this->socket->sendNotification([
                'channels' => $channels,
                'event' => $event,
                'data' => $data,
                'db_name' => $db_name
            ]);
            if (isset($result) && !empty($result['result'])) {
                $data = [
                    'status' => true,
                    'message' => 'Notification sent successfully',
                    'data' => $result['result']
                ];
            } else {
                $data = [
                    'status' => false,
                    'message' => 'Failed to send notification',
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
