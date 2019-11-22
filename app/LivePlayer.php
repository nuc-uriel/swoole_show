<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LivePlayer extends Model
{
    protected $table = "live_players";
    protected $fillable = ['id', 'name', 'image', 'age', 'position', 'status', 'created_at', 'updated_at'];
}
