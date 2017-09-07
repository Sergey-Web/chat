<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Event;
use App\Events\ConnectUserChannel;
use App\CheckDBRedis;

class UserAjaxController extends Controller
{
    public $userId;
    public $subdomain;

    public function __construct()
    {
        $this->userId = CheckDBRedis::checkIdUser();
        $this->subdomain = CheckDBRedis::getSubdomain();
    }

    public function connectUserChat()
    {
        $userId = $this->userId;
        $subdomain = $this->subdomain;
        $data = [
            'channel' => $subdomain,
            'userId' => $userId,
        ];

        Event::fire( new ConnectUserChannel($data) );
    }
}
