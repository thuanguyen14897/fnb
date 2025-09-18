<?php
namespace App\Libraries;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class Socket
{
    protected $key = "";
    protected $env = "test";
    protected $baseURL = array(
        'test' => 'http://192.168.1.178:3005/',
        'live' => 'https://socketfoso.fmrp.vn/',
    );
    public $socket_link_connect;
    protected $URI = array(
        'add-user' => 'add-user',
        'send-notification' => 'send-notification',
    );

    public function __construct($opts = [])
    {
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

        if (!function_exists('curl_init')) {
            throw new Exception('Socket needs the CURL PHP extension.');
        }
        if (!function_exists('json_decode')) {
            throw new Exception('Socket needs the JSON PHP extension.');
        }
        $this->socket_link_connect = $this->baseURL[$this->env];
    }

    public function login($data)
    {
        $url = $this->baseURL[$this->env] . $this->URI['add-user'];
        $result = $this->sendRequestToSocket($data,'POST', $url);
        if (isset($result) && !empty($result['result'])) {
            return ($result);
        } else {
            return $result;
        }
    }

    public function sendNotification($data)
    {
        $url = $this->baseURL[$this->env] . $this->URI['send-notification'];
        $result = $this->sendRequestToSocket($data,'POST', $url);
        if (isset($result) && !empty($result['result'])) {
            return ($result);
        } else {
            return $result;
        }
    }

    private function sendRequestToSocket($data,$method = 'GET', $url)
    {
        $stringData = $data;
        $items = array(
            $stringData,
        );
        $data_string = json_encode($items[0]);
        $stringData = "";
        if ($method == 'GET'){
            if (!empty($data)){
                foreach ($data as $key => $value){
                    if($value === false){
                        $value = "false";
                    }
                    $stringData .="&$key=$value"."&";
                }
            }
        }
        $stringData = rtrim($stringData,"&");
        $url = $url.$stringData;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        $dataResult['result'] = json_decode($result);
        return $dataResult;
    }
}

?>
