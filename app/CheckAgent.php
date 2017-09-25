<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;

class CheckAgent extends Model
{
    public static function getDataAgent($agentId)
    {
        $getDataAgent = json_decode(Redis::command('get', [$agentId]), true);
        return $getDataAgent;
    }

    public static function getMessages($userId)
    {
        $getMessages = Redis::command('get', [$userId . '_messages']);
        return $getMessages;
    }

    public static function checkInvitations($company)
    {
        $invitations = Redis::command('scard', [$company . '_invite']);

        return $invitations;
    }

    public static function getDateDBRedis($agentId)
    {
        $dataUser = json_decode(Redis::command('get', [$agentId]), true);
        if($dataUser) {

            $invitations = self::checkInvitations($dataUser['channel']);

            if($invitations) {
                $data = [
                    'channel'     => $dataUser['agentId'],
                    'userId'      => $dataUser['channel'],
                    'agentId'     => $dataUser['userId'],
                    'name'        => $dataUser['role'],
                    'role'        => $dataUser['name'],
                    'invitations' => $invitations
                ];
                return $data;
            }
        }

        return $dataUser;
    }
}
