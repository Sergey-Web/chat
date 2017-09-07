<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AuthUserRedis;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        AuthUserRedis::login();
        AuthUserRedis::status();
        $data = AuthUserRedis::$data;
        return view('home', ['data'=>$data]);
    }

    public function startPage()
    {
        Redis::flushall();
        return view('welcome');
    }
}
