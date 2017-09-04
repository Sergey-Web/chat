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
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin()
    {
        AuthUserRedis::check();
        dump(Redis::command('smembers', [1]));
        dump(Redis::command('smembers', [0]));
        return view('home');
    }

    public function index()
    {
        AuthUserRedis::check();
        return view('welcome');
    }
}
