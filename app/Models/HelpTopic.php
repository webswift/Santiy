<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpTopic extends Model {

    public $timestamps = false;
    protected $table = 'helptopics';
    protected $fillable = ['id', 'topic', 'timeCreated'];
}