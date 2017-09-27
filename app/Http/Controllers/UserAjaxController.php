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
        $userId = $this->userId;
        $subdomain = $this->subdomain;
        $connectionId = $this->connectionId;

        $data = CheckUser::getDataUser($userId, $subdomain);
        $isConnected = CheckUser::isConnected($userId, $subdomain, $connectionId);
        if($isConnected) {
            $data['agentId'] = $isConnected['agentId'];
        }
        return $data;
    }

    public function sendMessage(Request $request)
    {
        $userId = $this->userId;
        $subdomain = $this->subdomain;
        $connectionId = $this->connectionId;
        $message = $request->all()['messages'];
        $timestamp = time();

        $checkUser = CheckUser::saveMessageRedis($this->userId, $request->all(), $timestamp);
        return $checkUser;
        $isConnected = CheckUser::isConnected(
            $userId, $subdomain, $connectionId, $message, $timestamp
        );

        if($isConnected) {
            Event::fire( new ConnectionUserChannel($isConnected) );
            return $isConnected;
        }
        CheckUser::_saveInvite($userId, $subdomain);
        $data = CheckUser::getDataUser($userId, $subdomain);
        $data['messages'] = $message;
        Event::fire( new ConnectionUserChannel($data) );

        return $data;
    }
}
