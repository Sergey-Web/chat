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

    public static function getDataDBRedis($agentId)
    {
        $dataUser = json_decode(Redis::command('get', [$agentId]), true);
        if($dataUser) {

            $invitations = self::checkInvitations($dataUser['channel']);

            if($invitations) {
                $data = [
                    'channel'     => $dataUser['channel'],
                    'userId'      => $dataUser['userId'],
                    'agentId'     => $dataUser['agentId'],
                    'name'        => $dataUser['role'],
                    'role'        => $dataUser['name'],
                    'invitations' => $invitations
                ];
                return $data;
            }
        }

        return $dataUser;
    }

    public static function pickUpInvite($company)
    {
        $invitations = Redis::command('smembers', [$company . '_invite']);
        $countInvitations = count($invitations);
        if($countInvitations == 0) {
            return FALSE;
        } 
        $lastUser = $invitations[$countInvitations-1];
        $delInvite = Redis::command('srem', [$company . '_invite', $lastUser]);

        return $lastUser;
    }

    public static function changeStatus($agentId, $pickUpInvite)
    {
        //save in DBredis for User
        Redis::command('set', [$pickUpInvite . '_connected', $agentId]);

        //save status Agents in DBredis
        $getDataAgent = CheckAgent::getDataAgent($agentId);
        $getDataAgent['userId'] = $pickUpInvite;
        Redis::command('set', [$agentId, json_encode($getDataAgent)]);

        return $getDataAgent;

    }

    public static function saveMessages($userId, $agentName, $messages, $response)
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

    public static function saveMessagesDB($agentId, $messages)
    {
        $saveMessagesDB = Message::create([
           'user_id'  => $agentId,
           'messages' => $messages
        ]);

        return $saveMessagesDB;
    }

    public static function delDataAgentDBRedis($userId, $agentId)
    {
        Redis::command('del', [$userId . '_connected']);
        Redis::command('del', [$agentId]);
    }
}
