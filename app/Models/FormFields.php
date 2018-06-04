<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class FormFields extends Model {

	public $timestamps = false;
	protected $table = 'formfields';
	protected $fillable = ['id', 'fieldName'];

	//use simple strtotime for now
	//returns Y-m-d format if parseable or '' on error
	public static function validateDateFieldValue($value)
	{
		//\Log::error($value);
		$value = trim($value);
		if($value == '') {
			return $value;
		}
		$res = strtotime($value);
		if($res !== false) {
			$res = date('Y-m-d', $res); 
		} else {
			$res = '';
		}
		return $res;
	}
	
	//format to dd-mm-yy (from Y-m-d in db)
	public static function formatDateFieldValue($value)
	{
		$value = trim($value);
		if($value == '') {
			return $value;
		}
		//\Log::error($value);
		$res = strtotime($value);
		if($res !== false) {
			$res = date('d-m-Y', $res); 
		} else {
			$res = '';
		}
		return $res;
	}

	/* returns array with names of fields for all campaigns, ordered by min order in forms
	 */
	public static function getFieldNamesForCampaigns($campaigns) 
	{
		$fieldNames = Campaign::whereIn('campaigns.id', $campaigns)
					->select(['formfields.fieldName'
						, DB::raw('MIN(formfields.order) min_order')
					])
					->join('formfields', 'formfields.formID', '=', 'campaigns.formID')
					->orderBy('min_order', 'asc')
					->orderBy('formfields.fieldName', 'asc')
					->groupBy('formfields.fieldName')
					->lists('formfields.fieldName')
					->all();

		return $fieldNames;
	}
	
	/* returns array with names of drop-down fields for campaign, ordered by name
	 */
	public static function getDropDownNamesForCampaign($campaignID) {
		$fieldNames = Campaign::join('forms', 'forms.id', '=', 'campaigns.formID')
					->join('formfields', 'formfields.formID', '=', 'forms.id')
					->where('campaigns.id', $campaignID)
					->where('formfields.type', 'dropdown')
					->select('formfields.fieldName')
					->orderBy('formfields.fieldName', 'asc')
					->lists('fieldName')
					->all();

		return $fieldNames;
	}

	/* returns collection of drop-down fields for campaign, ordered by name
	 */
	public static function getDropDownsForCampaign($campaignID) {
		$formFields = Campaign::join('forms', 'forms.id', '=', 'campaigns.formID')
				->join('formfields', 'formfields.formID', '=', 'forms.id')
				->where('campaigns.id', $campaignID)
				->where('formfields.type', 'dropdown')
				->select('formfields.*')
				->orderBy('formfields.fieldName', 'asc')
				->get();
		return $formFields;
	}

	public static function getFieldsForCampaign($campaignID) {
		$formFields = Campaign::join('forms', 'forms.id', '=', 'campaigns.formID')
				->join('formfields', 'formfields.formID', '=', 'forms.id')
				->where('campaigns.id', $campaignID)
				->select('formfields.*')
				->orderBy('formfields.fieldName', 'asc')
				->get();
		return $formFields;
	}

}
