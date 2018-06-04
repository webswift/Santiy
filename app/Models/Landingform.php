<?php
/**
 * Created by PhpStorm.
 * User: Shalu
 * Date: 21/08/2015
 * Time: 12:46 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableServiceProvider;
use Cviebrock\EloquentSluggable\SluggableTrait;

class Landingform extends Model implements SluggableInterface {

	use SluggableTrait;

	protected $table = 'landingform';

	public $timestamps = false;

	protected $fillable = ['id', 'formName', 'creator', 'time'];

	protected $sluggable  = [
		'build_from' => 'name',
		'save_to'   => 'slug'
	];

	function getFormFields(){
		return Landingformfields::where('formID', $this->id)->orderBy('order', 'ASC')->get();
	}

	function deleteCustomFormFields($fieldNames){
		Landingformfields::where('formID', $this->id)->whereIn('fieldName', $fieldNames)->delete();
	}
}