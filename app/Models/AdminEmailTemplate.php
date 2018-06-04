<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminEmailTemplate extends Model {

    public $timestamps = false;
    protected $table = 'adminemailtemplates';
    protected $fillable = ['id', 'name', 'description', 'from', 'replyTo', 'subject', 'content', 'variables'];
}