<?php
namespace App\Libraries;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CallCenter
{
    protected $token = "";
    protected $tokenVoice = "";
    protected $env = "live";
    protected $baseURL = array(
        'live' => 'https://api.caresoft.vn/kanow/api/v1'
    );
    protected $URI = array(
        'agents' => '/agents',
        'contactsByPhone' => '/contactsByPhone',
        'detailContacts' => '/contacts',
        'detailTickets' => '/tickets',
    );

    public function __construct($opts)
    {
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

        // set KEY
        if (isset($opts) && !empty($opts["token"])) {
            $this->token = $opts["token"];
        } else {
            throw new Exception("token key is required !");
        }
        if (isset($opts) && !empty($opts["tokenVoice"])) {
            $this->tokenVoice = $opts["tokenVoice"];
        } else {
            throw new Exception("tokenVoice key is required !");
        }
    }


    public function sendListAgents($data = [])
    {
        $url = $this->baseURL[$this->env] . $this->URI['agents'];
        $result = $this->sendRequestCallCenter($data, $url,'GET');
        if (isset($result) && $result->code == 'ok') {
            return ($result);
        } else {
            return $result;
        }
    }

    public function sendContactsByPhone($data = [])
    {
        $url = $this->baseURL[$this->env] . $this->URI['contactsByPhone'];
        $result = $this->sendRequestCallCenter($data, $url,'GET');
        if (isset($result) && $result->code == 'ok') {
            return ($result);
        } else {
            return $result;
        }
    }

    public function sendUpdateContacts($data = [])
    {
        $contactId = !empty($data['contactId']) ? $data['contactId'] : 0;
        unset($data['contactId']);
        $url = $this->baseURL[$this->env] . $this->URI['detailContacts'].'/'.$contactId;
        $result = $this->sendRequestCallCenter($data, $url,'PUT');
        if (isset($result) && $result->code == 'ok') {
            return ($result);
        } else {
            return $result;
        }
    }

    public function sendAddContacts($data = [])
    {
        $url = $this->baseURL[$this->env] . $this->URI['detailContacts'];
        $result = $this->sendRequestCallCenter($data, $url,'POST');
        if (isset($result) && $result->code == 'ok') {
            return ($result);
        } else {
            return $result;
        }
    }

    public function sendAddTicket($data = [])
    {
        $url = $this->baseURL[$this->env] . $this->URI['detailTickets'];
        $result = $this->sendRequestCallCenter($data, $url,'POST');
        if (isset($result) && $result->code == 'ok') {
            return ($result);
        } else {
            return $result;
        }
    }

    public function sendUpdateTicket($data = [])
    {
        $ticket_id = !empty($data['ticket_id']) ? $data['ticket_id'] : 0;
        unset($data['ticket_id']);
        $url = $this->baseURL[$this->env] . $this->URI['detailTickets'].'/'.$ticket_id;
        $result = $this->sendRequestCallCenter($data, $url,'PUT');
        if (isset($result) && $result->code == 'ok') {
            return ($result);
        } else {
            return $result;
        }
    }


    private function sendRequestCallCenter($data, $url,$method = 'GET')
    {
        $data_string = $data;
        $data_string = json_encode($data_string);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->token.'',
                'Content-Type: application/json'
            ),
        ));
        $result = curl_exec($curl);
        return json_decode($result);
    }
}

?>
