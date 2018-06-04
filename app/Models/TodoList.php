<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TodoList extends Model {

    public $timestamps = false;
    protected $table = 'todolists';
    protected $fillable = ['id', 'todoText', 'userID', 'time', 'status'];
}