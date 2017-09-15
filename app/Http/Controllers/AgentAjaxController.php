<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CheckUser;
use App\AuthUserRedis;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use App\Events\ConnectionUserChannel;

class AgentAjaxController extends Controller
{
    public $agentId;
    public $channel;
    public $userId;


    public function connectAgent()
    {
        $this->agentId = Auth::id();
        $dataAgentRedis = $this->_getDataAgent();

        if($dataAgentRedis && $dataAgentRedis['userId'] != '') {
            $userId = $dataAgentRedis['userId'];
            $dataAgentRedis['messages'] = $this->_getMessages($userId);
            return $dataAgentRedis;
        }

        $dataAgent = AuthUserRedis::login();

        if(!$dataAgent) {
            return;
        }

        $data = !empty($this->_getDateDBRedis($this->agentId)) ? 
            $this->_getDateDBRedis($this->agentId) : 
                $dataAgent;
        return $data;
    }

    public function connectAgentUser()
    {
        $this->agentId = Auth::id();
        $this->_getDateDBRedis($this->agentId);
        $pickUpInvite = $this->_pickUpInvite();

        if($pickUpInvite == FALSE) {
            return 'false';
        }

        return $this->changeStatus($pickUpInvite);
    }

    public function sendMessage(Request $request)
    {
        $this->agentId = Auth::id();
        $data = $this->_getDataAgent();
        $userId = $this->_getDataAgent()['userId'];
        $agentName = $this->_getDataAgent()['name'];
        $responseMessage = $request->all()['message'];

        $messages = $this->_getMessages($userId);
        $saveMessage = $this->_saveMessages($userId, $agentName, $messages, $responseMessage);
        $getNewMessages = $this->_getMessages($userId);
        $data['messages'] = $getNewMessages;
        Event::fire( new ConnectionUserChannel($data) );
        return $getNewMessages;
    }

    private function _getMessages($userId)
    {
        $getMessages = Redis::command('get', [$userId . '_messages']);
        return $getMessages;
    }

    private function _saveMessages($userId, $agentName, $messages, $response)
    {
        $saveMessage = Redis::command('set', [
                    $userId . '_messages', $messages . "\n" . $agentName . ':' . $response 
                ]
            );
        return $saveMessage;
    }

    private function _getDateDBRedis($agentId)
    {
        $dataUser = json_decode(Redis::command('get', [$agentId]), true);

        if($dataUser) {
            $this->channel = $dataUser['channel'];
            $this->userId = $dataUser['userId'];
            $this->role = $dataUser['role'];
        }

        $invitations = $this->_checkInvitations();

        if($invitations) {
            $data = [
                'channel'     => $this->channel,
                'userId'      => $this->userId,
                'role'        => $this->role,
                'invitations' => $invitations
            ];
            return $data;
        }

        return $dataUser;
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

    private function changeStatus($pickUpInvite)
    {
        //save in DBredis for User
        Redis::command('set', [$pickUpInvite . '_connected', $this->agentId]);

        //save status Agents in DBredis
        $getDataAgent = $this->_getDataAgent();
        $getDataAgent['userId'] = $pickUpInvite;
        Redis::command('set', [$this->agentId, json_encode($getDataAgent)]);

        return $getDataAgent;

    }

    private function _getDataAgent()
    {
        $getDataAgent = json_decode(Redis::command('get', [$this->agentId]), true);
        return $getDataAgent;
    }
}