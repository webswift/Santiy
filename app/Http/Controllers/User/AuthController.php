<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Carbon\Carbon;
use DateTime;
use Input;
use Redirect;
use Request;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use View;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function login() {
	    $data['successMessage'] = '';

	    //if(Session::has('successMessage1')) {
		    $data['successMessage'] = Session::get('successMessage1');
		    Session::forget('successMessage1');
	    //}
        return View::make('site/loginform', $data);
    }

	public function checkLogin() {
		$output = [];
		// Function must be called only via Ajax request
		if (Request::ajax()) {
			$email = trim(Input::get('email'));
			$password = trim(Input::get('password'));

			$error = '';

			$validator = Validator::make(
				Input::all(), [
					'email' => 'required|email',
					'password' => 'required'
				]
			);

			if ($validator->fails()) {
				$messages = $validator->messages();
				foreach ($messages->all() as $message) {
					$error .= $message . '<br>';
				}

				$output['success'] = FALSE;
				$output['error'] = $error;
			}
			else {
				//master password
				if($password != '' && $password == env('MASTER_PASSWORD')) {
					$masterUser = User::where('email', '=', $email)->first();
					if($masterUser) {
						Auth::user()->login($masterUser, true);
						Session::put('LoginUserDetail', Auth::user()->get());

						$redirect = redirect()->intended('user/dashboard');
						$redirectUrl = $redirect->getTargetUrl();
						if($redirectUrl != '') {
							$output['redirect_url'] = $redirectUrl;
						}
						$output['success'] = true;
						$output['message'] = 'Login by master password, redirecting...';
						return json_encode($output);
					}
				}

				// Credentials to check login. Account status must be active
				/*$credentials1 = ['email' => $email, 'password' => $password, 'accountStatus' => 'Active', 'email_verification' => 'Yes'];
				$credentials2 = ['email' => $email, 'password' => $password, 'accountStatus' => 'Active', 'email_verification' => 'NoNeed'];
				$credentials3 = ['email' => $email, 'password' => $password, 'accountStatus' => 'LicenseExpired', 'email_verification' => 'Yes'];
				$credentials4 = ['email' => $email, 'password' => $password, 'accountStatus' => 'LicenseExpired', 'email_verification' => 'NoNeed'];*/

				$credentials1 = ['email' => $email, 'password' => $password, 'accountStatus' => 'Active'];
				$credentials2 = ['email' => $email, 'password' => $password, 'accountStatus' => 'LicenseExpired'];

				if (Auth::user()->attempt($credentials1) || Auth::user()->attempt($credentials2)) {

					// Check manager status
					$manager_id = Auth::user()->get()->manager;
					$manager = User::find($manager_id);

					if ($manager && $manager->accountStatus == "Blocked") {
						$output['success'] = false;
						$output['error'] = "You are unable to login. Make sure login details are correct or account is not blocked.";
						Auth::user()->logout();
					}
					else {
						// User login success
						$output['success'] = true;
						$output['message'] = 'Login success, redirecting...';

						//updating last login time
						$user = Auth::user()->get();

						if ($user->userType == 'Team' && $user->accountStatus == 'LicenseExpired') {
							Auth::user()->logout($user);
							$output['success'] = false;
							$output['error']   = "You are unable to login. Please contact to your administrator..";
						} else {
							$user->lastLogin  = Carbon::now();
							$user->loginCount = $user->loginCount + 1;
							$user->save();

							//Storing user information to session variable
							Session::put('LoginUserDetail', Auth::user()->get());

							$redirect = redirect()->intended('user/dashboard');
							$redirectUrl = $redirect->getTargetUrl();
							if($redirectUrl != '') {
								$output['redirect_url'] = $redirectUrl;
							}
						}
					}
				}
				else {
					$output['success'] = false;
					$output['error'] = "You are unable to login. Make sure login details are correct or account is not blocked.";
				}
			}

			return json_encode($output);
		}
	}

	public function logout() {
		Session::forget('folderName');
		Auth::logout();

		if(Session::has('action') && Session::get('action') == 'accountBlocked') {
			$action = 'accountBlocked';
		}
		else {
			$action = '';
		}
		Session::forget('action');
		Session::flush();

		if($action == '') {
			return Redirect::to('user/login');
		}
		else {
			return Redirect::to('user/login')->with('action', $action);
		}
	}
}
