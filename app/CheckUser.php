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
            setcookie('userId', $_COOKIE['userId'], time()+600);
        } else {
            self::_assingIdUser();
            setcookie('userId', self::$userId, time()+600);
        }

        return self::$userId;
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
}
