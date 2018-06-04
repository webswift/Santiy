<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignPrevNextFilter extends Model
{
	public $timestamps = false;
	protected $table = 'campaign_prevnext_filters';
    protected $primaryKey = 'id';
	protected $fillable = ['campaign_id', 'user_id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'prevNextFilter' => 'array',
    ];
}
