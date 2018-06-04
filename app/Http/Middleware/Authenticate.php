<?php

namespace App\Http\Middleware;

use App\Http\Requests\Request;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Redirect;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        //$this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::user()->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('user/login');
            }
        }

        $user = Auth::user()->get();
        $timezone = $user->timeZoneName;
		if(@date_default_timezone_set($timezone)) {
			Config::set("app.timezone", $timezone);
			\DB::statement("SET time_zone = ?", [$timezone]);
			//\Log::error($timezone);
			//\Log::error(date('c'));
		}

        $path = \Request::path();
        $allowed = [
            "user/makeaddmemberpayment",
            "user/makerenewlicensepayment",
            "user/logout",
            "user/changepassword",
            "user/profile",
            "user/editprofile",
            "user/enableTimer",
            "user/profile/addbcc",
            "user/dialerSetting"
        ];
        if ($user->accountStatus == "LicenseExpired" && !in_array($path, $allowed)) {
            return Redirect::route("user.profile");
        }

        return $next($request);
    }
}
