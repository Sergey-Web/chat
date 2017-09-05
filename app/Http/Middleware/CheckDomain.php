<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Company;
use App\AuthUserRedis;

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
        $domain = $this->getDomain();
        $subdomain = $this->getSubdomain($request, $domain);
        $userIp = $this->getIpUser();

        if($domain != $subdomain) {
            $company = Company::where('domain', $subdomain)->first();
            if(!$company) {
                return abort(404);
            }

            AuthUserRedis::login($subdomain, $userIp);
        }

        return $next($request);
    }

    private function getDomain()
    {
        $domain = str_replace('\\', '', preg_quote(env('APP_DOMAIN', 'chat.dev')));
        return $domain;
    }

    private function getSubdomain($request, $domain)
    {
        $subdomain = preg_replace('/\.' . $domain . '$/s', '', $request->getHttpHost());
        return $subdomain;
    }

    private function getIpUser()
    {
        $userIp = request()->server('REMOTE_ADDR');
        return $userIp;
    }

}
