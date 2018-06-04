<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostalCode extends Model {

    public $timestamps = false;
    protected $table = 'postalcodes';
    protected $fillable = ['postalCode', 'countryCode', 'latitude', 'longitude', 'accuracy'];


}