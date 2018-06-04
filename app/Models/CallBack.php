<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallBack extends Model {

	public $timestamps = false;
	protected $table = 'callbacks';
	protected $fillable = ['id', 'leadID', 'time', 'actioner', 'timeCreated', 'status'];

	function callBackUser(){
		return $this->belongsTo('App\Models\User', 'actioner', 'id');
	}
}