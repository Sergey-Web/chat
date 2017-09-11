<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CheckDBRedis;
use App\AuthUserRedis;
use Illuminate\Support\Facades\Redis;

class AgentAjaxController extends Controller
{
    public $userId;
    public $channel;
    public $status;

    public function connectAgent()
    {
        $dataAgent = AuthUserRedis::login();

        if(!$dataAgent) {
            return 'false';
        }

        $this->userId = $dataAgent['userId'];
        $this->getDateDBRedis($this->userId);
        $data = [
            'channel' => $this->channel,
            'status'  => $this->status,
        ];

        return $data;
    }

    private function getDateDBRedis()
    {
        $userId = $this->userId;
        $dataUser = json_decode(Redis::command('get', [$userId]));
        $this->channel = $dataUser->channel;
        $this->status = $dataUser->status;
        $this->role = $dataUser->role;
    }
}
