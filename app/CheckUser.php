<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class CheckUser extends Model
{
    private static $userId;

    public static function checkIdUser()
    {
        if(isset($_COOKIE['userId'])) {
            self::$userId = $_COOKIE['userId'];
            setcookie('userId', $_COOKIE['userId'], time()+3600);
        } else {
            self::_assingIdUser();
            setcookie('userId', self::$userId, time()+3600);
        }

        return self::$userId;
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

    public static function isConnected($userId, $subdomain, $connectionId, $messages = '')
    {
        $agentId = Redis::command('get', [$connectionId]);
        if($agentId) {
            $data = [
                'userId'   => $userId,
                'channel'  => $subdomain,
                'agentId'  => $agentId,
                'messages' => $messages
            ];

            return $data;
        }

        return $agentId;
    }

    public static function saveMessageRedis($userId, $messages)
    {
        $isMessages = self::_getMessage($userId);
        if($isMessages) {

            $decodeMessages = json_decode($isMessages, true);

            $decodeMessages[] = [
                'id'       => $userId,
                'name'     => '',
                'role'     => 4,
                'messages' => $messages['messages'], 
                'date'     => time()
            ];

            Redis::command('set', [
                    $userId . '_messages', json_encode($decodeMessages)
                ]
            );
        } else {
            $arrMessage[] = [
                'id'       => $userId,
                'name'     => '',
                'role'     => 4,
                'messages' => $messages['messages'], 
                'date'     => time()
            ];
            Redis::command('set', [
                    $userId . '_messages', json_encode($arrMessage)
                ]
            );
        }
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
    }

    private static function _getIpUser() {
        $userIp = request()->server('REMOTE_ADDR');
        return $userIp;
    }

    public static function getDomain()
    {
        $domain = env('APP_DOMAIN', 'birdchat.dev');
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
    }
}
