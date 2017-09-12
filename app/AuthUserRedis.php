<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Event;
use App\Events\ConnectUserChannel;

class AuthUserRedis
{
    private static $_data = NULL;
    private static $_channel = NULL;
    private static $_userId = NULL;
    private static $_role = NULL;
    private static $_name = NULL;
    private static $_status = NULL;

    public static function login()
    {
        if(self::$_data == NULL) {
            if(Auth::check()) {
                $userObj = User::find(Auth::id());

                self::$_channel = $userObj->company->first()->domain;
                self::$_userId = Auth::id();
                self::$_role = $userObj->role->first()->id;
                self::$_name = $userObj->name;
                self::$_status = 'on';
            } else {
                return false;
            }

            self::$_data = [
                'channel' => self::$_channel,
                'userId'  => self::$_userId,
                'role'    => self::$_role,
                'name'    => self::$_name,
                'status'  => self::$_status
            ];

            $saveDBRedis = self::_addUserCompanyRedis();
        }

        if(!$saveDBRedis) {
            return false; 
        }

        return self::$_data;
    }

    public static function logout()
    {
        if(self::$_data == NULL) {
           AuthUserRedis::login();
        }
        
        Redis::command('srem', [self::$_channel, self::$_userId]);
        Redis::command('srem', [self::$_role, self::$_userId]);
        Redis::command('del', [self::$_userId]);
    }

    public static function status()
    {
       $data = [
            'company_'.self::$_channel => Redis::command('smembers', [self::$_channel]),
            'role_'.self::$_role => Redis::command('smembers', [self::$_role]),
            'agent_'.self::$_userId => Redis::command('get', [self::$_userId])
        ];

        return $data;
    }

    private static function _addUserCompanyRedis()
    {
        try {
            Redis::command('sadd', [self::$_channel, self::$_userId]);
            Redis::command('sadd', [self::$_role, self::$_userId]);
            Redis::command('set', [self::$_userId, json_encode(self::$_data)]);
        } catch (Throwable $t) {
            return false;
        }

        return true;
    }
}
