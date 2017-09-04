<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Event;
use App\Events\ConnectUserChannel;

class AuthUserRedis
{
    private static $data = NULL;
    private static $channel = NULL;
    private static $userId = NULL;
    private static $role = NULL;
    private static $room = NULL;

    public static function check()
    {
        if(Auth::check()) {
            self::login();
        } else {
            self::logout();
        }
    }

    private static function login()
    {
        if(self::$data == NULL) {
            $userObj = User::find(Auth::id());
            self::$channel = $userObj->company->first()->name;
            self::$userId = Auth::id();
            self::$role = $userObj->role->first()->role;
            self::$room = FALSE;
            $data = [
                'userId' => self::$userId,
                'role'   => self::$role
            ];
            self::$data = $data;
        }

        self::addUserCompanyRedis();

        return self::$data;
    }

    private static function logout()
    {
        Redis::command('srem', [self::$channel, self::$userId]);
        Redis::command('srem', [self::$role, self::$userId]);
        //self::dropUserCompanyRedis();
    }

    private static function addUserCompanyRedis()
    {
        Redis::command('sadd', [self::$channel, self::$userId]);
        Redis::command('sadd', [self::$role, self::$userId]);
        self::connectUserChannel(self::$channel, self::$data);
    }

    private static function dropUserCompanyRedis(){

        self::disconnectUserChannel(self::$channel, self::$data);
    }

    private static function connectUserChannel($channel, $data)
    {
        Event::fire( new ConnectUserChannel($channel, $data) );
    }

    private static function disconnectUserChannel($channel, $data)
    {

    }


    private function __constract(){}
    private function __clone(){}
}
