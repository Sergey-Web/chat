<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AuthAgentRedis;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use App\Events\ConnectionUserChannel;
use App\CheckAgent;
use App\Message;

class AgentAjaxController extends Controller
{
    public $channel;
    public $userId;

    public function connectAgent()
    {
        $agentId = Auth::id();
        $dataAgentRedis = CheckAgent::getDataAgent($agentId);

        if($dataAgentRedis['userId'] != '') {
            $userId = $dataAgentRedis['userId'];
            $dataAgentRedis['messages'] = CheckAgent::getMessages($userId);
            return $dataAgentRedis;
        }

        $dataAgent = AuthAgentRedis::login();

        if(!$dataAgent) {
            return;
        }

        $data = !empty(CheckAgent::getDataDBRedis($agentId)) ? 
            CheckAgent::getDataDBRedis($agentId) : 
                $dataAgent;
        return $data;
    }

    public function connectAgentUser()
    {
        $agentId = Auth::id();
        $data = CheckAgent::getDataDBRedis($agentId);

        //After acceptance of the invitation by the agent, it will be deleted
        $pickUpInvite = CheckAgent::pickUpInvite($data['channel']);
        CheckAgent::changeStatus($agentId, $pickUpInvite);
        
        if($pickUpInvite == FALSE) {
            return 'false';
        }

        $data['connect'] = TRUE;
        $data['userId'] = $pickUpInvite;
        $data['messages'] = CheckAgent::getMessages($data['userId']);
        $checkInvitations = CheckAgent::checkInvitations($data['channel']);

        if($checkInvitations < 1) {
            $data['storageInvite'] = 'false';
        }

        Event::fire( new ConnectionUserChannel($data) );

        return $data;
    }

    public function sendMessage(Request $request)
    {
        $agentId = Auth::id();
        $data = CheckAgent::getDataAgent($agentId);
        $userId = $data['userId'];
        $agentName = $data['name'];
        $responseMessage = $request->all()['message'];

        $messages = CheckAgent::getMessages($userId);
        $saveMessage = CheckAgent::saveMessages($userId, $agentName, $messages, $responseMessage);
        $getNewMessages = CheckAgent::getMessages($userId);
        $data['messages'] = $responseMessage;
        Event::fire( new ConnectionUserChannel($data) );
        return $data;
    }

    public function disconnectChat()
    {
        $agentId = Auth::id();
        $data = CheckAgent::getDataAgent($agentId);
        $messages = CheckAgent::getMessages($data['userId']);

        //Save a message from the database Mysql
        CheckAgent::saveMessagesDB($agentId, $messages);

        //Removing from the Radis DB connection with the user
        CheckAgent::delDataAgentDBRedis($data['userId'], $agentId);

        //update Agent data
        $dataAgent = AuthAgentRedis::login();

        $countInvite = CheckAgent::checkInvitations($data['channel']);
        if($countInvite > 0) {
            $dataAgent['invitations'] = $countInvite;
        }

        return $dataAgent;
    }
}