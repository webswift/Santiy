<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseType extends Model {

	public $timestamps = false;
	protected $table = 'licensetypes';
	protected $fillable = ['id', 'licenseClass', 'name', 'expireIn', 'description', 'free_users', 'max_users', 'priceUSD', 'priceGBP', 'priceEuro', 'added'];

	public function scopeType($query, $type=null) {
		if($type == null) {
			return $query;
		}

		return $query->where('type', $type);
	}

	public function currencyWisePricing() {
		return [
			'USD' => ['Monthly' => $this->priceUSD, 'Annually' => $this->priceUSD_year],
			'GBP' => ['Monthly' => $this->priceGBP, 'Annually' => $this->priceGBP_year],
			'EUR' => ['Monthly' => $this->priceEuro, 'Annually' => $this->priceEuro_year]
		];
	}

	//safety checks after deletion of columns
	public function getVolumeAttribute() 
	{
		throw new \Exception('Unsupported');
	}

	public function setVolumeAttribute($value) 
	{
		throw new \Exception('Unsupported');
	}
}
