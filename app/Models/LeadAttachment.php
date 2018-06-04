<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadAttachment extends Model
{
	public $timestamps = false;
	protected $table = 'lead_attachment';
	protected $fillable = ['id'];
}

