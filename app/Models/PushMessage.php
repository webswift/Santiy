<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushMessage extends Model {

    public $timestamps = false;
    protected $table = 'pushmessages';
    protected $fillable = ['id', 'message', 'sender', 'receiver', 'time', 'status'];


}