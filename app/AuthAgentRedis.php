<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Event;
use App\Events\ConnectUserChannel;

class AuthAgentRedis
{
    private static $_data = NULL;
    private static $_channel = NULL;
    public static $_agentId = NULL;
    private static $_role = NULL;
    private static $_name = NULL;
    private static $_userId = NULL;

    public static function login()
    {
        if(self::$_data == NULL) {
            if(Auth::check()) {
                $userObj = User::find(Auth::id());

                self::$_channel = $userObj->company->first()->domain;
                self::$_agentId = Auth::id();
                self::$_role = $userObj->role->first()->id;
                self::$_name = $userObj->name;
                self::$_userId = '';
            } else {
                return false;
            }

            self::$_data = [
                'channel' => self::$_channel,
                'agentId'  => self::$_agentId,
                'role'    => self::$_role,
                'name'    => self::$_name,
                'userId'  => self::$_userId
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
           self::login();
        }
        
        Redis::command('srem', [self::$_channel, self::$_agentId]);
        Redis::command('srem', [self::$_role, self::$_agentId]);
        Redis::command('del', [self::$_agentId]);
    }

    public static function status()
    {
       $data = [
            'company_'.self::$_channel => Redis::command('smembers', [self::$_channel]),
            'role_'.self::$_role => Redis::command('smembers', [self::$_role]),
            'agent_'.self::$_agentId => Redis::command('get', [self::$_agentId])
        ];

        return $data;
    }

    private static function _addUserCompanyRedis()
    {
        try {
            Redis::command('sadd', [self::$_channel, self::$_agentId]);
            Redis::command('sadd', [self::$_role, self::$_agentId]);
            Redis::command('set', [self::$_agentId, json_encode(self::$_data)]);
        } catch (Throwable $t) {
            return false;
        }

        return true;
    }
}
