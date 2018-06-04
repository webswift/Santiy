<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingTransaction extends Model
{
	protected $table = 'transactions_pending';

	public $timestamps = true;
}
