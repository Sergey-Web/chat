<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Event;
use App\Events\ConnectUserChannel;

class AuthUserRedis
{
    public static $data = NULL;
    private static $channel = NULL;
    private static $userId = NULL;
    private static $role = NULL;
    private static $name = NULL;
    private static $room = NULL;

    public static function login()
    {
        if(self::$data == NULL) {
            if(Auth::check()) {
                $userObj = User::find(Auth::id());
                self::$channel = $userObj->company->first()->domain;
                self::$userId = Auth::id();
                self::$role = $userObj->role->first()->id;
                self::$name = $userObj->name;
            }

            self::$data = [
                'channel' => self::$channel,
                'userId'  => self::$userId,
                'role'    => self::$role,
                'name'    => self::$name
            ];

            self::addUserCompanyRedis();
        }

        return self::$data;
    }

    public static function logout()
    {
        if(self::$data == NULL) {
           AuthUserRedis::login();
        }
        
        Redis::command('srem', [self::$channel, self::$userId]);
        Redis::command('srem', [self::$role, self::$userId]);
    }

    public static function status()
    {
        dump(['company_'.self::$channel => Redis::command('smembers', [self::$channel])]);
        dump(['role_'.self::$role => Redis::command('smembers', [self::$role])]);
    }

    private static function addUserCompanyRedis()
    {
        Redis::command('sadd', [self::$channel, self::$userId]);
        Redis::command('sadd', [self::$role, self::$userId]);

        Event::fire( new ConnectUserChannel(self::$data) );
    }

    private function __constract(){}
    private function __clone(){}
}
