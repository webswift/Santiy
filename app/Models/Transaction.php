<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {

	protected $table = 'transactions';

	public $timestamps = false;

	protected $fillable = ['id', 'type', 'time', 'purchaser', 'amount', 'discount', 'licenseType'];
}