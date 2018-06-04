<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model {

	protected $table = 'forms';

	public $timestamps = false;

	protected $fillable = ['id', 'formName', 'creator', 'time'];

	function getFormFields(){
		return FormFields::where('formID', $this->id)->orderBy('order')->get();
	}

	function deleteCustomFormFields($fieldNames){
		FormFields::where('formID', $this->id)->whereIn('fieldName', $fieldNames)->delete();
	}
}