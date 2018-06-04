<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model {

	protected $table = 'appointments';

	public $timestamps = false;

	protected $fillable = ['id', 'leadID', 'salesMember', 'time', 'creator'];

	function salesMember(){
		return $this->belongsTo('App\Models\SalesMember', 'salesMember', 'id');
	}
}