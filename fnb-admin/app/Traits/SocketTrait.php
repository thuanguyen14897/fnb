<?php
namespace App\Traits;
use App\Libraries\Socket;

trait SocketTrait
{
    public function sendNotificationSocket($dataParams = [],$type = 'notification',$id = 0){
        $this->socket = new Socket();
        $arrStaffID = [];
        $arrType = ['change-status','notification'];
        if (in_array($type, $arrType)){
            if (!empty($dataParams['channels'])) {
                foreach ($dataParams['channels'] as $user) {
                    array_push($arrStaffID, $user['object_id']);
                }
            } else {
                $arrStaffID = [-1];
            }

        } else {
            $arrStaffID = array_merge($arrStaffID,$dataParams['channels'] ?? []);
        }
        $arrStaffID = array_unique($arrStaffID);
        $channels = $arrStaffID;
        $event = $dataParams['event'] ?? null;
        $data = $dataParams['data'] ?? null;
        $db_name = $dataParams['db_name'] ?? null;
        if ($event && $db_name) {
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
            return $data;
        } else {
            $data = [
                'status' => false,
                'message' => 'Invalid input data',
                'data' => null
            ];
            return $data;
        }
    }
}
