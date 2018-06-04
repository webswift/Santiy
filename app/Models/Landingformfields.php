<?php
/**
 * Created by PhpStorm.
 * User: Shalu
 * Date: 31/08/2015
 * Time: 06:22 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Landingformfields extends Model{
	protected $table = 'landingformfields';

	function getMappingWithLeadForm(){
		return Formmapping::where('landingFieldID', $this->id)->get();
	}
}