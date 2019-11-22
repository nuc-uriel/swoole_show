<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LiveOut extends Model
{
    protected $table = "live_outs";
    protected $fillable = ['id', 'game_id', 'team_id', 'content', 'image', 'type', 'status', 'created_at', 'updated_at'];
}
