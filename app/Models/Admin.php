<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Kbwebs\MultiAuth\PasswordResets\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Kbwebs\MultiAuth\PasswordResets\Contracts\CanResetPassword as CanResetPasswordContract;

class Admin extends Model implements AuthenticatableContract,
		AuthorizableContract,
		CanResetPasswordContract {

	use Authenticatable, Authorizable, CanResetPassword;

	protected $table = 'admins';

	public $timestamps = false;

	// Don't forget to fill this array
	protected $fillable = ['firstName', 'lastName', 'email', 'password', 'lastLogin'];

}