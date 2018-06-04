<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallHistory extends Model
{
	public $timestamps = false;
	protected $table = 'call_history';
	protected $fillable = ['id'];
}
