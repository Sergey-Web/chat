<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CheckDBRedis;
use App\AuthUserRedis;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;

class AgentAjaxController extends Controller
{
    public $userId;
    public $channel;
    public $status;
    public $invitations;

    public function connectAgent()
    {
        $this->userId = Auth::id();
        $dataAgentRedis = $this->_getDataAgent();
        if($dataAgentRedis['status'] != 'on') {
            return;
        }
        $dataAgent = AuthUserRedis::login();

        if(!$dataAgent) {
            return 'false';
        }

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
        $this->userId = Auth::id();
        $this->_getDateDBRedis($this->userId);
        $pickUpInvite = $this->_pickUpInvite();

        if($pickUpInvite == FALSE) {
            return 'false';
        }

        return $this->changeStatus($pickUpInvite);
    }

    public function sendMessage($pickUpInvite)
    {
        
    }

    private function _getDateDBRedis($userId)
    {
        $dataUser = json_decode(Redis::command('get', [$userId]));
        $this->channel = $dataUser->channel;
        $this->status = $dataUser->status;
        $this->role = $dataUser->role;

        return $dataUser;
    }

    private function _checkInvitations()
    {
        $company = $this->channel;
        $invitations = Redis::command('scard', [$company . '_invite']);

        return $invitations;
    }

    private function _checkStatusAgent()
    {
       $dataAgent = $this->_getDataAgent();
       return $dataAgent['status'];
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

    private function changeStatus($pickUpInvite)
    {
        //save in DBredis for User
        Redis::command('set', [$pickUpInvite . '_connected', $this->userId]);

        //save status Agents in DBredis
        $getDataAgent = $this->_getDataAgent();
        $getDataAgent['status'] = $pickUpInvite;
        Redis::command('set', [$this->userId, json_encode($getDataAgent)]);

        return $getDataAgent;

    }

    private function _getDataAgent()
    {
        $getDataAgent = json_decode(Redis::command('get', [$this->userId]), true);
        return $getDataAgent;
    }
}