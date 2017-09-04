<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Auth;

class AuthUserRedis
{
    private static $data = NULL;

    public static function check()
    {
        if(self::$data != NULL) {
            return self::$data;
        } else {
            $userObj = User::find(Auth::id());
            $companyId = $userObj->company->first()->id;
            $userId = Auth::id();
            $role = $userObj->role->first()->role;
            $room = FALSE;
            $data = ['companyId' => $companyId, 'userId' => $userId, 'role' => $role, 'room' => $room];
            self::$data = $data;
            return self::$data;
        }

        return $data;
    }

    private static function addUserCompany()
    {

    }

    private static function connectUser()
    {

    }

    private function __constract(){}
    private function __clone(){}
}
