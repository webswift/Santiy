<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpArticle extends Model {

    public $timestamps = false;
    protected $table = 'helparticles';
    protected $fillable = ['id', 'topicID', 'articleName', 'text', 'keywords', 'timeCreated', 'timeEdited'];
}