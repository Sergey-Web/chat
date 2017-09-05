<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AuthUserRedis;
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
        return view('home');
    }

    public function startPage()
    {
        if(Auth::check()){
            AuthUserRedis::status();
        } else {
            $userIp = request()->server('REMOTE_ADDR');
            AuthUserRedis::status();
            return view('welcome', compact($userIp));
        }
        return view('welcome');
    }

}
