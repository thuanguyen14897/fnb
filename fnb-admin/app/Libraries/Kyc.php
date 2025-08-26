<?php
namespace App\Libraries;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Kyc
{

    protected $publicKey = "";
    protected $publicSecret = "";
    protected $env = "test";
    protected $baseURL = array(
        'test' => 'https://demo.computervision.com.vn/api/v2',
        'live' => 'https://cloud.computervision.com.vn/api/v2',
    );
    protected $URI = array(
        'cards' => '/ekyc/cards',
        'face_matching' => '/ekyc/face_matching',
    );

    public function __construct($opts)
    {
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');


        // set KEY
        if (isset($opts) && !empty($opts["apiKey"])) {
            $this->publicKey = $opts["apiKey"];
        } else {
            throw new Exception("API key is required !");
        }
        if (isset($opts) && !empty($opts["secretKey"])) {
            $this->publicSecret = $opts["secretKey"];
        } else {
            throw new Exception("Secret key is required !");
        }
    }

    public function sendCardToKyc($data,$params = [])
    {
        $url = $this->baseURL[$this->env] . $this->URI['cards'];
        if ($params['format_type'] == 'base64') $data = json_encode($data);
        $result = $this->sendRequestToKyc($data, $url,'POST',$params);
        if (isset($result) && $result->errorCode == '0') {
            return ($result);
        } else {
            return $result;
        }
    }

    private function sendRequestToKyc($data, $url,$method = "POST",$params = [])
    {
        if (!empty($params)) {
            $url = $url. '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',)
        );
        curl_setopt($ch, CURLOPT_USERPWD, "$this->publicKey:$this->publicSecret");
        $result = curl_exec($ch);
        return json_decode($result);
    }
}

?>
