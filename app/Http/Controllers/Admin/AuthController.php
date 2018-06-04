<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

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
        $this->middleware('admin.auth', ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    /*protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }*/

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    /*protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }*/

    public function login() {
        return view('admin.adminloginform');
    }

    public function checkLogin(Request $request) {
        // Function must be called only via Ajax request
        if ($request->ajax()) {
            $output = [];

            $email = $request->input('email');
            $password = $request->input('password');

            $error = '';

            $validator = Validator::make(
                $request->all(), [
                    'email' => 'required|email',
                    'password' => 'required|min:6'
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
                // Credentials to check login. Account status must be active
                $credentials = ['email' => $email, 'password' => $password];

                if (\Auth::admin()->attempt($credentials)) {
                    $output['success'] = TRUE;
                    $output['message'] = 'Login success, redirecting...';
                }
                else {
                    $output['success'] = false;
                    $output['error'] = "Login details incorrect or account blocked.";
                }
            }
            return $output;
        }
    }

    public function logout() {
        \Auth::logout();
        return \Redirect::route('admin.login');
    }
}
