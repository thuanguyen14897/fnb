<?php
namespace App\Libraries;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Pay2s
{
    protected $callbackUrl;
    protected $env = "live";
    protected $baseURL = array(
        'dev' => 'https://sandbox-payment.pay2s.vn/v1/gateway/api',
        'live' => 'https://payment.pay2s.vn/v1/gateway/api',
    );
    protected $partnerCode = array(
        'dev' => "PAY2S7EPF0SB1ZP27W71",
        'live' => "PAY2SI1F6DJQZMSB8ATY"
    );
    protected $accessKey = array(
        'dev' => "66e862c89d4d4d1f34063dc1967fbd64deec4da3cba90af65167fbb8503b2eb3",
        'live' => "211be4c34295c104947a1c24b22bfc8fa86dadccf5b9e4ab46781fa9709b9591"
    );
    protected $secretKey = array(
        'dev' => "3cb0ba535605a7f1bad779d727bd234e822703f3c3f531b394524c2e4644ff97",
        'live' => "999903243d70fe42b85b525f7f9a4e58ad21a1fa1c3171aa2686a5d52fe6f008"
    );
    protected $URI = array(
        'create' => '/create',
    );

    public function __construct($opts)
    {
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

        if (!function_exists('curl_init')) {
            throw new Exception('Pay2s needs the CURL PHP extension.');
        }
        if (!function_exists('json_decode')) {
            throw new Exception('Pay2s needs the JSON PHP extension.');
        }
        if (isset($opts) && !empty($opts["callbackUrl"])) {
            $this->callbackUrl = $opts["callbackUrl"];
        }
    }

    public function sendOrderToPay2s($data)
    {
        $payment_mode_id = $data['payment_mode_id'] ?? 0;
        $transaction_package_id = $data['transaction_package_id'];
        unset($data['payment_mode_id']);
        unset($data['transaction_package_id']);
        $data['redirectUrl'] = $this->callbackUrl . "?payment_mode_id=" . $payment_mode_id . "&transaction_package_id=" . $transaction_package_id;
        $data['ipnUrl'] = $this->callbackUrl . "?payment_mode_id=" . $payment_mode_id . "&transaction_package_id=" . $transaction_package_id;
        $data['accessKey'] = $this->accessKey[$this->env];
        $data['partnerCode'] = $this->partnerCode[$this->env];
        $data['requestId'] = time();
        $url = $this->baseURL[$this->env] . $this->URI['create'];
        $result = $this->sendRequestToPay2s($data, $url);
        if (isset($result) && isset($result->resultCode) && $result->resultCode == '0') {
            return ($result);
        } else {
            return $result;
        }
    }

    private function sendRequestToPay2s($data, $url)
    {
        $orderType = $data['orderType'];
        unset($data['orderType']);
        $bankAccounts = $data['bankAccounts'];
        $data['bankAccounts'] = 'Array';
        $signature = $this->makeSignature($data);

        $data['signature'] = $signature;
        $data['bankAccounts'] = $bankAccounts;
        $data['partnerName'] = 'Payment Transaction';
        $data['orderType'] = $orderType;
        $stringData = $data;

        $items = array(
            $stringData,
        );
        $data_string = json_encode($items[0]);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        return json_decode($result);
    }

    public function makeSignature($data)
    {
        ksort($data);
        $stringData = "";
        if (!empty($data)){
            foreach ($data as $key => $value){
                if($value === true){
                    $value = "true";
                }
                if($value === false){
                    $value = "false";
                }
                if ($key == 'bankAccounts'){
                    $value = "Array";
                }
                $stringData .="$key=$value"."&";
            }
        }
        $stringData = rtrim($stringData,"&");
        $signature = hash_hmac("sha256",$stringData,$this->secretKey[$this->env]);
        return $signature;
    }

}

?>
