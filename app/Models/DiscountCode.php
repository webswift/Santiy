<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model {

	public $timestamps = false;
	protected $table = 'discountcodes';
	protected $fillable = ['id', 'licenseType', 'code', 'discountPercent'];
}