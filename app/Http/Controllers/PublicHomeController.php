<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DateTime;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Input;
use Log;
use Mail;
use Redirect;
use Session;
use URL;
use Validator;
use View;
use File;

class PublicHomeController extends Controller
{
	public function page(Request $request) {
		$path = $request->path();
		if($path == 'home_new') {
			$path = 'index';
		}
		//Log::error($path);
		if(strlen($path) > 0 && substr($path, -1) != '/') {
			$page = basename($path);
		} else {
			$page = 'index';
		}

		if(strlen($page) < 5 || substr($page, -5) != '.html') {
			$page .= '.html';
		}

		$path_to_page = public_path() . '/public_home/' . $page;
		//Log::error($path_to_page);

		if(file_exists($path_to_page)) {
			return File::get($path_to_page);
		} else {
			return App::abort(404, 'Requested page not found');
		}



	}

}

