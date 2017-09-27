<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class CheckUser extends Model
{
    private static $lifetimeId = 3600;
    private static $lifetimeMessage = 3600;
    private static $lifetimeInvitations = 300;
    private static $userId;
    private static $company;

    public static function checkIdUser()
    {
        $userIdCookie = isset($_COOKIE['userId']) ?
            $_COOKIE['userId'] :
                self::_assingIdUser();

        self::$company = self::getDomain();

        return $userIdCookie;
    }

    public static function getDataUser($userId, $subdomain)
    {
        $messages = self::_getMessage($userId);
        $data = [
            'channel'  => $subdomain,
            'role'     => 4,
            'userId'   => $userId,
            'agentId'  => '',
            'messages' => $messages
        ];

        return $data;
    }

    public static function isConnected($userId, $subdomain, $connectionId, $messages = '', $timestamp = '')
    {
        $agentId = Redis::command('get', [$connectionId]);
        if($agentId) {
            $data = [
                'userId'    => $userId,
                'channel'   => $subdomain,
                'agentId'   => $agentId,
                'messages'  => $messages,
                'timestamp' => $timestamp
            ];

            return $data;
        }

        return $agentId;
    }

    public static function saveMessageRedis($userId, $messages, $timestamp)
    {
        $isMessages = self::_getMessage($userId);
        if($isMessages) {

            $decodeMessages = json_decode($isMessages, true);

            $decodeMessages[] = [
                'id'        => $userId,
                'name'      => '',
                'role'      => 4,
                'messages'  => $messages['messages'], 
                'timestamp' => $timestamp
            ];

            Redis::command('set', [
                    $userId . '_messages', json_encode($decodeMessages)
                ]
            );
        } else {
            $arrMessage[] = [
                'id'        => $userId,
                'name'      => '',
                'role'      => 4,
                'messages'  => $messages['messages'], 
                'timestamp' => $timestamp
            ];
            Redis::command('set', [
                    $userId . '_messages', json_encode($arrMessage)
                ]
            );
        }

       return self::_timerMessages($userId);
    }

    private static function _getMessage($userId)
    {
        $messageId = $userId . '_messages';
        $messages = Redis::command('get', [$messageId]);

        return $messages;
    }

    private static function checkConnected()
    {
        $invite = Redis::command('set', [$pickUpInvite . '_connected', $this->agentId]);
        return $invite;
    }

    private static function _assingIdUser()
    {
        $microtime = microtime(true);
        self::$userId = substr(md5($microtime), -10, 10);

        return self::$userId;
    }

    private static function _getIpUser() {
        $userIp = request()->server('REMOTE_ADDR');
        return $userIp;
    }

    public static function getDomain()
    {
        $domain = env('APP_DOMAIN');
        return $domain;
    }

    public static function getSubdomain()
    {
        $domain = self::getDomain();
        $subdomain = preg_replace('/\.' . $domain . '$/s', '', request()->getHttpHost());
        return $subdomain;
    }

    private function _checkInvitations()
    {
        $company = $this->getSubdomain();
        $invitations = Redis::command('smembers', [$company . '_invite']);

        return $invitations;
    }

    public static function _saveInvite($userId, $company)
    {
        Redis::command('sadd', [$company . '_invite', $userId]);
        self::_timerInvitations($company);
    }

    private static function _timerCookieId($userIdCookie)
    {
        setcookie('userId', $userIdCookie, time()+self::$lifetimeId);
    }

    private static function _timerMessages($userId)
    {
        self::$lifetimeMessage;
        Redis::command('expire', [$userId . '_messages', time()+self::$lifetimeMessage]);
    }

    private static function _timerInvitations($channel)
    {
        Redis::command('expire', [$channel . '_invite', time()+self::$lifetimeInvitations]);
    }
}
