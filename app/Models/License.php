<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model {

	protected $table = 'licenses';

	protected $primaryKey = 'owner';

	public $timestamps = false;

	protected $fillable = ['owner', 'purchaseTime', 'expireDate', 'licenseType', 'licenseClass', 'licenseVolume'];

	public function currencyWisePricing() 
	{
		return [
			'USD' => ['Monthly' => $this->priceUSD, 'Annually' => $this->priceUSD_year],
			'GBP' => ['Monthly' => $this->priceGBP, 'Annually' => $this->priceGBP_year],
			'EUR' => ['Monthly' => $this->priceEuro, 'Annually' => $this->priceEuro_year]
		];
	}
	
	public function getPaidLicenseType() 
	{
		return LicenseType::where('licenseClass', $this->licenseClass)
			->where('type', 'Paid')
			->first();
	}

	//safety checks after deletion of columns
	public function getIdAttribute() 
	{
		throw new \Exception('Unsupported');
	}

	public function setIdAttribute($value) 
	{
		throw new \Exception('Unsupported');
	}

	public function getVolumeAttribute() 
	{
		throw new \Exception('Unsupported');
	}

	public function setVolumeAttribute($value) 
	{
		throw new \Exception('Unsupported');
	}
}
