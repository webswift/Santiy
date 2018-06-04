<?php

namespace App\Http\Controllers;

use App\Models\CallBack;
use Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use View;
use App\Models\Setting;
use DateTime;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $user;
    private $admin;

    function __construct() {
        if (Auth::user()->check()) {
            $this->user = Auth::user()->get();
			
			$date = new DateTime;
			$today = $date->format('Y-m-d');
			$date->modify("+7 days");
			$exactPendingDays = $date->format('Y-m-d');

            $totalPendingCallBacks = CallBack::where('actioner', $this->user->id)
										->where('status', 'Pending')
										->where('callbacks.time', '<=', $exactPendingDays)
										->where('callbacks.time', '>', $today)
										->count();

            View::share("user", $this->user);
            View::composer("layouts.dashboard", function($view) use($totalPendingCallBacks) {
                $view->with("totalPendingCallBacks", $totalPendingCallBacks);
                $view->with("userHelpUrl", Setting::get('userHelpUrl'));
            });

        }

        if (Auth::admin()->check()) {
            $this->user = Auth::admin()->get();
            View::share("admin", $this->admin);
        }
		$this->initPHPSession();
    }

	private function initPHPSession() {
		//use normal php session to pass data to filemanager/uploader
		include public_path('assets/fileman/memcached.inc.php');

		if(!is_null($this->user)) {

			if(!isset($_SESSION["userID"]) || $_SESSION["userID"] != $this->user->id) {
				$_SESSION["userID"] = $this->user->id;
			}
		} else {
			unset($_SESSION["userID"]);
		}
		session_write_close();
	}
}
