<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model {

    public $timestamps = false;
    protected $table = 'smtpsettings';
    protected $fillable = ['id', 'userID', 'host', 'port', 'userName', 'password', 'security'];
}