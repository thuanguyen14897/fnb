<?php
namespace App\Libraries;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Alepay
{

    protected $alepayUtils;
    protected $publicKey = "";
    protected $checksumKey = "";
    protected $apiKey = "";
    protected $callbackUrl = "";
    protected $callbackUrlCar = "";
    protected $env = "test";
    protected $env_refund = "test_refund";
    protected $baseURL = array(
        'dev' => 'localhost:8080',
        'test' => 'https://alepay-v3-sandbox.nganluong.vn/api/v3/checkout',
        'live' => 'https://alepay-v3.nganluong.vn/api/v3/checkout',
        'test_refund' => 'https://alepay-v3-sandbox.nganluong.vn/api/v1/checkout',
        'live_refund' => 'https://alepay-v3.nganluong.vn/api/v1/checkout'
    );
    protected $publicKeyRsa = array(
        'test' => "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCbhZfDGF8ABYOS5GLtsgJJh8P9
j0JnpyOzs1KtFw4f858rCCnt327RhkmkU9YkYmOGujPHrA+j/9VDyXdh9VrobdDR
uKr4FBp7wBHy0JTTd62+XpYiyU50pat40KBZtCM41CbUte/ihheCQpzzoNND0EKK
iaYSX/NhHQkyvZ5trwIDAQAB
-----END PUBLIC KEY-----",
        'live' => "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCYnfOYwclyuFg4NGmi+/AfstAH
BEQnYAkXwloW7pU64qr32YHLn6Ak44yZBJSRMqB0dpFez5fbnQ1KSa2oQVdtzzeT
D7ZdvEA+Woiu5uaGK82+kVcDsF6laY4y7G5gk7iaDFB7q5xasoBbyZmZDc029BbC
ZJHsQbkVmJuZNavwoQIDAQAB
-----END PUBLIC KEY-----"
    );
    protected $URI = array(
        'request-payment' => '/request-payment',
        'get-transaction-info' => '/get-transaction-info',
        'get-list-banks' => '/get-list-banks',
        'request-profile' => '/request-profile',
        'get-customer-info' => '/get-customer-info',
        'cancel-profile' => '/cancel-profile',
        'request-tokenization-payment' => '/request-tokenization-payment',
        'merchant-request-refund' => '/merchant-request-refund',
    );

    public function __construct($opts)
    {
//        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

        if (!function_exists('curl_init')) {
            throw new Exception('Alepay needs the CURL PHP extension.');
        }
        if (!function_exists('json_decode')) {
            throw new Exception('Alepay needs the JSON PHP extension.');
        }

        // set KEY
        if (isset($opts) && !empty($opts["apiKey"])) {
            $this->apiKey = $opts["apiKey"];
        } else {
            throw new Exception("API key is required !");
        }
        if (isset($opts) && !empty($opts["encryptKey"])) {
            $this->publicKey = $opts["encryptKey"];
        } else {
            throw new Exception("Encrypt key is required !");
        }
        if (isset($opts) && !empty($opts["checksumKey"])) {
            $this->checksumKey = $opts["checksumKey"];
        } else {
            throw new Exception("Checksum key is required !");
        }
        if (isset($opts) && !empty($opts["callbackUrl"])) {
            $this->callbackUrl = $opts["callbackUrl"];
        }
        if (isset($opts) && !empty($opts["callbackUrlCar"])) {
            $this->callbackUrlCar = $opts["callbackUrlCar"];
        }
        if (isset($opts) && !empty($opts["callbackUrlDriver"])) {
            $this->callbackUrlDriver = $opts["callbackUrlDriver"];
        }
    }

    public function getPublicKeyRsa(){
        return $this->publicKeyRsa[$this->env];
    }

    public function sendOrderToAlepay($data)
    {
        $payment_mode_id = $data['payment_mode_id'];
        $transaction_id = $data['transaction_id'];
        unset($data['payment_mode_id']);
        unset($data['transaction_id']);
        if (!empty($data['check_driver'])){
            $data['returnUrl'] = $this->callbackUrlDriver . "?payment_mode_id=" . $payment_mode_id . "&transaction_id=" . $transaction_id;
            $data['cancelUrl'] = $this->callbackUrlDriver . "?payment_mode_id=" . $payment_mode_id . "&transaction_id=" . $transaction_id;
        } else {
            $data['returnUrl'] = $this->callbackUrl . "?payment_mode_id=" . $payment_mode_id . "&transaction_id=" . $transaction_id;
            $data['cancelUrl'] = $this->callbackUrl . "?payment_mode_id=" . $payment_mode_id . "&transaction_id=" . $transaction_id;
        }
        $url = $this->baseURL[$this->env] . $this->URI['request-payment'];
        $result = $this->sendRequestToAlepay($data, $url);
        if (isset($result) && $result->code == '000') {
            return ($result);
        } else {
            return $result;
        }
    }

