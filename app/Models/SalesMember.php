<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class SalesMember extends Model {

	public $timestamps = false;
	protected $table = 'salesmembers';
	protected $fillable = ['id', 'firstName', 'lastName', 'email', 'contactNumber', 'skypeID', 'gender', 'manager', 'creationDate'];
	
	public function getCampaigns() 
	{
		return DB::table('salesmembers_campaigns')
			->select('campaign_id')
			->where('salesmember_id', $this->id)
			->lists("campaign_id")
			;
	}
	
	public function setCampaigns($arrayOfCampaignIds) 
	{
		DB::table('salesmembers_campaigns')
			->where('salesmember_id', $this->id)
			->delete();

		$rows = [];

		if(is_array($arrayOfCampaignIds)) {
			foreach($arrayOfCampaignIds as $campaignId) {
				if(is_numeric($campaignId)) {
					$rows[] = [
						'campaign_id' => $campaignId,	
						'salesmember_id' => $this->id,	
					];
				}
			}

			DB::table('salesmembers_campaigns')
				->insert($rows);
		}
	}
}
