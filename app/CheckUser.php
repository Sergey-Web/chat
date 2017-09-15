<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class CheckUser extends Model
{
    public static $userId;

    public function __construct()
    {
        $userId = self::_getIpUser();
        self::$userId = Redis::command('get', [$userId]);
    }

    public static function checkIdUser()
    {
        $userId = self::$userId;
        if(empty($userId)) {
            $userId = self::_getIpUser();
        }

        return $userId;
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
}
