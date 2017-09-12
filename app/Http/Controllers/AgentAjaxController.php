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
    public $invitations;

    public function connectAgent()
    {
        $dataAgent = AuthUserRedis::login();

        if(!$dataAgent) {
            return 'false';
        }

        $this->userId = $dataAgent['userId'];
        $this->_getDateDBRedis($this->userId);

        $this->invitations = $this->_checkInvitations();

        $data = [
            'channel'     => $this->channel,
            'role'        => $this->role,
            'status'      => $this->status,
            'invitations' => $this->invitations
        ];

        return $data;
    }

    public function connectAgentUser()
    {
        $this->connectAgent();
        $pickUpInvite = $this->_pickUpInvite();
        if($pickUpInvite == FALSE) {
            return 'false';
        }
        //save in DBredis for User
        Redis::command('set', [$pickUpInvite . '_connected', $this->userId]);

        //save in DBredis for Agent

        return $pickUpInvite;
    }

    public function sendMessage()
    {
        
    }

    private function _getDateDBRedis()
    {
        $userId = $this->userId;
        $dataUser = json_decode(Redis::command('get', [$userId]));
        $this->channel = $dataUser->channel;
        $this->status = $dataUser->status;
        $this->role = $dataUser->role;
    }

    private function _checkInvitations()
    {
        $company = $this->channel;
        $invitations = Redis::command('scard', [$company . '_invite']);

        return $invitations;
    }

    private function _pickUpInvite()
    {
        $company = $this->channel;
        $invitations = Redis::command('smembers', [$company . '_invite']);
        $countInvitations = count($invitations);
        if($countInvitations == 0) {
            return FALSE;
        } 
        $lastUser = $invitations[$countInvitations-1];
        $delInvite = Redis::command('srem', [$company . '_invite', $lastUser]);
        return $lastUser;
    }
}