    public function sendOrderToAlepayDomestic($data)
    {
        // get demo data
        $data = $this->createCheckoutDomesticData();
        $data['returnUrl'] = $this->callbackUrl;
        $data['cancelUrl'] = $this->callbackUrl;
        $url = $this->baseURL[$this->env] . $this->URI['requestPayment'];
        $result = $this->sendRequestToAlepay($data, $url);
        if ($result->errorCode == '000') {
            return json_decode($result);
        } else {
            echo json_encode($result);
        }
    }


    public function getTransactionInfo($data)
    {
        $url = $this->baseURL[$this->env] . $this->URI['get-transaction-info'];
        $result = $this->sendRequestToAlepay($data, $url);
        if (isset($result) && $result->code == '000') {
            return $result;
        } else {
            return $result;
        }
    }

    public function getListBanks($data)
    {
        $url = $this->baseURL[$this->env] . $this->URI['get-list-banks'];
        $result = $this->sendRequestToAlepay($data, $url);
        if (isset($result) && $result->code == '000') {
            return $result;
        } else {
            return $result;
        }
    }

    public function getCustomerInfo($data)
    {
        $url = $this->baseURL[$this->env] . $this->URI['get-customer-info'];
        $result = $this->sendRequestToAlepay($data, $url);
        if (isset($result) && $result->code == '000') {
            return $result;
        } else {
            return $result;
        }
    }

    public function sendCardLinkRequest($data)
    {
        $data['returnUrl'] = $this->callbackUrl;
        $data['callback'] = $this->callbackUrlCar;
        $data['street'] = !empty($data['street']) ? $data['street'] : '-';
        $data['city'] = !empty($data['city']) ? $data['city'] : '-';
        $data['state'] = !empty($data['state']) ? $data['state'] : '-';
        $url = $this->baseURL[$this->env] . $this->URI['request-profile'];
        $result = $this->sendRequestToAlepay($data, $url);
        if (isset($result) && $result->code == '000') {
            return $result;
        } else {
            return $result;
        }
    }

    public function sendTokenizationPayment($data)
    {
        $payment_mode_id = $data['payment_mode_id'];
        $transaction_id = $data['transaction_id'];
        unset($data['payment_mode_id']);
        unset($data['transaction_id']);
        if (!empty($data['check_driver'])){
            $data['returnUrl'] = $this->callbackUrlDriver . "?payment_mode_id=" . $payment_mode_id . "&transaction_id=" . $transaction_id;
            $data['cancelUrl'] = $this->callbackUrlDriver . "?payment_mode_id=" . $payment_mode_id . "&transaction_id=" . $transaction_id;
        } else {
            $data['returnUrl'] = $this->callbackUrl . "?payment_mode_id=" . $payment_mode_id . "&transaction_id=" . $transaction_id;
            $data['cancelUrl'] = $this->callbackUrl . "?payment_mode_id=" . $payment_mode_id . "&transaction_id=" . $transaction_id;
        }
        $url = $this->baseURL[$this->env] . $this->URI['request-tokenization-payment'];
        $result = $this->sendRequestToAlepay($data, $url);
        return $result;
    }

    public function cancelCardLink($data)
    {
        $url = $this->baseURL[$this->env] . $this->URI['cancel-profile'];
        $result = $this->sendRequestToAlepay($data, $url);
        return $result;
    }

    public function getRefundTransaction($data)
    {
        $url = $this->baseURL[$this->env_refund] . $this->URI['merchant-request-refund'];
        unset($data['transaction_id']);
        $checksum = hash('sha256', ($data['transactionCode'] .''. $data['merchantRefundCode'] .''. $data['refundAmount'] .''. $this->checksumKey));
        $data['checksum'] = $checksum;
        $result = $this->sendRequestToAlepay($data, $url);
        if (isset($result) && $result->code == '000') {
            return $result;
        } else {
            return $result;
        }
    }

    private function sendRequestToAlepay($data, $url)
    {
        $signature = $this->makeSignature($data);

        $data['tokenKey'] = $this->apiKey;
        $data['signature'] = $signature;
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
                $stringData .="$key=$value"."&";
            }
        }
        $stringData = rtrim($stringData,"&");
        $signature = hash_hmac("sha256",$stringData,$this->checksumKey);
        return $signature;
    }

}

?>
