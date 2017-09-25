<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AuthUserRedis;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use App\Events\ConnectionUserChannel;
use App\CheckAgent;

class AgentAjaxController extends Controller
{
    public $agentId;
    public $channel;
    public $userId;

    public function connectAgent()
    {
        $this->agentId = Auth::id();
        $dataAgentRedis = CheckAgent::getDataAgent($this->agentId);

        if($dataAgentRedis['userId'] != '') {
            $userId = $dataAgentRedis['userId'];
            $dataAgentRedis['messages'] = CheckAgent::getMessages($userId);
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
        $data = $this->_getDateDBRedis($this->agentId);

        //After acceptance of the invitation by the agent, it will be deleted
        $pickUpInvite = $this->_pickUpInvite();
        $this->_changeStatus($pickUpInvite);
        
        if($pickUpInvite == FALSE) {
            return 'false';
        }

        $data['connect'] = TRUE;
        $data['userId'] = $pickUpInvite;
        $data['messages'] = CheckAgent::getMessages($data['userId']);
        $checkInvitations = CheckAgent::checkInvitations($this->channel);

        if($checkInvitations < 1) {
            $data['storageInvite'] = 'false';
        }

        Event::fire( new ConnectionUserChannel($data) );

        return $data;
    }

    public function sendMessage(Request $request)
    {
        $this->agentId = Auth::id();
        $data = CheckAgent::getDataAgent($this->agentId);
        $userId = $data['userId'];
        $agentName = $data['name'];
        $responseMessage = $request->all()['message'];

        $messages = CheckAgent::getMessages($userId);
        $saveMessage = $this->_saveMessages($userId, $agentName, $messages, $responseMessage);
        $getNewMessages = CheckAgent::getMessages($userId);
        $data['messages'] = $responseMessage;
        Event::fire( new ConnectionUserChannel($data) );
        return $data;
    }

    public function disconnectChat()
    {
        return 'disconnect';
    }

    private function _saveMessages($userId, $agentName, $messages, $response)
    {

        $decodeMessages = json_decode($messages, true);
        $decodeMessages[] = [
            'id'       => $userId,
            'name'     => $agentName,
            'role'     => 3,
            'date'     => time(), 
            'messages' => $response
        ];

        $saveMessage = Redis::command('set', [
                    $userId . '_messages', json_encode($decodeMessages) 
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
            $this->name = $dataUser['name'];
        }

        $invitations = CheckAgent::checkInvitations($this->channel);

        if($invitations) {
            $data = [
                'channel'     => $this->channel,
                'userId'      => $this->userId,
                'agentId'     => $this->agentId,
                'name'        => $this->name,
                'role'        => $this->role,
                'invitations' => $invitations
            ];
            return $data;
        }

        return $dataUser;
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

    private function _changeStatus($pickUpInvite)
    {
        //save in DBredis for User
        Redis::command('set', [$pickUpInvite . '_connected', $this->agentId]);

        //save status Agents in DBredis
        $getDataAgent = CheckAgent::getDataAgent($this->agentId);
        $getDataAgent['userId'] = $pickUpInvite;
        Redis::command('set', [$this->agentId, json_encode($getDataAgent)]);

        return $getDataAgent;

    }
}