<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Event;
use App\Events\ConnectionUserChannel;
use App\CheckUser;

class UserAjaxController extends Controller
{
    public $userId;
    public $subdomain;
    public $connectionId;
    public $message;

    public function __construct()
    {
        $this->userId = CheckUser::checkIdUser();
        $this->connectionId = $this->userId . '_connected';
        $this->subdomain = CheckUser::getSubdomain();
    }

    public function connectUser()
    {
        $data = $this->_getDataUser();
        $isConnected = $this->_isConnected();
        if($isConnected) {
            $data['agentId'] = $isConnected['agentId'];
        }
        return $data;
    }

    public function sendMessage(Request $request)
    {
        $this->message = $request->all()['messages'];
        $this->_saveMessageRedis($this->userId, $request->all());

        $isConnected = $this->_isConnected();
        if($isConnected) {
            Event::fire( new ConnectionUserChannel($isConnected) );
            return $isConnected;
        }
        $this->_saveInvite();
        $data = $this->_getDataUser();
        $data['messages'] = $this->message;
        Event::fire( new ConnectionUserChannel($data) );

        return $data;
    }

    private function _getDataUser()
    {
        $userId = $this->userId;
        $subdomain = $this->subdomain;
        $messages = $this->_getMessage();
        $data = [
            'channel'  => $subdomain,
            'role'     => 4,
            'userId'   => $userId,
            'agentId'  => '',
            'messages' => $messages
        ];

        return $data;
    }

    private function _saveMessageRedis($userId, $messages)
    {
        $isMessages = $this->_getMessage();
        if($isMessages) {

            $decodeMessages = json_decode($isMessages, true);

            $decodeMessages[] = [ 
                'id'       => $userId,
                'name'     => '',
                'role'     => 4,
                'messages' => $messages['messages'], 
                'date'     => time()
            ];

            Redis::command('set', [
                    $userId . '_messages', json_encode($decodeMessages)
                ]
            );
        } else {
            $arrMessage[] = [
                'id'       => $userId,
                'name'     => '',
                'role'     => 4,
                'messages' => $messages['messages'], 
                'date'     => time()
            ];
            Redis::command('set', [
                    $userId . '_messages', json_encode($arrMessage)
                ]
            );
        }
    }

    private function _isConnected()
    {
        $agentId = Redis::command('get', [$this->connectionId]);
        if($agentId) {
            $messages = $this->message;
            $data = [
                'userId'   => $this->userId,
                'channel'  => $this->subdomain,
                'agentId'  => $agentId,
                'messages' => $messages
            ];

            return $data;
        }

        return $agentId;
    }

    private function _getMessage()
    {
        $messageId = $this->userId . '_messages';
        $messages = Redis::command('get', [$messageId]);

        return $messages;
    }

    private function _saveInvite()
    {
        $company = $this->subdomain;
        $userId = $this->userId;
        Redis::command('sadd', [$company . '_invite', $userId]);
    }
}
