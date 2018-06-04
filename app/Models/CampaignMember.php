<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignMember extends Model {

    public $incrementing = false;
	public $timestamps = false;
	protected $table = 'campaignmembers';
    protected $primaryKey = 'id';
	protected $fillable = ['campaignID', 'userID'];

	
}
