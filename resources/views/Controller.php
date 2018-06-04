<?php

namespace App\Http\Controllers;

use App\Models\CallBack;
use Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use View;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $user;
    private $admin;

    function __construct() {
        if (Auth::user()->check()) {
            $this->user = Auth::user()->get();
            $totalPendingCallBacks = CallBack::where('actioner', $this->user->id)
                                             ->where('status', 'Pending')
                                             ->count();

            View::share("user", $this->user);
            View::composer("layouts.dashboard", function($view) use($totalPendingCallBacks) {
                $view->with("totalPendingCallBacks", $totalPendingCallBacks);
            });
        }

        if (Auth::admin()->check()) {
            $this->user = Auth::admin()->get();
            View::share("admin", $this->admin);
        }
    }
}
