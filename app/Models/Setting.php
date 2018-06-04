<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model {

    protected $table = 'settings';

    public $timestamps = false;

    protected $fillable = ['name', 'value'];

    public static function get($key = null) {
        if($key != null && $key != '') {
            $data =  \DB::table('settings')->where('name', $key)->first();

	        if($key == 'names' || $key == 'outboundMail' || $key == 'inboundMail' || $key == 'trialUserLimit'
                || $key == 'singleUserLimit' || $key == 'multiUserLimit' || $key == 'massMailServer') {
		        return json_decode($data->value, true);
	        }
	        else {
		        return $data->value;
	        }
        }
        else {
            return null;
        }
    }

    public static function set($key, $value) {
        \DB::table('settings')->where('name', $key)->update(['value' => $value]);
    }
}