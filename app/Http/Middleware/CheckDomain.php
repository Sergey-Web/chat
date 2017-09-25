<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\CheckUser;
use App\Company;
use App\AuthAgentRedis;

class CheckDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $domain = CheckUser::getDomain();
        $subdomain = CheckUser::getSubdomain();
        $userIp = CheckUser::checkIdUser();

        if($domain != $subdomain) {
            $company = Company::where('domain', $subdomain)->first();
            if(!$company) {
                return abort(404);
            }

            AuthAgentRedis::login($subdomain, $userIp);
        }

        return $next($request);
    }
}
