<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Event;
use App\Events\ConnectionUserChannel;
use App\Events\ConnectionUserAgent;
use App\CheckDBRedis;

class UserAjaxController extends Controller
{
    public $userId;
    public $subdomain;
    public $connectionId;

    public function __construct()
    {
        $this->userId = CheckDBRedis::checkIdUser();
        $this->connectionId = $this->userId . '_connected';
        $this->subdomain = CheckDBRedis::getSubdomain();
    }

    public function connectUser()
    {
        $isConnected = $this->_isConnected();

        if($isConnected) {
            Event::fire( new ConnectionUserAgent($isConnected) );
            return;
        }

        $userId = $this->userId;
        $subdomain = $this->subdomain;
        $data = [
            'channel' => $subdomain,
            'userId'  => $userId,
            'agent'   => ''
        ];

        Event::fire( new ConnectionUserChannel($data) );

        return $data;
    }

    private function _isConnected()
    {
        $agentId = Redis::command('get', [$this->connectionId]);
        if($agentId) {
            $messages = $this->_getMessage();
            $data = [
                'userId'  => $this->userId,
                'channel' => $subdomain,
                'agent'   => $agentId,
                'message' => $messages
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
}
