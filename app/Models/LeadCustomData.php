<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class LeadCustomData extends Model {

	public $timestamps = false;
	protected $table = 'leadcustomdata';
	protected $fillable = ['id', 'leadID', 'fieldID', 'fieldName', 'value'];

	/* returns array like ['fieldName' => fieldValue, ...]
	 */
	public static function getLeadInformation($leadID, $columns){
		$output = [];

		if(sizeof($columns) > 0){
			$values = LeadCustomData::where('leadID', $leadID)
					->select([DB::raw('LOWER(fieldName) as fieldName'), 'value'])
					->whereIn('fieldName', $columns)
					->get()
					->pluck('value', 'fieldName');


			foreach($columns as $column){
				$columnLowerCase = strtolower($column);
				$output[$column] = isset($values[$columnLowerCase]) ? $values[$columnLowerCase] : null;
			}
		}

		return $output;
	}
	
	/* returns array of arrays like [leadId => ['fieldName' => fieldValue, ...], ...]
	 */
	public static function getValues($leadIDs, $columns){
		$output = [];

		if(sizeof($columns) > 0){
			$valuesPerLead = LeadCustomData::whereIn('leadID', $leadIDs)
					->select(['leadID', DB::raw('LOWER(fieldName) as fieldName'), 'value'])
					->whereIn('fieldName', $columns)
					->get()
					->groupBy('leadID');

			foreach($valuesPerLead as $leadId => $valuePerLead) {
				$values = $valuePerLead->pluck('value', 'fieldName');
				//\Log::error(print_r($values, true));
				foreach($columns as $column){
					$columnLowerCase = strtolower($column);
					$output[$leadId][$column] = isset($values[$columnLowerCase]) ? $values[$columnLowerCase] : null;
				}
			}
		}

		//\Log::error(print_r($output, true));

		return $output;
	}
}
