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

    public function __construct()
    {
        $this->userId = CheckUser::checkIdUser();
        $this->connectionId = $this->userId . '_connected';
        $this->subdomain = CheckUser::getSubdomain();
    }

    private function _getDataUser()
    {
        $userId = $this->userId;
        $subdomain = $this->subdomain;
        $messages = $this->_getMessage();
        $data = [
            'channel'  => $subdomain,
            'userId'   => $userId,
            'messages' => $messages,
            'agentId'    => '',
        ];

        return $data;
    }

    public function connectUser()
    {
        $isConnected = $this->_isConnected();
        if($isConnected) {
            return $isConnected;
        }
        $data = $this->_getDataUser();
        return $data;
    }

    public function sendMessage(Request $request)
    {
        $this->_saveMessageRedis($request->all());

        $isConnected = $this->_isConnected();
        if($isConnected) {
            Event::fire( new ConnectionUserChannel($isConnected) );
            return $isConnected;
        }

        $this->_saveInvite();
        $data = $this->_getDataUser();
        Event::fire( new ConnectionUserChannel($data) );

        return $data;
    }

    private function _saveMessageRedis($messages)
    {
        $isMessages = $this->_getMessage();
        if($isMessages) {
            Redis::command('set', [
                    $messages['userId'] . '_messages', $isMessages . "\n" . 'user:' . $messages['messages'] 
                ]
            );
        } else {
            Redis::command('set', [
                    $messages['userId'] . '_messages', 'user:' . $messages['messages'] 
                ]
            );
        }
    }

    private function _isConnected()
    {
        $agentId = Redis::command('get', [$this->connectionId]);
        if($agentId) {
            $messages = $this->_getMessage();
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
